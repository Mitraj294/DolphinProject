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
            // Refresh the announcement from database to get latest state
            $this->announcement->refresh();
            
            // Check if already sent to avoid processing duplicates
            if ($this->announcement->sent_at) {
                Log::info("Announcement ID {$this->announcement->id} already marked as sent, skipping processing.");
                return;
            }

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

            // Separate collection for different recipient types
            $orgAndAdminUsers = $this->collectOrganizationAndAdminUsers();
            $groupUsers = $this->collectGroupUsers();
            $memberEmails = $this->collectMemberEmails();

            $totalRecipients = $orgAndAdminUsers->count() + $groupUsers->count() + count($memberEmails);

            if ($totalRecipients === 0) {
                Log::info("No recipients found for announcement ID: {$this->announcement->id}. Marking as sent.");
                $this->announcement->update(['sent_at' => now()]);
                return;
            }

            Log::info("Processing announcement ID: {$this->announcement->id} for {$totalRecipients} total recipients (org/admin: {$orgAndAdminUsers->count()}, group: {$groupUsers->count()}, member emails: " . count($memberEmails) . ")");

            // Step 1: Create database notifications ONLY for organization/admin users
            foreach ($orgAndAdminUsers as $user) {
                try {
                    LaravelNotification::sendNow($user, new GeneralNotification($this->announcement));
                    Log::debug("Sent DB notification for org/admin user {$user->id} and announcement {$this->announcement->id}");
                } catch (\Exception $e) {
                    Log::warning("Failed to create DB notification for org/admin user {$user->id} on announcement {$this->announcement->id}: {$e->getMessage()}");
                }
            }

            // Step 2: Send exactly ONE email to each unique user (deduplicated by ID, not object)
            $allUsers = $orgAndAdminUsers->merge($groupUsers);
            $orgAdminUserIds = $orgAndAdminUsers->pluck('id')->toArray();
            // Track which user IDs we've already processed
            $processedUserIds = [];
            
            foreach ($allUsers as $user) {
                // Skip if we've already processed this user ID
                if (in_array($user->id, $processedUserIds)) {
                    Log::info("SKIPPING DUPLICATE: User {$user->id} already processed for announcement {$this->announcement->id}");
                    continue;
                }
                $processedUserIds[] = $user->id;
                Log::info("PROCESSING USER: User {$user->id} ({$user->email}) for announcement {$this->announcement->id}");
                try {
                    if (!empty($user->email)) {
                        // Format user display name properly
                        $displayName = trim($user->first_name . ' ' . $user->last_name);
                        if (empty($displayName)) {
                            $displayName = $user->email;
                        }
                        
                        Log::info("ABOUT TO SEND EMAIL: User {$user->id} ({$user->email}) for announcement {$this->announcement->id}");
                        Mail::to($user->email)->send(new AnnouncementMail($this->announcement, $displayName));
                        Log::info("EMAIL SENT SUCCESSFULLY: User {$user->id} ({$user->email}) for announcement {$this->announcement->id}");
                        
                        // Log based on user type for clarity (prioritize org/admin status)
                        if (in_array($user->id, $orgAdminUserIds)) {
                            Log::info("Sent email to org/admin user {$user->email} (ID: {$user->id}) for announcement {$this->announcement->id}");
                        } else {
                            Log::info("Sent email to group-only user {$user->email} (ID: {$user->id}) for announcement {$this->announcement->id}");
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to send email for user {$user->id} on announcement {$this->announcement->id}: {$e->getMessage()}");
                }
            }

            // Step 3: Send emails to member emails that don't belong to any User (deduplicated)
            $allUserEmails = $allUsers->pluck('email')->filter()->unique()->values()->toArray();
            $uniqueMemberEmails = array_values(array_diff($memberEmails, $allUserEmails));
            
            foreach ($uniqueMemberEmails as $email) {
                try {
                    Mail::to($email)->send(new AnnouncementMail($this->announcement, $email));
                    Log::info("Sent email to member email {$email} for announcement {$this->announcement->id}");
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
     * Collect users from organizations and direct admin assignments (these get database notifications)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function collectOrganizationAndAdminUsers(): Collection
    {
        // Start with directly associated users (admin recipients)
        $recipients = new Collection($this->announcement->users);

        // Add all users from the targeted organizations
        foreach ($this->announcement->organizations as $organization) {
            $recipients = $recipients->merge($organization->users);
        }

        // Return a collection of unique User models
        return $recipients->unique('id');
    }

    /**
     * Collect users from groups only (these get email-only, no database notifications)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function collectGroupUsers(): Collection
    {
        $recipients = new Collection();

        // Add all users from the targeted groups
        foreach ($this->announcement->groups as $group) {
            // Use group->users which returns User models via the relationship
            if (method_exists($group, 'users')) {
                $recipients = $recipients->merge($group->users);
            }
        }

        // Return a collection of unique User models
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
