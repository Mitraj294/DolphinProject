<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
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
            // Get all user IDs to notify (admins, orgs, groups)
            $userIds = [];
            $userIds = array_merge($userIds, $announcement->admins()->pluck('users.id')->toArray());
            foreach ($announcement->organizations as $org) {
                if (method_exists($org, 'users')) {
                    $userIds = array_merge($userIds, $org->users()->pluck('users.id')->toArray());
                }
            }
            foreach ($announcement->groups as $group) {
                if (method_exists($group, 'users')) {
                    $userIds = array_merge($userIds, $group->users()->pluck('users.id')->toArray());
                }
            }
            $userIds = array_unique($userIds);
            $users = \App\Models\User::whereIn('id', $userIds)->get();

            Notification::send($users, new GeneralNotification($announcement));
            $announcement->sent_at = Carbon::now();
            $announcement->save();
            $this->info("Sent announcement ID {$announcement->id} to " . count($users) . " users.");
        }
    }
}
