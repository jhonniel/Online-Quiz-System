<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAssignment;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptHistory;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (! $user || ! $user->canViewAssignedQuizzes()) {
                abort(403, 'You do not have access to quizzes.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $assignedQuizzes = QuizAssignment::where('user_id', auth()->id())
            ->with(['quiz.creator'])
            ->whereHas('quiz', function($query) {
                $query->where('is_active', true);
            })
            ->get();

        return view('user.quizzes.index', compact('assignedQuizzes'));
    }

    public function enterCode()
    {
        return view('user.quizzes.enter-code');
    }

    public function validateCode(Request $request)
    {
        $request->validate([
            'quiz_code' => 'required|string|min:3|max:20',
        ]);

        $quiz = Quiz::where('quiz_code', strtoupper($request->quiz_code))
            ->where('is_active', true)
            ->first();

        if (!$quiz) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid quiz code. Please check and try again.',
                    'type' => 'error'
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['quiz_code' => 'Invalid quiz code.'])
                ->withInput();
        }

        // Check if user is assigned to this quiz
        $assignment = QuizAssignment::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$assignment) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this quiz. Please contact your instructor.',
                    'type' => 'error'
                ], 403);
            }

            return redirect()->back()
                ->withErrors(['quiz_code' => 'You are not assigned to this quiz.'])
                ->withInput();
        }

        if ($assignment->is_completed) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already completed this quiz.',
                    'type' => 'warning'
                ], 409);
            }

            return redirect()->back()
                ->withErrors(['quiz_code' => 'You have already completed this quiz.'])
                ->withInput();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quiz code validated successfully! Redirecting to quiz...',
                'type' => 'success',
                'redirect_url' => url('/quizzes/' . $quiz->id . '/take')
            ]);
        }

        return redirect('/quizzes/' . $quiz->id . '/take');
    }

    public function start(Request $request, Quiz $quiz)
    {
        // Check if user is assigned to this quiz
        $assignment = QuizAssignment::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$assignment || $assignment->is_completed) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to take this quiz.',
                    'type' => 'error'
                ], 403);
            }
            abort(403, 'You are not authorized to take this quiz.');
        }

        // Check if quiz has already been started
        if ($assignment->started_at) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quiz already started. Redirecting...',
                    'type' => 'info',
                    'redirect_url' => url('/quizzes/' . $quiz->id . '/take')
                ]);
            }
            return redirect('/quizzes/' . $quiz->id . '/take');
        }

        // Start the quiz
        $assignment->update([
            'started_at' => now(),
            'status' => 'in_progress'
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quiz started successfully!',
                'type' => 'success',
                'redirect_url' => url('/quizzes/' . $quiz->id . '/take')
            ]);
        }

        return redirect('/quizzes/' . $quiz->id . '/take');
    }

    public function take(Quiz $quiz)
    {
        // Check if user is assigned to this quiz
        $assignment = QuizAssignment::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->with('quiz')
            ->first();

        if (!$assignment || $assignment->is_completed) {
            abort(403, 'You are not authorized to take this quiz.');
        }

        // If quiz hasn't been started yet, show instructions page
        if (!$assignment->started_at) {
            return view('user.quizzes.start', compact('quiz', 'assignment'));
        }

        // Update status to in_progress if quiz was started but status wasn't updated
        if ($assignment->status === 'assigned') {
            $assignment->update(['status' => 'in_progress']);
        }

        $quiz->load(['questions.answers']);

        // Get all questions
        $allQuestions = $quiz->questions;

        // If questions_to_show is set, randomly select that many questions
        // Otherwise, show all questions
        if ($quiz->questions_to_show && $quiz->questions_to_show > 0 && $quiz->questions_to_show < $allQuestions->count()) {
            // Randomly select the specified number of questions
            $questions = $allQuestions->shuffle()->take($quiz->questions_to_show);
        } else {
            // Show all questions (shuffled)
            $questions = $allQuestions->shuffle();
        }

        // Calculate remaining time if time limit is set
        $remainingTime = $assignment->remaining_time;
        $timeExpired = $assignment->isTimeExpired();

        // If time has expired, redirect to a time expired page or show message
        if ($timeExpired) {
            return redirect('/quizzes/' . $quiz->id . '/time-expired')
                ->with('error', 'Time has expired for this quiz.');
        }

        // Don't pass questions to view - they will be fetched via API when Start Quiz is clicked
        return view('user.quizzes.take', compact('quiz', 'remainingTime'));
    }

    public function getQuestions(Request $request, Quiz $quiz)
    {
        // Check if user is assigned to this quiz
        $assignment = QuizAssignment::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->with('quiz')
            ->first();

        if (!$assignment || $assignment->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to take this quiz.',
            ], 403);
        }

        // Check if quiz has been started
        if (!$assignment->started_at) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz has not been started yet.',
            ], 400);
        }

        // Update status to in_progress if quiz was started but status wasn't updated
        if ($assignment->status === 'assigned') {
            $assignment->update(['status' => 'in_progress']);
        }

        $quiz->load(['questions.answers']);

        // Get all questions
        $allQuestions = $quiz->questions;

        $questions = $this->resolveQuizQuestionsForAttempt($quiz, $allQuestions);

        // Randomize answer choices for each question and create randomized options
        $questions->each(function ($question) {
            if ($question->question_type === 'multiple_choice') {
                $options = [];

                // Check if question has options in the option_a, option_b, etc. fields
                if (!empty($question->option_a) || !empty($question->option_b) || !empty($question->option_c) || !empty($question->option_d)) {
                    // Use the option fields (newer format)
                    $options = [
                        ['label' => 'A', 'text' => $question->option_a, 'is_correct' => $question->correct_answer === 'A'],
                        ['label' => 'B', 'text' => $question->option_b, 'is_correct' => $question->correct_answer === 'B'],
                        ['label' => 'C', 'text' => $question->option_c, 'is_correct' => $question->correct_answer === 'C'],
                        ['label' => 'D', 'text' => $question->option_d, 'is_correct' => $question->correct_answer === 'D'],
                    ];
                } else {
                    // Use the answers relationship (older format)
                    $answers = $question->answers->sortBy('order');
                    $labels = ['A', 'B', 'C', 'D'];

                    foreach ($answers as $index => $answer) {
                        if ($index < 4) { // Limit to 4 options
                            $options[] = [
                                'label' => $labels[$index],
                                'text' => $answer->answer_text,
                                'is_correct' => $answer->is_correct,
                            ];
                        }
                    }
                }

                // Filter out empty options
                $options = array_filter($options, function($option) {
                    return !empty($option['text']);
                });

                // Keep options in original order (no randomization)
                $orderedOptions = [];
                $labels = ['A', 'B', 'C', 'D'];

                foreach ($options as $index => $option) {
                    if ($index < 4) { // Limit to 4 options
                        $orderedOptions[] = [
                            'id' => $labels[$index],
                            'label' => $labels[$index],
                            'text' => $option['text'],
                            'is_correct' => $option['is_correct'],
                        ];
                    }
                }

                // Add ordered options to question
                $question->ordered_options = $orderedOptions;
            }
        });

        // Calculate remaining time if time limit is set
        $remainingTime = $assignment->remaining_time;
        $timeExpired = $assignment->isTimeExpired();

        // If time has expired, return error
        if ($timeExpired) {
            return response()->json([
                'success' => false,
                'message' => 'Time has expired for this quiz.',
            ], 408);
        }

        return response()->json([
            'success' => true,
            'questions' => $questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'points' => $question->points,
                    'ordered_options' => $question->ordered_options ?? null,
                ];
            })->values(),
            'remaining_time' => $remainingTime,
            'saved_progress' => $assignment->progress_answers ?? [],
        ]);
    }

    public function saveProgress(Request $request, Quiz $quiz)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable',
        ]);

        $assignment = QuizAssignment::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$assignment || $assignment->is_completed) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $assignment->progress_answers = $request->answers;
        $assignment->save();

        return response()->json(['success' => true]);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*' => 'nullable', // allow empty for text/fill_blank
        ]);

        $submittedAnswers = collect($request->answers)
            ->filter(fn ($answer) => filled($answer))
            ->all();

        if ($submittedAnswers === []) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please answer at least one question before submitting.',
                    'type' => 'error',
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Please answer at least one question before submitting.']);
        }

        // Check if user is assigned to this quiz
        $assignment = QuizAssignment::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->with('quiz')
            ->first();

        if (!$assignment) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this quiz. Please contact your instructor.',
                    'type' => 'error'
                ], 403);
            }
            abort(403, 'You are not assigned to this quiz.');
        }

        if ($assignment->is_completed) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already completed this quiz.',
                    'type' => 'warning'
                ], 409);
            }
            abort(403, 'You have already completed this quiz.');
        }

        // Check if time has expired
        // For non-AJAX submissions, block after time has expired.
        // For AJAX submissions (including auto-submit when timer ends), allow the submission
        // so that answers are still recorded for review.
        $timeExpired = $assignment->isTimeExpired();
        if ($timeExpired && !$this->wantsJsonResponse($request)) {
            abort(408, 'Time has expired.');
        }

        DB::beginTransaction();
        try {
            $totalPoints = 0;
            $correctAnswers = 0;
            $hasTextQuestions = false;
            $processedAnswers = 0;

            foreach ($submittedAnswers as $questionId => $userAnswer) {
                $question = Question::where('quiz_id', $quiz->id)->find($questionId);
                if (!$question) {
                    continue;
                }

                $processedAnswers++;
                $pointsEarned = 0;
                $isCorrect = false;
                $userAnswerValue = is_array($userAnswer) ? json_encode($userAnswer) : (string) $userAnswer;

                if ($question->question_type === 'multiple_choice') {
                    if ($userAnswerValue === (string) $question->correct_answer) {
                        $pointsEarned = (int) ($question->points ?? 0);
                        $isCorrect = true;
                        $correctAnswers++;
                    }
                } elseif ($question->question_type === 'true_false') {
                    if ($userAnswerValue === (string) $question->correct_answer) {
                        $pointsEarned = (int) ($question->points ?? 0);
                        $isCorrect = true;
                        $correctAnswers++;
                    }
                } elseif ($question->question_type === 'text') {
                    $pointsEarned = 0;
                    $isCorrect = false;
                    $hasTextQuestions = true;
                } elseif ($question->question_type === 'fill_blank') {
                    $pointsEarned = 0;
                    $isCorrect = false;
                    $hasTextQuestions = true;
                } else {
                    $pointsEarned = 0;
                    $isCorrect = false;
                }

                QuizAttempt::create([
                    'quiz_id' => $quiz->id,
                    'user_id' => auth()->id(),
                    'question_id' => $question->id,
                    'answer_id' => null,
                    'user_answer' => $userAnswerValue,
                    'is_correct' => $isCorrect,
                    'points_earned' => (int) $pointsEarned,
                    'started_at' => now(),
                    'completed_at' => now(),
                ]);

                $totalPoints += $pointsEarned;
            }

            if ($processedAnswers === 0) {
                DB::rollBack();

                if ($this->wantsJsonResponse($request)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No valid answers were found for this quiz. Please refresh and try again.',
                        'type' => 'error',
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors(['error' => 'No valid answers were found for this quiz. Please refresh and try again.']);
            }

            // Save attempt to history
            $status = $hasTextQuestions ? 'partial' : 'completed';
            $this->saveAttemptToHistory($assignment, $totalPoints, $correctAnswers, $quiz->total_questions, $submittedAnswers, $status);

            // Mark assignment as completed and clear saved progress
            $assignment->update([
                'is_completed' => true,
                'status' => 'completed',
                'progress_answers' => null,
            ]);

            $this->clearQuizQuestionSession($quiz);

            DB::commit();

            $message = $hasTextQuestions
                ? 'Quiz submitted successfully! Your score is partial and will be updated after manual review of text answers.'
                : 'Quiz submitted successfully! Redirecting to results...';

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'type' => 'success',
                    'redirect_url' => '/quizzes/' . $quiz->id . '/result',
                ]);
            }

            return redirect('/quizzes/' . $quiz->id . '/result')
                ->with('success', $hasTextQuestions
                    ? 'Quiz submitted successfully! Your score is partial and will be updated after manual review of text answers.'
                    : 'Quiz submitted successfully!');

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Quiz submission error: ' . $e->getMessage(), [
                'quiz_id' => $quiz->id,
                'user_id' => auth()->id(),
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            $message = 'An error occurred while submitting the quiz. Please try again.';
            if (config('app.debug')) {
                $message .= ' ' . $e->getMessage();
            }

            if ($this->wantsJsonResponse($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'type' => 'error'
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while submitting the quiz.']);
        }
    }

    public function cancel(Request $request, Quiz $quiz)
    {
        // Check if user is assigned to this quiz
        $assignment = QuizAssignment::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$assignment) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this quiz.',
                    'type' => 'error'
                ], 403);
            }
            abort(403, 'You are not assigned to this quiz.');
        }

        if ($assignment->is_completed) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already completed this quiz.',
                    'type' => 'warning'
                ], 409);
            }
            abort(403, 'You have already completed this quiz.');
        }

        // Reset the started_at timestamp and mark as cancelled to allow restart
        $assignment->update([
            'started_at' => null,
            'status' => 'cancelled',
            'progress_answers' => null,
        ]);

        $this->clearQuizQuestionSession($quiz);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quiz cancelled successfully. You can restart it anytime.',
                'type' => 'success',
                'redirect_url' => url('/dashboard')
            ]);
        }

        return redirect('/dashboard')
            ->with('success', 'Quiz cancelled successfully. You can restart it anytime.');
    }

    public function timeExpired(Quiz $quiz)
    {
        // Check if user is assigned to this quiz
        $assignment = QuizAssignment::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$assignment) {
            abort(403, 'You are not authorized to view this quiz.');
        }

        return view('user.quizzes.time-expired', compact('quiz', 'assignment'));
    }

    public function result(Quiz $quiz)
    {
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->with(['question'])
            ->get();

        $totalPoints = $attempts->sum('points_earned');
        $maxPoints = $quiz->questions()->sum('points');
        $correctAnswers = $attempts->where('is_correct', true)->count();
        $totalQuestions = $attempts->count();

        // Check if there are any text or fill-in-the-blank questions that require manual grading
        $hasManualGradingQuestions = $quiz->questions()
            ->whereIn('question_type', ['text', 'fill_blank'])
            ->exists();

        // Determine if there are still manual-graded questions pending review
        $hasPendingManualGrading = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->whereNull('graded_at')
            ->whereHas('question', function($q) {
                $q->whereIn('question_type', ['text', 'fill_blank']);
            })
            ->exists();

        // Fallback to history status if needed
        $latestAttempt = QuizAttemptHistory::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        $isPartialScore = $hasPendingManualGrading || ($latestAttempt && $latestAttempt->status === 'partial');

        return view('user.quizzes.result', compact(
            'quiz',
            'attempts',
            'totalPoints',
            'maxPoints',
            'correctAnswers',
            'totalQuestions',
            'hasManualGradingQuestions',
            'isPartialScore'
        ));
    }

    private function wantsJsonResponse(Request $request): bool
    {
        return $request->ajax() || $request->expectsJson() || $request->wantsJson();
    }

    private function quizQuestionSessionKey(Quiz $quiz): string
    {
        return 'quiz_question_ids.' . $quiz->id . '.' . auth()->id();
    }

    private function clearQuizQuestionSession(Quiz $quiz): void
    {
        session()->forget($this->quizQuestionSessionKey($quiz));
    }

    /**
     * Keep the same question set for an in-progress attempt (do not reshuffle on every API call).
     */
    private function resolveQuizQuestionsForAttempt(Quiz $quiz, $allQuestions)
    {
        $sessionKey = $this->quizQuestionSessionKey($quiz);
        $storedIds = session($sessionKey);

        if (is_array($storedIds) && $storedIds !== []) {
            $ordered = collect($storedIds)
                ->map(fn ($id) => $allQuestions->firstWhere('id', (int) $id))
                ->filter();

            if ($ordered->isNotEmpty()) {
                return $ordered->values();
            }
        }

        if ($quiz->questions_to_show && $quiz->questions_to_show > 0 && $quiz->questions_to_show < $allQuestions->count()) {
            $questions = $allQuestions->shuffle()->take($quiz->questions_to_show);
        } else {
            $questions = $allQuestions->shuffle();
        }

        session([
            $sessionKey => $questions->pluck('id')->values()->all(),
        ]);

        return $questions->values();
    }

    private function saveAttemptToHistory($assignment, $totalScore, $correctAnswers, $totalQuestions, $userAnswers = [], $status = 'completed')
    {
        // Ensure we have a started_at time - use assignment started_at or current time
        $startedAt = $assignment->started_at ? \Carbon\Carbon::parse($assignment->started_at) : now();
        $timeTaken = $assignment->started_at
            ? (int) (now()->timestamp - $startedAt->timestamp)
            : null;

        // Get the current attempt's answers from QuizAttempt table
        $currentAttemptAnswers = QuizAttempt::where('quiz_id', $assignment->quiz_id)
            ->where('user_id', $assignment->user_id)
            ->where('created_at', '>=', now()->subMinutes(5)) // Get answers from the last 5 minutes
            ->get()
            ->map(function($attempt) {
                return [
                    'question_id' => $attempt->question_id,
                    'user_answer' => $attempt->user_answer,
                    'is_correct' => $attempt->is_correct,
                    'points_earned' => $attempt->points_earned,
                ];
            })->toArray();

        // If no answers found in QuizAttempt, use the userAnswers parameter
        if (empty($currentAttemptAnswers) && !empty($userAnswers)) {
            $currentAttemptAnswers = collect($userAnswers)->map(function($answer, $questionId) {
                return [
                    'question_id' => $questionId,
                    'user_answer' => $answer,
                    'is_correct' => false, // Will be calculated later if needed
                    'points_earned' => 0, // Will be calculated later if needed
                ];
            })->values()->toArray();
        }

        // Save to history (set answers after create so array→json cast is applied for SQLite)
        $history = new QuizAttemptHistory([
            'quiz_id' => $assignment->quiz_id,
            'user_id' => $assignment->user_id,
            'attempt_number' => $assignment->attempt_count + 1,
            'score' => $totalScore,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'time_taken_seconds' => $timeTaken,
            'started_at' => $startedAt,
            'completed_at' => now(),
            'status' => $status,
        ]);
        $history->answers = $currentAttemptAnswers;
        $history->save();

        // Update assignment statistics
        $assignment->update([
            'attempt_count' => $assignment->attempt_count + 1,
            'total_score' => ($assignment->total_score ?? 0) + $totalScore,
            'best_score' => max($assignment->best_score ?? 0, $totalScore),
            'last_attempt_at' => now(),
        ]);
    }
}
