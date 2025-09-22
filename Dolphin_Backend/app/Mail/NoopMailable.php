<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

/**
 * A No-op mailable used as a safe fallback when no recipient is available.
 * Overriding send() prevents the framework from attempting to dispatch an empty email.
 */
class NoopMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $announcement;
    public $displayName;

    public function __construct($announcement = null, $displayName = '')
    {
        $this->announcement = $announcement;
        $this->displayName = $displayName;
    }

    // Minimal build; not used because send() is overridden, but implemented for safety
    public function build()
    {
        $this->subject('Noop');
        $this->html('');
        return $this;
    }

    // Override send to be a no-op and avoid invoking the mail transport
    public function send($mailer)
    {
        Log::warning('[NoopMailable] send() called - skipping actual mail send', ['announcement_id' => $this->announcement->id ?? null, 'displayName' => $this->displayName]);
  
    }
}
