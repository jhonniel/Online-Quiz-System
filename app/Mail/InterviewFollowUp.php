<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewFollowUp extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $position;
    public $interviewDate;
    public $socialMediaLink;

    /**
     * Create a new message instance.
     */
    public function __construct($application, $interviewDate, $position = null, $socialMediaLink = null)
    {
        $this->application = $application;
        $this->position = $position;
        $this->interviewDate = $interviewDate;
        $this->socialMediaLink = $socialMediaLink;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $positionTitle = $this->position ? $this->position->title : ($this->application->position_applied ?? 'Position');
        return new Envelope(
            subject: "Interview Follow-Up - {$positionTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.interview-follow-up',
            with: [
                'application' => $this->application,
                'position' => $this->position,
                'interviewDate' => $this->interviewDate,
                'socialMediaLink' => $this->socialMediaLink,
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
