<?php

namespace App\Mail;

use App\Models\HiringApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class InternQuizAssignment extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\Quiz>  $quizzes
     */
    public function __construct(
        public HiringApplication $application,
        public User $user,
        public Collection $quizzes,
        public ?Carbon $dueDate = null,
    ) {
        $this->quizzes = $quizzes->values();
    }

    public function envelope(): Envelope
    {
        $n = $this->quizzes->count();
        if ($n === 0) {
            return new Envelope(subject: 'Quiz assignment');
        }
        if ($n === 1) {
            $quiz = $this->quizzes->first();

            return new Envelope(
                subject: 'Quiz assigned: '.$quiz->title,
            );
        }

        $preview = $this->quizzes->take(2)->pluck('title')->implode(', ');
        $suffix = $n > 2 ? ', …' : '';

        return new Envelope(
            subject: 'Quizzes assigned ('.$n.'): '.$preview.$suffix,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.intern-quiz-assignment',
            with: [
                'application' => $this->application,
                'user' => $this->user,
                'quizzes' => $this->quizzes,
                'dueDate' => $this->dueDate,
                'loginUrl' => url('/login'),
                'quizzesUrl' => url('/quizzes'),
            ],
        );
    }
}
