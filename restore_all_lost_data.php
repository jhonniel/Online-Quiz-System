<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\QuizAttemptHistory;
use App\Models\QuizAttempt;
use App\Models\Question;

echo "Starting comprehensive restoration of ALL lost data...\n";

// Get today's history records
$today = now()->startOfDay();
$history = QuizAttemptHistory::where('created_at', '>=', $today)->whereNotNull('answers')->get();

echo "Found " . $history->count() . " history records from today\n";

$restored = 0;
$skipped = 0;
$mapped = 0;
$allAnswers = [];

// First, collect ALL answers from today's history
foreach($history as $h) {
    $answers = is_string($h->answers) ? json_decode($h->answers, true) : $h->answers;

    if($answers && is_array($answers)) {
        foreach($answers as $answer) {
            if(isset($answer['user_answer']) && !empty($answer['user_answer']) && isset($answer['question_id'])) {
                $allAnswers[] = [
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

echo "Collected " . count($allAnswers) . " total answers from today's history\n";

// Group answers by user and quiz
$groupedAnswers = [];
foreach($allAnswers as $answer) {
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

// Now restore ALL the answers
foreach($groupedAnswers as $group) {
    // Get all current questions in this quiz
    $currentQuestions = Question::where('quiz_id', $group['quiz_id'])
        ->orderBy('id')
        ->get();

    if($currentQuestions->count() > 0) {
        echo "Quiz '{$group['quiz_title']}' has {$currentQuestions->count()} questions\n";

        // Map old answers to current questions
        $questionIndex = 0;
        foreach($group['answers'] as $answer) {
            // Use the next available question
            if($questionIndex < $currentQuestions->count()) {
                $currentQuestion = $currentQuestions[$questionIndex];

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
                    echo "Restored: {$group['user_name']} - Q{$currentQuestion->id} ({$currentQuestion->question_type}) - Answer: " . substr($answer['user_answer'], 0, 30) . "...\n";
                } else {
                    $skipped++;
                }

                $questionIndex++;
            }
        }
    } else {
        echo "No questions found in quiz '{$group['quiz_title']}'\n";
    }
}

echo "\nRestoration complete!\n";
echo "Restored: $restored answers\n";
echo "Skipped: $skipped (already exist)\n";

// Check final counts by question type
$textCount = QuizAttempt::whereHas('question', function($q) {
    $q->where('question_type', 'text');
})->count();

$multipleChoiceCount = QuizAttempt::whereHas('question', function($q) {
    $q->where('question_type', 'multiple_choice');
})->count();

$trueFalseCount = QuizAttempt::whereHas('question', function($q) {
    $q->where('question_type', 'true_false');
})->count();

echo "Total attempts by type:\n";
echo "- Text: $textCount\n";
echo "- Multiple Choice: $multipleChoiceCount\n";
echo "- True/False: $trueFalseCount\n";

// Show today's attempts
$todayAttempts = QuizAttempt::where('created_at', '>=', $today)->get();
echo "\nToday's total attempts: " . $todayAttempts->count() . "\n";
