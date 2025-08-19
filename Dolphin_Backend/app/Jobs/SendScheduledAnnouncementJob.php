<?php
namespace App\Jobs;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
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

        // Admins
        $userIds = array_merge($userIds, $this->announcement->admins()->pluck('users.id')->toArray());

        // For each organization: send mail to org admin_email and notify the admin user (if exists).
        foreach ($this->announcement->organizations as $org) {
            // If organization provides an admin_email, send email there and notify the user account with that email (if any)
            if (!empty($org->admin_email)) {
                $memberEmails[] = $org->admin_email;

                // try to find a User with that email to show a notification on their page
                $adminUser = \App\Models\User::where('email', $org->admin_email)->first();
                if ($adminUser) {
                    $userIds[] = $adminUser->id;
                } else {
                    \Log::warning('[Job] Organization admin_email has no linked user account', ['organization_id' => $org->id, 'email' => $org->admin_email]);
                }
            }

            // Also collect member emails from any groups that belong to this organization
            $groups = \App\Models\Group::where('organization_id', $org->id)->get();
            foreach ($groups as $group) {
                $groupMemberIds = \DB::table('group_member')->where('group_id', $group->id)->pluck('member_id')->toArray();
                $emails = \App\Models\Member::whereIn('id', $groupMemberIds)->pluck('email')->toArray();
                $memberEmails = array_merge($memberEmails, $emails);
            }
        }

        // For each selected group: collect member emails (members don't have logins -> email only)
        foreach ($this->announcement->groups as $group) {
            $groupMemberIds = \DB::table('group_member')->where('group_id', $group->id)->pluck('member_id')->toArray();
            $emails = \App\Models\Member::whereIn('id', $groupMemberIds)->pluck('email')->toArray();
            $memberEmails = array_merge($memberEmails, $emails);
        }

        $userIds = array_unique($userIds);
        $memberEmails = array_unique($memberEmails);

        \Log::info('Announcement job user IDs', ['user_ids' => $userIds]);
        \Log::info('Announcement job member emails', ['member_emails' => $memberEmails]);

        // Get user models for notification
        $users = \App\Models\User::whereIn('id', $userIds)->get();
        \Log::info('Announcement job found users', ['count' => $users->count(), 'user_ids' => $users->pluck('id')->toArray()]);

        // Send notification to users (database + mail)
        if ($users->count() > 0) {
            try {
                Notification::send($users, new GeneralNotification($this->announcement));
                \Log::info('[Job] Notifications sent to users', [
                    'announcement_id' => $this->announcement->id,
                    'user_ids' => $users->pluck('id')->toArray()
                ]);
            } catch (\Exception $e) {
                \Log::error('[Job] Failed to send notifications', [
                    'announcement_id' => $this->announcement->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            \Log::warning('[Job] No users found for announcement notification', ['announcement_id' => $this->announcement->id]);
        }

        // Send mail to member emails (if needed). Guard against missing Mailable to avoid job failures.
        $memberEmails = array_unique($memberEmails);
        if (count($memberEmails) > 0) {
            if (!class_exists(\App\Mail\AnnouncementMail::class)) {
                \Log::error('[Job] Mail class App\\Mail\\AnnouncementMail not found; skipping email sends', ['announcement_id' => $this->announcement->id]);
            } else {
                foreach ($memberEmails as $email) {
                    try {
                        \Mail::to($email)->send(new \App\Mail\AnnouncementMail($this->announcement));
                        \Log::info('[Job] Mail sent to member', [
                            'announcement_id' => $this->announcement->id,
                            'email' => $email
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('[Job] Failed to send mail to member', [
                            'announcement_id' => $this->announcement->id,
                            'email' => $email,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        } else {
            \Log::warning('[Job] No member emails found for announcement', ['announcement_id' => $this->announcement->id]);
        }
        // Only set sent_at if notifications or mails were attempted
        $this->announcement->sent_at = now();
        $this->announcement->save();
        \Log::info('[Job] Announcement job completed', ['announcement_id' => $this->announcement->id]);
    }

}