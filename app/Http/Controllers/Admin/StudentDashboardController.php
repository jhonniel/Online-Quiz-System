<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dtr;
use App\Models\QuizAttemptHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to access Student Management.');
        }

        // Get all active students with their required training hours
        $students = User::with('university')
            ->where('role', 'student')
            ->where('is_active', true)
            ->get();

        if ($students->isEmpty()) {
            $ranked = collect();
            $studentsWithRemainingTime = 0;
        } else {
            $studentIds = $students->pluck('id');

            // Sum total DTR hours per student (using total_hours field)
            $totalsByStudent = Dtr::whereIn('user_id', $studentIds)
                ->selectRaw('user_id, COALESCE(SUM(total_hours), 0) as total_hours_sum')
                ->groupBy('user_id')
                ->pluck('total_hours_sum', 'user_id');

            $ranked = $students->map(function ($student) use ($totalsByStudent) {
                $required = (float) ($student->required_training_hours ?? 0);
                $total = (float) ($totalsByStudent[$student->id] ?? 0);
                $remaining = $required - $total; // can be negative

                return [
                    'student' => $student,
                    'required_hours' => $required,
                    'total_hours' => $total,
                    'remaining_hours' => $remaining,
                    'required_hours_formatted' => $this->formatHours($required),
                    'total_hours_formatted' => $this->formatHours($total),
                    'remaining_hours_formatted' => $this->formatHours($remaining),
                ];
            })->sortByDesc('remaining_hours')->values();

            // Get rank history and last arrow from cache
            $rankHistory = Cache::get('student_rankings_history', []); // Array of [student_id => [rank1, rank2, rank3, ...]]
            $lastArrows = Cache::get('student_rankings_last_arrow', []); // Array of [student_id => 'up'|'down']
            $previousRankings = Cache::get('student_rankings_previous', []); // Last rank for quick comparison
            
            // Build current rankings map before processing
            $currentRankings = [];
            foreach ($ranked as $index => $row) {
                $currentRankings[$row['student']->id] = $index + 1;
            }

            // Add current rank and determine arrow direction based on trend analysis
            $ranked = $ranked->map(function ($row, $index) use ($rankHistory, $lastArrows, $previousRankings) {
                $currentRank = $index + 1;
                $studentId = $row['student']->id;
                $previousRank = $previousRankings[$studentId] ?? null;
                $studentHistory = $rankHistory[$studentId] ?? [];
                $lastArrow = $lastArrows[$studentId] ?? null;

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
                if (!isset($rankHistory[$studentId])) {
                    $rankHistory[$studentId] = [];
                }
                $rankHistory[$studentId][] = $currentRank;
                if (count($rankHistory[$studentId]) > 5) {
                    array_shift($rankHistory[$studentId]); // Remove oldest
                }

                // Store last arrow direction
                $lastArrows[$studentId] = $arrowDirection;

                $row['current_rank'] = $currentRank;
                $row['previous_rank'] = $previousRank;
                $row['arrow_direction'] = $arrowDirection;

                return $row;
            });

            // Store updated data for next comparison
            Cache::put('student_rankings_previous', $currentRankings, now()->addDays(30));
            Cache::put('student_rankings_history', $rankHistory, now()->addDays(30));
            Cache::put('student_rankings_last_arrow', $lastArrows, now()->addDays(30));

            // Count students with remaining time needed (remaining > 0)
            $studentsWithRemainingTime = $ranked->filter(function ($row) {
                return $row['remaining_hours'] > 0;
            })->count();
        }

        return view('admin.student-management.dashboard', [
            'students' => $ranked,
            'studentsWithRemainingTime' => $studentsWithRemainingTime,
        ]);
    }

    protected function formatHours(float $hours): string
    {
        $isNegative = $hours < 0;
        $minutes = (int) round(abs($hours) * 60);
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return ($isNegative ? '-' : '') . sprintf('%02d:%02d', $h, $m);
    }
}


