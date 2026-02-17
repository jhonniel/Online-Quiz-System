<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'question_type',
        'points',
        'order',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'alternative_answer_1',
        'alternative_answer_2',
        'alternative_answer_3',
        'requires_manual_grading',
    ];

    // Relationships
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // Helper methods
    public function getCorrectAnswers()
    {
        return $this->answers()->where('is_correct', true)->get();
    }
}
