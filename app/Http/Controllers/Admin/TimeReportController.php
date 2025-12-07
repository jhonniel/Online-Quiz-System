<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dtr;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeReportController extends Controller
{
    /**
     * Display the time report page with weekly report.
     */
    public function index(Request $request)
    {
        // Get the week start date (default to current week)
        $weekStart = $request->get('week_start', now()->startOfWeek()->format('Y-m-d'));
        $weekStartDate = Carbon::parse($weekStart)->startOfWeek();
        $weekEndDate = $weekStartDate->copy()->endOfWeek();

        // Get all active employees
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
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

            $dailyBreakdown = [];
            $totalHours = 0;
            $totalOvertime = 0;
            $currentDate = $weekStartDate->copy();
            while ($currentDate <= $weekEndDate) {
                $dateKey = $currentDate->format('Y-m-d');
                $dtr = $dtrMap[$dateKey] ?? null;
                
                $dayHours = $dtr ? (float) $dtr->total_hours : 0;
                $dayOvertime = $dtr ? (float) $dtr->overtime_hours : 0;
                
                $totalHours += $dayHours;
                $totalOvertime += $dayOvertime;
                
                $dailyBreakdown[] = [
                    'date' => $currentDate->copy(),
                    'dtr' => $dtr,
                    'total_hours' => $dayHours,
                    'overtime_hours' => $dayOvertime,
                    'status' => $dtr ? $dtr->status : 'absent',
                ];
                $currentDate->addDay();
            }

            $daysPresent = $dtrs->where('status', 'present')->count();
            $daysAbsent = $dtrs->where('status', 'absent')->count();
            $daysLate = $dtrs->where('status', 'late')->count();
            $daysOnLeave = $dtrs->where('status', 'on_leave')->count();
            $daysHalfDay = $dtrs->where('status', 'half_day')->count();
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

        // Previous and next week dates
        $previousWeek = $weekStartDate->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $weekStartDate->copy()->addWeek()->format('Y-m-d');

        return view('admin.time-report.index', compact(
            'weeklyReports',
            'employees',
            'selectedEmployeeId',
            'weekStartDate',
            'weekEndDate',
            'previousWeek',
            'nextWeek',
            'overallStats'
        ));
    }
}

