<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestLog;
use App\Models\User;
use App\Models\Dtr;
use App\Models\QuizAttemptHistory;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentDashboardController extends Controller
{
    /**
     * Base query for student list in Student Management: role student, DTR aggregates, department scope (no search).
     * Excludes disabled or unapproved students unless they have any logged DTR hours.
     *
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    private function studentManagementStudentsBaseQuery(User $user)
    {
        $dtrAgg = Dtr::query()
            ->selectRaw('user_id, MIN(date) as internship_start, MAX(date) as internship_last, COALESCE(SUM(total_hours), 0) as internship_total_hours')
            ->groupBy('user_id');

        $allowedDepartmentIds = $user->canAccessStudentManagement()
            ? $user->getAllowedStudentDepartmentIds()
            : null;

        $studentsQuery = User::query()
            ->with(['university', 'department'])
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

        // Hide disabled or not-yet-approved students unless they already have logged DTR hours (shows prior interns with time on record).
        $studentsQuery->where(function ($outer) {
            $outer->where(function ($q) {
                $q->where('users.is_active', true)
                    ->where('users.is_approved', true);
            })
                ->orWhereRaw('COALESCE(dtr_agg.internship_total_hours, 0) > 0');
        });

        if (is_array($allowedDepartmentIds) && !empty($allowedDepartmentIds)) {
            $studentsQuery->whereIn('users.department_id', $allowedDepartmentIds);
        }

        return $studentsQuery;
    }

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

        $studentsQuery = $this->studentManagementStudentsBaseQuery($user);
        $studentsQueryUnfilteredForExitConference = clone $studentsQuery;

        if ($search !== '') {
            $studentsQuery->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('users.id', (int) $search);
                }

                $q->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhereHas('department', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('university', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('location', 'like', "%{$search}%");
                    });
            });
        }

        $studentsForStats = (clone $studentsQuery)->get();

        $statsTotalStudents = $studentsForStats->count();
        $statsWithLoggedTime = $studentsForStats->filter(function ($s) {
            return (float) ($s->internship_total_hours ?? 0) > 0;
        })->count();
        $statsCompleted = $studentsForStats->filter(function ($s) {
            $required = (float) ($s->required_training_hours ?? 0);
            $total = (float) ($s->internship_total_hours ?? 0);

            return $required > 0 && $total >= $required;
        })->count();
        $statsOngoing = max($statsTotalStudents - $statsCompleted, 0);

        // Matches "Ongoing" / incomplete in the Internship Ended column: required hours set but not yet fully logged.
        $statsOngoingIncomplete = $studentsForStats->filter(function ($s) {
            $required = (float) ($s->required_training_hours ?? 0);
            $total = (float) ($s->internship_total_hours ?? 0);

            return $required > 0 && $total < $required;
        })->count();

        $completionPercents = $studentsForStats->map(function ($s) {
            $required = (float) ($s->required_training_hours ?? 0);
            $total = (float) ($s->internship_total_hours ?? 0);
            if ($required <= 0) {
                return 0.0;
            }

            return min(($total / $required) * 100, 100);
        });
        $statsAvgCompletion = $completionPercents->isNotEmpty()
            ? round((float) $completionPercents->avg(), 1)
            : 0.0;

        $approvedLeaveCountsByStudent = collect();
        if ($studentsForStats->isNotEmpty()) {
            $approvedLeaveCountsByStudent = LeaveRequest::query()
                ->whereIn('user_id', $studentsForStats->pluck('id'))
                ->where('status', 'approved')
                ->selectRaw('user_id, COUNT(*) as approved_count')
                ->groupBy('user_id')
                ->pluck('approved_count', 'user_id');
        }
        $statsTotalApprovedLeaveRequests = (int) $approvedLeaveCountsByStudent->sum();

        $statsTopLeaveRequester = null;
        if ($approvedLeaveCountsByStudent->isNotEmpty()) {
            $topLeaveUserId = (int) $approvedLeaveCountsByStudent->sortDesc()->keys()->first();
            $topLeaveCount = (int) ($approvedLeaveCountsByStudent[$topLeaveUserId] ?? 0);
            $topLeaveStudent = $studentsForStats->firstWhere('id', $topLeaveUserId);
            if ($topLeaveStudent && $topLeaveCount > 0) {
                $statsTopLeaveRequester = [
                    'name' => (string) $topLeaveStudent->name,
                    'count' => $topLeaveCount,
                ];
            }
        }

        $statsTopSchools = $studentsForStats
            ->groupBy(function ($s) {
                return optional($s->university)->name ?? 'No school assigned';
            })
            ->map->count()
            ->sortDesc()
            ->take(5);
        $statsTopSchoolsMax = (int) max((int) ($statsTopSchools->max() ?? 0), 1);

        $statsRemainingBuckets = [
            'Over 80 hours' => 0,
            '40 to 80 hours' => 0,
            '1 to 39.99 hours' => 0,
            'Done / exceeded' => 0,
        ];
        foreach ($studentsForStats as $s) {
            $required = (float) ($s->required_training_hours ?? 0);
            $total = (float) ($s->internship_total_hours ?? 0);
            $remaining = $required - $total;
            if ($remaining <= 0) {
                $statsRemainingBuckets['Done / exceeded']++;
            } elseif ($remaining >= 80) {
                $statsRemainingBuckets['Over 80 hours']++;
            } elseif ($remaining >= 40) {
                $statsRemainingBuckets['40 to 80 hours']++;
            } else {
                $statsRemainingBuckets['1 to 39.99 hours']++;
            }
        }
        $statsRemainingBucketsMax = (int) max(max($statsRemainingBuckets), 1);

        $students = $studentsQuery
            ->orderBy('users.name')
            ->paginate($perPage)
            ->appends($request->query());

        $exitConferenceClosestBySchool = $this->buildExitConferenceClosestBySchoolRows(
            $studentsQueryUnfilteredForExitConference->get()
        );

        return view('admin.student-management.students', compact(
            'students',
            'search',
            'perPage',
            'statsTotalStudents',
            'statsWithLoggedTime',
            'statsCompleted',
            'statsOngoing',
            'statsOngoingIncomplete',
            'statsAvgCompletion',
            'statsTotalApprovedLeaveRequests',
            'statsTopLeaveRequester',
            'statsTopSchools',
            'statsTopSchoolsMax',
            'statsRemainingBuckets',
            'statsRemainingBucketsMax',
            'exitConferenceClosestBySchool'
        ));
    }

    /**
     * One row per school that has at least one student still completing OJT (required hours not yet met).
     * Picks the ongoing student whose effective exit-conference date is closest to today, then sorts rows by that date (earliest first).
     *
     * @param  \Illuminate\Support\Collection<int, User>  $students
     * @return list<array{school_name: string, student_id: ?int, student_name: ?string, student_email: ?string, exit_date: ?string, exit_date_formatted: ?string, source: ?string, signed_days_from_today: ?int, possible_exit_date: ?string, possible_exit_date_formatted: ?string, possible_exit_signed_days_from_today: ?int}>
     */
    private function buildExitConferenceClosestBySchoolRows($students): array
    {
        if ($students->isEmpty()) {
            return [];
        }

        $today = Carbon::today()->timezone((string) config('app.timezone'))->startOfDay();

        $uniIds = $students->filter(fn (User $s) => $this->studentIsOngoingOjt($s))
            ->pluck('university_id')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $rows = [];

        foreach (University::query()->whereIn('id', $uniIds)->orderBy('name')->get() as $uni) {
            $group = $students->where('university_id', $uni->id)->filter(fn (User $s) => $this->studentIsOngoingOjt($s));
            if ($group->isEmpty()) {
                continue;
            }
            $picked = $this->pickStudentClosestExitConferenceRow($group, $today);
            if (empty($picked['student_id'])) {
                continue;
            }
            $rows[] = array_merge(['school_name' => (string) $uni->name], $picked);
        }

        $noSchoolGroup = $students->filter(fn (User $s) => empty($s->university_id))->filter(fn (User $s) => $this->studentIsOngoingOjt($s));
        if ($noSchoolGroup->isNotEmpty()) {
            $picked = $this->pickStudentClosestExitConferenceRow($noSchoolGroup, $today);
            if (!empty($picked['student_id'])) {
                $rows[] = array_merge(
                    ['school_name' => 'No school assigned'],
                    $picked
                );
            }
        }

        usort($rows, function (array $a, array $b): int {
            $dateA = (string) ($a['exit_date'] ?? '');
            $dateB = (string) ($b['exit_date'] ?? '');
            if ($dateA === '' && $dateB === '') {
                return strcasecmp((string) ($a['school_name'] ?? ''), (string) ($b['school_name'] ?? ''));
            }
            if ($dateA === '') {
                return 1;
            }
            if ($dateB === '') {
                return -1;
            }
            $byDate = strcmp($dateA, $dateB);
            if ($byDate !== 0) {
                return $byDate;
            }

            return strcasecmp((string) ($a['school_name'] ?? ''), (string) ($b['school_name'] ?? ''));
        });

        return $rows;
    }

    /**
     * Student still has a training requirement and has not yet logged enough hours (active / ongoing OJT).
     */
    private function studentIsOngoingOjt(User $student): bool
    {
        $required = (float) ($student->required_training_hours ?? 0);
        $total = (float) ($student->internship_total_hours ?? 0);

        return $required > 0 && $total < $required;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>|iterable<User>  $group
     * @return array{student_name: ?string, student_id: ?int, student_email: ?string, exit_date: ?string, exit_date_formatted: ?string, source: ?string, signed_days_from_today: ?int, possible_exit_date: ?string, possible_exit_date_formatted: ?string, possible_exit_signed_days_from_today: ?int}
     */
    private function pickStudentClosestExitConferenceRow(iterable $group, Carbon $today): array
    {
        $best = null;
        $bestAbs = null;

        foreach ($group as $student) {
            $resolved = $this->resolveExitConferenceDateForStudentStats($student);
            if ($resolved === null) {
                continue;
            }
            /** @var Carbon $exitStart */
            $exitStart = $resolved['date']->copy()->startOfDay();
            /** @var string $source */
            $source = $resolved['source'];
            $signedDaysFromToday = (int) $today->diffInDays($exitStart, false);
            $abs = abs($signedDaysFromToday);

            $shouldReplace = $best === null
                || $abs < $bestAbs
                || ($abs === $bestAbs && $exitStart->lt($best['exit']));

            if ($shouldReplace) {
                $best = [
                    'exit' => $exitStart,
                    'student' => $student,
                    'source' => $source,
                    'signed_days_from_today' => $signedDaysFromToday,
                ];
                $bestAbs = $abs;
            }
        }

        if ($best === null) {
            return [
                'student_name' => null,
                'student_id' => null,
                'student_email' => null,
                'exit_date' => null,
                'exit_date_formatted' => null,
                'source' => null,
                'signed_days_from_today' => null,
                'possible_exit_date' => null,
                'possible_exit_date_formatted' => null,
                'possible_exit_signed_days_from_today' => null,
            ];
        }

        /** @var User $u */
        $u = $best['student'];
        $possibleExit = $this->resolvePossibleExitConferenceDateForStudent($u);
        $possibleFormatted = null;
        $possibleSigned = null;
        if ($possibleExit !== null) {
            $possibleExit = $possibleExit->copy()->startOfDay()->timezone((string) config('app.timezone'));
            $possibleFormatted = $possibleExit->format('M j, Y');
            $possibleSigned = (int) $today->diffInDays($possibleExit, false);
        }

        return [
            'student_name' => $u->name,
            'student_id' => (int) $u->id,
            'student_email' => $u->email,
            'exit_date' => $best['exit']->toDateString(),
            'exit_date_formatted' => $best['exit']->timezone((string) config('app.timezone'))->format('M j, Y'),
            'source' => $best['source'],
            'signed_days_from_today' => (int) $best['signed_days_from_today'],
            'possible_exit_date' => $possibleExit?->toDateString(),
            'possible_exit_date_formatted' => $possibleFormatted,
            'possible_exit_signed_days_from_today' => $possibleSigned,
        ];
    }

    /**
     * Hours-based weekday estimate from first DTR (or from today if no DTR), only when admin has not set OJT target.
     * Same rules as the student dashboard "Possible exit conference (estimate)" when no admin OJT target is set.
     */
    private function resolvePossibleExitConferenceDateForStudent(User $student): ?Carbon
    {
        $tz = (string) config('app.timezone');
        $requiredHours = (float) ($student->required_training_hours ?? 0);
        $loggedHours = (float) ($student->internship_total_hours ?? 0);
        $remainingHours = max($requiredHours - $loggedHours, 0);
        $adminTarget = $student->ojt_target_end_date;

        if ($adminTarget !== null || $requiredHours <= 0 || $remainingHours <= 0) {
            return null;
        }

        $weekdaysForFullRequirement = max(1, (int) ceil($requiredHours / 8.0));
        $firstDtrRaw = $student->internship_start ?? null;
        if ($firstDtrRaw !== null && $firstDtrRaw !== '') {
            $anchor = Carbon::parse($firstDtrRaw)->timezone($tz)->startOfDay();

            return $anchor->copy()->addWeekdays($weekdaysForFullRequirement);
        }

        $todayStart = now()->timezone($tz)->startOfDay();
        $weekdaysForRemaining = max(1, (int) ceil($remainingHours / 8.0));

        return $todayStart->copy()->addWeekdays($weekdaysForRemaining);
    }

    /**
     * @return array{date: Carbon, source: 'admin'|'estimated'}|null
     */
    private function resolveExitConferenceDateForStudentStats(User $student): ?array
    {
        $tz = (string) config('app.timezone');
        $requiredHours = (float) ($student->required_training_hours ?? 0);
        $loggedHours = (float) ($student->internship_total_hours ?? 0);
        $remainingHours = max($requiredHours - $loggedHours, 0);

        $adminTarget = $student->ojt_target_end_date;
        if ($adminTarget !== null) {
            return [
                'date' => Carbon::parse($adminTarget)->timezone($tz)->startOfDay(),
                'source' => 'admin',
            ];
        }

        if ($requiredHours <= 0 || $remainingHours <= 0) {
            return null;
        }

        $weekdaysForFullRequirement = max(1, (int) ceil($requiredHours / 8.0));
        $firstDtrRaw = $student->internship_start ?? null;
        if ($firstDtrRaw !== null && $firstDtrRaw !== '') {
            $anchor = Carbon::parse($firstDtrRaw)->timezone($tz)->startOfDay();

            return [
                'date' => $anchor->copy()->addWeekdays($weekdaysForFullRequirement),
                'source' => 'estimated',
            ];
        }

        $todayStart = now()->timezone($tz)->startOfDay();
        $weekdaysForRemaining = max(1, (int) ceil($remainingHours / 8.0));

        return [
            'date' => $todayStart->copy()->addWeekdays($weekdaysForRemaining),
            'source' => 'estimated',
        ];
    }

    public function index(Request $request)
    {
        $this->reconcileStudentAdditionalTimeResubmissions();

        // Check if user has student_management permission or is admin
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->canAccessStudentManagement()) {
            abort(403, 'Access denied. You do not have permission to access Student Management.');
        }

        // If user reaches this page, they already passed student-management access checks.
        $showApprovedLeaveRequests = true;
        $sortBy = (string) $request->input('sort_by', 'remaining_hours');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc'));

        $allowedSortBy = [
            'school',
            'required_hours',
            'total_hours',
            'remaining_hours',
            'approved_leave_requests',
            'estimated_end_date',
        ];
        if (!in_array($sortBy, $allowedSortBy, true)) {
            $sortBy = 'remaining_hours';
        }
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }
        $sortDescending = $sortDir === 'desc';

        // Get all active students with their required training hours
        $allowedDepartmentIds = $user->canAccessStudentManagement()
            ? $user->getAllowedStudentDepartmentIds()
            : null;

        $students = User::with(['university', 'department'])
            ->where('role', 'student')
            ->where('is_active', true);

        if (is_array($allowedDepartmentIds) && !empty($allowedDepartmentIds)) {
            $students->whereIn('department_id', $allowedDepartmentIds);
        }

        $students = $students->get();

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

            $approvedAbsentDaysByStudent = collect();
            if ($showApprovedLeaveRequests) {
                // Sum approved absent days per student (type=absent only)
                $approvedAbsentDaysByStudent = LeaveRequest::whereIn('user_id', $studentIds)
                    ->where('type', 'absent')
                    ->where('status', 'approved')
                    ->get(['user_id', 'start_date', 'end_date'])
                    ->groupBy('user_id')
                    ->map(function ($requests) {
                        return (int) $requests->sum(function ($request) {
                            $start = Carbon::parse($request->start_date);
                            $end = $request->end_date ? Carbon::parse($request->end_date) : $start;

                            return $start->diffInDays($end) + 1;
                        });
                    });
            }

            $ranked = $students->map(function ($student) use ($totalsByStudent, $approvedAbsentDaysByStudent) {
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
                    'approved_leave_requests' => (int) ($approvedAbsentDaysByStudent[$student->id] ?? 0),
                    'required_hours_formatted' => $this->formatHours($required),
                    'total_hours_formatted' => $this->formatHours($total),
                    'remaining_hours_formatted' => $this->formatHours($remaining),
                    'estimated_end_date' => $estimatedEndDate,
                    'estimated_end_date_formatted' => $estimatedEndDateFormatted,
                ];
            })->filter(function ($row) {
                return ($row['remaining_hours'] ?? 0) > 0;
            });

            $ranked = $ranked->sortBy(function ($row) use ($sortBy, $sortDescending) {
                return match ($sortBy) {
                    'school' => strtolower((string) (optional($row['student']->university)->name ?? '')),
                    'required_hours' => (float) ($row['required_hours'] ?? 0),
                    'total_hours' => (float) ($row['total_hours'] ?? 0),
                    'approved_leave_requests' => (int) ($row['approved_leave_requests'] ?? 0),
                    'estimated_end_date' => $row['estimated_end_date']
                        ?? ($sortDescending ? '0000-00-00' : '9999-12-31'),
                    default => (float) ($row['remaining_hours'] ?? 0),
                };
            }, SORT_NATURAL, $sortDescending)->values();

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
            'showApprovedLeaveRequests' => $showApprovedLeaveRequests,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
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
            ->whereIn('type', ['additional_time', 'leave', 'vacation_leave', 'sick_leave', 'travel'])
            ->where('status', 'pending')
            ->whereHas('logs', function ($q) {
                $q->where('action', 'approved');
            })
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
            if (in_array($request->type, ['leave', 'vacation_leave', 'sick_leave'], true)) {
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
                $q->where('action', 'approved');
            })
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


