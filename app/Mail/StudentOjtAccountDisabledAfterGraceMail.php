<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentOjtAccountDisabledAfterGraceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your student account has been disabled — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-ojt-account-disabled-after-grace',
            with: [
                'user' => $this->user,
                'appName' => config('app.name'),
            ],
        );
    }
}
