<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HiringApplicationStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $position;
    public $status;
    public $statusMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($application, $status, $statusMessage = null, $position = null)
    {
        $this->application = $application;
        $this->position = $position;
        $this->status = $status;
        $this->statusMessage = $statusMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $positionTitle = $this->position ? $this->position->title : ($this->application->position_applied ?? 'Position');
        $statusText = ucfirst($this->status);
        return new Envelope(
            subject: "Application Status Update: {$statusText} - {$positionTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.hiring-application-status-update',
            with: [
                'application' => $this->application,
                'position' => $this->position,
                'status' => $this->status,
                'statusMessage' => $this->statusMessage,
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
