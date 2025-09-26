<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ScheduledEmail;
use App\Models\AssessmentAnswerToken;
use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SendScheduledAssessmentEmail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $scheduledEmailId;

    public function __construct($scheduledEmailId)
    {
        $this->scheduledEmailId = $scheduledEmailId;
    }

    public function handle()
    {
        $scheduled = ScheduledEmail::find($this->scheduledEmailId);
        if (!$scheduled) {
            Log::warning('ScheduledEmail not found: ' . $this->scheduledEmailId);
            return;
        }

        // Ensure member exists
        $member = Member::find($scheduled->member_id);
        if (!$member) {
            Log::warning('Member not found for ScheduledEmail: ' . $this->scheduledEmailId);
            return;
        }

 

        // For local testing: log the intended email content and mark as sent
        try {
            $logMsg = sprintf("ScheduledEmail id=%d recipient=%s assessment=%d group=%d member=%d\nSubject: %s\nBody: %s",
                $scheduled->id,
                $scheduled->recipient_email,
                $scheduled->assessment_id,
                $scheduled->group_id,
                $scheduled->member_id,
                $scheduled->subject,
                $scheduled->body
            );
            Log::info($logMsg);
            // Mark sent timestamp (if column exists)
            if (Schema::hasColumn('scheduled_emails', 'sent_at')) {
                $scheduled->sent_at = now();
                $scheduled->save();
            }
        } catch (\Exception $e) {
            Log::error('Failed to process scheduled email id=' . $scheduled->id . ' error=' . $e->getMessage());
        }
    }
}
