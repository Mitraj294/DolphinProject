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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Illuminate\Support\Str;

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
        $lock = Cache::lock('announcement.'.$this->announcement->id, 10);

        if (!$lock->get()) {
            // If the lock cannot be acquired, it means another worker is already
            // processing this announcement. Release the job back to the queue.
            $this->release(10);
            return;
        }

        try {
            // Eager load relationships for efficiency. If any relationship name
            // or underlying column is missing (migration/model mismatch), loading
            // could throw an exception. Catch those and mark the announcement as
            // sent to avoid repeated failures while the system is being fixed.
            try {
                // Eager-load users, organization users and group members' user relations.
                // Note: do NOT eager-load `groups.users` because the pivot table in this
                // project uses `group_member.member_id` (not `user_id`) and an eager-load
                // of `groups.users` can trigger SQL looking for `group_member.user_id`.
                // Instead, rely on `groups.members.user` which maps members to users safely.
                $this->announcement->load(['users', 'organizations.users', 'groups.members.user']);
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
                    // Force synchronous delivery of the notification so the
                    // database notification row is created within this job's
                    // lifecycle. GeneralNotification implements ShouldQueue,
                    // but sendNow will bypass queuing for immediate persistence.
                    LaravelNotification::sendNow($user, new GeneralNotification($this->announcement));
                    Log::debug("Sent notification synchronously for user {$user->id} and announcement {$this->announcement->id}");
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

                // Ensure a DB notification row exists for this user and announcement.
                try {
                    // Use JSON_UNQUOTE to compare the extracted JSON value as text
                    $exists = \Illuminate\Support\Facades\DB::table('notifications')
                        ->where('notifiable_type', User::class)
                        ->where('notifiable_id', $user->id)
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.announcement_id')) = ?", [(string)$this->announcement->id])
                        ->exists();

                    if (!$exists) {
                        $nid = (string) Str::uuid();
                        \Illuminate\Support\Facades\DB::table('notifications')->insert([
                            'id' => $nid,
                            'type' => GeneralNotification::class,
                            'notifiable_type' => User::class,
                            'notifiable_id' => $user->id,
                            'data' => json_encode([ 'announcement_id' => $this->announcement->id, 'message' => $this->announcement->body ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        Log::info("Inserted DB notification for user {$user->id} and announcement {$this->announcement->id}");
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to ensure DB notification row for user {$user->id}: {$e->getMessage()}");
                }
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
        } finally {
            if (isset($lock)) {
                $lock->release();
            }
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
            // Also collect users from members that have a user_id
            if (method_exists($group, 'members')) {
                foreach ($group->members as $member) {
                    if ($member->user) {
                        $recipients->push($member->user);
                    }
                }
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
