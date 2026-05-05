<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentRulesNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  'violation'|'final'  $noticeType
     */
    public function __construct(
        public User $user,
        public string $noticeType,
        public ?string $noticeMessage = null
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->noticeType === 'final'
            ? 'Final notice: rules and regulations'
            : 'Rules violation notice';

        return new Envelope(
            subject: $subject.' — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.student-rules-notice',
            with: [
                'user' => $this->user,
                'noticeType' => $this->noticeType,
                'noticeMessage' => $this->noticeMessage,
                'torUrl' => url('/tor'),
                'loginUrl' => url('/login'),
            ],
        );
    }
}
