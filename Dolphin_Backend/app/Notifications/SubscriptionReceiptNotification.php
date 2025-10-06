<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Mail\SubscriptionReceipt;

class SubscriptionReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;

    public function __construct(array $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // When notifications are queued using AnonymousNotifiable::route('mail', $email)
        // the MailChannel will not automatically apply the recipient to a Mailable.
        // Set the recipient here so the serialized Mailable includes the To header
        // and Symfony Mailer doesn't reject it when the job is processed.
        $recipient = $notifiable->routeNotificationFor('mail', $this) ?? null;

        $mailable = new SubscriptionReceipt($this->subscription);
        if ($recipient) {
            $mailable->to($recipient);
        }

        return $mailable;
    }

    public function toArray($notifiable)
    {
        return $this->subscription;
    }
}
