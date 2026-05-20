<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'description',
        'quiz_code',
        'time_limit',
        'topic',
        'total_questions',
        'questions_to_show',
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

    /**
     * Rank quizzes by student performance using attempt history scores
     * (per-student best attempt, includes manual grading via history.score).
     */
    public static function performanceRanking(bool $activeOnly = false, ?int $limit = null): Collection
    {
        $quizzes = static::query()
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->with([
                'questions:id,quiz_id,points',
                'attemptHistory' => fn ($q) => $q
                    ->select('id', 'quiz_id', 'user_id', 'score', 'status')
                    ->scorable(),
            ])
            ->get();

        $ranked = $quizzes
            ->map(function (Quiz $quiz) {
                $bestPerUser = $quiz->attemptHistory
                    ->groupBy('user_id')
                    ->map(fn ($attempts) => (int) $attempts->max('score'));

                $maxPoints = (int) $quiz->questions->sum('points');

                $quiz->student_count = $bestPerUser->count();
                $quiz->average_score = $bestPerUser->isNotEmpty()
                    ? round($bestPerUser->avg(), 1)
                    : 0;
                $quiz->highest_score = $bestPerUser->isNotEmpty()
                    ? (int) $bestPerUser->max()
                    : 0;
                $quiz->max_points = $maxPoints;
                $quiz->average_percent = ($maxPoints > 0 && $bestPerUser->isNotEmpty())
                    ? round($quiz->average_score / $maxPoints * 100, 1)
                    : 0;

                return $quiz;
            })
            ->filter(fn (Quiz $quiz) => $quiz->student_count > 0)
            ->sortByDesc(fn (Quiz $quiz) => $quiz->average_score)
            ->values();

        if ($limit !== null) {
            return $ranked->take($limit);
        }

        return $ranked;
    }
}
