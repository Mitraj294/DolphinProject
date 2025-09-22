<?php

namespace App\Listeners;

use App\Notifications\GeneralNotification;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateAnnouncementSentTimestamp
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
        if ($event->notification instanceof GeneralNotification) {
            try {
                // Use the announcement id to perform an atomic update in case the announcement
                // model held inside the queued notification is stale or was serialized.
                $announcement = $event->notification->getAnnouncement();
                $announcementId = $announcement->id ?? null;

                Log::info('[UpdateAnnouncementSentTimestamp] NotificationSent event received', [
                    'announcement_id' => $announcementId,
                    'channel' => $event->channel ?? null,
                    'notifiable' => is_object($event->notifiable) ? get_class($event->notifiable) : (string) $event->notifiable,
                ]);

                if ($announcementId) {
                    $updated = DB::table('announcements')
                        ->where('id', $announcementId)
                        ->whereNull('sent_at')
                        ->update(['sent_at' => now()]);

                    if ($updated) {
                        Log::info('[UpdateAnnouncementSentTimestamp] announcement.sent_at updated', ['announcement_id' => $announcementId]);
                    } else {
                        Log::debug('[UpdateAnnouncementSentTimestamp] announcement.sent_at not updated (already set or missing)', ['announcement_id' => $announcementId]);
                    }
                } else {
                    Log::warning('[UpdateAnnouncementSentTimestamp] no announcement id on notification', []);
                }
            } catch (\Exception $e) {
                // Do not throw from an event listener; just log so queue doesn't fail the notification.
                Log::error('[UpdateAnnouncementSentTimestamp] failed', ['error' => $e->getMessage()]);
            }
        }
    }
}
