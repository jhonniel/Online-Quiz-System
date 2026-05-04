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
    public $address;
    public $interviewFormat;
    public $meetingLink;

    /**
     * Create a new message instance.
     */
    public function __construct($application, $interviewDate, $adminNotes = null, $position = null, $isReschedule = false, $address = null, $interviewFormat = 'on_site', $meetingLink = null)
    {
        $this->application = $application;
        $this->position = $position;
        $this->interviewDate = $interviewDate;
        $this->adminNotes = $adminNotes;
        $this->isReschedule = $isReschedule;
        $this->address = $address;
        $this->interviewFormat = $interviewFormat;
        $this->meetingLink = $meetingLink;
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
        $formatSuffix = (($this->interviewFormat ?? 'on_site') === 'online') ? ' (Online)' : ' (On-site)';
        $subject .= $formatSuffix;

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
            view: 'emails.interview-rescheduled',
            with: [
                'application' => $this->application,
                'position' => $this->position,
                'interviewDate' => $this->interviewDate,
                'adminNotes' => $this->adminNotes,
                'isReschedule' => $this->isReschedule,
                'address' => $this->address,
                'interviewFormat' => $this->interviewFormat,
                'meetingLink' => $this->meetingLink,
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



