<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskListInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $taskListName;
    public $inviteCode;
    public $shareLink;
    public $inviterName;

    /**
     * Create a new message instance.
     */
    public function __construct($taskListName, $inviteCode, $shareLink, $inviterName)
    {
        $this->taskListName = $taskListName;
        $this->inviteCode = $inviteCode;
        $this->shareLink = $shareLink;
        $this->inviterName = $inviterName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Task List Invitation: {$this->taskListName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.task-list-invitation',
            with: [
                'taskListName' => $this->taskListName,
                'inviteCode' => $this->inviteCode,
                'shareLink' => $this->shareLink,
                'inviterName' => $this->inviterName,
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
