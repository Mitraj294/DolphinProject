<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AssessmentAnswerLinkNotification extends Notification
{
    use Queueable;

    protected $answerUrl;
    protected $name;
    protected $subject;

    public function __construct($answerUrl, $name, $subject = null)
    {
        $this->answerUrl = $answerUrl;
        $this->name = $name;
        $this->subject = $subject;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject($this->subject ?: 'Answer Assessment Questions')
            ->greeting('Hello, ' . ($this->name ?: ''))
            ->line('You have been invited to answer assessment questions.')
            ->action('Answer Now', $this->answerUrl)
            ->line('If you did not expect this, you can ignore this email.')
            ->line('Thank you,')
            ->line('Dolphin Team');
        return $mail;
    }
}
