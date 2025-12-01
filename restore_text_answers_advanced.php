<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\QuizAttemptHistory;
use App\Models\QuizAttempt;
use App\Models\Question;

echo "Starting advanced text answers restoration...\n";

// Get all history records with text answers
$history = QuizAttemptHistory::whereHas('quiz', function($q) {
    $q->whereHas('questions', function($qq) {
        $qq->where('question_type', 'text');
    });
})->whereNotNull('answers')->get();

echo "Found " . $history->count() . " history records with text answers\n";

$restored = 0;
$skipped = 0;
$mapped = 0;

foreach($history as $h) {
    $answers = is_string($h->answers) ? json_decode($h->answers, true) : $h->answers;

    if($answers && is_array($answers)) {
        foreach($answers as $answer) {
            if(isset($answer['user_answer']) && !empty($answer['user_answer']) && isset($answer['question_id'])) {

                // Check if the original question still exists
                $originalQuestion = Question::find($answer['question_id']);

                if($originalQuestion) {
                    // Original question exists, check if answer already exists
                    $existing = QuizAttempt::where('question_id', $answer['question_id'])
                        ->where('user_id', $h->user_id)
                        ->where('quiz_id', $h->quiz_id)
                        ->first();

                    if(!$existing) {
                        // Create new attempt record with restored answer
                        QuizAttempt::create([
                            'quiz_id' => $h->quiz_id,
                            'question_id' => $answer['question_id'],
                            'user_id' => $h->user_id,
                            'user_answer' => $answer['user_answer'],
                            'is_correct' => $answer['is_correct'] ?? false,
                            'points_earned' => $answer['points_earned'] ?? 0,
                            'graded_at' => $h->completed_at,
                            'graded_by' => 1,
                            'started_at' => $h->started_at,
                            'created_at' => $h->started_at,
                            'updated_at' => $h->completed_at
                        ]);
                        $restored++;
                        echo "Restored: User {$h->user->name} - Answer: " . substr($answer['user_answer'], 0, 50) . "...\n";
                    } else {
                        $skipped++;
                    }
                } else {
                    // Original question doesn't exist, try to find a similar text question in the same quiz
                    $similarQuestion = Question::where('quiz_id', $h->quiz_id)
                        ->where('question_type', 'text')
                        ->where('question_text', 'like', '%' . substr($answer['user_answer'], 0, 20) . '%')
                        ->first();

                    if($similarQuestion) {
                        // Found a similar question, check if answer already exists
                        $existing = QuizAttempt::where('question_id', $similarQuestion->id)
                            ->where('user_id', $h->user_id)
                            ->where('quiz_id', $h->quiz_id)
                            ->first();

                        if(!$existing) {
                            // Create new attempt record with mapped answer
                            QuizAttempt::create([
                                'quiz_id' => $h->quiz_id,
                                'question_id' => $similarQuestion->id,
                                'user_id' => $h->user_id,
                                'user_answer' => $answer['user_answer'],
                                'is_correct' => $answer['is_correct'] ?? false,
                                'points_earned' => $answer['points_earned'] ?? 0,
                                'graded_at' => $h->completed_at,
                                'graded_by' => 1,
                                'started_at' => $h->started_at,
                                'created_at' => $h->started_at,
                                'updated_at' => $h->completed_at
                            ]);
                            $mapped++;
                            echo "Mapped: User {$h->user->name} - Old Q{$answer['question_id']} -> New Q{$similarQuestion->id} - Answer: " . substr($answer['user_answer'], 0, 50) . "...\n";
                        } else {
                            $skipped++;
                        }
                    } else {
                        // Try to find any text question in the same quiz
                        $anyTextQuestion = Question::where('quiz_id', $h->quiz_id)
                            ->where('question_type', 'text')
                            ->first();

                        if($anyTextQuestion) {
                            // Check if answer already exists
                            $existing = QuizAttempt::where('question_id', $anyTextQuestion->id)
                                ->where('user_id', $h->user_id)
                                ->where('quiz_id', $h->quiz_id)
                                ->first();

                            if(!$existing) {
                                // Create new attempt record with mapped answer
                                QuizAttempt::create([
                                    'quiz_id' => $h->quiz_id,
                                    'question_id' => $anyTextQuestion->id,
                                    'user_id' => $h->user_id,
                                    'user_answer' => $answer['user_answer'],
                                    'is_correct' => $answer['is_correct'] ?? false,
                                    'points_earned' => $answer['points_earned'] ?? 0,
                                    'graded_at' => $h->completed_at,
                                    'graded_by' => 1,
                                    'started_at' => $h->started_at,
                                    'created_at' => $h->started_at,
                                    'updated_at' => $h->completed_at
                                ]);
                                $mapped++;
                                echo "Mapped to any text question: User {$h->user->name} - Old Q{$answer['question_id']} -> New Q{$anyTextQuestion->id} - Answer: " . substr($answer['user_answer'], 0, 50) . "...\n";
                            } else {
                                $skipped++;
                            }
                        } else {
                            echo "No text questions found in quiz {$h->quiz_id} for answer: " . substr($answer['user_answer'], 0, 50) . "...\n";
                        }
                    }
                }
            }
        }
    }
}

echo "\nRestoration complete!\n";
echo "Restored: $restored text answers (original questions)\n";
echo "Mapped: $mapped text answers (to similar questions)\n";
echo "Skipped: $skipped (already exist)\n";

// Check final count
$finalCount = QuizAttempt::whereHas('question', function($q) {
    $q->where('question_type', 'text');
})->count();

echo "Total text attempts now: $finalCount\n";
