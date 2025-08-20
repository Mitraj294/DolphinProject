<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Announcement;
use Illuminate\Notifications\AnonymousNotifiable;

class AnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public Announcement $announcement;
    public $displayName;

    /**
     * Create a new message instance.
     */
    public function __construct(Announcement $announcement, $displayName = '')
    {
        $this->announcement = $announcement;
        $this->displayName = $displayName ?? '';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Announcement')
                    ->view('emails.announcement')
                    ->with([
                        'announcement' => $this->announcement,
                        'displayName' => $this->displayName,
                    ]);
    }
}
