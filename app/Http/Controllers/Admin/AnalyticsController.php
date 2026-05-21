<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptHistory;
use App\Models\User;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    /** Roles treated as learners in analytics (excludes admin/technician). */
    private function studentRoles(): array
    {
        return ['student', 'user', 'applicant', 'employee', 'teacher'];
    }

    /** Active users who have at least one scorable quiz submission (any learner role). */
    private function usersWithQuizAttempts()
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('quizAttemptHistory', function ($query) {
                $this->scorableAttemptHistory($query);
            });
    }

    private function scorableAttemptHistory($query)
    {
        return $query->scorable();
    }

    /**
     * Points earned for a history row (sums live quiz_attempts after manual grading).
     */
    private function effectiveHistoryScore(QuizAttemptHistory $history): int
    {
        $completedAt = $history->completed_at ?? $history->created_at;
        if (! $completedAt) {
            return (int) $history->score;
        }

        $start = $completedAt->copy()->subMinutes(15);
        $end = $completedAt->copy()->addMinute();

        $liveScore = (int) QuizAttempt::query()
            ->where('quiz_id', $history->quiz_id)
            ->where('user_id', $history->user_id)
            ->whereBetween('created_at', [$start, $end])
            ->sum('points_earned');

        return $liveScore > 0 ? $liveScore : (int) $history->score;
    }

    private function historyMaxPoints(QuizAttemptHistory $history): int
    {
        $history->loadMissing('quiz.questions');

        return (int) ($history->quiz?->questions?->sum('points') ?? 0);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, QuizAttemptHistory>  $attempts
     * @return \Illuminate\Support\Collection<int, QuizAttemptHistory>
     */
    private function enrichAttemptScores(Collection $attempts): Collection
    {
        return $attempts->map(function (QuizAttemptHistory $attempt) {
            $maxPoints = $this->historyMaxPoints($attempt);
            $effectiveScore = $this->effectiveHistoryScore($attempt);
            $percent = $maxPoints > 0
                ? round($effectiveScore / $maxPoints * 100, 1)
                : 0.0;
            $gradingPending = $attempt->status === 'partial'
                && $effectiveScore === 0
                && QuizAttempt::query()
                    ->where('quiz_id', $attempt->quiz_id)
                    ->where('user_id', $attempt->user_id)
                    ->whereNull('graded_at')
                    ->whereHas('question', function ($q) {
                        $q->whereIn('question_type', ['text', 'fill_blank'])
                            ->orWhere('requires_manual_grading', true);
                    })
                    ->exists();

            $attempt->setAttribute('effective_score', $effectiveScore);
            $attempt->setAttribute('max_points', $maxPoints);
            $attempt->setAttribute('score_percent', $percent);
            $attempt->setAttribute('grading_pending', $gradingPending);

            return $attempt;
        });
    }

    public static function scoreBadgeClass(float $percent, bool $gradingPending = false): string
    {
        if ($gradingPending) {
            return 'bg-amber-100 text-amber-800';
        }
        if ($percent >= 90) {
            return 'bg-green-100 text-green-800';
        }
        if ($percent >= 70) {
            return 'bg-yellow-100 text-yellow-800';
        }

        return 'bg-red-100 text-red-800';
    }

    /**
     * One row per student: their best-scoring attempt for the given history set.
     */
    private function bestScorePerStudent($attempts)
    {
        return $this->enrichAttemptScores($attempts)
            ->groupBy('user_id')
            ->map(fn ($userAttempts) => $userAttempts->sortByDesc('effective_score')->first())
            ->values();
    }

    /**
     * @return array{total_attempts: int, student_count: int, average_score: float, average_percent: float, highest_score: int, max_points: int}
     */
    private function quizLeaderboardStats($attempts): array
    {
        $bests = $this->bestScorePerStudent($attempts);
        $maxPoints = (int) ($bests->first()->max_points ?? 0);

        return [
            'total_attempts' => $attempts->count(),
            'student_count' => $bests->count(),
            'max_points' => $maxPoints,
            'average_score' => $bests->isNotEmpty() ? round($bests->avg('effective_score'), 1) : 0,
            'average_percent' => $bests->isNotEmpty() ? round($bests->avg('score_percent'), 1) : 0,
            'highest_score' => (int) ($bests->max('effective_score') ?? 0),
        ];
    }

    public function index()
    {
        return view('admin.analytics.index', [
            'topPerformers' => $this->safeAnalytics(fn () => $this->getTopPerformers(), collect()),
            'studentStats' => $this->safeAnalytics(fn () => $this->getStudentPerformanceStats(), $this->emptyStudentStats()),
            'topicPerformance' => $this->safeAnalytics(fn () => $this->getTopicPerformance(), collect()),
            'studentTopicStrengths' => $this->safeAnalytics(fn () => $this->getStudentTopicStrengths(), collect()),
            'topPerformersByQuiz' => $this->safeAnalytics(fn () => $this->getTopPerformersByQuiz(), collect()),
            'quizStats' => $this->safeAnalytics(fn () => $this->getQuizPerformanceStats(), collect()),
            'universityPerformance' => $this->safeAnalytics(fn () => $this->getUniversityPerformance(), collect()),
            'recentHighScores' => $this->safeAnalytics(fn () => $this->getRecentHighScores(), collect()),
            'overallStats' => $this->safeAnalytics(fn () => $this->getOverallStats(), $this->emptyOverallStats()),
            'quizRankings' => $this->safeAnalytics(fn () => $this->getQuizRankings(), collect()),
        ]);
    }

    private function safeAnalytics(callable $callback, $default)
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::warning('Analytics section failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $default;
        }
    }

    private function emptyStudentStats(): array
    {
        return [
            'total_students' => 0,
            'active_students' => 0,
            'participation_rate' => 0,
            'average_total_score' => 0,
            'highest_total_score' => 0,
            'average_per_attempt' => 0,
            'average_percent_per_attempt' => 0,
            'total_quiz_attempts' => 0,
            'unranked_students' => 0,
        ];
    }

    private function emptyOverallStats(): array
    {
        return [
            'total_users' => 0,
            'active_users' => 0,
            'total_quizzes' => 0,
            'total_attempts' => 0,
            'average_score' => 0,
            'completion_rate' => 0,
        ];
    }

    /** Active learners: role pool, quiz assignments, or any scorable attempt. */
    private function learnerPoolQuery()
    {
        $participantIds = QuizAttemptHistory::query()->scorable()->distinct()->pluck('user_id');

        return User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($participantIds) {
                $query->whereIn('role', $this->studentRoles());
                if ($participantIds->isNotEmpty()) {
                    $query->orWhereIn('id', $participantIds);
                }
                $query->orWhereHas('quizAssignments');
            });
    }

    private function getTopPerformers()
    {
        return $this->usersWithQuizAttempts()
            ->with(['university', 'quizAttemptHistory' => function ($query) {
                $this->scorableAttemptHistory($query)->with('quiz.questions');
            }])
            ->get()
            ->map(function ($user) {
                $attempts = $this->enrichAttemptScores($user->quizAttemptHistory);
                $user->setAttribute('analytics_total_score', (int) $attempts->sum('effective_score'));
                $user->setAttribute('analytics_attempt_count', $attempts->count());
                $user->setAttribute('analytics_avg_percent', $attempts->isNotEmpty()
                    ? round($attempts->avg('score_percent'), 1)
                    : 0);

                return $user;
            })
            ->filter(fn ($user) => ($user->analytics_attempt_count ?? 0) > 0)
            ->sort(function ($a, $b) {
                $scoreCmp = ($b->analytics_total_score ?? 0) <=> ($a->analytics_total_score ?? 0);
                if ($scoreCmp !== 0) {
                    return $scoreCmp;
                }

                return ($b->analytics_attempt_count ?? 0) <=> ($a->analytics_attempt_count ?? 0);
            })
            ->take(50)
            ->values()
            ->map(function ($user, $index) {
                $totalAttempts = (int) ($user->analytics_attempt_count ?? 0);

                return [
                    'rank' => $index + 1,
                    'user' => $user,
                    'total_score' => (int) ($user->analytics_total_score ?? 0),
                    'total_attempts' => $totalAttempts,
                    'average_score' => (float) ($user->analytics_avg_percent ?? 0),
                    'university' => $user->university,
                    'rank_badge_class' => $this->getRankBadgeClass($index + 1),
                    'rank_icon' => $this->getRankIcon($index + 1),
                ];
            });
    }

    private function getStudentPerformanceStats(): array
    {
        $totalStudents = $this->learnerPoolQuery()->count();
        $activeStudents = $this->usersWithQuizAttempts()->count();

        $scorableAttempts = $this->enrichAttemptScores(
            QuizAttemptHistory::query()->scorable()->with('quiz.questions')->get()
        );
        $totalQuizAttempts = $scorableAttempts->count();

        $bestPerStudent = $scorableAttempts
            ->groupBy('user_id')
            ->map(fn ($rows) => (int) $rows->max('effective_score'));

        $averageBestPerStudent = $bestPerStudent->isNotEmpty()
            ? round($bestPerStudent->avg(), 1)
            : 0;

        $bestSingleAttempt = $bestPerStudent->isNotEmpty()
            ? (int) $bestPerStudent->max()
            : 0;

        $averagePerAttempt = $totalQuizAttempts > 0
            ? round($scorableAttempts->avg('effective_score'), 1)
            : 0;

        $averagePercentPerAttempt = $totalQuizAttempts > 0
            ? round($scorableAttempts->avg('score_percent'), 1)
            : 0;

        return [
            'total_students' => max($totalStudents, $activeStudents),
            'active_students' => $activeStudents,
            'participation_rate' => $totalStudents > 0
                ? round(($activeStudents / $totalStudents) * 100, 1)
                : ($activeStudents > 0 ? 100 : 0),
            'average_total_score' => $averageBestPerStudent,
            'highest_total_score' => $bestSingleAttempt,
            'average_per_attempt' => $averagePerAttempt,
            'average_percent_per_attempt' => $averagePercentPerAttempt,
            'total_quiz_attempts' => $totalQuizAttempts,
            'unranked_students' => max(0, $totalStudents - $activeStudents),
        ];
    }

    private function getTopPerformersByQuiz()
    {
        return Quiz::with(['questions:id,quiz_id,points', 'attemptHistory' => function ($query) {
                $this->scorableAttemptHistory($query);
                $query->with('user.university');
            }])
            ->where('is_active', true)
            ->get()
            ->map(function ($quiz) {
                $attempts = $quiz->attemptHistory;
                $stats = $this->quizLeaderboardStats($attempts);
                $allTakers = $this->bestScorePerStudent($attempts)
                    ->sortByDesc('effective_score')
                    ->values();

                return [
                    'quiz' => $quiz,
                    'top_attempts' => $allTakers,
                    'max_points' => (int) $quiz->questions->sum('points'),
                    ...$stats,
                ];
            })
            ->filter(fn ($data) => $data['student_count'] > 0)
            ->sortByDesc('student_count')
            ->values();
    }

    private function getQuizPerformanceStats()
    {
        return Quiz::performanceRanking(activeOnly: true)
            ->map(function ($quiz) {
                $totalAttempts = QuizAttemptHistory::where('quiz_id', $quiz->id)->scorable()->count();
                $maxPoints = (int) ($quiz->max_points ?? 0);

                return [
                    'quiz' => $quiz,
                    'total_attempts' => $totalAttempts,
                    'student_count' => $quiz->student_count,
                    'average_score' => $quiz->average_score,
                    'highest_score' => $quiz->highest_score,
                    'average_percent' => $quiz->average_percent,
                    'completion_rate' => $totalAttempts > 0 ? 100 : 0,
                    'difficulty_score' => $maxPoints > 0 ? $quiz->average_percent : 0,
                ];
            })
            ->sortByDesc('total_attempts')
            ->values();
    }

    private function getUniversityPerformance()
    {
        return University::with(['users' => function ($query) {
                $query->whereIn('role', $this->studentRoles())
                    ->with(['quizAttemptHistory' => function ($q) {
                        $this->scorableAttemptHistory($q);
                    }]);
            }])
            ->get()
            ->map(function ($university) {
                $allAttempts = $university->users->flatMap->quizAttemptHistory;

                return [
                    'university' => $university,
                    'total_students' => $university->users->whereIn('role', $this->studentRoles())->count(),
                    'total_attempts' => $allAttempts->count(),
                    'average_score' => $allAttempts->isNotEmpty() ? round($allAttempts->avg('score'), 1) : 0,
                    'highest_score' => (int) ($allAttempts->max('score') ?? 0),
                    'active_students' => $university->users->whereIn('role', $this->studentRoles())->where('is_active', true)->count(),
                ];
            })
            ->sortByDesc('average_score');
    }

    private function getRecentHighScores()
    {
        $attempts = QuizAttemptHistory::with(['user.university', 'quiz.questions'])
            ->scorable()
            ->orderByDesc('completed_at')
            ->get();

        return $this->enrichAttemptScores($attempts)
            ->groupBy(fn ($a) => $a->quiz_id.'-'.$a->user_id)
            ->map(function ($userQuizAttempts) {
                $best = $userQuizAttempts->sortByDesc('effective_score')->first();

                return [
                    'attempt' => $best,
                    'percentage' => (float) $best->score_percent,
                    'grading_pending' => (bool) $best->grading_pending,
                    'user' => $best->user,
                    'quiz' => $best->quiz,
                    'university' => $best->user->university,
                ];
            })
            ->sortByDesc(fn ($row) => $row['attempt']->completed_at)
            ->take(10)
            ->values();
    }

    private function getOverallStats()
    {
        $totalUsers = User::whereIn('role', $this->studentRoles())->count();
        $activeUsers = User::whereIn('role', $this->studentRoles())->where('is_active', true)->count();
        $totalQuizzes = Quiz::where('is_active', true)->count();
        $totalAttempts = QuizAttemptHistory::scorable()->count();
        $averageScore = QuizAttemptHistory::scorable()->avg('score') ?? 0;
        $allHistoryCount = QuizAttemptHistory::count();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'total_quizzes' => $totalQuizzes,
            'total_attempts' => $totalAttempts,
            'average_score' => round((float) $averageScore, 1),
            'completion_rate' => $allHistoryCount > 0
                ? round((QuizAttemptHistory::where('status', 'completed')->count() / $allHistoryCount) * 100, 2)
                : 0,
        ];
    }

    public function getQuizDetails($quizId)
    {
        $quiz = Quiz::with(['questions', 'attemptHistory' => function ($query) {
                $this->scorableAttemptHistory($query);
                $query->with('user.university');
            }])
            ->where('is_active', true)
            ->findOrFail($quizId);

        $attempts = $quiz->attemptHistory;
        $stats = $this->quizLeaderboardStats($attempts);
        $studentBests = $this->bestScorePerStudent($attempts)
            ->sortByDesc('effective_score')
            ->values();

        $performanceData = [
            'quiz' => $quiz,
            'total_attempts' => $stats['total_attempts'],
            'student_count' => $stats['student_count'],
            'max_points' => $stats['max_points'] ?: (int) $quiz->questions->sum('points'),
            'average_score' => $stats['average_score'],
            'average_percent' => $stats['average_percent'],
            'highest_score' => $stats['highest_score'],
            'lowest_score' => (int) ($studentBests->min('effective_score') ?? 0),
            'completion_rate' => $stats['student_count'] > 0 ? 100 : 0,
            'top_performers' => $studentBests,
            'score_distribution' => $this->getScoreDistribution($studentBests),
            'university_breakdown' => $this->getUniversityBreakdown($studentBests),
        ];

        return response()->json($performanceData);
    }

    /**
     * Per-topic stats: student totals (best per quiz) and per-quiz breakdown with max points.
     *
     * @return array{topic_max_points: int, students: Collection, quizzes: Collection}
     */
    private function buildTopicAnalytics(Collection $topicAttempts): array
    {
        $enriched = $this->enrichAttemptScores(
            $topicAttempts->loadMissing(['user.university', 'quiz.questions'])
        );

        $quizMaxPoints = $enriched
            ->groupBy('quiz_id')
            ->map(fn ($rows) => (int) ($rows->first()->max_points ?? 0));

        $students = $enriched
            ->groupBy('user_id')
            ->map(function ($rows) {
                $bestPerQuiz = $rows
                    ->groupBy('quiz_id')
                    ->map(fn ($qRows) => $qRows->sortByDesc('effective_score')->first());

                $score = (int) $bestPerQuiz->sum(fn ($a) => (int) $a->effective_score);
                $maxPossible = (int) $bestPerQuiz->sum(fn ($a) => (int) ($a->max_points ?? 0));
                $percent = $maxPossible > 0
                    ? round($score / $maxPossible * 100, 1)
                    : 0.0;

                return [
                    'user' => $rows->first()->user,
                    'university' => $rows->first()->user->university,
                    'total_score' => $score,
                    'max_possible' => $maxPossible,
                    'score_percent' => $percent,
                    'quizzes_taken' => $bestPerQuiz->count(),
                    'grading_pending' => $bestPerQuiz->contains(fn ($a) => (bool) $a->grading_pending),
                    'total_attempts' => $rows->count(),
                ];
            })
            ->values();

        $quizzes = $enriched
            ->groupBy('quiz_id')
            ->map(function ($quizAttempts) {
                $quiz = $quizAttempts->first()->quiz;
                $maxPoints = (int) ($quizAttempts->first()->max_points ?? 0);
                $bests = $this->bestScorePerStudent($quizAttempts);

                return [
                    'id' => (int) $quiz->id,
                    'title' => $quiz->title ?? 'Quiz',
                    'attempts' => $quizAttempts->count(),
                    'student_count' => $bests->count(),
                    'max_points' => $maxPoints,
                    'average_score' => $bests->isNotEmpty()
                        ? round($bests->avg(fn ($a) => $a->effective_score), 1)
                        : 0,
                    'highest_score' => (int) ($bests->max(fn ($a) => $a->effective_score) ?? 0),
                    'average_percent' => $bests->isNotEmpty()
                        ? round($bests->avg(fn ($a) => $a->score_percent), 1)
                        : 0,
                ];
            })
            ->values();

        return [
            'topic_max_points' => (int) $quizMaxPoints->sum(),
            'students' => $students,
            'quizzes' => $quizzes,
        ];
    }

    private function getTopicPerformance(): Collection
    {
        $attempts = QuizAttemptHistory::query()
            ->scorable()
            ->with(['user.university', 'quiz.questions'])
            ->whereHas('quiz', fn ($q) => $q->where('is_active', true))
            ->get();

        if ($attempts->isEmpty()) {
            return collect();
        }

        return $attempts
            ->groupBy(fn ($attempt) => trim((string) ($attempt->quiz->topic ?? '')) ?: 'Uncategorized')
            ->map(function ($topicAttempts, $topic) {
                $stats = $this->buildTopicAnalytics($topicAttempts);
                $students = $stats['students'];

                return [
                    'topic' => $topic,
                    'total_quizzes' => $topicAttempts->pluck('quiz_id')->unique()->count(),
                    'total_attempts' => $topicAttempts->count(),
                    'unique_students' => $students->count(),
                    'topic_max_points' => $stats['topic_max_points'],
                    'average_score' => $students->isNotEmpty()
                        ? round($students->avg('total_score'), 1)
                        : 0,
                    'average_percent' => $students->isNotEmpty()
                        ? round($students->avg('score_percent'), 1)
                        : 0,
                    'highest_score' => (int) ($students->max('total_score') ?? 0),
                    'top_performers' => $students->sortByDesc('total_score')->values(),
                    'quizzes' => $stats['quizzes'],
                ];
            })
            ->filter(fn (array $data) => $data['total_attempts'] > 0)
            ->sortByDesc('total_attempts')
            ->values();
    }

    private function getStudentTopicStrengths()
    {
        return $this->usersWithQuizAttempts()
            ->with(['university', 'quizAttemptHistory.quiz.questions'])
            ->get()
            ->map(function ($user) {
                $attempts = $this->enrichAttemptScores(
                    $user->quizAttemptHistory->filter(
                        fn ($a) => in_array($a->status, QuizAttemptHistory::SCORABLE_STATUSES, true)
                    )
                );

                $topicPerformance = $attempts
                    ->groupBy(fn ($a) => trim((string) ($a->quiz->topic ?? '')) ?: 'Uncategorized')
                    ->map(function ($topicAttempts, $topic) {
                        $bestPerQuiz = $topicAttempts
                            ->groupBy('quiz_id')
                            ->map(fn ($qRows) => $qRows->sortByDesc('effective_score')->first());

                        $score = (int) $bestPerQuiz->sum(fn ($a) => (int) $a->effective_score);
                        $maxPossible = (int) $bestPerQuiz->sum(fn ($a) => (int) ($a->max_points ?? 0));
                        $percent = $maxPossible > 0
                            ? round($score / $maxPossible * 100, 1)
                            : 0.0;

                        return [
                            'topic' => $topic,
                            'total_score' => $score,
                            'max_possible' => $maxPossible,
                            'score_percent' => $percent,
                            'quizzes_taken' => $bestPerQuiz->count(),
                            'grading_pending' => $bestPerQuiz->contains(fn ($a) => (bool) $a->grading_pending),
                        ];
                    })
                    ->sortByDesc('score_percent')
                    ->values();

                $bestPerQuiz = $attempts
                    ->groupBy('quiz_id')
                    ->map(fn ($rows) => $rows->sortByDesc('effective_score')->first());

                $overallScore = (int) $bestPerQuiz->sum(fn ($a) => (int) $a->effective_score);
                $overallMax = (int) $bestPerQuiz->sum(fn ($a) => (int) ($a->max_points ?? 0));
                $overallPercent = $overallMax > 0
                    ? round($overallScore / $overallMax * 100, 1)
                    : 0.0;

                $strongestTopic = $topicPerformance->first();

                return [
                    'user' => $user,
                    'total_topics' => $topicPerformance->count(),
                    'quizzes_taken' => $bestPerQuiz->count(),
                    'overall_score' => $overallScore,
                    'overall_max_points' => $overallMax,
                    'overall_percent' => $overallPercent,
                    'overall_grading_pending' => $bestPerQuiz->contains(fn ($a) => (bool) $a->grading_pending),
                    'strongest_topic' => $strongestTopic['topic'] ?? null,
                    'strongest_topic_score' => (float) ($strongestTopic['score_percent'] ?? 0),
                    'strongest_topic_points' => $strongestTopic
                        ? (int) ($strongestTopic['total_score'] ?? 0)
                        : 0,
                    'strongest_topic_max' => $strongestTopic
                        ? (int) ($strongestTopic['max_possible'] ?? 0)
                        : 0,
                    'strongest_topic_pending' => (bool) ($strongestTopic['grading_pending'] ?? false),
                    'topic_performance' => $topicPerformance,
                    'university' => $user->university,
                ];
            })
            ->filter(fn ($student) => $student['quizzes_taken'] > 0)
            ->sortByDesc('overall_percent')
            ->values();
    }

    public function getStudentDetails($userId)
    {
        $user = User::with(['university', 'quizAttemptHistory.quiz.questions'])
            ->whereHas('quizAttemptHistory', function ($query) {
                $this->scorableAttemptHistory($query);
            })
            ->findOrFail($userId);

        $attempts = $user->quizAttemptHistory->filter(
            fn ($a) => in_array($a->status, QuizAttemptHistory::SCORABLE_STATUSES, true)
        );

        $performanceData = [
            'user' => $user,
            'total_score' => (int) $attempts->sum('score'),
            'total_attempts' => $attempts->count(),
            'average_score' => $attempts->isNotEmpty() ? round($attempts->avg('score'), 1) : 0,
            'highest_score' => (int) ($attempts->max('score') ?? 0),
            'rank' => $user->getRank(),
            'rank_text' => $user->getRankText(),
            'rank_icon' => $user->getRankIcon(),
            'rank_badge_class' => $user->getRankBadgeClass(),
            'quiz_performance' => $attempts->groupBy('quiz_id')->map(function ($quizAttempts) {
                $quiz = $quizAttempts->first()->quiz;
                $maxPoints = (int) ($quiz->questions->sum('points') ?? 0);
                $bestScore = (int) $quizAttempts->max('score');

                return [
                    'quiz' => $quiz,
                    'best_score' => $bestScore,
                    'max_points' => $maxPoints,
                    'best_percent' => $maxPoints > 0 ? round($bestScore / $maxPoints * 100, 1) : 0,
                    'attempts' => $quizAttempts->count(),
                    'average_score' => round($quizAttempts->avg('score'), 1),
                    'last_attempt' => $quizAttempts->max('completed_at'),
                ];
            })->values(),
            'university' => $user->university,
        ];

        return response()->json($performanceData);
    }

    public function getTopicDetails($topic)
    {
        $topic = urldecode($topic);

        $quizzes = Quiz::query()
            ->where('is_active', true)
            ->when($topic === 'Uncategorized', function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('topic')->orWhere('topic', '');
                });
            }, fn ($query) => $query->where('topic', $topic))
            ->with(['attemptHistory' => function ($query) {
                $this->scorableAttemptHistory($query);
                $query->with('user.university');
            }, 'questions'])
            ->get();

        $allAttempts = $this->enrichAttemptScores($quizzes->flatMap->attemptHistory);
        $topicStats = $this->buildTopicAnalytics($allAttempts);

        $performanceData = [
            'topic' => $topic,
            'total_quizzes' => $quizzes->count(),
            'total_attempts' => $allAttempts->count(),
            'unique_students' => $topicStats['students']->count(),
            'topic_max_points' => $topicStats['topic_max_points'],
            'average_score' => $topicStats['students']->isNotEmpty()
                ? round($topicStats['students']->avg('total_score'), 1)
                : 0,
            'average_percent' => $topicStats['students']->isNotEmpty()
                ? round($topicStats['students']->avg('score_percent'), 1)
                : 0,
            'highest_score' => (int) ($topicStats['students']->max('total_score') ?? 0),
            'lowest_score' => (int) ($topicStats['students']->min('total_score') ?? 0),
            'quizzes' => $topicStats['quizzes']->map(function ($quiz) use ($quizzes) {
                $full = $quizzes->firstWhere('id', $quiz['id']);

                return array_merge($quiz, [
                    'description' => $full?->description,
                    'total_questions' => $full?->questions?->count() ?? 0,
                    'total_points' => $quiz['max_points'],
                    'completion_rate' => $quiz['attempts'] > 0 ? 100 : 0,
                ]);
            })->values(),
            'top_performers' => $topicStats['students']->sortByDesc('total_score')->values(),
            'university_breakdown' => $this->getUniversityBreakdown($allAttempts),
            'score_distribution' => $this->getScoreDistribution($allAttempts),
        ];

        return response()->json($performanceData);
    }

    private function getRankBadgeClass($rank)
    {
        if ($rank === 1) {
            return 'bg-yellow-100 text-yellow-800 border-yellow-200'; // Gold
        } elseif ($rank === 2) {
            return 'bg-gray-100 text-gray-800 border-gray-200'; // Silver
        } elseif ($rank === 3) {
            return 'bg-orange-100 text-orange-800 border-orange-200'; // Bronze
        } elseif ($rank <= 10) {
            return 'bg-blue-100 text-blue-800 border-blue-200'; // Top 10
        } elseif ($rank <= 50) {
            return 'bg-green-100 text-green-800 border-green-200'; // Top 50
        } else {
            return 'bg-gray-100 text-gray-600 border-gray-200'; // Default
        }
    }

    private function getRankIcon($rank)
    {
        if ($rank === 1) {
            return '🥇'; // Gold medal
        } elseif ($rank === 2) {
            return '🥈'; // Silver medal
        } elseif ($rank === 3) {
            return '🥉'; // Bronze medal
        } elseif ($rank <= 10) {
            return '⭐'; // Star for top 10
        } else {
            return '🏆'; // Trophy for others
        }
    }

    private function getScoreDistribution($attempts)
    {
        $ranges = [
            '0-20%' => 0,
            '21-40%' => 0,
            '41-60%' => 0,
            '61-80%' => 0,
            '81-100%' => 0,
        ];

        foreach ($attempts as $attempt) {
            $maxPoints = (int) ($attempt->quiz?->questions?->sum('points') ?? 0);
            if ($maxPoints === 0 && $attempt->relationLoaded('quiz') === false) {
                $attempt->loadMissing('quiz.questions');
                $maxPoints = (int) ($attempt->quiz?->questions?->sum('points') ?? 0);
            }
            $score = (int) ($attempt->effective_score ?? $attempt->score ?? 0);
            if (! isset($attempt->effective_score) && $attempt instanceof QuizAttemptHistory) {
                $score = $this->effectiveHistoryScore($attempt);
                $attempt->loadMissing('quiz.questions');
                $maxPoints = (int) ($attempt->quiz?->questions?->sum('points') ?? $maxPoints);
            }
            $percentage = $maxPoints > 0 ? ($score / $maxPoints) * 100 : 0;

            if ($percentage <= 20) {
                $ranges['0-20%']++;
            } elseif ($percentage <= 40) {
                $ranges['21-40%']++;
            } elseif ($percentage <= 60) {
                $ranges['41-60%']++;
            } elseif ($percentage <= 80) {
                $ranges['61-80%']++;
            } else {
                $ranges['81-100%']++;
            }
        }

        return $ranges;
    }

    private function getUniversityBreakdown($attempts)
    {
        return $attempts->groupBy('user.university.name')
            ->map(function ($universityAttempts, $universityName) {
                return [
                    'university' => $universityName,
                    'university_name' => $universityName ?? 'N/A',
                    'attempts' => $universityAttempts->count(),
                    'total_attempts' => $universityAttempts->count(),
                    'total_students' => $universityAttempts->unique('user_id')->count(),
                    'average_score' => round($universityAttempts->avg(fn ($a) => (int) ($a->effective_score ?? $a->score ?? 0)), 1),
                    'highest_score' => (int) ($universityAttempts->max(fn ($a) => (int) ($a->effective_score ?? $a->score ?? 0)) ?? 0),
                ];
            })
            ->sortByDesc('average_score')
            ->values();
    }

    /**
     * Get quiz-based rankings for students with arrow indicators
     * Based on rank movement (lower number = better rank)
     */
    private function getQuizRankings()
    {
        try {
            $students = $this->usersWithQuizAttempts()
                ->with(['university', 'quizAttemptHistory' => function ($query) {
                    $this->scorableAttemptHistory($query)->with('quiz.questions');
                }])
                ->get()
                ->map(function ($user) {
                    $attempts = $this->enrichAttemptScores($user->quizAttemptHistory);
                    $user->setAttribute('analytics_total_score', (int) $attempts->sum('effective_score'));
                    $user->setAttribute('analytics_attempt_count', $attempts->count());
                    $user->setAttribute('analytics_avg_percent', $attempts->isNotEmpty()
                        ? round($attempts->avg('score_percent'), 1)
                        : 0);

                    return $user;
                })
                ->filter(fn ($user) => ($user->analytics_attempt_count ?? 0) > 0)
                ->sortByDesc('analytics_total_score')
                ->values();

            // Get quiz rank history and last arrow from cache
            $quizRankHistory = Cache::get('student_quiz_rankings_history', []);
            $quizLastArrows = Cache::get('student_quiz_rankings_last_arrow', []);
            $quizPreviousRankings = Cache::get('student_quiz_rankings_previous', []);

            // Build current rankings map before processing
            $currentQuizRankings = [];
            foreach ($students as $index => $student) {
                $currentQuizRankings[$student->id] = $index + 1;
            }

            // Add current rank and determine arrow direction based on trend analysis
            $ranked = $students->map(function($user, $index) use ($quizRankHistory, $quizLastArrows, $quizPreviousRankings) {
                $currentRank = $index + 1;
                $studentId = $user->id;
                $previousRank = $quizPreviousRankings[$studentId] ?? null;
                $studentHistory = $quizRankHistory[$studentId] ?? [];
                $lastArrow = $quizLastArrows[$studentId] ?? null;

                $arrowDirection = null;

                if ($previousRank !== null && $previousRank > 0) {
                    // Compare current rank with previous rank
                    if ($currentRank < $previousRank) {
                        // Rank improved (lower number = better)
                        $arrowDirection = 'up';
                    } elseif ($currentRank > $previousRank) {
                        // Rank declined (higher number = worse)
                        $arrowDirection = 'down';
                    } else {
                        // No rank change - keep the previous arrow
                        $arrowDirection = $lastArrow ?? 'up'; // Default to up if no previous arrow
                    }
                } else {
                    // No previous rank - default to up arrow
                    $arrowDirection = 'up';
                }

                // Analyze trend if we have history (at least 3 data points for trend analysis)
                if (count($studentHistory) >= 3) {
                    $recentHistory = array_slice($studentHistory, -3); // Last 3 ranks
                    $isConsistentImprovement = true;
                    $isConsistentDecline = true;

                    // Check if consistently improving (each rank is better than previous)
                    for ($i = 1; $i < count($recentHistory); $i++) {
                        if ($recentHistory[$i] >= $recentHistory[$i - 1]) {
                            $isConsistentImprovement = false;
                        }
                        if ($recentHistory[$i] <= $recentHistory[$i - 1]) {
                            $isConsistentDecline = false;
                        }
                    }

                    // Override arrow based on trend
                    if ($isConsistentImprovement) {
                        $arrowDirection = 'up';
                    } elseif ($isConsistentDecline) {
                        $arrowDirection = 'down';
                    }
                    // If fluctuating, keep the last arrow (already set above)
                }

                // Add current rank to history (keep last 5 ranks)
                if (!isset($quizRankHistory[$studentId])) {
                    $quizRankHistory[$studentId] = [];
                }
                $quizRankHistory[$studentId][] = $currentRank;
                if (count($quizRankHistory[$studentId]) > 5) {
                    array_shift($quizRankHistory[$studentId]); // Remove oldest
                }

                // Store last arrow direction
                $quizLastArrows[$studentId] = $arrowDirection;

                $totalScore = (int) ($user->analytics_total_score ?? 0);
                $totalAttempts = (int) ($user->analytics_attempt_count ?? 0);
                $averageScore = (float) ($user->analytics_avg_percent ?? 0);

                return [
                    'rank' => $currentRank,
                    'student' => $user,
                    'total_score' => $totalScore,
                    'total_attempts' => $totalAttempts,
                    'average_score' => $averageScore,
                    'university' => $user->university,
                    'previous_rank' => $previousRank,
                    'arrow_direction' => $arrowDirection,
                ];
            });

            // Store updated data for next comparison
            Cache::put('student_quiz_rankings_previous', $currentQuizRankings, now()->addDays(30));
            Cache::put('student_quiz_rankings_history', $quizRankHistory, now()->addDays(30));
            Cache::put('student_quiz_rankings_last_arrow', $quizLastArrows, now()->addDays(30));

            return $ranked;
        } catch (\Exception $e) {
            \Log::error('Error getting quiz rankings: ' . $e->getMessage());
            return collect();
        }
    }
}
