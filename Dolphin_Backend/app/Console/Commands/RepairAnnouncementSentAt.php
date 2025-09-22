<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairAnnouncementSentAt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Optional parameter: announcement id
     */
    protected $signature = 'repair:announcement-sent-at {announcement_id?}';

    /**
     * The console command description.
     */
    protected $description = 'Repair announcements.sent_at by inferring from notifications or dispatched_at';

    public function handle()
    {
        $id = $this->argument('announcement_id');

        $query = DB::table('announcements')
            ->whereNotNull('dispatched_at')
            ->whereNull('sent_at');

        if ($id) {
            $query->where('id', $id);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->info('No announcements found needing repair.');
            return 0;
        }

        foreach ($rows as $r) {
            $this->info("Processing announcement id={$r->id}");

            // try to find notifications referencing this announcement
            $notif = DB::table('notifications')
                ->where('type', 'App\\Notifications\\GeneralNotification')
                ->whereRaw("JSON_EXTRACT(data, '$.announcement_id') = ?", [$r->id])
                ->orderBy('created_at')
                ->first();

            if ($notif) {
                $sentAt = $notif->created_at;
                $this->info("Found notification id={$notif->id}, using created_at={$sentAt}");
            } else {
                $sentAt = $r->dispatched_at ?? now();
                $this->info("No notification rows; falling back to dispatched_at={$sentAt}");
            }

            $updated = DB::table('announcements')
                ->where('id', $r->id)
                ->update(['sent_at' => $sentAt]);

            if ($updated) {
                $this->info("Announcement {$r->id} updated sent_at={$sentAt}");
            } else {
                $this->warn("Announcement {$r->id} was not updated (concurrent change?)");
            }
        }

        return 0;
    }
}
