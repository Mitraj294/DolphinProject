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
                // Ensure we eager-load both group users (User models) and group members (Member models)
                // so we can create DB notifications for User models and still email Member records.
                $this->announcement->load(['users', 'organizations.users', 'groups.users', 'groups.members']);
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

            // Also collect member emails (Member model may not be a User and therefore
            // only needs an email send).
            $memberEmails = $this->collectMemberEmails();

            foreach ($recipients as $user) {
                // 1. Create Database Notification
                try {
                    // Use notifyNow to synchronously write the database notification
                    // and avoid relying on the queue worker's environment.
                    if (method_exists($user, 'notifyNow')) {
                        $user->notifyNow(new GeneralNotification($this->announcement));
                    } else {
                        $user->notify(new GeneralNotification($this->announcement));
                    }
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

            // Send email-only to member emails collected from groups (dedupe against user emails)
            $userEmails = $recipients->pluck('email')->filter()->unique()->values()->toArray();
            $memberEmails = array_values(array_diff($memberEmails, $userEmails));
            foreach ($memberEmails as $email) {
                try {
                    Mail::to($email)->send(new AnnouncementMail($this->announcement, $email));
                } catch (\Exception $e) {
                    Log::warning("Failed to send announcement email to member {$email} for announcement {$this->announcement->id}: {$e->getMessage()}");
                }
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
        // Start with directly associated users (User models).
        $recipients = new Collection($this->announcement->users);

        // Add all users from the targeted organizations (User models).
        foreach ($this->announcement->organizations as $organization) {
            $recipients = $recipients->merge($organization->users);
        }

        // Add all users from the targeted groups (User models).
        foreach ($this->announcement->groups as $group) {
            // Prefer group->users which returns User models. group->members may return Member models
            // which are not App\Models\User and therefore won't create DB notifications.
            if (method_exists($group, 'users')) {
                $recipients = $recipients->merge($group->users);
            }
        }

        // Return a collection of unique User models, preventing duplicate notifications.
        return $recipients->unique('id');
    }

    /**
     * Collect member emails from groups for mail-only sends (Member model may not be a User)
     *
     * @return array<string>
     */
    private function collectMemberEmails(): array
    {
        $emails = [];
        foreach ($this->announcement->groups as $group) {
            if (method_exists($group, 'members')) {
                try {
                    $members = $group->members;
                    foreach ($members as $m) {
                        $email = $m->email ?? null;
                        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $emails[] = $email;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('[Dispatch] Failed to collect group member emails', ['group_id' => $group->id, 'error' => $e->getMessage()]);
                }
            }
        }
        return array_values(array_unique($emails));
    }
}
