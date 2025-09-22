<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
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
        $channels = [];

        if ($notifiable instanceof \App\Models\User) {
            $channels[] = 'database';
            if (filter_var($notifiable->email, FILTER_VALIDATE_EMAIL)) {
                $channels[] = 'mail';
            }
        } elseif ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
            try {
                $route = $notifiable->routeNotificationFor('mail');
                if (is_string($route) && filter_var($route, FILTER_VALIDATE_EMAIL)) {
                    $channels[] = 'mail';
                } elseif (is_array($route)) {
                    foreach ($route as $r) {
                        if (is_string($r) && filter_var($r, FILTER_VALIDATE_EMAIL)) {
                            $channels[] = 'mail';
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                // ignore
            }
        } elseif (is_object($notifiable) && property_exists($notifiable, 'email') && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL)) {
            $channels[] = 'mail';
        }

        return array_unique($channels);
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
     * Use the custom Announcement Mailable so we can render a full blade template
     * and include personalization when possible.
     */
    public function toMail($notifiable)
    {
        $displayName = '';

        if (is_object($notifiable)) {
            if (property_exists($notifiable, 'name') && $notifiable->name) {
                $displayName = $notifiable->name;
            } else {
                $first = $notifiable->first_name ?? '';
                $last = $notifiable->last_name ?? '';
                $displayName = trim($first . ' ' . $last) ?: ($notifiable->email ?? '');
            }
        } elseif (is_string($notifiable)) {
            $displayName = $notifiable;
        } elseif ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
            try {
                $route = $notifiable->routeNotificationFor('mail');
                if (is_string($route)) {
                    $displayName = $route;
                } elseif (is_array($route)) {
                    $displayName = implode(', ', array_values($route));
                }
            } catch (\Exception $e) {
                Log::warning('[Notification] Failed to read AnonymousNotifiable mail route', ['error' => $e->getMessage()]);
            }
        }

        $subject = $this->announcement->subject ?? 'New Announcement';
        
        $mailMessage = (new MailMessage)->subject($subject);
        
        if ($displayName) {
            $mailMessage->greeting('Hello ' . $displayName . ',');
        }
        
        $mailMessage->markdown('mail.announcement', ['content' => $this->announcement->body]);

        return $mailMessage;
    }

  
  
}