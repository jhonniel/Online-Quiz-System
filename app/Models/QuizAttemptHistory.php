<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttemptHistory extends Model
{
    use HasFactory;

    protected $table = 'quiz_attempt_history';

    protected $fillable = [
        'quiz_id',
        'user_id',
        'attempt_number',
        'score',
        'total_questions',
        'correct_answers',
        'time_taken_seconds',
        'started_at',
        'completed_at',
        'status',
        'answers',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers' => 'array',
    ];

    /**
     * Ensure answers is always stored as JSON string (avoids "Array to string conversion" on SQLite).
     */
    public function setAnswersAttribute($value): void
    {
        $this->attributes['answers'] = is_array($value) ? json_encode($value) : $value;
    }

    /** Statuses that count toward analytics (includes partial scores updated after manual grading). */
    public const SCORABLE_STATUSES = ['completed', 'partial', 'time_expired'];

    public function scopeScorable($query)
    {
        return $query->whereIn('status', self::SCORABLE_STATUSES);
    }

    // Relationships
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getPercentageAttribute()
    {
        if ($this->total_questions === 0) {
            return 0;
        }

        return round(($this->correct_answers / $this->total_questions) * 100, 2);
    }

    public function getTimeTakenFormattedAttribute()
    {
        if (!$this->time_taken_seconds) {
            return 'N/A';
        }

        $minutes = floor($this->time_taken_seconds / 60);
        $seconds = $this->time_taken_seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'completed' => 'bg-green-100 text-green-800',
            'partial' => 'bg-yellow-100 text-yellow-800',
            'time_expired' => 'bg-yellow-100 text-yellow-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusText()
    {
        return match($this->status) {
            'completed' => 'Completed',
            'partial' => 'Pending Review',
            'time_expired' => 'Time Expired',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }
}
