<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptHistory;
use App\Models\User;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    private function studentRoles(): array
    {
        return ['student', 'user'];
    }

    private function completedAttemptHistory($query)
    {
        return $query->where('status', 'completed');
    }

    public function index()
    {
        // Get overall student performance rankings
        $topPerformers = $this->getTopPerformers();

        // Get student performance statistics
        $studentStats = $this->getStudentPerformanceStats();

        // Get topic-based performance analytics
        $topicPerformance = $this->getTopicPerformance();

        // Get student strengths by topic
        $studentTopicStrengths = $this->getStudentTopicStrengths();

        // Get quiz performance statistics
        $quizStats = $this->getQuizPerformanceStats();

        // Get university performance
        $universityPerformance = $this->getUniversityPerformance();

        // Get recent high scores
        $recentHighScores = $this->getRecentHighScores();

        // Get overall statistics
        $overallStats = $this->getOverallStats();

        // Get top performers by quiz (for backward compatibility)
        $topPerformersByQuiz = $this->getTopPerformersByQuiz();

        // Get quiz-based rankings with arrow indicators
        $quizRankings = $this->getQuizRankings();

        return view('admin.analytics.index', compact(
            'topPerformers',
            'studentStats',
            'topicPerformance',
            'studentTopicStrengths',
            'topPerformersByQuiz',
            'quizStats',
            'universityPerformance',
            'recentHighScores',
            'overallStats',
            'quizRankings'
        ));
    }

    private function getTopPerformers()
    {
        return User::whereIn('role', $this->studentRoles())
            ->where('is_active', true)
            ->with(['university', 'quizAttemptHistory' => function($query) {
                $this->completedAttemptHistory($query);
            }])
            ->withSum(['quizAttemptHistory' => function ($query) {
                $this->completedAttemptHistory($query);
            }], 'score')
            ->withCount(['quizAttemptHistory' => function($query) {
                $this->completedAttemptHistory($query);
            }])
            ->get()
            ->filter(function($user) {
                return ($user->quiz_attempt_history_sum_score ?? 0) > 0;
            })
            ->sortByDesc('quiz_attempt_history_sum_score')
            ->take(50)
            ->values()
            ->map(function($user, $index) {
                $totalScore = $user->quiz_attempt_history_sum_score ?? 0;
                $totalAttempts = $user->quiz_attempt_history_count ?? 0;
                $averageScore = $totalAttempts > 0 ? round($totalScore / $totalAttempts, 2) : 0;

                return [
                    'rank' => $index + 1,
                    'user' => $user,
                    'total_score' => $totalScore,
                    'total_attempts' => $totalAttempts,
                    'average_score' => $averageScore,
                    'university' => $user->university,
                    'rank_badge_class' => $this->getRankBadgeClass($index + 1),
                    'rank_icon' => $this->getRankIcon($index + 1)
                ];
            });
    }

    private function getStudentPerformanceStats()
    {
        $totalStudents = User::whereIn('role', $this->studentRoles())->where('is_active', true)->count();
        $studentsWithAttempts = User::whereIn('role', $this->studentRoles())
            ->where('is_active', true)
            ->whereHas('quizAttemptHistory', function($query) {
                $this->completedAttemptHistory($query);
            })
            ->count();

        $totalScore = User::whereIn('role', $this->studentRoles())
            ->where('is_active', true)
            ->withSum(['quizAttemptHistory' => function ($query) {
                $this->completedAttemptHistory($query);
            }], 'score')
            ->get()
            ->sum('quiz_attempt_history_sum_score');

        $averageScore = $studentsWithAttempts > 0 ? round($totalScore / $studentsWithAttempts, 2) : 0;

        $topScore = User::whereIn('role', $this->studentRoles())
            ->where('is_active', true)
            ->withSum(['quizAttemptHistory' => function ($query) {
                $this->completedAttemptHistory($query);
            }], 'score')
            ->get()
            ->max('quiz_attempt_history_sum_score') ?? 0;

        return [
            'total_students' => $totalStudents,
            'active_students' => $studentsWithAttempts,
            'participation_rate' => $totalStudents > 0 ? round(($studentsWithAttempts / $totalStudents) * 100, 2) : 0,
            'average_total_score' => $averageScore,
            'highest_total_score' => $topScore,
            'unranked_students' => $totalStudents - $studentsWithAttempts
        ];
    }

    private function getTopPerformersByQuiz()
    {
        return Quiz::with(['attemptHistory' => function($query) {
                $query->where('status', 'completed')
                      ->with('user.university')
                      ->orderBy('score', 'desc')
                      ->orderBy('completed_at', 'desc');
            }])
            ->where('is_active', true)
            ->get()
            ->map(function($quiz) {
                $topAttempts = $quiz->attemptHistory->take(5);
                return [
                    'quiz' => $quiz,
                    'top_attempts' => $topAttempts,
                    'total_attempts' => $quiz->attemptHistory->count(),
                    'average_score' => $quiz->attemptHistory->avg('score') ?? 0,
                    'highest_score' => $quiz->attemptHistory->max('score') ?? 0
                ];
            });
    }

    private function getQuizPerformanceStats()
    {
        return Quiz::withCount(['attempts' => function($query) {
                $query->whereNotNull('completed_at');
            }])
            ->with(['attempts' => function($query) {
                $query->whereNotNull('completed_at');
            }, 'questions'])
            ->where('is_active', true)
            ->get()
            ->map(function($quiz) {
                $attempts = $quiz->attempts;
                $totalPoints = $attempts ? $attempts->sum('points_earned') : 0;
                $maxPossiblePoints = $quiz->questions ? $quiz->questions->sum('points') : 0;
                $attemptsCount = $attempts ? $attempts->count() : 0;

                return [
                    'quiz' => $quiz,
                    'total_attempts' => $attemptsCount,
                    'average_score' => $attemptsCount > 0 ? round($totalPoints / $attemptsCount, 2) : 0,
                    'highest_score' => $attempts ? ($attempts->max('points_earned') ?? 0) : 0,
                    'completion_rate' => $attemptsCount > 0 && $attempts ? round(($attempts->whereNotNull('completed_at')->count() / $attemptsCount) * 100, 2) : 0,
                    'difficulty_score' => ($maxPossiblePoints > 0 && $attemptsCount > 0) ? round(($totalPoints / ($attemptsCount * $maxPossiblePoints)) * 100, 2) : 0
                ];
            })
            ->sortByDesc('total_attempts');
    }

    private function getUniversityPerformance()
    {
        return University::with(['users.quizAttempts' => function($query) {
                $query->whereNotNull('completed_at');
            }])
            ->get()
            ->map(function($university) {
                $allAttempts = $university->users->flatMap->quizAttempts;

                return [
                    'university' => $university,
                    'total_students' => $university->users->whereIn('role', $this->studentRoles())->count(),
                    'total_attempts' => $allAttempts->count(),
                    'average_score' => $allAttempts->count() > 0 ? round($allAttempts->avg('points_earned'), 2) : 0,
                    'highest_score' => $allAttempts->max('points_earned') ?? 0,
                    'active_students' => $university->users->whereIn('role', $this->studentRoles())->where('is_active', true)->count()
                ];
            })
            ->sortByDesc('average_score');
    }

    private function getRecentHighScores()
    {
        return QuizAttemptHistory::with(['user.university', 'quiz'])
            ->where('status', 'completed')
            ->orderBy('score', 'desc')
            ->orderBy('completed_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($attempt) {
                $maxPossiblePoints = $attempt->quiz->questions->sum('points');
                $percentage = $maxPossiblePoints > 0 ? round(($attempt->score / $maxPossiblePoints) * 100, 2) : 0;

                return [
                    'attempt' => $attempt,
                    'percentage' => $percentage,
                    'user' => $attempt->user,
                    'quiz' => $attempt->quiz,
                    'university' => $attempt->user->university
                ];
            });
    }

    private function getOverallStats()
    {
        $totalUsers = User::whereIn('role', $this->studentRoles())->count();
        $activeUsers = User::whereIn('role', $this->studentRoles())->where('is_active', true)->count();
        $totalQuizzes = Quiz::where('is_active', true)->count();
        $totalAttempts = QuizAttemptHistory::where('status', 'completed')->count();
        $averageScore = QuizAttemptHistory::where('status', 'completed')->avg('score') ?? 0;

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'total_quizzes' => $totalQuizzes,
            'total_attempts' => $totalAttempts,
            'average_score' => round($averageScore, 2),
            'completion_rate' => QuizAttemptHistory::count() > 0 ? round((QuizAttemptHistory::where('status', 'completed')->count() / QuizAttemptHistory::count()) * 100, 2) : 0
        ];
    }

    public function getQuizDetails($quizId)
    {
        $quiz = Quiz::with(['questions', 'attemptHistory.user.university'])
            ->where('is_active', true)
            ->findOrFail($quizId);

        $attempts = $quiz->attemptHistory->where('status', 'completed');

        // Get detailed performance data
        $performanceData = [
            'quiz' => $quiz,
            'total_attempts' => $attempts->count(),
            'average_score' => $attempts->avg('score') ?? 0,
            'highest_score' => $attempts->max('score') ?? 0,
            'lowest_score' => $attempts->min('score') ?? 0,
            'completion_rate' => ($attempts->count() > 0 && $quiz->attemptHistory->count() > 0) ? round(($attempts->count() / $quiz->attemptHistory->count()) * 100, 2) : 0,
            'top_performers' => $attempts->sortByDesc('score')->take(10),
            'score_distribution' => $this->getScoreDistribution($attempts),
            'university_breakdown' => $this->getUniversityBreakdown($attempts)
        ];

        return response()->json($performanceData);
    }

    private function getTopicPerformance()
    {
        return Quiz::whereNotNull('topic')
            ->where('is_active', true)
            ->with(['attemptHistory' => function($query) {
                $query->where('status', 'completed')
                      ->with('user.university');
            }])
            ->get()
            ->groupBy('topic')
            ->map(function($quizzes, $topic) {
                $allAttempts = $quizzes->flatMap->attemptHistory;
                $uniqueUsers = $allAttempts->pluck('user')->unique('id');

                // Get top performers for this topic
                $topPerformers = $allAttempts->groupBy('user_id')
                    ->map(function($userAttempts, $userId) {
                        $user = $userAttempts->first()->user;
                        $totalScore = $userAttempts->sum('score');
                        $totalAttempts = $userAttempts->count();
                        $averageScore = $totalAttempts > 0 ? round($totalScore / $totalAttempts, 2) : 0;

                        return [
                            'user' => $user,
                            'total_score' => $totalScore,
                            'total_attempts' => $totalAttempts,
                            'average_score' => $averageScore,
                            'university' => $user->university
                        ];
                    })
                    ->sortByDesc('total_score')
                    ->take(5)
                    ->values();

                return [
                    'topic' => $topic,
                    'total_quizzes' => $quizzes->count(),
                    'total_attempts' => $allAttempts->count(),
                    'unique_students' => $uniqueUsers->count(),
                    'average_score' => $allAttempts->count() > 0 ? round($allAttempts->avg('score'), 2) : 0,
                    'highest_score' => $allAttempts->max('score') ?? 0,
                    'top_performers' => $topPerformers,
                    'quizzes' => $quizzes->map(function($quiz) {
                        return [
                            'id' => $quiz->id,
                            'title' => $quiz->title,
                            'attempts' => $quiz->attemptHistory->count(),
                            'average_score' => $quiz->attemptHistory->avg('score') ?? 0
                        ];
                    })
                ];
            })
            ->sortByDesc('total_attempts');
    }

    private function getStudentTopicStrengths()
    {
        return User::whereIn('role', $this->studentRoles())
            ->where('is_active', true)
            ->with(['quizAttemptHistory.quiz'])
            ->get()
            ->map(function($user) {
                $attempts = $user->quizAttemptHistory->where('status', 'completed');

                // Group attempts by topic
                $topicPerformance = $attempts->groupBy('quiz.topic')
                    ->map(function($topicAttempts, $topic) {
                        if (!$topic) return null;

                        $totalScore = $topicAttempts->sum('score');
                        $totalAttempts = $topicAttempts->count();
                        $averageScore = $totalAttempts > 0 ? round($totalScore / $totalAttempts, 2) : 0;
                        $quizzesTaken = $topicAttempts->pluck('quiz_id')->unique()->count();

                        return [
                            'topic' => $topic,
                            'total_score' => $totalScore,
                            'total_attempts' => $totalAttempts,
                            'average_score' => $averageScore,
                            'quizzes_taken' => $quizzesTaken,
                            'best_score' => $topicAttempts->max('score') ?? 0
                        ];
                    })
                    ->filter()
                    ->sortByDesc('average_score');

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
        $user = User::with(['university', 'quizAttempts.quiz'])
            ->whereIn('role', $this->studentRoles())
            ->findOrFail($userId);

        $attempts = $user->quizAttempts->where('completed_at', '!=', null);

        $performanceData = [
            'user' => $user,
            'total_score' => $attempts->sum('points_earned'),
            'total_attempts' => $attempts->count(),
            'average_score' => $attempts->avg('points_earned') ?? 0,
            'highest_score' => $attempts->max('points_earned') ?? 0,
            'rank' => $user->getRank(),
            'rank_text' => $user->getRankText(),
            'rank_icon' => $user->getRankIcon(),
            'rank_badge_class' => $user->getRankBadgeClass(),
            'quiz_performance' => $attempts->groupBy('quiz_id')->map(function($quizAttempts, $quizId) {
                $quiz = $quizAttempts->first()->quiz;
                return [
                    'quiz' => $quiz,
                    'best_score' => $quizAttempts->max('points_earned'),
                    'attempts' => $quizAttempts->count(),
                    'average_score' => $quizAttempts->avg('points_earned'),
                    'last_attempt' => $quizAttempts->max('completed_at')
                ];
            })->values(),
            'university' => $user->university
        ];

        return response()->json($performanceData);
    }

    public function getTopicDetails($topic)
    {
        // Decode the topic parameter in case it was URL encoded
        $topic = urldecode($topic);

        $quizzes = Quiz::where('topic', $topic)
            ->where('is_active', true)
            ->with(['attempts.user.university', 'questions'])
            ->get();

        $allAttempts = $quizzes->flatMap->attempts->where('completed_at', '!=', null);
        $uniqueUsers = $allAttempts->pluck('user')->unique('id');

        // Get detailed performance data for this topic
        $performanceData = [
            'topic' => $topic,
            'total_quizzes' => $quizzes->count(),
            'total_attempts' => $allAttempts->count(),
            'unique_students' => $uniqueUsers->count(),
            'average_score' => $allAttempts->avg('points_earned') ?? 0,
            'highest_score' => $allAttempts->max('points_earned') ?? 0,
            'lowest_score' => $allAttempts->min('points_earned') ?? 0,
            'quizzes' => $quizzes->map(function($quiz) {
                $attempts = $quiz->attempts->where('completed_at', '!=', null);
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'total_questions' => $quiz->questions->count(),
                    'total_points' => $quiz->questions->sum('points'),
                    'attempts' => $attempts->count(),
                    'average_score' => $attempts->avg('points_earned') ?? 0,
                    'highest_score' => $attempts->max('points_earned') ?? 0,
                    'completion_rate' => $attempts->count() > 0 ? round(($attempts->whereNotNull('completed_at')->count() / $attempts->count()) * 100, 2) : 0
                ];
            }),
            'top_performers' => $allAttempts->groupBy('user_id')
                ->map(function($userAttempts, $userId) {
                    $user = $userAttempts->first()->user;
                    $totalScore = $userAttempts->sum('points_earned');
                    $totalAttempts = $userAttempts->count();
                    $averageScore = $totalAttempts > 0 ? round($totalScore / $totalAttempts, 2) : 0;
                    $quizzesTaken = $userAttempts->pluck('quiz_id')->unique()->count();

                    return [
                        'user' => $user,
                        'total_score' => $totalScore,
                        'total_attempts' => $totalAttempts,
                        'average_score' => $averageScore,
                        'quizzes_taken' => $quizzesTaken,
                        'best_score' => $userAttempts->max('points_earned') ?? 0,
                        'university' => $user->university
                    ];
                })
                ->sortByDesc('total_score')
                ->take(10)
                ->values(),
            'university_breakdown' => $allAttempts->groupBy('user.university.name')
                ->map(function ($universityAttempts, $universityName) {
                    $totalPoints = $universityAttempts->sum('points_earned');
                    $totalStudents = $universityAttempts->unique('user_id')->count();
                    return [
                        'university_name' => $universityName ?? 'N/A',
                        'total_attempts' => $universityAttempts->count(),
                        'average_score' => $universityAttempts->avg('points_earned') ?? 0,
                        'total_students' => $totalStudents
                    ];
                })
                ->sortByDesc('average_score')
                ->values(),
            'score_distribution' => $this->getScoreDistribution($allAttempts)
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
        $maxScore = $attempts->max('points_earned') ?? 0;
        $ranges = [
            '0-20%' => 0,
            '21-40%' => 0,
            '41-60%' => 0,
            '61-80%' => 0,
            '81-100%' => 0
        ];

        foreach ($attempts as $attempt) {
            $percentage = $maxScore > 0 ? ($attempt->points_earned / $maxScore) * 100 : 0;

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
            ->map(function($universityAttempts, $universityName) {
                return [
                    'university' => $universityName,
                    'attempts' => $universityAttempts->count(),
                    'average_score' => round($universityAttempts->avg('points_earned'), 2),
                    'highest_score' => $universityAttempts->max('points_earned')
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
            $students = User::whereIn('role', $this->studentRoles())
                ->where('is_active', true)
                ->with(['university', 'quizAttemptHistory' => function($query) {
                    $this->completedAttemptHistory($query);
                }])
                ->withSum(['quizAttemptHistory' => function ($query) {
                    $this->completedAttemptHistory($query);
                }], 'score')
                ->withCount(['quizAttemptHistory' => function($query) {
                    $this->completedAttemptHistory($query);
                }])
                ->get()
                ->filter(function($user) {
                    return ($user->quiz_attempt_history_sum_score ?? 0) > 0;
                })
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
