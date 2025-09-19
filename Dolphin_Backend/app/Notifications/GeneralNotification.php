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
        // If the notifiable is an anonymous mail route (raw email address)
        // only enable the mail channel when a valid routed address exists.
        if ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
            try {
                $route = $notifiable->routeNotificationFor('mail');
                // route can be string or array; normalize and validate
                if (is_string($route) && filter_var($route, FILTER_VALIDATE_EMAIL)) {
                    return ['mail'];
                }
                if (is_array($route)) {
                    foreach ($route as $r) {
                        if (is_string($r) && filter_var($r, FILTER_VALIDATE_EMAIL)) {
                            return ['mail'];
                        }
                    }
                }
            } catch (\Exception $e) {
                // fall through and treat as no-mail
            }

            // no valid routed email found -> no channels
            return [];
        }

        // Only send database notifications for real User models.
        // If the user has a valid email, also include mail channel.
        if ($notifiable instanceof \App\Models\User) {
            $email = $notifiable->email ?? null;
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['database', 'mail'];
            }

            return ['database'];
        }

        // Fallback: if the notifiable has an email property, prefer mail; otherwise nothing.
        if (is_object($notifiable) && property_exists($notifiable, 'email') && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL)) {
            return ['mail'];
        }

        return [];
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

        // Compute a displayName for personalization
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
            // attempt to read the routed email address
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

        $mailable = new \App\Mail\AnnouncementMail($this->announcement, $displayName);

        $recipientFound = false;

        // If the notifiable is a User model and has a valid email, explicitly set it
        if ($notifiable instanceof \App\Models\User) {
            $email = $notifiable->email ?? null;
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mailable->to($email);
                $recipientFound = true;
            }
        }

        // If the notifiable is an anonymous mail route or a raw email string, ensure the mail has a recipient
        if (!$recipientFound) {
            if (is_string($notifiable)) {
                $mailable->to($notifiable);
                $recipientFound = true;
            } elseif ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
                try {
                    $route = $notifiable->routeNotificationFor('mail');
                    // route can be string or array; pick first valid address
                    $target = null;
                    if (is_string($route) && filter_var($route, FILTER_VALIDATE_EMAIL)) {
                        $target = $route;
                    } elseif (is_array($route)) {
                        foreach ($route as $r) {
                            if (is_string($r) && filter_var($r, FILTER_VALIDATE_EMAIL)) {
                                $target = $r;
                                break;
                            }
                        }
                    }

                    if ($target) {
                        $mailable->to($target);
                        $recipientFound = true;
                    } else {
                        // no valid target found; log for debugging
                        Log::warning('[Notification] AnonymousNotifiable without valid mail route', ['route' => $route]);
                    }
                } catch (\Exception $e) {
                    Log::warning('[Notification] Failed to read AnonymousNotifiable mail route', ['error' => $e->getMessage()]);
                }
            }
        }

        if (!$recipientFound) {
            // Defensive log
            Log::warning('[Notification] toMail called but no recipient was found for announcement', ['announcement_id' => $this->announcement->id, 'notifiable' => is_object($notifiable) ? get_class($notifiable) : (string)$notifiable]);
            // Return a Noop mailable which overrides send() to avoid invoking mail transport
            return new \App\Mail\NoopMailable($this->announcement, $displayName);
        }

        return $mailable;
    }

  
  
}