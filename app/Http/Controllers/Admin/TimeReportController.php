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
        $weeklyReports = [];
        foreach ($employees as $employee) {
            $dtrs = Dtr::where('user_id', $employee->id)
                ->whereBetween('date', [$weekStartDate->format('Y-m-d'), $weekEndDate->format('Y-m-d')])
                ->orderBy('date')
                ->get();

            // Daily breakdown - calculate from daily records
            // Create a map of dates to DTR records for easier lookup
            $dtrMap = [];
            foreach ($dtrs as $dtr) {
                $dateKey = $dtr->date->format('Y-m-d');
                $dtrMap[$dateKey] = $dtr;
            }

            // Calculate total hours from all DTR records (including weekends)
            $totalHours = $dtrs->sum('total_hours');
            $totalOvertime = $dtrs->sum('overtime_hours');

            // Daily breakdown - only show weekdays (Monday-Friday)
            $dailyBreakdown = [];
            $currentDate = $weekStartDate->copy();
            while ($currentDate <= $weekEndDate) {
                // Skip Saturday (6) and Sunday (0)
                $dayOfWeek = $currentDate->dayOfWeek;
                if ($dayOfWeek !== Carbon::SATURDAY && $dayOfWeek !== Carbon::SUNDAY) {
                    $dateKey = $currentDate->format('Y-m-d');
                    $dtr = $dtrMap[$dateKey] ?? null;

                    $dayHours = $dtr ? (float) $dtr->total_hours : 0;
                    $dayOvertime = $dtr ? (float) $dtr->overtime_hours : 0;

                    // Determine status label based on hours
                    if ($dayHours >= 8.0) {
                        $statusLabel = 'completed';
                        $statusBadgeClass = 'bg-green-100 text-green-800';
                    } elseif ($dayHours > 0 && $dayHours < 8.0) {
                        $statusLabel = 'under_time';
                        $statusBadgeClass = 'bg-yellow-100 text-yellow-800';
                    } else {
                        $statusLabel = 'absent';
                        $statusBadgeClass = 'bg-red-100 text-red-800';
                    }

                    $dailyBreakdown[] = [
                        'date' => $currentDate->copy(),
                        'dtr' => $dtr,
                        'total_hours' => $dayHours,
                        'overtime_hours' => $dayOvertime,
                        'status' => $dtr ? $dtr->status : 'absent',
                        'status_label' => $statusLabel,
                        'status_badge_class' => $statusBadgeClass,
                    ];
                }
                $currentDate->addDay();
            }

            $daysPresent = $dtrs->where('status', 'present')->count();
            $daysAbsent = $dtrs->where('status', 'absent')->count();
            $daysLate = $dtrs->where('status', 'late')->count();
            $daysOnLeave = $dtrs->where('status', 'on_leave')->count();
            $daysHalfDay = $dtrs->where('status', 'half_day')->count();
            $daysTravel = $dtrs->where('status', 'travel')->count();
            $totalDays = $dtrs->count();

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
            'endDate'
        ));
    }
}

