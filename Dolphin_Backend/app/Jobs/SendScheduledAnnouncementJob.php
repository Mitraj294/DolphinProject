<?php

namespace App\Jobs;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendScheduledAnnouncementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The announcement to be sent.
     *
     * @var \App\Models\Announcement
     */
    protected Announcement $announcement;

    /**
     * Create a new job instance.
     * The job now only needs the announcement itself, as it will determine
     * the recipients based on the announcement's relationships.
     *
     * @param \App\Models\Announcement $announcement
     * @return void
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Execute the job.
     * This method now handles a hybrid notification strategy:
     * 1. Creates a database notification for an in-app notification center.
     * 2. Sends a corresponding email to the user.
     * 3. Updates the announcement's 'sent_at' timestamp upon successful completion.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Eager load relationships for efficiency. If any relationship name
            // or underlying column is missing (migration/model mismatch), loading
            // could throw an exception. Catch those and mark the announcement as
            // sent to avoid repeated failures while the system is being fixed.
            try {
                $this->announcement->load(['users', 'organizations.users', 'groups.members']);
            } catch (\Exception $e) {
                Log::error("Failed to eager-load announcement relations for ID {$this->announcement->id}. Marking as sent to avoid retry loop. Error: {$e->getMessage()}");
                // Mark as sent to prevent the scheduler from repeatedly dispatching
                // the same failing announcement. An operator can re-open or re-send
                // manually after fixing data/model migrations if needed.
                try {
                    $this->announcement->update(['sent_at' => now()]);
                } catch (\Exception $inner) {
                    Log::warning("Could not mark announcement ID {$this->announcement->id} as sent: {$inner->getMessage()}");
                }
                return;
            }

            $recipients = $this->collectRecipients();

            if ($recipients->isEmpty()) {
                Log::info("No recipients found for announcement ID: {$this->announcement->id}. Marking as sent.");
                $this->announcement->update(['sent_at' => now()]);
                return;
            }

            Log::info("Processing announcement ID: {$this->announcement->id} for {$recipients->count()} unique recipients.");

            foreach ($recipients as $user) {
                // 1. Create Database Notification
                try {
                    $user->notify(new GeneralNotification($this->announcement));
                } catch (\Exception $e) {
                    Log::warning("Failed to create DB notification for user {$user->id} on announcement {$this->announcement->id}: {$e->getMessage()}");
                }

                // 2. Send Email Notification (best-effort)
                try {
                    if (!empty($user->email)) {
                        Mail::to($user->email)->send(new AnnouncementMail($this->announcement, $user));
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to send email for user {$user->id} on announcement {$this->announcement->id}: {$e->getMessage()}");
                }

                Log::info("Attempted announcement {$this->announcement->id} for user {$user->email} (ID: {$user->id})");
            }

            // 3. Mark the announcement as sent after all notifications are attempted.
            $this->announcement->update(['sent_at' => now()]);
            Log::info("Successfully processed and sent announcement ID: {$this->announcement->id}.");

        } catch (\Exception $e) {
            // Catch-all: log and avoid throwing to the worker so other jobs can run.
            Log::error("Failed to process announcement ID {$this->announcement->id}. Error: {$e->getMessage()}");
        }
    }

    /**
     * Gathers and de-duplicates all recipients from the announcement's relationships.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function collectRecipients(): Collection
    {
        // Start with directly associated users.
        $recipients = new Collection($this->announcement->users);

        // Add all users from the targeted organizations.
        foreach ($this->announcement->organizations as $organization) {
            $recipients = $recipients->merge($organization->users);
        }

        // Add all members from the targeted groups.
        foreach ($this->announcement->groups as $group) {
            // Assuming 'members' on the Group model returns a collection of User models.
            $recipients = $recipients->merge($group->members);
        }

        // Return a collection of unique users, preventing duplicate notifications.
        return $recipients->unique('id');
    }
}
