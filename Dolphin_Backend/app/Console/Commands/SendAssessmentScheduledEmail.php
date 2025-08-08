<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\ScheduledAssessmentMail;
use Carbon\Carbon;

class SendAssessmentScheduledEmail extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'assessment:send-scheduled-emails';

    /**
     * The console command description.
     */
    protected $description = 'Send scheduled assessment emails for organization assessments.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emails = ScheduledEmail::where('sent', false)->get();

        foreach ($emails as $email) {
            try {
                // Ensure send_at is UTC (already handled in ScheduledEmailController)
                $sendAtUtc = Carbon::parse($email->send_at, 'UTC');
                Mail::to($email->recipient_email)
                    ->later($sendAtUtc, new ScheduledAssessmentMail($email->subject, $email->body));
                $email->sent = true;
                $email->save();
                $this->info("Queued scheduled assessment email to {$email->recipient_email} (subject: {$email->subject}, send_at: {$sendAtUtc} UTC)");
            } catch (\Exception $e) {
                $this->error("Failed to queue assessment email to {$email->recipient_email}: {$e->getMessage()}");
            }
        }
    }
}
