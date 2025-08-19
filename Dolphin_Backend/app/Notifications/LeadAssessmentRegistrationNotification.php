<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeadAssessmentRegistrationNotification extends Notification
{
    use Queueable;

    protected $registrationUrl;
    protected $name;
    protected $body;
    protected $subject;

    public function __construct($registrationUrl, $name, $body, $subject)
    {
        $this->registrationUrl = $registrationUrl;
        $this->name = $name;
        $this->body = $body;
        $this->subject = $subject;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject($this->subject ?: 'Complete Your Registration');

        if (!empty($this->body)) {
            $mail->view('emails.lead_registration', [
                'registrationUrl' => $this->registrationUrl,
                'body' => $this->body,
            ]);
        } else {
            $mail->greeting('Hello, ' . ($this->name ?: ''))
                ->line('You have been invited to complete your registration.')
                ->action('Complete Registration', $this->registrationUrl)
                ->line('If you did not request this, you can ignore this email.')
                ->line('Thank you,')
                ->line('Dolphin Team');
        }
        return $mail;
    }
}
