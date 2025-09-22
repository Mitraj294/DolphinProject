<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAnnouncement extends Notification implements ShouldQueue
{
    use Queueable;

    protected $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        // This uses Markdown for simple email templating, removing the need for a separate Blade file.
        return (new MailMessage)
                    ->subject($this->announcement->subject)
                    ->markdown('mail.announcement', ['content' => $this->announcement->content]);
    }
    
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'New Announcement: ' . $this->announcement->subject,
            'link' => '/announcements/' . $this->announcement->id
        ];
    }
}
