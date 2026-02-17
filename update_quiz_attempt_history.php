<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\QuizAttemptHistory;
use App\Models\QuizAttempt;
use App\Models\Question;

echo "Updating QuizAttemptHistory with correct question IDs and answers...\n";

// Get today's history records
$today = now()->startOfDay();
$history = QuizAttemptHistory::where('created_at', '>=', $today)->whereNotNull('answers')->get();

echo "Found " . $history->count() . " history records from today\n";

$updated = 0;

foreach($history as $h) {
    $answers = is_string($h->answers) ? json_decode($h->answers, true) : $h->answers;

    if($answers && is_array($answers)) {
        // Get current questions for this quiz
        $currentQuestions = Question::where('quiz_id', $h->quiz_id)
            ->orderBy('id')
            ->get();

        if($currentQuestions->count() > 0) {
            echo "Updating history for user: {$h->user->name} - Quiz: {$h->quiz->title}\n";

            $updatedAnswers = [];
            $questionIndex = 0;

            foreach($answers as $answer) {
                if(isset($answer['user_answer']) && !empty($answer['user_answer']) && isset($answer['question_id'])) {
                    // Use the next available question
                    if($questionIndex < $currentQuestions->count()) {
                        $currentQuestion = $currentQuestions[$questionIndex];

                        // Update the answer with the current question ID
                        $updatedAnswer = $answer;
                        $updatedAnswer['question_id'] = $currentQuestion->id;
                        $updatedAnswers[] = $updatedAnswer;

                        echo "  Mapped old Q{$answer['question_id']} -> new Q{$currentQuestion->id} ({$currentQuestion->question_type})\n";
                        $questionIndex++;
                    }
                }
            }

            // Update the history record with the corrected answers
            $h->answers = $updatedAnswers;
            $h->save();

            $updated++;
            echo "  Updated " . count($updatedAnswers) . " answers\n";
        }
    }
}

echo "\nUpdate complete!\n";
echo "Updated: $updated history records\n";

// Verify the update
$history = QuizAttemptHistory::where('created_at', '>=', $today)->whereNotNull('answers')->get();
echo "\nVerification:\n";
foreach($history as $h) {
    $answers = is_string($h->answers) ? json_decode($h->answers, true) : $h->answers;
    $textAnswers = [];
    foreach($answers as $answer) {
        if(isset($answer['user_answer']) && !empty($answer['user_answer']) && strlen($answer['user_answer']) > 2) {
            $textAnswers[] = $answer;
        }
    }
    echo "User: {$h->user->name} - Text answers: " . count($textAnswers) . "\n";
}
