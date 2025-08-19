<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $announcement;

    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Notification channels
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Store notification in database
     */
    public function toDatabase($notifiable)
    {
        return [
            'title'   => 'New Announcement',
            'message' => $this->announcement->body,
            'announcement_id' => $this->announcement->id,
            'scheduled_at' => $this->announcement->scheduled_at,
            'sent_at' => $this->announcement->sent_at,
        ];
    }

    /**
     * Send notification as email
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Announcement')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->announcement->body)
            ->action('View Announcement', url('/announcements/' . $this->announcement->id))
            ->line('Thank you for using our application!');
    }
}
