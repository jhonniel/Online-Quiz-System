<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\User;
use App\Models\QuizAssignment;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptHistory;
use App\Models\Setting;
use App\Imports\QuestionsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with(['creator', 'assignments'])
            ->orderByDesc('created_at')
            ->paginate(10);
        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        try {
            // Manually load settings to debug
            $settings = [
                'system_name' => Setting::get('system_name', 'Quiz System'),
                'system_logo' => Setting::get('system_logo'),
                'system_icon' => Setting::get('system_icon'),
                'system_description' => Setting::get('system_description', 'Online Quiz Management System'),
                'primary_color' => Setting::get('primary_color', '#4F46E5'),
                'secondary_color' => Setting::get('secondary_color', '#6B7280'),
            ];

            return view('admin.quizzes.create', compact('settings'));
        } catch (\Exception $e) {
            \Log::error('Error in QuizController create method: ' . $e->getMessage());
            throw $e;
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'topic' => 'nullable|string',
            'is_active' => 'required|in:0,1',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|in:multiple_choice,true_false,text',
            'questions.*.points' => 'required|integer|min:1',
            'questions.*.option_a' => 'required_if:questions.*.question_type,multiple_choice|nullable|string',
            'questions.*.option_b' => 'required_if:questions.*.question_type,multiple_choice|nullable|string',
            'questions.*.option_c' => 'required_if:questions.*.question_type,multiple_choice|nullable|string',
            'questions.*.option_d' => 'required_if:questions.*.question_type,multiple_choice|nullable|string',
            'questions.*.correct_answer' => 'required_if:questions.*.question_type,multiple_choice|nullable|in:A,B,C,D',
        ]);

        $quiz = Quiz::create([
            'title' => $request->title,
            'description' => $request->description,
            'quiz_code' => $this->generateQuizCode(),
            'time_limit' => $request->time_limit,
            'topic' => $request->topic,
            'is_active' => (bool) $request->is_active,
            'total_questions' => count($request->questions),
            'created_by' => auth()->id(),
        ]);

        foreach ($request->questions as $index => $questionData) {
            Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'points' => $questionData['points'],
                'option_a' => $questionData['option_a'] ?? null,
                'option_b' => $questionData['option_b'] ?? null,
                'option_c' => $questionData['option_c'] ?? null,
                'option_d' => $questionData['option_d'] ?? null,
                'correct_answer' => $questionData['correct_answer'] ?? null,
                'alternative_answer_1' => $questionData['alternative_answer_1'] ?? null,
                'alternative_answer_2' => $questionData['alternative_answer_2'] ?? null,
                'alternative_answer_3' => $questionData['alternative_answer_3'] ?? null,
                'requires_manual_grading' => $questionData['question_type'] === 'fill_blank' || $questionData['question_type'] === 'text' || ($questionData['requires_manual_grading'] ?? false),
                'order' => $index + 1,
            ]);
        }

        return redirect()->route('quizzes.index')
            ->with('success', 'Quiz created successfully.');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load(['questions', 'creator']);
        return view('admin.quizzes.show', compact('quiz'));
    }

    public function edit(Quiz $quiz)
    {
        $quiz->load(['questions']);
        return view('admin.quizzes.edit', compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'topic' => 'nullable|string',
            'is_active' => 'required|in:0,1',
            'questions' => 'nullable|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.question_type' => 'required|in:multiple_choice,true_false,text',
            'questions.*.points' => 'required|integer|min:1',
            'questions.*.option_a' => 'required_if:questions.*.question_type,multiple_choice|nullable|string',
            'questions.*.option_b' => 'required_if:questions.*.question_type,multiple_choice|nullable|string',
            'questions.*.option_c' => 'required_if:questions.*.question_type,multiple_choice|nullable|string',
            'questions.*.option_d' => 'required_if:questions.*.question_type,multiple_choice|nullable|string',
            'questions.*.correct_answer' => 'required_if:questions.*.question_type,multiple_choice|nullable|in:A,B,C,D',
        ]);

        $quiz->update([
            'title' => $request->title,
            'description' => $request->description,
            'time_limit' => $request->time_limit,
            'topic' => $request->topic,
            'is_active' => (bool) $request->is_active,
        ]);

        // Update questions if provided
        if ($request->has('questions')) {
            // Delete existing questions
            $quiz->questions()->delete();

            // Create new questions
            foreach ($request->questions as $index => $questionData) {
                $quiz->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'points' => $questionData['points'],
                    'option_a' => $questionData['option_a'] ?? null,
                    'option_b' => $questionData['option_b'] ?? null,
                    'option_c' => $questionData['option_c'] ?? null,
                    'option_d' => $questionData['option_d'] ?? null,
                    'correct_answer' => $questionData['correct_answer'] ?? null,
                    'alternative_answer_1' => $questionData['alternative_answer_1'] ?? null,
                    'alternative_answer_2' => $questionData['alternative_answer_2'] ?? null,
                    'alternative_answer_3' => $questionData['alternative_answer_3'] ?? null,
                    'requires_manual_grading' => $questionData['question_type'] === 'fill_blank' || $questionData['question_type'] === 'text' || ($questionData['requires_manual_grading'] ?? false),
                    'order' => $index + 1,
                ]);
            }

            // Update total questions count
            $quiz->update(['total_questions' => count($request->questions)]);
        }

            return redirect()->route('quizzes.index')
                ->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('quizzes.index')
            ->with('success', 'Quiz deleted successfully.');
    }

    public function assignToUsers(Request $request, Quiz $quiz)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'due_date' => 'nullable|date|after:now',
        ]);

        // Get currently assigned user IDs
        $currentlyAssigned = $quiz->assignments()->pluck('user_id')->toArray();

        // Users to assign (new assignments)
        $usersToAssign = $request->user_ids;

        // Users to remove (currently assigned but not in new list)
        $usersToRemove = array_diff($currentlyAssigned, $usersToAssign);

        // Remove users who are no longer assigned
        if (!empty($usersToRemove)) {
            $quiz->assignments()->whereIn('user_id', $usersToRemove)->delete();
        }

        // Add or update assignments for selected users
        foreach ($usersToAssign as $userId) {
            QuizAssignment::updateOrCreate(
                ['quiz_id' => $quiz->id, 'user_id' => $userId],
                ['due_date' => $request->due_date]
            );
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quiz assignments updated successfully.'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Quiz assignments updated successfully.');
    }

    public function getAssignedUsers(Quiz $quiz)
    {
        $assignments = $quiz->assignments()
            ->with(['user.university'])
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'status' => $assignment->status,
                    'due_date' => $assignment->due_date ? $assignment->due_date->format('M d, Y H:i') : null,
                    'completed_at' => $assignment->completed_at ? $assignment->completed_at->format('M d, Y H:i') : null,
                    'user' => [
                        'id' => $assignment->user->id,
                        'name' => $assignment->user->name,
                        'email' => $assignment->user->email,
                        'profile_picture' => $assignment->user->profile_picture,
                        'profile_picture_url' => $assignment->user->getProfilePictureUrl(),
                        'university' => $assignment->user->university ? [
                            'id' => $assignment->user->university->id,
                            'name' => $assignment->user->university->name,
                        ] : null,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'assignments' => $assignments,
        ]);
    }

    public function results(Quiz $quiz)
    {
        $attempts = $quiz->attempts()
            ->with(['user', 'question', 'answer'])
            ->get()
            ->groupBy('user_id');

        return view('admin.quizzes.results', compact('quiz', 'attempts'));
    }

    public function exportQuizHistoryPdf(Quiz $quiz)
    {
        $quiz->load(['assignments.user', 'assignments.attemptHistory']);

        $rows = $quiz->assignments->map(function ($assignment) use ($quiz) {
            $attempts = $assignment->attemptHistory;
            $attemptCount = $attempts->count();
            $avgCorrect = $attemptCount > 0 ? round($attempts->avg('correct_answers'), 2) : 0;
            $avgPercent = ($attemptCount > 0 && $quiz->total_questions) ? round(($avgCorrect / $quiz->total_questions) * 100, 2) : 0;
            $totalScore = (int) $attempts->sum('correct_answers');
            return [
                'user_name' => $assignment->user->name,
                'user_email' => $assignment->user->email,
                'attempt_count' => $attemptCount,
                'best_score' => (int) ($assignment->best_score ?? 0),
                'total_score' => $totalScore,
                'avg_correct' => $avgCorrect,
                'avg_percent' => $avgPercent,
                'last_attempt_at' => $assignment->last_attempt_at,
            ];
        });

        $data = [
            'quiz' => $quiz,
            'rows' => $rows,
        ];

        $pdf = Pdf::loadView('admin.quizzes.quiz-history-pdf', $data)->setPaper('a4', 'portrait');
        $filename = 'quiz_history_' . $quiz->id . '.pdf';
        return $pdf->download($filename);
    }

    public function importQuestions(Request $request, Quiz $quiz)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            // Import questions from Excel
            $import = new QuestionsImport($quiz->id);
            Excel::import($import, $request->file('excel_file'));

            // Update total questions count
            $totalQuestions = $quiz->questions()->count();
            $quiz->update(['total_questions' => $totalQuestions]);

            return redirect()->route('quizzes.show', $quiz)
                ->with('success', "Successfully imported questions! Total questions: {$totalQuestions}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Import failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function downloadTemplate()
    {
        $templateData = [
            ['Question Text', 'Question Type', 'Points', 'Answer 1', 'Answer 2', 'Answer 3', 'Answer 4', 'Correct Answer'],
            ['What is the capital of France?', 'multiple_choice', '10', 'London', 'Berlin', 'Paris', 'Madrid', '3'],
            ['PHP is a server-side language.', 'true_false', '5', 'True', 'False', '', '', '1'],
            ['Explain the concept of OOP.', 'text', '15', '', '', '', '', ''],
        ];

        $filename = 'quiz_questions_template.xlsx';

        return Excel::download(new class($templateData) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;

            public function __construct($data) {
                $this->data = $data;
            }

            public function array(): array {
                return $this->data;
            }
        }, $filename);
    }

    public function importForm()
    {
        return view('admin.quizzes.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'quiz_title' => 'required|string|max:255',
            'quiz_description' => 'nullable|string',
            'time_limit' => 'nullable|integer|min:1',
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            // Create the quiz first
            $quiz = Quiz::create([
                'title' => $request->quiz_title,
                'description' => $request->quiz_description,
                'quiz_code' => $this->generateQuizCode(),
                'time_limit' => $request->time_limit,
                'total_questions' => 0, // Will be updated after import
                'created_by' => auth()->id(),
            ]);

            // Import questions from Excel
            $import = new QuestionsImport($quiz->id);
            Excel::import($import, $request->file('excel_file'));

            // Update total questions count
            $totalQuestions = $quiz->questions()->count();
            $quiz->update(['total_questions' => $totalQuestions]);

            return redirect()->route('quizzes.index')
                ->with('success', "Quiz '{$quiz->title}' created successfully with {$totalQuestions} questions imported from Excel!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing quiz: ' . $e->getMessage())
                ->withInput();
        }
    }


    public function resetAssignment(Request $request, QuizAssignment $assignment)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        // Save current attempt to history before resetting
        if ($assignment->started_at) {
            $this->saveAttemptToHistory($assignment, $request->reason ?? 'Admin Reset');
        }

        // Reset the assignment
        $assignment->resetForRetake();

        return redirect()->back()
            ->with('success', "Quiz assignment has been reset. User can now retake the quiz.");
    }

    public function viewAttemptHistory(QuizAssignment $assignment)
    {
        $assignment->load(['quiz', 'user']);

        return view('admin.quizzes.attempt-history', compact('assignment'));
    }

    public function exportAttemptHistoryPdf(QuizAssignment $assignment)
    {
        $assignment->load(['quiz', 'user']);

        $attempts = $assignment->attemptHistory()->orderBy('attempt_number')->get();

        // Build detailed Q&A per attempt
        $questionsById = $assignment->quiz->questions->keyBy('id');
        $attemptsDetailed = $attempts->map(function ($attempt) use ($questionsById) {
            $details = [
                'id' => $attempt->id,
                'attempt_number' => $attempt->attempt_number,
                'correct_answers' => $attempt->correct_answers,
                'total_questions' => $attempt->total_questions,
                'percentage' => $attempt->total_questions > 0 ? round(($attempt->correct_answers / $attempt->total_questions) * 100) : 0,
                'time_taken_seconds' => $attempt->time_taken_seconds,
                'status_text' => method_exists($attempt, 'getStatusText') ? $attempt->getStatusText() : $attempt->status,
                'started_at' => $attempt->started_at,
                'completed_at' => $attempt->completed_at,
                'questions' => [],
            ];

            if (is_array($attempt->answers)) {
                foreach ($attempt->answers as $answer) {
                    if (!isset($answer['question_id'])) {
                        continue;
                    }
                    $question = $questionsById->get($answer['question_id']);
                    if (!$question) {
                        continue;
                    }
                    $userAnswerKey = $answer['user_answer'] ?? null;
                    $correctKey = $question->correct_answer;
                    $isCorrect = (bool) ($answer['is_correct'] ?? ($userAnswerKey && $correctKey && $userAnswerKey === $correctKey));

                    $details['questions'][] = [
                        'question_text' => $question->question_text,
                        'points' => $question->points,
                        'type' => $question->question_type,
                        'option_a' => $question->option_a,
                        'option_b' => $question->option_b,
                        'option_c' => $question->option_c,
                        'option_d' => $question->option_d,
                        'correct_key' => $correctKey,
                        'user_key' => $userAnswerKey,
                        'is_correct' => $isCorrect,
                    ];
                }
            }

            return $details;
        });

        $totalAttempts = $attempts->count();
        $totalQuestions = (int) ($assignment->quiz->total_questions ?? 0);

        $totalCorrectSum = (int) $attempts->sum('correct_answers');
        $totalScoreSum = $totalCorrectSum;

        $totalTimeSeconds = (int) $attempts->sum('time_taken_seconds');
        $averageTimeSeconds = $totalAttempts > 0 ? (int) round($totalTimeSeconds / $totalAttempts) : 0;

        $averageCorrect = $totalAttempts > 0 ? round($attempts->avg('correct_answers'), 2) : 0;
        $averagePercentage = ($totalAttempts > 0 && $totalQuestions > 0)
            ? round(($averageCorrect / $totalQuestions) * 100, 2)
            : 0;

        $data = [
            'assignment' => $assignment,
            'attempts' => $attempts,
            'attempts_detailed' => $attemptsDetailed,
            'stats' => [
                'total_attempts' => $totalAttempts,
                'total_questions' => $totalQuestions,
                'total_score_sum' => $totalScoreSum,
                'average_correct' => $averageCorrect,
                'average_percentage' => $averagePercentage,
                'total_time_seconds' => $totalTimeSeconds,
                'average_time_seconds' => $averageTimeSeconds,
            ],
        ];

        $pdf = Pdf::loadView('admin.quizzes.attempt-history-pdf', $data)->setPaper('a4', 'portrait');

        $filename = 'quiz_attempt_history_' . $assignment->id . '.pdf';
        return $pdf->download($filename);
    }

    public function getAttemptDetails($attemptId)
    {
        $attempt = QuizAttemptHistory::with(['quiz.questions', 'user'])
            ->findOrFail($attemptId);

        // Get the quiz questions with their correct answers
        $questions = $attempt->quiz->questions->map(function($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'points' => $question->points,
                'option_a' => $question->option_a,
                'option_b' => $question->option_b,
                'option_c' => $question->option_c,
                'option_d' => $question->option_d,
                'correct_answer' => $question->correct_answer,
            ];
        });

        // Get user's submitted answers and convert to key-value format
        $userAnswers = [];
        if ($attempt->answers) {
            foreach ($attempt->answers as $answer) {
                if (isset($answer['question_id']) && isset($answer['user_answer'])) {
                    $userAnswers[$answer['question_id']] = $answer['user_answer'];
                }
            }
        }

        return response()->json([
            'attempt' => [
                'id' => $attempt->id,
                'attempt_number' => $attempt->attempt_number,
                'score' => $attempt->score,
                'total_questions' => $attempt->total_questions,
                'correct_answers' => $attempt->correct_answers,
                'time_taken_seconds' => $attempt->time_taken_seconds,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at,
                'completed_at' => $attempt->completed_at,
            ],
            'quiz' => [
                'id' => $attempt->quiz->id,
                'title' => $attempt->quiz->title,
                'description' => $attempt->quiz->description,
            ],
            'user' => [
                'id' => $attempt->user->id,
                'name' => $attempt->user->name,
                'email' => $attempt->user->email,
            ],
            'questions' => $questions,
            'user_answers' => $userAnswers,
        ]);
    }

    public function getUserQuizHistory($quizId, $userId)
    {
        $assignment = QuizAssignment::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->with(['quiz', 'user', 'attemptHistory'])
            ->first();

        if (!$assignment) {
            return redirect()->back()->with('error', 'Quiz assignment not found.');
        }

        return redirect()->route('admin.quiz-assignments.history', $assignment);
    }

    public function allowRetake(Request $request, QuizAssignment $assignment)
    {
        $assignment->update(['can_retake' => true]);

        return redirect()->back()
            ->with('success', "User has been granted permission to retake this quiz.");
    }

    private function saveAttemptToHistory(QuizAssignment $assignment, $reason = 'Time Expired')
    {
        // Calculate score from current attempts
        $attempts = QuizAttempt::where('quiz_id', $assignment->quiz_id)
                              ->where('user_id', $assignment->user_id)
                              ->get();

        $totalScore = $attempts->sum('points_earned');
        $correctAnswers = $attempts->where('is_correct', true)->count();
        $totalQuestions = $assignment->quiz->total_questions;

        $timeTaken = $assignment->started_at ?
            now()->timestamp - $assignment->started_at->timestamp : null;

        // Determine status
        $status = 'completed';
        if ($assignment->isTimeExpired()) {
            $status = 'time_expired';
        } elseif ($assignment->status === 'cancelled') {
            $status = 'cancelled';
        }

        // Save to history
        QuizAttemptHistory::create([
            'quiz_id' => $assignment->quiz_id,
            'user_id' => $assignment->user_id,
            'attempt_number' => $assignment->attempt_count,
            'score' => $totalScore,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'time_taken_seconds' => $timeTaken,
            'started_at' => $assignment->started_at,
            'completed_at' => now(),
            'status' => $status,
            'answers' => $attempts->map(function($attempt) {
                return [
                    'question_id' => $attempt->question_id,
                    'user_answer' => $attempt->user_answer,
                    'is_correct' => $attempt->is_correct,
                    'points_earned' => $attempt->points_earned,
                ];
            })->toArray(),
        ]);

        // Update assignment statistics
        $assignment->update([
            'attempt_count' => $assignment->attempt_count + 1,
            'total_score' => ($assignment->total_score ?? 0) + $totalScore,
            'best_score' => max($assignment->best_score ?? 0, $totalScore),
            'last_attempt_at' => now(),
        ]);

        // Delete current attempts to prepare for retake
        $attempts->each->delete();
    }

    public function manualGrading()
    {
        // Get all quiz attempts that need manual grading (fill-in-the-blank and text questions)
        $attempts = QuizAttempt::whereHas('question', function($query) {
            $query->whereIn('question_type', ['fill_blank', 'text']);
        })
        // Only show attempts that haven't been graded yet
        ->whereNull('graded_at')
        ->with(['question', 'user', 'quiz'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return view('admin.quizzes.manual-grading', compact('attempts'));
    }

    public function gradeAttempt(Request $request, QuizAttempt $attempt)
    {
        // Debug: Log incoming request data
        \Log::info('Manual grading request:', [
            'attempt_id' => $attempt->id,
            'is_correct' => $request->is_correct,
            'points_earned' => $request->points_earned,
            'feedback' => $request->feedback,
            'question_points' => $attempt->question->points,
            'all_request_data' => $request->all()
        ]);

        // More lenient validation for debugging
        $request->validate([
            'is_correct' => 'required|string|in:0,1',
            'points_earned' => 'required|integer|min:0|max:' . $attempt->question->points,
            'feedback' => 'nullable|string|max:500',
        ]);

        $attempt->update([
            'is_correct' => (bool) $request->is_correct,
            'points_earned' => $request->points_earned,
            'graded_at' => now(),
            'graded_by' => auth()->id(),
            'feedback' => $request->feedback,
        ]);

        // Update the user's total score in quiz attempt history
        try {
            $this->updateUserScore($attempt);
            \Log::info('Manual grading completed successfully for attempt:', ['attempt_id' => $attempt->id]);

            // If there are no more ungraded manual attempts for this user+quiz, mark the latest attempt history as completed
            $hasPendingManual = QuizAttempt::where('quiz_id', $attempt->quiz_id)
                ->where('user_id', $attempt->user_id)
                ->whereNull('graded_at')
                ->whereHas('question', function($q) {
                    $q->whereIn('question_type', ['fill_blank', 'text']);
                })
                ->exists();

            if (!$hasPendingManual) {
                $latestHistory = QuizAttemptHistory::where('quiz_id', $attempt->quiz_id)
                    ->where('user_id', $attempt->user_id)
                    ->latest()
                    ->first();
                if ($latestHistory && $latestHistory->status === 'partial') {
                    $latestHistory->update(['status' => 'completed']);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error updating user score:', [
                'attempt_id' => $attempt->id,
                'error' => $e->getMessage()
            ]);
            // Don't fail the request if score update fails
        }

        return response()->json([
            'success' => true,
            'message' => 'Attempt graded successfully.',
        ]);
    }

    private function updateUserScore(QuizAttempt $attempt)
    {
        // Get all attempts for this user and quiz
        $allAttempts = QuizAttempt::where('quiz_id', $attempt->quiz_id)
            ->where('user_id', $attempt->user_id)
            ->get();

        $totalScore = $allAttempts->sum('points_earned');
        $correctAnswers = $allAttempts->where('is_correct', true)->count();

        // Update the quiz attempt history
        $history = QuizAttemptHistory::where('quiz_id', $attempt->quiz_id)
            ->where('user_id', $attempt->user_id)
            ->latest()
            ->first();

        if ($history) {
            $history->update([
                'score' => $totalScore,
                'correct_answers' => $correctAnswers,
            ]);
        }

        // Update the quiz assignment
        $assignment = QuizAssignment::where('quiz_id', $attempt->quiz_id)
            ->where('user_id', $attempt->user_id)
            ->first();

        if ($assignment) {
            $assignment->update([
                'total_score' => $totalScore,
                'best_score' => max($assignment->best_score ?? 0, $totalScore),
            ]);
        }
    }

    private function generateQuizCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Quiz::where('quiz_code', $code)->exists());

        return $code;
    }
}
