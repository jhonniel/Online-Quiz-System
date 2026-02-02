<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function assignments()
    {
        return $this->hasMany(QuizAssignment::class);
    }

    public function attemptHistory()
    {
        return $this->hasMany(QuizAttemptHistory::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
