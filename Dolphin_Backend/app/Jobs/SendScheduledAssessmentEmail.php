<?php

namespace App\Jobs;

use App\Models\ScheduledEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ScheduledAssessmentMail;
use App\Models\Assessment;
use App\Models\AssessmentAnswerToken;
use App\Models\Member;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;


class SendScheduledAssessmentEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $emailId;

    /**
     * Create a new job instance.
     */
    public function __construct($emailId)
    {
        $this->emailId = $emailId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        \Log::info('SendScheduledAssessmentEmail: Looking up ScheduledEmail', [
            'emailId' => $this->emailId
        ]);
        $email = ScheduledEmail::find($this->emailId);
        if (!$email) {
            \Log::warning('SendScheduledAssessmentEmail: ScheduledEmail not found', [
                'emailId' => $this->emailId
            ]);
            return;
        }
        if ($email->sent) {
            \Log::info('SendScheduledAssessmentEmail: Email already marked as sent', [
                'emailId' => $this->emailId
            ]);
            return;
        }
        \Log::info('SendScheduledAssessmentEmail: ScheduledEmail found', [
            'email' => $email
        ]);
            \Log::info('SendScheduledAssessmentEmail job started', [
                'email' => $this->emailId
            ]);
        try {
            $recipientEmail = trim(strtolower($email->recipient_email));
            $member = \App\Models\Member::whereRaw('LOWER(TRIM(email)) = ?', [$recipientEmail])->first();
            if (!$member) {
                \Log::error('SendScheduledAssessmentEmail: Member not found for email.', [
                    'email' => $recipientEmail,
                    'emailId' => $this->emailId
                ]);
                return; // Exit the job as we cannot proceed without a member_id
            }
            $token = Str::random(40);
            $expiresAt = Carbon::now()->addDays(7);
            // Get group_id from the email or member (customize as needed)
            $groupId = $email->group_id;
            if (is_null($groupId)) {
                $firstGroup = $member->groups()->first();
                if ($firstGroup) {
                    $groupId = $firstGroup->id;
                } else {
                    \Log::error('SendScheduledAssessmentEmail: No group_id found for member.', [
                        'member_id' => $member->id,
                        'emailId' => $this->emailId
                    ]);
                    return; // Exit the job as we cannot proceed without group_id
                }
            }
            $tokenRow = AssessmentAnswerToken::create([
                'assessment_id' => $email->assessment_id,
                'member_id' => $member->id,
                'group_id' => $groupId,
                'token' => $token,
                'expires_at' => $expiresAt,
                'used' => false,
            ]);
            \Log::info('AssessmentAnswerToken created', [
                'token_id' => $tokenRow->id,
                'assessment_id' => $email->assessment_id,
                'member_id' => $member->id,
                'group_id' => $groupId,
                'token' => $token
            ]);
            $frontendBase = env('FRONTEND_URL', 'http://localhost:8080');
            $assessmentUrl = $frontendBase . '/assessment/answer/' . $token . '?group_id=' . $groupId . '&member_id=' . $member->id;
            try {
                Mail::to($member->email)
                    ->send(new ScheduledAssessmentMail($email->subject, $assessmentUrl));
                \Log::info('Assessment email sent', [
                    'to' => $member->email,
                    'subject' => $email->subject,
                    'member_id' => $member->id,
                    'group_id' => $groupId
                ]);
                // Mark scheduled email as sent
                $email->sent = 1;
                $email->save();
            } catch (\Exception $mailEx) {
                \Log::error('Mail send failed: ' . $mailEx->getMessage(), [
                    'to' => $member->email,
                    'subject' => $email->subject,
                    'token' => $token,
                    'member_id' => $member->id
                ]);
            }

            // (Removed redundant code that attempted to send email and create token for recipient_email again)
        } catch (\Exception $e) {
            \Log::error('Failed to send assessment email: ' . $e->getMessage());
        }
    }
}
