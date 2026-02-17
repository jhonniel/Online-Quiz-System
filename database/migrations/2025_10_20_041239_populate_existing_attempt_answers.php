<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\QuizAttemptHistory;
use App\Models\QuizAttempt;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Get all quiz attempt history records that have empty answers
        $query = QuizAttemptHistory::whereNull('answers');
        
        if ($driver === 'pgsql') {
            // PostgreSQL: Cast JSON to text for comparison
            $query->orWhereRaw("answers::text = '[]'");
        } else {
            // MySQL/SQLite
            $query->orWhere('answers', '[]');
        }
        
        $attemptHistories = $query->get();

        foreach ($attemptHistories as $attemptHistory) {
            // Get the corresponding QuizAttempt records for this attempt
            $quizAttempts = QuizAttempt::where('quiz_id', $attemptHistory->quiz_id)
                ->where('user_id', $attemptHistory->user_id)
                ->whereBetween('created_at', [
                    $attemptHistory->started_at->subMinutes(10),
                    $attemptHistory->completed_at->addMinutes(10)
                ])
                ->get();

            if ($quizAttempts->count() > 0) {
                $answers = $quizAttempts->map(function($attempt) {
                    return [
                        'question_id' => $attempt->question_id,
                        'user_answer' => $attempt->user_answer,
                        'is_correct' => $attempt->is_correct,
                        'points_earned' => $attempt->points_earned,
                    ];
                })->toArray();

                $attemptHistory->update(['answers' => $answers]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration
    }
};
