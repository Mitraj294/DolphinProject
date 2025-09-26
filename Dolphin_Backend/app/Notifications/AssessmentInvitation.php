<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssessmentInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assessmentLink;
    protected $assessmentName;
    protected $assessmentId;

    public function __construct(string $assessmentLink, string $assessmentName, ?int $assessmentId = null)
    {
        $this->assessmentLink = $assessmentLink;
        $this->assessmentName = $assessmentName;
        $this->assessmentId = $assessmentId;
    }

    public function via($notifiable)
    {
        // This notification can be sent via email and stored in the database.
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('You have been invited to an assessment')
                    ->line('You have been invited to take the assessment: ' . $this->assessmentName)
                    ->action('Take Assessment', $this->assessmentLink)
                    ->line('Thank you for your participation!');
    }
    
    public function toDatabase($notifiable)
    {
        // This will be stored in the 'notifications' table for in-app display.
        return [
            'message' => 'You have a new assessment to complete: ' . $this->assessmentName,
            'link' => '/assessments',
            'assessment_id' => $this->assessmentId,
            // keep assessment_name for backwards compatibility
            'assessment_name' => $this->assessmentName,
        ];
    }
}
