<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;
use App\Models\User;
use App\Models\Dtr;
use App\Models\QuizAttemptHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    public function students(Request $request)
    {
        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to access Student Management.');
        }

        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        // Aggregate DTRs to determine internship start/end (first and last DTR dates)
        $dtrAgg = Dtr::query()
            ->selectRaw('user_id, MIN(date) as internship_start, MAX(date) as internship_last, COALESCE(SUM(total_hours), 0) as internship_total_hours')
            ->groupBy('user_id');

        $studentsQuery = User::query()
            ->with('university')
            ->where('role', 'student')
            ->leftJoinSub($dtrAgg, 'dtr_agg', function ($join) {
                $join->on('dtr_agg.user_id', '=', 'users.id');
            })
            ->select([
                'users.*',
                DB::raw('dtr_agg.internship_start as internship_start'),
                DB::raw('dtr_agg.internship_last as internship_last'),
                DB::raw('COALESCE(dtr_agg.internship_total_hours, 0) as internship_total_hours'),
            ]);

        if ($search !== '') {
            $studentsQuery->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('users.id', (int) $search);
                }

                $q->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhereHas('university', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('location', 'like', "%{$search}%");
                    });
            });
        }

        $students = $studentsQuery
            ->orderBy('users.name')
            ->paginate($perPage)
            ->appends($request->query());

        return view('admin.student-management.students', compact('students', 'search', 'perPage'));
    }

    public function index(Request $request)
    {
        $this->reconcileStudentAdditionalTimeResubmissions();

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
                $totalRaw = (float) ($totalsByStudent[$student->id] ?? 0);
                $rollbackHours = $this->getPendingResubmissionRollbackHours((int) $student->id);
                $total = max($totalRaw - $rollbackHours, 0);
                $remaining = $required - $total; // can be negative

                // Estimate internship end date based on remaining hours (8 hours per weekday)
                $estimatedEndDate = null;
                $estimatedEndDateFormatted = null;
                if ($remaining > 0) {
                    $daysNeeded = (int) ceil($remaining / 8.0);
                    // If at least one day of work is needed, assume they can start today
                    $daysOffset = max($daysNeeded - 1, 0);
                    $endDate = Carbon::today()->addWeekdays($daysOffset);
                    $estimatedEndDate = $endDate->toDateString();
                    $estimatedEndDateFormatted = $endDate->format('M d, Y');
                }

                return [
                    'student' => $student,
                    'required_hours' => $required,
                    'total_hours' => $total,
                    'remaining_hours' => $remaining,
                    'required_hours_formatted' => $this->formatHours($required),
                    'total_hours_formatted' => $this->formatHours($total),
                    'remaining_hours_formatted' => $this->formatHours($remaining),
                    'estimated_end_date' => $estimatedEndDate,
                    'estimated_end_date_formatted' => $estimatedEndDateFormatted,
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

            // Count students who are estimated to finish within the current month
            $today = Carbon::today();
            $endOfMonth = $today->copy()->endOfMonth();
            $studentsEndingThisMonth = $ranked->filter(function ($row) use ($today, $endOfMonth) {
                if (($row['remaining_hours'] ?? 0) <= 0 || empty($row['estimated_end_date'])) {
                    return false;
                }
                try {
                    $endDate = Carbon::parse($row['estimated_end_date']);
                } catch (\Exception $e) {
                    return false;
                }
                return $endDate->between($today, $endOfMonth);
            })->count();
        }

        return view('admin.student-management.dashboard', [
            'students' => $ranked,
            'studentsWithRemainingTime' => $studentsWithRemainingTime,
            'studentsEndingThisMonth' => $studentsEndingThisMonth ?? 0,
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

    private function getPendingResubmissionRollbackHours(int $userId): float
    {
        $requests = LeaveRequest::where('user_id', $userId)
            ->whereIn('type', ['additional_time', 'vacation_leave', 'sick_leave', 'travel'])
            ->where('status', 'pending')
            ->whereHas('logs', function ($q) {
                $q->where('action', 'resubmission_requested');
            })
            ->whereDoesntHave('logs', function ($q) {
                $q->whereIn('action', ['additional_time_reverted', 'leave_time_reverted', 'travel_time_reverted']);
            })
            ->get();

        $total = 0.0;
        foreach ($requests as $request) {
            if ($request->type === 'travel') {
                $total += ((float) ($request->travel_hours ?? 8.0)) * $request->days;
                continue;
            }
            if (in_array($request->type, ['vacation_leave', 'sick_leave'], true)) {
                $total += 8.0 * $request->days;
                continue;
            }

            $raw = (string) ($request->reason ?? '');
            if (preg_match('/Additional Time Hours:\s*(\d{1,3}):(\d{2})/', $raw, $m)) {
                $hours = (int) $m[1];
                $minutes = (int) $m[2];
                if ($minutes >= 0 && $minutes <= 59) {
                    $total += $hours + ($minutes / 60);
                    continue;
                }
            }
            $total += 8.0 * $request->days;
        }

        return $total;
    }

    private function reconcileStudentAdditionalTimeResubmissions(): void
    {
        $requests = LeaveRequest::where('type', 'additional_time')
            ->where('status', 'pending')
            ->whereHas('logs', function ($q) {
                $q->where('action', 'resubmission_requested');
            })
            ->whereDoesntHave('logs', function ($q) {
                $q->where('action', 'additional_time_reverted');
            })
            ->get();

        foreach ($requests as $leaveRequest) {
            $didRevert = false;
            $rawReason = (string) ($leaveRequest->reason ?? '');
            if (preg_match('/Additional Time Hours:\s*(\d{1,3}):(\d{2})/', $rawReason, $m)) {
                $hours = (int) $m[1];
                $minutes = (int) $m[2];
                if ($minutes >= 0 && $minutes <= 59) {
                    $hoursToDeduct = $hours + ($minutes / 60);
                    if ($hoursToDeduct > 0) {
                        $dtr = Dtr::where('user_id', $leaveRequest->user_id)
                            ->whereDate('date', Carbon::parse($leaveRequest->start_date)->toDateString())
                            ->first();
                        if ($dtr) {
                            $dtr->total_hours = max(((float) $dtr->total_hours) - $hoursToDeduct, 0);
                            $dtr->overtime_hours = max(((float) $dtr->total_hours) - 8.0, 0);
                            $dtr->save();
                            $didRevert = true;
                        }
                    }
                }
            } else {
                $start = Carbon::parse($leaveRequest->start_date);
                $end = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : $start;
                $period = \Carbon\CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    $dtr = Dtr::where('user_id', $leaveRequest->user_id)
                        ->whereDate('date', $date->toDateString())
                        ->first();
                    if (!$dtr) {
                        continue;
                    }
                    $dtr->total_hours = max(((float) $dtr->total_hours) - 8.0, 0);
                    $dtr->overtime_hours = max(((float) $dtr->total_hours) - 8.0, 0);
                    $dtr->save();
                    $didRevert = true;
                }
            }

            if ($didRevert) {
                LeaveRequestLog::create([
                    'leave_request_id' => $leaveRequest->id,
                    'action' => 'additional_time_reverted',
                    'status_before' => 'approved',
                    'status_after' => 'pending',
                    'notes' => 'Reconciled rollback from Student Dashboard for resubmission requested Additional Time.',
                    'performed_by' => auth()->id(),
                ]);
            }
        }
    }
}


