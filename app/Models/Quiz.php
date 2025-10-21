<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'topic',
        'quiz_code',
        'time_limit',
        'total_questions',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function assignments()
    {
        return $this->hasMany(QuizAssignment::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function attemptHistory()
    {
        return $this->hasMany(QuizAttemptHistory::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'quiz_assignments')
                    ->withPivot(['assigned_at', 'due_date', 'is_completed'])
                    ->withTimestamps();
    }

    // Helper methods
    public function generateQuizCode()
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        } while (self::where('quiz_code', $code)->exists());

        return $code;
    }

    public function getTotalPoints()
    {
        return $this->questions()->sum('points');
    }
}
