<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HiringApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $position;

    /**
     * Create a new message instance.
     */
    public function __construct($application, $position = null)
    {
        $this->application = $application;
        $this->position = $position;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $positionTitle = $this->position ? $this->position->title : ($this->application->position_applied ?? 'Position');
        return new Envelope(
            subject: "New Hiring Application: {$this->application->first_name} {$this->application->last_name} - {$positionTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.hiring-application-received',
            with: [
                'application' => $this->application,
                'position' => $this->position,
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
