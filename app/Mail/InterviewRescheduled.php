<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewRescheduled extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $position;
    public $interviewDate;
    public $adminNotes;
    public $isReschedule;

    /**
     * Create a new message instance.
     */
    public function __construct($application, $interviewDate, $adminNotes = null, $position = null, $isReschedule = false)
    {
        $this->application = $application;
        $this->position = $position;
        $this->interviewDate = $interviewDate;
        $this->adminNotes = $adminNotes;
        $this->isReschedule = $isReschedule;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $positionTitle = $this->position ? $this->position->title : ($this->application->position_applied ?? 'Position');
        $subject = $this->isReschedule
            ? "Interview Rescheduled - {$positionTitle}"
            : "Interview Scheduled - {$positionTitle}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.interview-rescheduled',
            with: [
                'application' => $this->application,
                'position' => $this->position,
                'interviewDate' => $this->interviewDate,
                'adminNotes' => $this->adminNotes,
                'isReschedule' => $this->isReschedule,
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
