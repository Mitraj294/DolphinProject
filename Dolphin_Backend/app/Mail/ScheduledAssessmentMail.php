<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduledAssessmentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $assessmentUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($subjectText, $assessmentUrl)
    {
        $this->subjectText = $subjectText;
        $this->assessmentUrl = $assessmentUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $html = view('emails.assessment', [
            'assessmentUrl' => $this->assessmentUrl,
        ])->render();
        return $this->subject($this->subjectText)
            ->html($html);
    }
}
