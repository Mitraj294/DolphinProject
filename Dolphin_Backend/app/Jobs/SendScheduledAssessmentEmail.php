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
        $email = ScheduledEmail::find($this->emailId);
        if (!$email || $email->sent) {
            return;
        }
        try {
            // Generate a unique token for the assessment answer
            $token = Str::random(40);
            $expiresAt = Carbon::now()->addDays(7);

            // Save the token in the assessment answer tokens table
            AssessmentAnswerToken::create([
                'assessment_id' => $email->assessment_id,
                'member_id' => $email->member_id ?? null, // Adjust if you have member_id
                'token' => $token,
                'expires_at' => $expiresAt,
                'used' => false,
            ]);

            // Build the answer URL
            $frontendBase = env('FRONTEND_URL', 'http://localhost:8080');
            $assessmentUrl = $frontendBase . '/assessment/answer/' . $token;

            Mail::to($email->recipient_email)
                ->send(new ScheduledAssessmentMail($email->subject, $assessmentUrl));
            $email->sent = true;
            $email->save();
        } catch (\Exception $e) {
            // Optionally log error
        }
    }
}
