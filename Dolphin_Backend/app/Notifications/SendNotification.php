<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public $notification;

    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Notification')
            ->line($this->notification->body);
    }

    public function toArray($notifiable)
    {
        return [
            'body' => $this->notification->body,
            'sender_id' => $this->notification->sender_id,
            'organization_ids' => $this->notification->organization_ids,
            'admin_ids' => $this->notification->admin_ids,
            'group_ids' => $this->notification->group_ids,
            'scheduled_at' => $this->notification->scheduled_at,
            'sent_at' => $this->notification->sent_at,
        ];
    }
}
