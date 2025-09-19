<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Notifications\GeneralNotification;

class SendScheduledAnnouncements extends Command
{
    protected $signature = 'announcement:send-scheduled';
    protected $description = 'Send scheduled announcements using Laravel Notifications.';

    public function handle()
    {
        $announcements = Announcement::whereNull('sent_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', Carbon::now())
            ->get();

        foreach ($announcements as $announcement) {
            // Collect user IDs and member emails
            $userIds = [];
            $memberEmails = [];

            $userIds = array_merge($userIds, $announcement->admins()->pluck('users.id')->toArray());
            foreach ($announcement->organizations as $org) {
                if (method_exists($org, 'users')) {
                    $userIds = array_merge($userIds, $org->users()->pluck('users.id')->toArray());
                }
                $orgAdminEmail = $org->admin_email ?? ($org->user->email ?? null);
                if (!empty($orgAdminEmail)) {
                    $memberEmails[] = $orgAdminEmail;
                    $adminUser = \App\Models\User::where('email', $orgAdminEmail)->first();
                    if ($adminUser) {
                        $userIds[] = $adminUser->id;
                    }
                }
            }
            // Only collect group member emails if groups are attached to the announcement
            if ($announcement->groups && $announcement->groups->isNotEmpty()) {
                foreach ($announcement->groups as $group) {
                    // Use members() email list only. Do not rely on group->users() pivot or member->user_id.
                    if (method_exists($group, 'members')) {
                        $memberEmails = array_merge($memberEmails, $group->members()->pluck('email')->toArray());
                    }
                }
            }

            $userIds = array_values(array_unique(array_filter($userIds)));
            $memberEmails = array_values(array_unique(array_filter($memberEmails)));

            $users = $userIds ? \App\Models\User::whereIn('id', $userIds)->get() : collect();

            // notify users (db + mail)
            Notification::send($users, new GeneralNotification($announcement));

            // mail-only to member emails not already covered by user emails
            $userEmails = $users->pluck('email')->filter()->unique()->values()->toArray();
            $emailsToSend = array_values(array_diff($memberEmails, $userEmails));
            foreach ($emailsToSend as $email) {
                $email = trim((string)$email);
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    Notification::route('mail', $email)->notify(new GeneralNotification($announcement));
                } else {
                    Log::warning('[SendScheduledAnnouncements] skipping invalid member email', ['announcement_id' => $announcement->id, 'email' => $email]);
                }
            }

            $announcement->sent_at = Carbon::now();
            $announcement->save();
            $this->info("Sent announcement ID {$announcement->id} to " . ($users->count()) . " users and " . count($emailsToSend) . " member emails.");
        }
    }
}
