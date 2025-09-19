<?php
namespace App\Jobs;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Notifications\GeneralNotification;

class SendScheduledAnnouncementJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function handle()
    {
        $userIds = [];
        $memberEmails = [];
if ($this->announcement->sent_at) {
    Log::info('[Job] Announcement already sent, skipping', ['announcement_id' => $this->announcement->id]);
    return;
}
        // Determine attachment types early. If this is a pure group-only
        // announcement (groups attached, no organizations), take a fast-path
        // that only collects group member emails and sends mail. This avoids
        // collecting any user IDs or creating DB notifications for group
        // members.
        $hasGroups = $this->announcement->groups && $this->announcement->groups->isNotEmpty();
        $hasOrgs = $this->announcement->organizations && $this->announcement->organizations->isNotEmpty();
        if ($hasGroups && !$hasOrgs) {
            // collect group member emails
            foreach ($this->announcement->groups as $group) {
                if (method_exists($group, 'members')) {
                    try {
                        $emails = $group->members()->pluck('email')->toArray();
                        $memberEmails = array_merge($memberEmails, $emails);
                    } catch (\Exception $e) {
                        Log::warning('[Job] Failed to pluck group members (group-only fast-path)', ['group_id' => $group->id, 'error' => $e->getMessage()]);
                    }
                }
            }
            $memberEmails = array_values(array_unique(array_filter($memberEmails)));
            Log::info('Announcement job member emails (group-only)', ['member_emails' => $memberEmails]);

            // Send mail-only to group member emails
            foreach ($memberEmails as $email) {
                $email = trim((string)$email);
                if (empty($email)) continue;
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        Notification::route('mail', $email)->notify(new GeneralNotification($this->announcement));
                        Log::info('[Job] Mail sent to member (group-only)', ['announcement_id' => $this->announcement->id, 'email' => $email]);
                    } catch (\Exception $e) {
                        Log::error('[Job] Failed to send mail to member (group-only)', ['announcement_id' => $this->announcement->id, 'email' => $email, 'error' => $e->getMessage()]);
                    }
                } else {
                    Log::warning('[Job] Skipping invalid member email (group-only)', ['announcement_id' => $this->announcement->id, 'email' => $email]);
                }
            }

            // mark sent and exit
            $this->announcement->sent_at = now();
            $this->announcement->save();
            Log::info('[Job] Announcement group-only fast-path completed', ['announcement_id' => $this->announcement->id]);
            return;
        }
    // NOTE: Only include announcement-level admins when organizations are
    // attached. This prevents announcement admins (or stale pivot rows)
    // from causing DB notifications for pure group-only announcements.

    // Organizations: include org users (if any) and org admin_email as member email
        foreach ($this->announcement->organizations as $org) {
            if (method_exists($org, 'users')) {
                $orgUserIds = $this->safePluckUserIds($org->users());
                // fallback: if no users found via users table mapping, include organization owner user_id if present
                if (empty($orgUserIds) && isset($org->user_id) && $org->user_id) {
                    $orgUserIds[] = $org->user_id;
                }
                $userIds = array_merge($userIds, $orgUserIds);
            }
            $orgAdminEmail = $org->admin_email ?? ($org->user->email ?? null);
            if (!empty($orgAdminEmail)) {
                $memberEmails[] = $orgAdminEmail;
                $adminUser = \App\Models\User::where('email', $orgAdminEmail)->first();
                if ($adminUser) {
                    $userIds[] = $adminUser->id;
                }
            }
            // For organization-level sends we prefer to notify org users (DB) and
            // email the organization's admin contact only (mail). Do NOT include
            // group members here unless those groups were explicitly selected.
        }

        // For each selected group: collect member emails and optionally user ids
        // Behavior: if this announcement is group-only (no organizations attached)
        // we must NOT add group members' user_ids into $userIds (to avoid
        // creating DB notifications for group members). We still collect their
        // emails for mail-only sending. If organizations are also attached
        // (mixed case), it's acceptable to include member user_ids so they
        // receive DB notifications as part of the org flow.
        $hasGroups = $this->announcement->groups && $this->announcement->groups->isNotEmpty();
        $hasOrgs = $this->announcement->organizations && $this->announcement->organizations->isNotEmpty();

        // If organizations are attached, include announcement admins as DB recipients.
        if ($hasOrgs) {
            $userIds = array_merge($userIds, $this->safePluckUserIds($this->announcement->admins()));
        }
        if ($hasGroups) {
            foreach ($this->announcement->groups as $group) {
                if (method_exists($group, 'members')) {
                    try {
                        $emails = $group->members()->pluck('email')->toArray();
                        $memberEmails = array_merge($memberEmails, $emails);
                        // Only include linked user ids for mixed (org+group) sends;
                        // skip adding user ids for pure group-only announcements.
                        if ($hasOrgs) {
                            $memberUserIds = $group->members()->whereNotNull('user_id')->pluck('user_id')->toArray();
                            $userIds = array_merge($userIds, $memberUserIds);
                        }
                    } catch (\Exception $e) {
                        Log::warning('[Job] Failed to pluck group members', ['group_id' => $group->id, 'error' => $e->getMessage()]);
                    }
                }
            }
        }

        // Clean up lists
        $userIds = array_values(array_unique(array_filter($userIds)));
        $memberEmails = array_values(array_unique(array_filter($memberEmails)));

        Log::info('Announcement job user IDs', ['user_ids' => $userIds]);
        Log::info('Announcement job member emails', ['member_emails' => $memberEmails]);

        // Get user models for notification
        $users = $userIds ? \App\Models\User::whereIn('id', $userIds)->get() : collect();
        Log::info('Announcement job found users', ['count' => $users->count(), 'user_ids' => $users->pluck('id')->toArray()]);

        // Send notification to users (database + mail)
        if ($users->isNotEmpty()) {
            try {
                Notification::send($users, new GeneralNotification($this->announcement));
                Log::info('[Job] Notifications sent to users', [
                    'announcement_id' => $this->announcement->id,
                    'user_ids' => $users->pluck('id')->toArray()
                ]);
            } catch (\Exception $e) {
                Log::error('[Job] Failed to send notifications', [
                    'announcement_id' => $this->announcement->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Log::warning('[Job] No users found for announcement notification', ['announcement_id' => $this->announcement->id]);
        }

        // Avoid sending duplicate emails to addresses that belong to User models
        $userEmails = $users->pluck('email')->filter()->unique()->values()->toArray();
        $emailsToSend = array_values(array_diff($memberEmails, $userEmails));

        // Send mail-only notifications to remaining member emails
      foreach ($emailsToSend as $email) {
    $email = trim((string)$email);
    // Add an explicit check to make sure the email is not empty after trimming
    if (empty($email)) {
        Log::warning('[Job] Skipping empty member email', ['announcement_id' => $this->announcement->id]);
        continue;
    }
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            Notification::route('mail', $email)->notify(new GeneralNotification($this->announcement));
            Log::info('[Job] Mail sent to member', ['announcement_id' => $this->announcement->id, 'email' => $email]);
        } catch (\Exception $e) {
            Log::error('[Job] Failed to send mail to member', ['announcement_id' => $this->announcement->id, 'email' => $email, 'error' => $e->getMessage()]);
        }
    } else {
        Log::warning('[Job] Skipping invalid member email', ['announcement_id' => $this->announcement->id, 'email' => $email]);
    }
}

  
        // Only set sent_at if notifications or mails were attempted
        $this->announcement->sent_at = now();
        $this->announcement->save();
        Log::info('[Job] Announcement job completed', ['announcement_id' => $this->announcement->id]);
    }

    /**
     * Safely pluck user ids from a relation/query builder. If the underlying users table
     * doesn't have expected columns (for example organization_id), the DB query may fail.
     * This helper returns an empty array on failure instead of throwing.
     */
    private function safePluckUserIds($relation)
    {
        try {
            return $relation ? $relation->pluck('users.id')->toArray() : [];
        } catch (\Exception $e) {
            Log::warning('[Job] safePluckUserIds failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

}