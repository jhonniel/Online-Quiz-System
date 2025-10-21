<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'assigned_at',
        'started_at',
        'due_date',
        'is_completed',
        'status',
        'attempt_count',
        'total_score',
        'best_score',
        'last_attempt_at',
        'can_retake',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'due_date' => 'datetime',
            'is_completed' => 'boolean',
            'last_attempt_at' => 'datetime',
            'can_retake' => 'boolean',
        ];
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

    public function attemptHistory()
    {
        return QuizAttemptHistory::where('quiz_id', $this->quiz_id)
                                ->where('user_id', $this->user_id)
                                ->orderBy('attempt_number', 'desc');
    }

    // Helper methods for timer calculations
    public function getRemainingTimeAttribute()
    {
        if (!$this->quiz->time_limit || !$this->started_at) {
            return null;
        }

        $elapsedSeconds = now()->timestamp - $this->started_at->timestamp;
        $totalSeconds = $this->quiz->time_limit * 60;

        return max(0, $totalSeconds - $elapsedSeconds);
    }

    public function isTimeExpired()
    {
        if (!$this->quiz->time_limit || !$this->started_at) {
            return false;
        }

        $elapsedSeconds = now()->timestamp - $this->started_at->timestamp;
        $totalSeconds = $this->quiz->time_limit * 60;

        return $elapsedSeconds >= $totalSeconds;
    }

    public function getElapsedTimeAttribute()
    {
        if (!$this->started_at) {
            return 0;
        }

        return now()->timestamp - $this->started_at->timestamp;
    }

    // Status helper methods
    public function isInProgress()
    {
        return $this->status === 'in_progress' && $this->started_at && !$this->is_completed;
    }

    public function isOngoing()
    {
        return $this->isInProgress() && !$this->isTimeExpired();
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'assigned' => 'bg-gray-100 text-gray-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getStatusText()
    {
        return match($this->status) {
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    // New helper methods for attempt tracking
    public function canRetake()
    {
        return $this->can_retake || $this->status === 'cancelled' || $this->isTimeExpired();
    }

    public function resetForRetake()
    {
        $this->update([
            'status' => 'assigned',
            'started_at' => null,
            'is_completed' => false,
            'can_retake' => false,
        ]);
    }

    public function getAverageScore()
    {
        if ($this->attempt_count === 0) {
            return 0;
        }

        return round($this->total_score / $this->attempt_count, 2);
    }

    public function getBestScorePercentage()
    {
        if (!$this->best_score || !$this->quiz->total_questions) {
            return 0;
        }

        return round(($this->best_score / $this->quiz->total_questions) * 100, 2);
    }
}
