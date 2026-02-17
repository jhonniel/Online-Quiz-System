<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\QuizAttemptHistory;
use App\Models\QuizAttempt;
use App\Models\Question;

echo "Starting comprehensive restoration of today's text answers...\n";

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
$textAnswers = [];

// First, collect all text answers from today's history
foreach($history as $h) {
    $answers = is_string($h->answers) ? json_decode($h->answers, true) : $h->answers;

    if($answers && is_array($answers)) {
        foreach($answers as $answer) {
            if(isset($answer['user_answer']) && !empty($answer['user_answer']) && isset($answer['question_id'])) {
                $textAnswers[] = [
                    'history_id' => $h->id,
                    'user_id' => $h->user_id,
                    'quiz_id' => $h->quiz_id,
                    'question_id' => $answer['question_id'],
                    'user_answer' => $answer['user_answer'],
                    'is_correct' => $answer['is_correct'] ?? false,
                    'points_earned' => $answer['points_earned'] ?? 0,
                    'started_at' => $h->started_at,
                    'completed_at' => $h->completed_at,
                    'user_name' => $h->user->name,
                    'quiz_title' => $h->quiz->title
                ];
            }
        }
    }
}

echo "Collected " . count($textAnswers) . " text answers from today's history\n";

// Group text answers by user and quiz
$groupedAnswers = [];
foreach($textAnswers as $answer) {
    $key = $answer['user_id'] . '_' . $answer['quiz_id'];
    if(!isset($groupedAnswers[$key])) {
        $groupedAnswers[$key] = [
            'user_id' => $answer['user_id'],
            'quiz_id' => $answer['quiz_id'],
            'user_name' => $answer['user_name'],
            'quiz_title' => $answer['quiz_title'],
            'started_at' => $answer['started_at'],
            'completed_at' => $answer['completed_at'],
            'answers' => []
        ];
    }
    $groupedAnswers[$key]['answers'][] = $answer;
}

echo "Grouped into " . count($groupedAnswers) . " user-quiz combinations\n";

// Now restore the text answers
foreach($groupedAnswers as $group) {
    // Find current text questions in this quiz
    $currentTextQuestions = Question::where('quiz_id', $group['quiz_id'])
        ->where('question_type', 'text')
        ->orderBy('id')
        ->get();

    if($currentTextQuestions->count() > 0) {
        echo "Quiz '{$group['quiz_title']}' has {$currentTextQuestions->count()} text questions\n";

        // Map old answers to current text questions
        $questionIndex = 0;
        foreach($group['answers'] as $answer) {
            // Skip non-text answers (A, B, C, D, etc.)
            if(strlen($answer['user_answer']) <= 2 && preg_match('/^[A-D]$/', $answer['user_answer'])) {
                continue;
            }

            // Use the next available text question
            if($questionIndex < $currentTextQuestions->count()) {
                $currentQuestion = $currentTextQuestions[$questionIndex];

                // Check if this answer already exists
                $existing = QuizAttempt::where('question_id', $currentQuestion->id)
                    ->where('user_id', $group['user_id'])
                    ->where('quiz_id', $group['quiz_id'])
                    ->first();

                if(!$existing) {
                    // Create new attempt record
                    QuizAttempt::create([
                        'quiz_id' => $group['quiz_id'],
                        'question_id' => $currentQuestion->id,
                        'user_id' => $group['user_id'],
                        'user_answer' => $answer['user_answer'],
                        'is_correct' => $answer['is_correct'],
                        'points_earned' => $answer['points_earned'],
                        'graded_at' => $group['completed_at'],
                        'graded_by' => 1,
                        'started_at' => $group['started_at'],
                        'created_at' => $group['started_at'],
                        'updated_at' => $group['completed_at']
                    ]);
                    $restored++;
                    echo "Restored: {$group['user_name']} - Answer: " . substr($answer['user_answer'], 0, 50) . "...\n";
                } else {
                    $skipped++;
                }

                $questionIndex++;
            }
        }
    } else {
        echo "No text questions found in quiz '{$group['quiz_title']}'\n";
    }
}

echo "\nRestoration complete!\n";
echo "Restored: $restored text answers\n";
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
