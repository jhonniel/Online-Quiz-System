<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentOjtCompletedCongratulationsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Carbon $requirementMetAt,
        public int $graceDays
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations on completing your OJT hours — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-ojt-completed-congratulations',
            with: [
                'user' => $this->user,
                'requirementMetAt' => $this->requirementMetAt,
                'graceDays' => $this->graceDays,
                'accessEndDate' => $this->requirementMetAt->copy()->addDays($this->graceDays),
                'appName' => config('app.name'),
            ],
        );
    }
}
