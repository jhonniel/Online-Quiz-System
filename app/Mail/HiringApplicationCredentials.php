<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

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
        $this->loginUrl = url('/login');
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
            view: 'emails.hiring-application-credentials',
            with: [
                'application' => $this->application,
                'position' => $this->position,
                'email' => $this->email,
                'password' => $this->password,
                'interviewDate' => $this->interviewDate,
                'loginUrl' => $this->loginUrl,
                'torPdfUrl' => url('/tor-pdf'),
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
        $attachments = [];

        // Check if employment type is Internship and attach TOR PDF
        if ($this->position && strcasecmp($this->position->employment_type ?? '', 'Internship') === 0) {
            $torPdfPath = Setting::get('hiring_tor_pdf');

            if ($torPdfPath) {
                try {
                    $storage = Storage::disk('digitalocean');

                    if ($storage->exists($torPdfPath)) {
                        // Get the file name
                        $fileName = basename($torPdfPath);
                        if (empty($fileName) || $fileName === $torPdfPath) {
                            $fileName = 'TOR.pdf';
                        }

                        // Create attachment from data using closure
                        $attachments[] = Attachment::fromData(
                            fn () => $storage->get($torPdfPath),
                            $fileName
                        )->withMime('application/pdf');
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to attach TOR PDF to email', [
                        'error' => $e->getMessage(),
                        'tor_path' => $torPdfPath,
                        'application_id' => $this->application->id ?? null
                    ]);
                }
            }
        }

        return $attachments;
    }
}
