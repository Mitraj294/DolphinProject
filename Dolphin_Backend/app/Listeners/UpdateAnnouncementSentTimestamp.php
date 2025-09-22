<?php

namespace App\Listeners;

use App\Notifications\GeneralNotification;
use Illuminate\Notifications\Events\NotificationSent;

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
            $announcement = $event->notification->getAnnouncement();
            // The NotificationSent event is fired for each channel. We only want to update the timestamp once.
            // We check if sent_at is null before updating.
            if ($announcement && is_null($announcement->sent_at)) {
                $announcement->sent_at = now();
                $announcement->save();
            }
        }
    }
}
