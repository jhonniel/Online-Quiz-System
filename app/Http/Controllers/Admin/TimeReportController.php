<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dtr;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        // Get selected department (if any)
        $selectedDepartmentId = $request->get('department_id');

        // Get all active employees (filter by department if selected)
        $employeesQuery = User::where('role', 'employee')
            ->where('is_active', true);

        if ($selectedDepartmentId) {
            $employeesQuery->where('department_id', $selectedDepartmentId);
        }

        $employees = $employeesQuery->orderBy('name')->get();

        // Get departments for filter dropdown
        $departments = Department::active()
            ->orderBy('name')
            ->get();

        // Get selected employee (if any)
        $selectedEmployeeId = $request->get('employee_id');

        // Calculate weekly statistics for each employee
        $today = Carbon::today();
            $isCurrentWeek = $today->lte($weekEndDate);

        $weeklyReports = [];
        foreach ($employees as $employee) {
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

            // Calculate total hours from all DTR records (only past and today, not future)
            $totalHours = $dtrs->sum('total_hours');

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
                    // CRITICAL: If hours > 0, NEVER mark as absent - always show actual status
                    if ($dayHours > 0) {
                        // Hours exist - determine status based on hours
                        if ($dayHours >= 8.0) {
                            $statusLabel = 'completed';
                            $statusBadgeClass = 'bg-green-100 text-green-800';
                        } else {
                            $statusLabel = 'under_time';
                            $statusBadgeClass = 'bg-yellow-100 text-yellow-800';
                        }
                        // Use DTR status if available, otherwise set to present
                        $dtrStatus = $dtr ? $dtr->status : 'present';
                        if ($dtrStatus === 'absent') {
                            $dtrStatus = 'present';
                        }
                    } elseif ($dtr) {
                        // DTR exists but 0 hours
                        $statusLabel = 'under_time';
                        $statusBadgeClass = 'bg-yellow-100 text-yellow-800';
                        $dtrStatus = $dtr->status === 'absent' ? 'present' : $dtr->status;
                    } elseif ($isFutureDate || $isToday) {
                        $statusLabel = 'not_recorded';
                        $statusBadgeClass = 'bg-gray-100 text-gray-600';
                        $dtrStatus = null;
                    } else {
                        $statusLabel = 'absent';
                        $statusBadgeClass = 'bg-red-100 text-red-800';
                        $dtrStatus = 'absent';
                    }

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
                    ];
                }
                $currentDate->addDay();
            }

            // Count absent only for days before today (completed days)
            $daysPresent = $dtrs->where('status', 'present')->count();
            $daysAbsent = 0;
            if (!$isCurrentWeek) {
                // Only count absent for dates strictly before today
                $daysAbsent = $dtrs->filter(function($dtr) use ($today) {
                    return $dtr->date->lt($today) && ($dtr->status === 'absent' || $dtr->total_hours == 0);
                })->count();

                // Also count weekdays before today that have no DTR record (0:00 = absent)
                $pastWeekdays = 0;
                $checkDate = $weekStartDate->copy();
                while ($checkDate <= $weekEndDate && $checkDate->lt($today)) {
                    $dayOfWeek = $checkDate->dayOfWeek;
                    if ($dayOfWeek !== Carbon::SATURDAY && $dayOfWeek !== Carbon::SUNDAY) {
                        $dateKey = $checkDate->format('Y-m-d');
                        if (!isset($dtrMap[$dateKey])) {
                            $pastWeekdays++;
                        }
                    }
                    $checkDate->addDay();
                }
                $daysAbsent += $pastWeekdays;
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
                    $rawDeficit = max(0, $baseHours - $totalHours);
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
                                $rawDeficit += max(0, 8.0 - $dayHours);
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

