<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_id',
        'question_id',
        'attempt_number',
        'answer_id',
        'user_answer',
        'is_correct',
        'points_earned',
        'status',
        'started_at',
        'completed_at',
        'graded_at',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Legacy relationship to the selected answer option (for old data).
     * Newer attempts may have null answer_id and instead use user_answer.
     */
    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function isGraded(): bool
    {
        return $this->graded_at !== null;
    }
}
