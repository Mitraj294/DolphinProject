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
        $emails = ScheduledEmail::where('sent', false)
            ->where('send_at', '<=', now())
            ->get();

        foreach ($emails as $email) {
            try {
                // Dispatch the job to send the email and mark as sent
                \App\Jobs\SendScheduledAssessmentEmail::dispatch($email->id);
                $this->info("Dispatched job to send scheduled assessment email to {$email->recipient_email} (subject: {$email->subject}, send_at: {$email->send_at})");
            } catch (\Exception $e) {
                $this->error("Failed to dispatch job for assessment email to {$email->recipient_email}: {$e->getMessage()}");
            }
        }
    }
}
