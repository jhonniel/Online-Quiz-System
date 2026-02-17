<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\QuizAttemptHistory;
use App\Models\QuizAttempt;
use App\Models\Question;

echo "Fixing text answers mapping...\n";

// Get today's history records
$today = now()->startOfDay();
$history = QuizAttemptHistory::where('created_at', '>=', $today)->whereNotNull('answers')->get();

echo "Found " . $history->count() . " history records from today\n";

// First, delete the incorrectly mapped text attempts
$incorrectTextAttempts = QuizAttempt::whereHas('question', function($q) {
    $q->where('question_type', 'text');
})->where('created_at', '>=', $today)
->where('user_answer', 'B')
->get();

echo "Deleting " . $incorrectTextAttempts->count() . " incorrectly mapped text attempts\n";
foreach($incorrectTextAttempts as $attempt) {
    $attempt->delete();
    echo "Deleted attempt ID: " . $attempt->id . " for user: " . $attempt->user->name . "\n";
}

// Now properly restore text answers
$restored = 0;

foreach($history as $h) {
    $answers = is_string($h->answers) ? json_decode($h->answers, true) : $h->answers;

    if($answers && is_array($answers)) {
        // Get current text questions for this quiz
        $textQuestions = Question::where('quiz_id', $h->quiz_id)
            ->where('question_type', 'text')
            ->orderBy('id')
            ->get();

        if($textQuestions->count() > 0) {
            echo "Quiz '{$h->quiz->title}' has {$textQuestions->count()} text questions\n";

            $textQuestionIndex = 0;
            foreach($answers as $answer) {
                if(isset($answer['user_answer']) && !empty($answer['user_answer']) && isset($answer['question_id'])) {
                    // Check if this is a text answer (not a single letter)
                    if(strlen($answer['user_answer']) > 2 || !preg_match('/^[A-D]$/', $answer['user_answer'])) {
                        // This is a text answer
                        if($textQuestionIndex < $textQuestions->count()) {
                            $textQuestion = $textQuestions[$textQuestionIndex];

                            // Check if this answer already exists
                            $existing = QuizAttempt::where('question_id', $textQuestion->id)
                                ->where('user_id', $h->user_id)
                                ->where('quiz_id', $h->quiz_id)
                                ->first();

                            if(!$existing) {
                                // Create new text attempt record
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
                                $restored++;
                                echo "Restored text answer: {$h->user->name} - Answer: " . substr($answer['user_answer'], 0, 50) . "...\n";
                            }

                            $textQuestionIndex++;
                        }
                    }
                }
            }
        }
    }
}

echo "\nText answers restoration complete!\n";
echo "Restored: $restored text answers\n";

// Check final text attempts
$textAttempts = QuizAttempt::whereHas('question', function($q) {
    $q->where('question_type', 'text');
})->where('created_at', '>=', $today)->get();

echo "\nFinal text attempts:\n";
foreach($textAttempts as $attempt) {
    echo "- User: {$attempt->user->name} - Answer: " . substr($attempt->user_answer, 0, 50) . "... - Question: " . substr($attempt->question->question_text, 0, 30) . "...\n";
}
