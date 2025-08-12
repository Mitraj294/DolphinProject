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
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.assessment',
            with: [
                'assessmentUrl' => $this->assessmentUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
