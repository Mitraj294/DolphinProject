<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssessmentAnswerLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $link;
    public $assessment;

    public function __construct($link, $assessment)
    {
        $this->link = $link;
        $this->assessment = $assessment;
    }

    public function build()
    {
        return $this->subject('Assessment Participation Link')
            ->view('emails.assessment', [
                'assessmentUrl' => $this->link
            ]);
    }
}
