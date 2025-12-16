<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dtr;
use App\Models\Department;
use App\Models\LeaveRequest;
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
        $selectedEmployeeId = $request->get('employee_id');

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

            // Calculate total hours from all DTR records
            // Note: Absent days should have total_hours = 0, so they won't contribute to totalHours
            // Missing DTR records for past weekdays are also treated as 0 hours (absent)
            $totalHours = 0;
            foreach ($dtrs as $dtr) {
                // Only count hours for non-absent records
                // Absent days should contribute 0 hours to totalHours
                if ($dtr->status !== 'absent') {
                    $totalHours += (float) ($dtr->total_hours ?? 0);
                }
                // If status is 'absent', don't add any hours (treat as 0)
            }

            // Determine if this is a single week filter or custom date range
            $isSingleWeek = !$startDate && !$endDate; // Using week_start filter (single week)
            $daysDiff = $weekStartDate->diffInDays($weekEndDate) + 1;
            $isSingleWeek = $isSingleWeek || ($daysDiff <= 7); // Also treat as week if range is 7 days or less

            // Calculate overtime based on filter type
            $totalOvertime = 0;
            if ($isSingleWeek) {
                // Weekly basis: if total hours > 40:00, overtime = total hours - 40:00
                $totalOvertime = max($totalHours - 40.0, 0);
            } else {
                // Daily basis: calculate overtime per day (hours > 8:00 per day)
                $totalOvertime = 0;
            }

            // Daily breakdown - only show weekdays (Monday-Friday)
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

                    // Get hours - ensure we're getting the actual value
                    $dayHours = $dtr ? (float) ($dtr->total_hours ?? 0) : 0;

                    // Calculate overtime based on filter type
                    if ($isSingleWeek) {
                        // Weekly basis: don't calculate per-day overtime, will be calculated weekly
                        $dayOvertime = 0;
                    } else {
                        // Daily basis: overtime = hours above 8:00 per day (excluding future/no-record dates)
                        if ($dtr) {
                            $dayOvertime = max($dayHours - 8.0, 0);
                        } else {
                            $dayOvertime = 0;
                        }
                        $totalOvertime += $dayOvertime;
                    }

                    // Determine status label based on hours and presence of DTR
                    // If on approved leave without DTR, show leave status
                    if ($leave && !$dtr) {
                        $statusLabel = 'leave';
                        $statusBadgeClass = 'bg-purple-100 text-purple-800';
                        $dtrStatus = 'on_leave';
                    } elseif ($dayHours > 0) {
                        // Hours exist - determine status based on hours
                        // Check if DTR status is travel first
                        if ($dtr && $dtr->status === 'travel') {
                            $statusLabel = 'travel';
                            $statusBadgeClass = 'bg-blue-100 text-blue-800';
                            $dtrStatus = 'travel';
                        } elseif ($dayHours >= 8.0) {
                            $statusLabel = 'completed';
                            $statusBadgeClass = 'bg-green-100 text-green-800';
                            // Use DTR status if available, otherwise set to present
                            $dtrStatus = $dtr ? $dtr->status : 'present';
                            if ($dtrStatus === 'absent') {
                                $dtrStatus = 'present';
                            }
                        } else {
                            $statusLabel = 'under_time';
                            $statusBadgeClass = 'bg-yellow-100 text-yellow-800';
                            // Use DTR status if available, otherwise set to present
                            $dtrStatus = $dtr ? $dtr->status : 'present';
                            if ($dtrStatus === 'absent') {
                                $dtrStatus = 'present';
                            }
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
                    $dailyBreakdown[] = [
                        'date' => $currentDate->copy(),
                        'dtr' => $dtr,
                        'total_hours' => $dayHours,
                        'overtime_hours' => $dayOvertime,
                        // If a DTR exists with data, never mark as absent
                        'status' => $dtrStatus ?? ($isFutureDate ? null : ($isToday ? null : 'absent')),
                        'status_label' => $statusLabel,
                        'status_badge_class' => $statusBadgeClass,
                        'is_future' => $isFutureDate,
                        'leave' => $leave,
                        'leave_type_label' => $leave ? ($leaveTypeLabel ?? 'Leave') : null,
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

            // Calculate deficit (only for completed date ranges)
            // Count actual weekdays in the date range
            $weekdayCount = 0;
            $checkDate = $weekStartDate->copy();
            while ($checkDate <= $weekEndDate) {
                $dayOfWeek = $checkDate->dayOfWeek;
                if ($dayOfWeek !== Carbon::SATURDAY && $dayOfWeek !== Carbon::SUNDAY) {
                    $weekdayCount++;
                }
                $checkDate->addDay();
            }

            // Calculate base hours: 8 hours per weekday
            $baseHours = $weekdayCount * 8.0;

            // Only calculate deficit if the date range has ended (end date is in the past)
            $rangeHasEnded = $weekEndDate->lt($today);

            // Calculate deficit and balance
            if ($rangeHasEnded) {
                if ($isSingleWeek) {
                    // Weekly deficit: baseHours - totalHours (max 0)
                    // Ensure absent days are counted: if a DTR has status='absent', it should contribute 0 hours to totalHours
                    // Also count past weekdays with no DTR record as 0 hours (absent)
                    $adjustedTotalHours = $totalHours;

                    // Check for past weekdays with no DTR record - these should count as absent (0 hours)
                    $checkDate = $weekStartDate->copy();
                    while ($checkDate <= $weekEndDate && $checkDate->lt($today)) {
                        $dayOfWeek = $checkDate->dayOfWeek;
                        if ($dayOfWeek !== Carbon::SATURDAY && $dayOfWeek !== Carbon::SUNDAY) {
                            $dateKey = $checkDate->format('Y-m-d');
                            if (!isset($dtrMap[$dateKey])) {
                                // No DTR record for this past weekday = absent = 0 hours (already accounted in adjustedTotalHours)
                                // This is already handled since totalHours only sums existing DTR records
                            }
                        }
                        $checkDate->addDay();
                    }

                    // Calculate deficit: required hours (baseHours) minus actual hours worked (adjustedTotalHours)
                    // If someone is absent, their totalHours will be lower, so deficit will be higher
                    $rawDeficit = max(0, $baseHours - $adjustedTotalHours);
                } else {
                    // Custom date range: sum per-day deficit (8:00 required per weekday)
                    // Only count deficit for dates that have DTR records OR are before today
                    $rawDeficit = 0;
                    $checkDate = $weekStartDate->copy();
                    while ($checkDate <= $weekEndDate) {
                        $dayOfWeek = $checkDate->dayOfWeek;
                        $dateKey = $checkDate->format('Y-m-d');
                        $dtr = $dtrMap[$dateKey] ?? null;
                        $isToday = $checkDate->eq($today);
                        $isPastDate = $checkDate->lt($today);

                        // Only calculate deficit for weekdays
                        if ($dayOfWeek !== Carbon::SATURDAY && $dayOfWeek !== Carbon::SUNDAY) {
                            // If DTR exists, always calculate deficit (even if future date)
                            // If no DTR, only calculate deficit for past dates (before today)
                            if ($dtr || $isPastDate) {
                                $dayHours = $dtr ? (float) $dtr->total_hours : 0;

                                // If DTR status is 'absent', count as full 8 hours deficit
                                if ($dtr && $dtr->status === 'absent') {
                                    $rawDeficit += 8.0;
                                } else {
                                    // Otherwise, calculate deficit based on hours worked
                                    $rawDeficit += max(0, 8.0 - $dayHours);
                                }
                            }
                        }
                        $checkDate->addDay();
                    }
                }

                // Balance: Overtime - Deficit
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
}

