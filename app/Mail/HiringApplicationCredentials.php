<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HiringApplicationCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $position;
    public $email;
    public $password;
    public $interviewDate;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($application, $email, $password, $interviewDate = null, $position = null)
    {
        $this->application = $application;
        $this->position = $position;
        $this->email = $email;
        $this->password = $password;
        $this->interviewDate = $interviewDate;
        $this->loginUrl = route('login');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $positionTitle = $this->position ? $this->position->title : ($this->application->position_applied ?? 'Position');
        return new Envelope(
            subject: "Your Account Credentials - {$positionTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.hiring-application-credentials',
            with: [
                'application' => $this->application,
                'position' => $this->position,
                'email' => $this->email,
                'password' => $this->password,
                'interviewDate' => $this->interviewDate,
                'loginUrl' => $this->loginUrl,
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
