<?php

namespace App\Jobs;

use App\Mail\ScheduledAssessmentMail;
use App\Models\AssessmentAnswerToken;
use App\Models\Member;
use App\Models\ScheduledEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendScheduledAssessmentEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


        //The ID of the scheduled email record.
        //@var int

    public $emailId;


        //Create a new job instance.
        //@param  int  $emailId
        //@return void

    public function __construct(int $emailId)
    {
        $this->emailId = $emailId;
    }


        //Execute the job.
        //@return void

    public function handle(): void
    {
        try {
            $email = $this->findAndValidateScheduledEmail();
            if (!$email) {
                return;
            }

            $member = $this->findRecipientMember($email->recipient_email);
            if (!$member) {
                return;
            }

            $groupId = $this->resolveGroupId($email, $member);
            if (!$groupId) {
                return;
            }

            $token = $this->createAssessmentToken($email->assessment_id, $member->id, $groupId);
            $assessmentUrl = $this->generateAssessmentUrl($token, $member->id, $groupId);

            $this->sendEmailAndUpdateStatus($email, $member, $assessmentUrl);

        } catch (\Exception $e) {
            Log::error('An unexpected error occurred in SendScheduledAssessmentEmail job.', [
                'emailId' => $this->emailId,
                'exception' => $e->getMessage(),
            ]);
        }
    }


        //Find and validate the scheduled email record.
        //@return ScheduledEmail|null

    private function findAndValidateScheduledEmail(): ?ScheduledEmail
    {
        $email = ScheduledEmail::find($this->emailId);

        if (!$email) {
            Log::warning('SendScheduledAssessmentEmail: ScheduledEmail not found.', [
                'emailId' => $this->emailId,
            ]);
            return null;
        }

        if ($email->sent) {
            Log::info('SendScheduledAssessmentEmail: Email already marked as sent.', [
                'emailId' => $this->emailId,
            ]);
            return null;
        }

        return $email;
    }


        //Find the recipient member by email.
        //@param  string  $recipientEmail
        //@return Member|null

    private function findRecipientMember(string $recipientEmail): ?Member
    {
        $normalizedEmail = trim(strtolower($recipientEmail));
        $member = Member::whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])->first();

        if (!$member) {
            Log::error('SendScheduledAssessmentEmail: Member not found for email.', [
                'email' => $recipientEmail,
                'emailId' => $this->emailId,
            ]);
        }

        return $member;
    }


        //Resolve the group ID for the assessment token.
        //@param  ScheduledEmail  $email
        //@param  Member  $member
        //@return int|null

    private function resolveGroupId(ScheduledEmail $email, Member $member): ?int
    {
        if ($email->group_id) {
            return $email->group_id;
        }

        $firstGroup = $member->groups()->first();
        if ($firstGroup) {
            return $firstGroup->id;
        }

        Log::error('SendScheduledAssessmentEmail: Could not resolve a group_id for the member.', [
            'member_id' => $member->id,
            'emailId' => $this->emailId,
        ]);

        return null;
    }


        //Create a new assessment answer token.
        //@param  int  $assessmentId
        //@param  int  $memberId
        //@param  int  $groupId
        //@return string

    private function createAssessmentToken(int $assessmentId, int $memberId, int $groupId): string
    {
        $token = Str::random(40);
        
        AssessmentAnswerToken::create([
            'assessment_id' => $assessmentId,
            'member_id' => $memberId,
            'group_id' => $groupId,
            'token' => $token,
            'expires_at' => Carbon::now()->addDays(7),
            'used' => false,
        ]);

        Log::info('AssessmentAnswerToken created.', [
            'assessment_id' => $assessmentId,
            'member_id' => $memberId,
            'group_id' => $groupId,
        ]);
        
        return $token;
    }


        //Generate the frontend URL for the assessment.
        //@param  string  $token
        //@param  int  $memberId
        //@param  int  $groupId
        //@return string

    private function generateAssessmentUrl(string $token, int $memberId, int $groupId): string
    {
        $frontendBase = rtrim(env('FRONTEND_URL', 'http:        //localhost:8080'), '/');
        $queryParams = http_build_query(['group_id' => $groupId, 'member_id' => $memberId]);

        return "{$frontendBase}/assessment/answer/{$token}?{$queryParams}";
    }


        //Send the assessment email and update the scheduled email status.
        //@param  ScheduledEmail  $email
        //@param  Member  $member
        //@param  string  $assessmentUrl
        //@return void

    private function sendEmailAndUpdateStatus(ScheduledEmail $email, Member $member, string $assessmentUrl): void
    {
        try {
            Mail::to($member->email)->send(new ScheduledAssessmentMail($email->subject, $assessmentUrl));

            $email->update(['sent' => true]);

            Log::info('Scheduled assessment email sent successfully.', [
                'to' => $member->email,
                'subject' => $email->subject,
                'scheduled_email_id' => $email->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Mail send failed for scheduled assessment.', [
                'to' => $member->email,
                'subject' => $email->subject,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
