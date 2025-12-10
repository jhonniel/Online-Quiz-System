<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestStatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveRequest;
    public $status;
    public $adminNotes;

    /**
     * Create a new message instance.
     */
    public function __construct(LeaveRequest $leaveRequest, string $status, ?string $adminNotes = null)
    {
        $this->leaveRequest = $leaveRequest;
        $this->status = $status;
        $this->adminNotes = $adminNotes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $statusLabel = ucfirst($this->status);
        if ($this->status === 'resubmission_requested' || ($this->status === 'pending' && $this->leaveRequest->reviewed_at)) {
            $statusLabel = 'Resubmission Required';
        } elseif ($this->status === 'approved') {
            $statusLabel = 'Approved';
        } elseif ($this->status === 'rejected') {
            $statusLabel = 'Rejected';
        }

        return new Envelope(
            subject: 'Leave Request ' . $statusLabel . ' - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave-request-status-update',
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
