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
        return new SubscriptionReceipt($this->subscription);
    }

    public function toArray($notifiable)
    {
        return $this->subscription;
    }
}
