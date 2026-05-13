<?php

namespace App\Mail;

use App\Models\HiringApplication;
use App\Models\Quiz;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InternQuizAssignment extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public HiringApplication $application,
        public User $user,
        public Quiz $quiz,
        public ?Carbon $dueDate = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Quiz assigned: '.$this->quiz->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.intern-quiz-assignment',
            with: [
                'application' => $this->application,
                'user' => $this->user,
                'quiz' => $this->quiz,
                'dueDate' => $this->dueDate,
                'loginUrl' => url('/login'),
                'quizzesUrl' => url('/quizzes'),
            ],
        );
    }
}
