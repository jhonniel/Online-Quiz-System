<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptHistory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users who have quiz attempts but no quiz attempt history
        $usersWithAttemptsButNoHistory = User::where('role', 'user')
            ->whereHas('quizAttempts')
            ->whereDoesntHave('quizAttemptHistory')
            ->get();

        echo "Found " . $usersWithAttemptsButNoHistory->count() . " users with quiz attempts but no history records.\n";

        foreach ($usersWithAttemptsButNoHistory as $user) {
            echo "Processing user: " . $user->name . " (ID: " . $user->id . ")\n";

            // Get all quiz attempts for this user, grouped by quiz
            $quizAttempts = QuizAttempt::where('user_id', $user->id)
                ->with('question')
                ->get()
                ->groupBy('quiz_id');

            foreach ($quizAttempts as $quizId => $attempts) {
                // Calculate total score for this quiz
                $totalScore = $attempts->sum('points_earned');
                $correctAnswers = $attempts->where('is_correct', true)->count();
                $totalQuestions = $attempts->count();

                // Get the first and last attempt times for this quiz
                $firstAttempt = $attempts->sortBy('created_at')->first();
                $lastAttempt = $attempts->sortByDesc('created_at')->first();

                // Calculate time taken (difference between first and last attempt)
                $timeTaken = null;
                if ($firstAttempt && $lastAttempt) {
                    $timeTaken = $lastAttempt->created_at->timestamp - $firstAttempt->created_at->timestamp;
                }

                // Prepare answers data
                $answersData = $attempts->map(function($attempt) {
                    return [
                        'question_id' => $attempt->question_id,
                        'user_answer' => $attempt->user_answer,
                        'is_correct' => $attempt->is_correct,
                        'points_earned' => $attempt->points_earned,
                    ];
                })->values()->toArray();

                // Create quiz attempt history record
                QuizAttemptHistory::create([
                    'quiz_id' => $quizId,
                    'user_id' => $user->id,
                    'attempt_number' => 1, // We'll treat this as attempt 1 for now
                    'score' => $totalScore,
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswers,
                    'time_taken_seconds' => $timeTaken,
                    'started_at' => $firstAttempt->created_at,
                    'completed_at' => $lastAttempt->created_at,
                    'status' => 'completed',
                    'answers' => $answersData,
                ]);

                echo "  - Created history for quiz ID: " . $quizId . " (Score: " . $totalScore . " pts)\n";
            }
        }

        echo "Migration completed successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration creates data, so we'll leave the down method empty
        // to avoid accidentally deleting user data
    }
};
