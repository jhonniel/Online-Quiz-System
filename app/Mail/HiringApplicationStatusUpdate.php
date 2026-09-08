<?php

namespace App\Mail;

use App\Support\EmployeeDocumentMaterial;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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
        $subject = match ($this->status) {
            'rejected' => 'Application Update',
            'hired' => $this->isInternshipHire()
                ? 'Internship Application Accepted'
                : 'Welcome to the Team',
            default => 'Application Status Update: '.ucfirst($this->status)." - {$positionTitle}",
        };

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
            view: 'emails.hiring-application-status-update',
            with: [
                'application' => $this->application,
                'position' => $this->position,
                'status' => $this->status,
                'statusMessage' => $this->statusMessage,
                'isInternshipHire' => $this->isInternshipHire(),
                'isEmployeeHire' => $this->isEmployeeHire(),
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
        if (! $this->isEmployeeHire()) {
            return [];
        }

        return array_merge(
            EmployeeDocumentMaterial::mailAttachmentsFromItems(
                'policy',
                EmployeeDocumentMaterial::hiredEmailPolicyMaterials()
            ),
            EmployeeDocumentMaterial::mailAttachmentsFromItems(
                'handbook',
                EmployeeDocumentMaterial::hiredEmailHandbookMaterials()
            )
        );
    }

    private function isInternshipHire(): bool
    {
        if ($this->status !== 'hired') {
            return false;
        }

        return strcasecmp($this->resolvedEmploymentType(), 'Internship') === 0;
    }

    private function isEmployeeHire(): bool
    {
        return $this->status === 'hired' && ! $this->isInternshipHire();
    }

    private function resolvedEmploymentType(): string
    {
        return trim((string) (
            $this->position?->employment_type
            ?? $this->application->hiringPosition?->employment_type
            ?? ''
        ));
    }
}
