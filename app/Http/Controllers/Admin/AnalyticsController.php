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
     * One row per student: their best-scoring attempt for the given history set.
     */
    private function bestScorePerStudent($attempts)
    {
        return $attempts
            ->groupBy('user_id')
            ->map(fn ($userAttempts) => $userAttempts->sortByDesc('score')->first())
            ->values();
    }

    /**
     * @return array{total_attempts: int, student_count: int, average_score: float, highest_score: int}
     */
    private function quizLeaderboardStats($attempts): array
    {
        $bests = $this->bestScorePerStudent($attempts);

        return [
            'total_attempts' => $attempts->count(),
            'student_count' => $bests->count(),
            'average_score' => $bests->isNotEmpty() ? round($bests->avg('score'), 1) : 0,
            'highest_score' => (int) ($bests->max('score') ?? 0),
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
                $this->scorableAttemptHistory($query);
            }])
            ->withSum(['quizAttemptHistory' => function ($query) {
                $this->scorableAttemptHistory($query);
            }], 'score')
            ->withCount(['quizAttemptHistory' => function ($query) {
                $this->scorableAttemptHistory($query);
            }])
            ->get()
            ->filter(fn ($user) => ($user->quiz_attempt_history_count ?? 0) > 0)
            ->sort(function ($a, $b) {
                $scoreCmp = ($b->quiz_attempt_history_sum_score ?? 0) <=> ($a->quiz_attempt_history_sum_score ?? 0);
                if ($scoreCmp !== 0) {
                    return $scoreCmp;
                }

                return ($b->quiz_attempt_history_count ?? 0) <=> ($a->quiz_attempt_history_count ?? 0);
            })
            ->take(50)
            ->values()
            ->map(function($user, $index) {
                $totalScore = $user->quiz_attempt_history_sum_score ?? 0;
                $totalAttempts = $user->quiz_attempt_history_count ?? 0;
                $averageScore = $totalAttempts > 0 ? round($totalScore / $totalAttempts, 1) : 0;

                return [
                    'rank' => $index + 1,
                    'user' => $user,
                    'total_score' => (int) $totalScore,
                    'total_attempts' => $totalAttempts,
                    'average_score' => $averageScore,
                    'university' => $user->university,
                    'rank_badge_class' => $this->getRankBadgeClass($index + 1),
                    'rank_icon' => $this->getRankIcon($index + 1)
                ];
            });
    }

    private function getStudentPerformanceStats(): array
    {
        $totalStudents = $this->learnerPoolQuery()->count();
        $activeStudents = $this->usersWithQuizAttempts()->count();

        $scorableAttempts = QuizAttemptHistory::query()->scorable()->get();
        $totalQuizAttempts = $scorableAttempts->count();

        $bestPerStudent = $scorableAttempts
            ->groupBy('user_id')
            ->map(fn ($rows) => (int) $rows->max('score'));

        $averageBestPerStudent = $bestPerStudent->isNotEmpty()
            ? round($bestPerStudent->avg(), 1)
            : 0;

        $bestSingleAttempt = $bestPerStudent->isNotEmpty()
            ? (int) $bestPerStudent->max()
            : 0;

        $averagePerAttempt = $totalQuizAttempts > 0
            ? round($scorableAttempts->avg('score'), 1)
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
                $topPerformers = $this->bestScorePerStudent($attempts)
                    ->sortByDesc('score')
                    ->values()
                    ->take(5);

                return [
                    'quiz' => $quiz,
                    'top_attempts' => $topPerformers,
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

        return $attempts
            ->groupBy(fn ($a) => $a->quiz_id.'-'.$a->user_id)
            ->map(function ($userQuizAttempts) {
                $best = $userQuizAttempts->sortByDesc('score')->first();
                $maxPossiblePoints = (int) ($best->quiz->questions->sum('points') ?? 0);
                $percentage = $maxPossiblePoints > 0
                    ? round(($best->score / $maxPossiblePoints) * 100, 1)
                    : 0;

                return [
                    'attempt' => $best,
                    'percentage' => $percentage,
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
        $studentBests = $this->bestScorePerStudent($attempts);

        $performanceData = [
            'quiz' => $quiz,
            'total_attempts' => $stats['total_attempts'],
            'student_count' => $stats['student_count'],
            'average_score' => $stats['average_score'],
            'highest_score' => $stats['highest_score'],
            'lowest_score' => (int) ($studentBests->min('score') ?? 0),
            'completion_rate' => $stats['student_count'] > 0 ? 100 : 0,
            'top_performers' => $studentBests->sortByDesc('score')->take(10)->values(),
            'score_distribution' => $this->getScoreDistribution($studentBests),
            'university_breakdown' => $this->getUniversityBreakdown($studentBests),
        ];

        return response()->json($performanceData);
    }

    private function getTopicPerformance(): Collection
    {
        $attempts = QuizAttemptHistory::query()
            ->scorable()
            ->with(['user.university', 'quiz'])
            ->whereHas('quiz', fn ($q) => $q->where('is_active', true))
            ->get();

        if ($attempts->isEmpty()) {
            return collect();
        }

        return $attempts
            ->groupBy(fn ($attempt) => trim((string) ($attempt->quiz->topic ?? '')) ?: 'Uncategorized')
            ->map(function ($topicAttempts, $topic) {
                $bestPerStudent = $topicAttempts
                    ->groupBy('user_id')
                    ->map(fn ($rows) => (int) $rows->max('score'));

                $topPerformers = $topicAttempts
                    ->groupBy('user_id')
                    ->map(function ($userAttempts) {
                        $user = $userAttempts->first()->user;
                        $bestScore = (int) $userAttempts->max('score');

                        return [
                            'user' => $user,
                            'total_score' => $bestScore,
                            'total_attempts' => $userAttempts->count(),
                            'average_score' => $bestScore,
                            'university' => $user->university,
                        ];
                    })
                    ->sortByDesc('total_score')
                    ->take(5)
                    ->values();

                return [
                    'topic' => $topic,
                    'total_quizzes' => $topicAttempts->pluck('quiz_id')->unique()->count(),
                    'total_attempts' => $topicAttempts->count(),
                    'unique_students' => $topicAttempts->pluck('user_id')->unique()->count(),
                    'average_score' => $bestPerStudent->isNotEmpty() ? round($bestPerStudent->avg(), 1) : 0,
                    'highest_score' => (int) ($bestPerStudent->max() ?? 0),
                    'top_performers' => $topPerformers,
                    'quizzes' => $topicAttempts
                        ->groupBy('quiz_id')
                        ->map(function ($quizAttempts) {
                            $quiz = $quizAttempts->first()->quiz;
                            $bests = $quizAttempts->groupBy('user_id')->map(fn ($rows) => (int) $rows->max('score'));

                            return [
                                'id' => $quiz->id,
                                'title' => $quiz->title,
                                'attempts' => $quizAttempts->count(),
                                'average_score' => $bests->isNotEmpty() ? round($bests->avg(), 1) : 0,
                            ];
                        })
                        ->values(),
                ];
            })
            ->filter(fn (array $data) => $data['total_attempts'] > 0)
            ->sortByDesc('total_attempts')
            ->values();
    }

    private function getStudentTopicStrengths()
    {
        return $this->usersWithQuizAttempts()
            ->with(['university', 'quizAttemptHistory.quiz'])
            ->get()
            ->map(function ($user) {
                $attempts = $user->quizAttemptHistory->filter(
                    fn ($a) => in_array($a->status, QuizAttemptHistory::SCORABLE_STATUSES, true)
                );

                $topicPerformance = $attempts
                    ->groupBy(fn ($a) => trim((string) ($a->quiz->topic ?? '')) ?: 'Uncategorized')
                    ->map(function ($topicAttempts, $topic) {
                        $bestPerQuiz = $topicAttempts
                            ->groupBy('quiz_id')
                            ->map(fn ($quizAttempts) => (int) $quizAttempts->max('score'));

                        $averageScore = $bestPerQuiz->isNotEmpty()
                            ? round($bestPerQuiz->avg(), 1)
                            : 0;

                        return [
                            'topic' => $topic,
                            'total_score' => (int) $topicAttempts->sum('score'),
                            'total_attempts' => $topicAttempts->count(),
                            'average_score' => $averageScore,
                            'quizzes_taken' => $topicAttempts->pluck('quiz_id')->unique()->count(),
                            'best_score' => (int) ($topicAttempts->max('score') ?? 0),
                        ];
                    })
                    ->sortByDesc('average_score')
                    ->values();

                // Find strongest topic
                $strongestTopic = $topicPerformance->first();

                return [
                    'user' => $user,
                    'total_topics' => $topicPerformance->count(),
                    'strongest_topic' => $strongestTopic ? $strongestTopic['topic'] : null,
                    'strongest_topic_score' => $strongestTopic ? $strongestTopic['average_score'] : 0,
                    'topic_performance' => $topicPerformance->values(),
                    'university' => $user->university
                ];
            })
            ->filter(function($student) {
                return $student['total_topics'] > 0;
            })
            ->sortByDesc('strongest_topic_score')
            ->take(20);
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

        $allAttempts = $quizzes->flatMap->attemptHistory;
        $uniqueUsers = $allAttempts->pluck('user')->unique('id');

        $performanceData = [
            'topic' => $topic,
            'total_quizzes' => $quizzes->count(),
            'total_attempts' => $allAttempts->count(),
            'unique_students' => $uniqueUsers->count(),
            'average_score' => $allAttempts->isNotEmpty() ? round($allAttempts->avg('score'), 1) : 0,
            'highest_score' => (int) ($allAttempts->max('score') ?? 0),
            'lowest_score' => (int) ($allAttempts->min('score') ?? 0),
            'quizzes' => $quizzes->map(function ($quiz) {
                $attempts = $quiz->attemptHistory;
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'total_questions' => $quiz->questions->count(),
                    'total_points' => $quiz->questions->sum('points'),
                    'attempts' => $attempts->count(),
                    'average_score' => $attempts->isNotEmpty() ? round($attempts->avg('score'), 1) : 0,
                    'highest_score' => (int) ($attempts->max('score') ?? 0),
                    'completion_rate' => $attempts->count() > 0 ? 100 : 0,
                ];
            }),
            'top_performers' => $allAttempts->groupBy('user_id')
                ->map(function ($userAttempts) {
                    $user = $userAttempts->first()->user;
                    $totalScore = (int) $userAttempts->sum('score');
                    $totalAttempts = $userAttempts->count();
                    $averageScore = $totalAttempts > 0 ? round($totalScore / $totalAttempts, 1) : 0;
                    $quizzesTaken = $userAttempts->pluck('quiz_id')->unique()->count();

                    return [
                        'user' => $user,
                        'total_score' => $totalScore,
                        'total_attempts' => $totalAttempts,
                        'average_score' => $averageScore,
                        'quizzes_taken' => $quizzesTaken,
                        'best_score' => (int) ($userAttempts->max('score') ?? 0),
                        'university' => $user->university,
                    ];
                })
                ->sortByDesc('total_score')
                ->take(10)
                ->values(),
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
            $score = (int) ($attempt->score ?? $attempt->points_earned ?? 0);
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
                    'average_score' => round($universityAttempts->avg('score'), 1),
                    'highest_score' => (int) ($universityAttempts->max('score') ?? 0),
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
                    $this->scorableAttemptHistory($query);
                }])
                ->withSum(['quizAttemptHistory' => function ($query) {
                    $this->scorableAttemptHistory($query);
                }], 'score')
                ->withCount(['quizAttemptHistory' => function ($query) {
                    $this->scorableAttemptHistory($query);
                }])
                ->get()
                ->filter(fn ($user) => ($user->quiz_attempt_history_count ?? 0) > 0)
                ->sortByDesc('quiz_attempt_history_sum_score')
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

                $totalScore = $user->quiz_attempt_history_sum_score ?? 0;
                $totalAttempts = $user->quiz_attempt_history_count ?? 0;
                $averageScore = $totalAttempts > 0 ? round($totalScore / $totalAttempts, 2) : 0;

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
