<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dtr;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Support\DtrEmployeeAttendance;
use App\Support\DtrRecordHours;
use App\Support\RequestQueryInteger;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TimeReportController extends Controller
{
    /**
     * Display the time report page with weekly report.
     */
    public function index(Request $request)
    {
        // Get date range filter (if provided)
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Determine date range: prioritize custom range if provided, otherwise use week filter
        if ($startDate && $endDate) {
            $startDateObj = Carbon::parse($startDate)->startOfDay();
            $endDateObj = Carbon::parse($endDate)->endOfDay();

            // Validate that end date is after start date
            if ($endDateObj->lt($startDateObj)) {
                return redirect()->back()->withErrors(['end_date' => 'End date must be after start date.']);
            }

            $weekStartDate = $startDateObj->copy();
            $weekEndDate = $endDateObj->copy();
        } else {
            // Get the week start date (default to current week)
            $weekStart = $request->get('week_start', now()->startOfWeek()->format('Y-m-d'));
            $weekStartDate = Carbon::parse($weekStart)->startOfWeek();
            $weekEndDate = $weekStartDate->copy()->endOfWeek();
            // Clear date range values when using week filter
            $startDate = null;
            $endDate = null;
        }

        $user = auth()->user();

        // Get selected department (if any)
        $selectedDepartmentId = $request->get('department_id');

        // Get all active employees (filter by department if selected, respecting restrictions)
        $employeesQuery = User::where('role', 'employee')
            ->where('is_active', true);

        // Apply department restrictions if user has Employee Management with restrictions
        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $employeesQuery->whereIn('department_id', $allowedDepartmentIds);
            }
        }

        // Filter by selected department (user-selected filter)
        if ($selectedDepartmentId && $user->canManageDepartment($selectedDepartmentId)) {
            $employeesQuery->where('department_id', $selectedDepartmentId);
        }

        $employees = $employeesQuery->orderBy('name')->get();

        // Get departments for filter dropdown (only departments user can manage)
        $departmentsQuery = Department::active();

        if ($user->canAccessEmployeeManagement()) {
            $allowedDepartmentIds = $user->getAllowedDepartmentIds();
            if ($allowedDepartmentIds !== null) {
                $departmentsQuery->whereIn('id', $allowedDepartmentIds);
            }
        }

        $departments = $departmentsQuery->orderBy('name')->get();

        // Get selected employee (if any)
        $selectedEmployeeId = RequestQueryInteger::positiveInt($request, 'employee_id');

        // Calculate weekly statistics for each employee
        $today = Carbon::today();
            $isCurrentWeek = $today->lte($weekEndDate);

        $weeklyReports = [];
        foreach ($employees as $employee) {
            // Preload approved leave requests overlapping the filter window for this employee
            $leaveRequests = LeaveRequest::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $weekEndDate->toDateString())
                ->where(function ($q) use ($weekStartDate) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $weekStartDate->toDateString());
                })
                ->get();
            $leaveDayMap = [];
            foreach ($leaveRequests as $lr) {
                $start = Carbon::parse($lr->start_date);
                $end = $lr->end_date ? Carbon::parse($lr->end_date) : $start->copy();
                $period = new CarbonPeriod($start, $end);
                foreach ($period as $day) {
                    $leaveDayMap[$day->format('Y-m-d')] = $lr;
                }
            }

            // Also create a map for approved absent requests specifically
            $approvedAbsentDayMap = [];
            foreach ($leaveRequests->where('type', 'absent') as $absentRequest) {
                $start = Carbon::parse($absentRequest->start_date);
                $end = $absentRequest->end_date ? Carbon::parse($absentRequest->end_date) : $start->copy();
                $period = new CarbonPeriod($start, $end);
                foreach ($period as $day) {
                    $approvedAbsentDayMap[$day->format('Y-m-d')] = true;
                }
            }

            // Get DTR records (allow future dates within the filter range so they count if present)
            // Use whereDate to ensure inclusive range
            $dtrs = Dtr::where('user_id', $employee->id)
                ->whereDate('date', '>=', $weekStartDate->format('Y-m-d'))
                ->whereDate('date', '<=', $weekEndDate->format('Y-m-d'))
                ->orderBy('date')
                ->get();

            // Daily breakdown - calculate from daily records
            // Create a map of dates to DTR records for easier lookup
            $dtrMap = [];
            foreach ($dtrs as $dtr) {
                // Ensure date is formatted consistently
                $dateKey = $dtr->date instanceof \Carbon\Carbon
                    ? $dtr->date->format('Y-m-d')
                    : \Carbon\Carbon::parse($dtr->date)->format('Y-m-d');
                $dtrMap[$dateKey] = $dtr;
            }

            // Daily breakdown - only show weekdays (Monday-Friday)
            $totalHours = 0.0;
            $totalOvertime = 0.0;
            $dailyBreakdown = [];
            $currentDate = $weekStartDate->copy();
            while ($currentDate <= $weekEndDate) {
                // Skip Saturday (6) and Sunday (0)
                $dayOfWeek = $currentDate->dayOfWeek;
                if ($dayOfWeek !== Carbon::SATURDAY && $dayOfWeek !== Carbon::SUNDAY) {
                    $dateKey = $currentDate->format('Y-m-d');
                    // Check DTR map - ensure we're using the correct date format
                    $dtr = $dtrMap[$dateKey] ?? null;
                    // Check leave map
                    $leave = $leaveDayMap[$dateKey] ?? null;
                    // Also try checking directly if not found in map (fallback - compare date strings)
                    if (!$dtr && $dtrs->isNotEmpty()) {
                        $dtr = $dtrs->first(function($dtrRecord) use ($dateKey) {
                            $recordDate = $dtrRecord->date instanceof \Carbon\Carbon
                                ? $dtrRecord->date->format('Y-m-d')
                                : \Carbon\Carbon::parse($dtrRecord->date)->format('Y-m-d');
                            return $recordDate === $dateKey;
                        });
                        // If found via fallback, add to map for future lookups
                        if ($dtr) {
                            $dtrMap[$dateKey] = $dtr;
                        }
                    }
                    $isToday = $currentDate->eq($today);
                    // If a DTR exists, treat as recorded regardless of date; otherwise future if after today
                    $isFutureDate = !$dtr && $currentDate->gt($today);

                    // Check if there's a leave request for this day
                    $hasLeaveRequest = $leave !== null;
                    $leaveTypeLabel = $leave ? ($leave->type_label ?? ucfirst(str_replace('_', ' ', $leave->type))) : null;

                    // Same capped Total Hours / overtime rules as employee DTR.
                    $dayHours = $this->resolveDayCountableHours($dtr, $leave);
                    $dayOvertime = $this->resolveDayOvertimeHours($dtr);

                    // Determine status label based on hours and presence of DTR
                    // Check for travel leave first (can be from DTR or leave request)
                    if (($dtr && $dtr->status === 'travel') || ($leave && $leave->type === 'travel')) {
                        $statusLabel = 'travel';
                        $statusBadgeClass = 'bg-blue-100 text-blue-800';
                        $dtrStatus = 'travel';
                    } elseif ($leave && ! $dtr && in_array($leave->type, ['vacation_leave', 'sick_leave'], true)) {
                        // If on approved vacation/sick leave without DTR, show leave status
                        $statusLabel = 'leave';
                        $statusBadgeClass = 'bg-purple-100 text-purple-800';
                        $dtrStatus = 'on_leave';
                    } elseif ($dayHours >= DtrRecordHours::STANDARD_DAY_HOURS - 0.001) {
                        $statusLabel = 'completed';
                        $statusBadgeClass = 'bg-green-100 text-green-800';
                        $dtrStatus = $dtr ? $dtr->status : 'present';
                        if ($dtrStatus === 'absent') {
                            $dtrStatus = 'present';
                        }
                    } elseif ($dayHours > 0) {
                        $statusLabel = 'under_time';
                        $statusBadgeClass = 'bg-yellow-100 text-yellow-800';
                        $dtrStatus = $dtr ? $dtr->status : 'present';
                        if ($dtrStatus === 'absent') {
                            $dtrStatus = 'present';
                        }
                    } elseif ($dtr) {
                        // DTR exists but 0 hours
                        // Check if DTR status is travel
                        if ($dtr->status === 'travel') {
                            $statusLabel = 'travel';
                            $statusBadgeClass = 'bg-blue-100 text-blue-800';
                            $dtrStatus = 'travel';
                        } else {
                            $statusLabel = 'under_time';
                            $statusBadgeClass = 'bg-yellow-100 text-yellow-800';
                            $dtrStatus = $dtr->status === 'absent' ? 'present' : $dtr->status;
                        }
                    } elseif ($isFutureDate || $isToday) {
                        $statusLabel = 'not_recorded';
                        $statusBadgeClass = 'bg-gray-100 text-gray-600';
                        $dtrStatus = null;
                    } else {
                        // Check if there's an approved absent leave request for this day
                        $dateKey = $currentDate->format('Y-m-d');
                        $hasApprovedAbsent = isset($approvedAbsentDayMap[$dateKey]);

                        if ($hasApprovedAbsent) {
                            // Has approved absent leave request
                            $statusLabel = 'absent';
                            $statusBadgeClass = 'bg-red-100 text-red-800';
                            $dtrStatus = 'absent';
                        } else {
                            // No DTR and no leave request - show "No Records"
                            $statusLabel = 'no_records';
                            $statusBadgeClass = 'bg-gray-100 text-gray-500';
                            $dtrStatus = null;
                        }
                    }

                    $dateKey = $currentDate->format('Y-m-d');
                    // Set leave type label for display
                    $leaveTypeLabelForDisplay = null;
                    if ($leave) {
                        $leaveTypeLabelForDisplay = $leave->type_label ?? ucfirst(str_replace('_', ' ', $leave->type));
                    } elseif ($dtr && $dtr->status === 'travel') {
                        $leaveTypeLabelForDisplay = 'Travel';
                    }

                    $dayDeficitMinutes = 0;
                    if ($dtr instanceof Dtr) {
                        $dayDeficitMinutes = DtrEmployeeAttendance::dailyColumnDeficitMinutes($dtr);
                    } elseif (! $isFutureDate) {
                        $dayDeficitMinutes = max(
                            0,
                            DtrEmployeeAttendance::STANDARD_DAY_MINUTES - (int) round($dayHours * 60)
                        );
                    }

                    if (! $isFutureDate) {
                        $totalHours += $dayHours;
                        $totalOvertime += $dayOvertime;
                    }

                    $dailyBreakdown[] = [
                        'date' => $currentDate->copy(),
                        'dtr' => $dtr,
                        'total_hours' => $dayHours,
                        'overtime_hours' => $dayOvertime,
                        'deficit_minutes' => $dayDeficitMinutes,
                        // If a DTR exists with data, never mark as absent
                        'status' => $dtrStatus ?? ($isFutureDate ? null : ($isToday ? null : 'absent')),
                        'status_label' => $statusLabel,
                        'status_badge_class' => $statusBadgeClass,
                        'is_future' => $isFutureDate,
                        'leave' => $leave,
                        'leave_type_label' => $leaveTypeLabelForDisplay ?? ($leave ? ($leaveTypeLabel ?? 'Leave') : null),
                        'has_leave_request' => $hasLeaveRequest ?? false,
                    ];
                }
                $currentDate->addDay();
            }

            // Count absent only for days before today (completed days) - only count approved absent leave requests
            $daysPresent = $dtrs->where('status', 'present')->count();
            $daysAbsent = 0;
            if (!$isCurrentWeek) {
                // Count only approved absent leave requests for past weekdays
                $checkDate = $weekStartDate->copy();
                while ($checkDate <= $weekEndDate && $checkDate->lt($today)) {
                    $dayOfWeek = $checkDate->dayOfWeek;
                    if ($dayOfWeek !== Carbon::SATURDAY && $dayOfWeek !== Carbon::SUNDAY) {
                        $dateKey = $checkDate->format('Y-m-d');
                        // Only count as absent if there's an approved absent leave request and no DTR record
                        if (isset($approvedAbsentDayMap[$dateKey]) && !isset($dtrMap[$dateKey])) {
                            $daysAbsent++;
                        }
                    }
                    $checkDate->addDay();
                }
            }
            $daysLate = $dtrs->where('status', 'late')->count();
            $daysOnLeave = $dtrs->where('status', 'on_leave')->count();
            $daysHalfDay = $dtrs->where('status', 'half_day')->count();
            $daysTravel = $dtrs->where('status', 'travel')->count();
            $totalDays = $dtrs->count();

            // Only calculate deficit if the date range has ended (end date is in the past)
            $rangeHasEnded = $weekEndDate->lt($today);

            if ($rangeHasEnded) {
                $deficitMinutes = 0;
                foreach ($dailyBreakdown as $dayRow) {
                    $deficitMinutes += (int) ($dayRow['deficit_minutes'] ?? 0);
                }
                $rawDeficit = $deficitMinutes / 60.0;
                $balanceHours = $totalOvertime - $rawDeficit;
            } else {
                $rawDeficit = null;
                $balanceHours = null;
            }

            $weeklyReports[] = [
                'employee' => $employee,
                'total_hours' => round($totalHours, 2),
                'total_overtime' => round($totalOvertime, 2),
                'days_present' => $daysPresent,
                'days_absent' => $daysAbsent,
                'days_late' => $daysLate,
                'days_on_leave' => $daysOnLeave,
                'days_half_day' => $daysHalfDay,
                'days_travel' => $daysTravel,
                'total_days' => $totalDays,
                'daily_breakdown' => $dailyBreakdown,
                'deficit_hours' => $rawDeficit,
                'balance_hours' => $balanceHours,
                'is_current_week' => !$rangeHasEnded, // Use rangeHasEnded to determine if it's current
            ];
        }

        // Filter by selected employee if provided
        if ($selectedEmployeeId) {
            $weeklyReports = array_filter($weeklyReports, function($report) use ($selectedEmployeeId) {
                return $report['employee']->id == $selectedEmployeeId;
            });
            $weeklyReports = array_values($weeklyReports);
        }

        // Calculate overall statistics
        $overallStats = [
            'total_employees' => count($employees),
            'total_hours_all' => array_sum(array_column($weeklyReports, 'total_hours')),
            'total_overtime_all' => array_sum(array_column($weeklyReports, 'total_overtime')),
            'total_days_present' => array_sum(array_column($weeklyReports, 'days_present')),
            'total_days_absent' => array_sum(array_column($weeklyReports, 'days_absent')),
        ];

        // Previous and next week dates (only if not using custom date range)
        $previousWeek = null;
        $nextWeek = null;
        if (!$startDate || !$endDate) {
            $previousWeek = $weekStartDate->copy()->subWeek()->format('Y-m-d');
            $nextWeek = $weekStartDate->copy()->addWeek()->format('Y-m-d');
        }

        return view('admin.time-report.index', compact(
            'weeklyReports',
            'employees',
            'departments',
            'selectedEmployeeId',
            'selectedDepartmentId',
            'weekStartDate',
            'weekEndDate',
            'previousWeek',
            'nextWeek',
            'overallStats',
            'startDate',
            'endDate',
            'isCurrentWeek'
        ));
    }

    /**
     * Capped Total Hours column value (matches employee DTR).
     */
    private function resolveDayCountableHours(?Dtr $dtr, ?LeaveRequest $leave): float
    {
        if ($dtr instanceof Dtr) {
            if (($dtr->status ?? '') === 'absent') {
                return 0.0;
            }

            return DtrEmployeeAttendance::displayedTotalHoursForColumn($dtr);
        }

        if ($leave && ($leave->type ?? '') === 'absent') {
            return 0.0;
        }

        if ($leave && in_array($leave->type ?? '', ['vacation_leave', 'sick_leave', 'travel'], true)) {
            return DtrRecordHours::STANDARD_DAY_HOURS;
        }

        return 0.0;
    }

    /**
     * Overtime for a day (stored field or computed from uncapped DTR total).
     */
    private function resolveDayOvertimeHours(?Dtr $dtr): float
    {
        if (! $dtr instanceof Dtr || ($dtr->status ?? '') === 'absent') {
            return 0.0;
        }

        $stored = (float) ($dtr->overtime_hours ?? 0);
        if ($stored > 0) {
            return $stored;
        }

        return DtrRecordHours::overtimeFromTotal(DtrEmployeeAttendance::displayedDailyTotal($dtr));
    }
}

