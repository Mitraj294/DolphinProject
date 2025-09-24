<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewUserInvitation extends Notification
{
    use Queueable;

    protected $plainPassword;
    protected $resetTokenUrl;

    public function __construct(string $plainPassword, ?string $resetTokenUrl = null)
    {
        $this->plainPassword = $plainPassword;
        $this->resetTokenUrl = $resetTokenUrl;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Your Dolphin account has been created')
            ->greeting('Hello ' . ($notifiable->first_name ?? $notifiable->email) . ',')
            ->line('An account has been created for you on Dolphin.')
            ->line('Temporary password: ' . $this->plainPassword)
            ->line('For security, please change your password after logging in.');

        if ($this->resetTokenUrl) {
            $mail->action('Change your password', $this->resetTokenUrl)
                 ->line('The link will allow you to set a permanent password.');
        } else {
            $mail->line('You can change your password from your account settings once you log in.');
        }

        $mail->line('If you did not expect this account, please contact your administrator.');

        return $mail;
    }
}
