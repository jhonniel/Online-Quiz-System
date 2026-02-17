<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\QuizAttemptHistory;
use App\Models\QuizAttempt;
use App\Models\Question;

echo "Starting restoration of today's text answers...\n";

// Get today's history records with text answers
$today = now()->startOfDay();
$history = QuizAttemptHistory::whereHas('quiz', function($q) {
    $q->whereHas('questions', function($qq) {
        $qq->where('question_type', 'text');
    });
})->where('created_at', '>=', $today)->whereNotNull('answers')->get();

echo "Found " . $history->count() . " history records from today with text answers\n";

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
                    // Original question doesn't exist, try to find a text question in the same quiz
                    $textQuestion = Question::where('quiz_id', $h->quiz_id)
                        ->where('question_type', 'text')
                        ->first();

                    if($textQuestion) {
                        // Check if answer already exists
                        $existing = QuizAttempt::where('question_id', $textQuestion->id)
                            ->where('user_id', $h->user_id)
                            ->where('quiz_id', $h->quiz_id)
                            ->first();

                        if(!$existing) {
                            // Create new attempt record with mapped answer
                            QuizAttempt::create([
                                'quiz_id' => $h->quiz_id,
                                'question_id' => $textQuestion->id,
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
                            echo "Mapped: User {$h->user->name} - Old Q{$answer['question_id']} -> New Q{$textQuestion->id} - Answer: " . substr($answer['user_answer'], 0, 50) . "...\n";
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

echo "\nRestoration complete!\n";
echo "Restored: $restored text answers (original questions)\n";
echo "Mapped: $mapped text answers (to current text questions)\n";
echo "Skipped: $skipped (already exist)\n";

// Check final count
$finalCount = QuizAttempt::whereHas('question', function($q) {
    $q->where('question_type', 'text');
})->count();

echo "Total text attempts now: $finalCount\n";

// Show today's text attempts
$todayAttempts = QuizAttempt::whereHas('question', function($q) {
    $q->where('question_type', 'text');
})->where('created_at', '>=', $today)->get();

echo "\nToday's text attempts:\n";
foreach($todayAttempts as $attempt) {
    echo "- User: {$attempt->user->name} - Answer: " . substr($attempt->user_answer, 0, 50) . "... - Graded: " . ($attempt->graded_at ? 'Yes' : 'No') . "\n";
}
