<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dtr;
use App\Models\DtrDeficit;
use App\Models\LeaveRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class KpiController extends Controller
{
    private const PRODUCTIVE_STATUSES = ['present', 'late', 'half_day'];
    private const EXCUSED_STATUSES = ['on_leave', 'travel'];

    public function dashboard(Request $request)
    {
        // Only super admins can access
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied. Only super administrators can view KPI dashboard.');
        }

        // Get filter parameters
        $dateFrom = $request->input('date_from', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfWeek()->format('Y-m-d'));
        $departmentId = $request->input('department_id');

        // Parse dates
        try {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
        } catch (\Exception $e) {
            $startDate = Carbon::now()->startOfWeek()->startOfDay();
            $endDate = Carbon::now()->endOfWeek()->endOfDay();
        }

        // Get only employees for Performance Ranking (always filter to employees only)
        $usersQuery = User::where('is_active', true)
            ->where('role', 'employee');

        if ($departmentId) {
            $usersQuery->where('department_id', $departmentId);
        }

        $users = $usersQuery->with('department')->get();

        // Get DTR records for the date range.
        $dtrs = Dtr::whereIn('user_id', $users->pluck('id'))
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->orderBy('date')
            ->get();

        // DTR deficits overlapping selected range (accuracy upgrade).
        $deficits = DtrDeficit::whereIn('user_id', $users->pluck('id'))
            ->whereDate('week_end_date', '>=', $startDate->toDateString())
            ->whereDate('week_start_date', '<=', $endDate->toDateString())
            ->orderBy('week_start_date')
            ->get();
        $deficitHoursByUser = $deficits->groupBy('user_id')->map(fn($rows) => (float) $rows->sum('deficit_hours'));

        // Approved absent leave requests overlapping selected range.
        $approvedAbsentLeaves = LeaveRequest::whereIn('user_id', $users->pluck('id'))
            ->where('status', 'approved')
            ->where('type', 'absent')
            ->whereDate('start_date', '<=', $endDate->toDateString())
            ->whereDate('end_date', '>=', $startDate->toDateString())
            ->get(['user_id', 'start_date', 'end_date']);

        $weekdays = [];
        foreach (CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay()) as $dt) {
            if (!$dt->isWeekend()) {
                $weekdays[] = $dt->toDateString();
            }
        }
        $expectedWeekdays = count($weekdays);

        $approvedAbsentDatesByUser = [];
        $approvedAbsentUsersByDate = [];
        foreach ($approvedAbsentLeaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date)->startOfDay();
            $leaveEnd = Carbon::parse($leave->end_date)->startOfDay();
            if ($leaveEnd->lt($startDate) || $leaveStart->gt($endDate)) {
                continue;
            }

            $periodStart = $leaveStart->copy()->max($startDate->copy()->startOfDay());
            $periodEnd = $leaveEnd->copy()->min($endDate->copy()->startOfDay());
            foreach (CarbonPeriod::create($periodStart, $periodEnd) as $dt) {
                if ($dt->isWeekend()) {
                    continue;
                }

                $dateStr = $dt->toDateString();
                $approvedAbsentDatesByUser[$leave->user_id][$dateStr] = true;
                $approvedAbsentUsersByDate[$dateStr][$leave->user_id] = true;
            }
        }

        // Calculate performance metrics for each user.
        $performanceData = [];

        foreach ($users as $user) {
            $userDtrs = $dtrs->where('user_id', $user->id);
            $byDate = $userDtrs->sortBy('date')->keyBy(fn($row) => Carbon::parse($row->date)->toDateString());

            $presentCount = 0;
            $lateCount = 0;
            $halfDayCount = 0;
            $excusedCount = 0;
            $workedDays = 0;
            $totalHours = 0.0;

            foreach ($weekdays as $dateStr) {
                $row = $byDate->get($dateStr);
                if (!$row) {
                    continue;
                }

                $status = (string) ($row->status ?? '');
                $hours = (float) ($row->total_hours ?? 0);
                $totalHours += $hours;

                if ($status === 'present') {
                    $presentCount++;
                    $workedDays++;
                } elseif ($status === 'late') {
                    $lateCount++;
                    $workedDays++;
                } elseif ($status === 'half_day') {
                    $halfDayCount++;
                    $workedDays++;
                } elseif (in_array($status, self::EXCUSED_STATUSES, true)) {
                    $excusedCount++;
                } elseif ($hours > 0) {
                    // Fallback: treat rows with hours as worked.
                    $workedDays++;
                }
            }

            $totalDays = $startDate->diffInDays($endDate) + 1;
            $absentCount = isset($approvedAbsentDatesByUser[$user->id])
                ? count($approvedAbsentDatesByUser[$user->id])
                : 0;
            $avgHoursPerDay = $workedDays > 0 ? $totalHours / $workedDays : 0;

            $consideredAttendanceDays = max($expectedWeekdays, 1);
            $attendanceRate = (($workedDays + $excusedCount) / $consideredAttendanceDays) * 100;
            $punctualityRate = $workedDays > 0 ? (($workedDays - $lateCount) / $workedDays) * 100 : 100;
            $hoursRate = min(($avgHoursPerDay / 8) * 100, 100);
            $activityValues = $userDtrs
                ->pluck('activity_percentage')
                ->filter(fn($value) => $value !== null);
            $activityRate = $activityValues->count() > 0
                ? min(max((float) $activityValues->avg(), 0), 100)
                : $hoursRate;
            $deficitHours = (float) ($deficitHoursByUser->get($user->id, 0));
            $deficitPenaltyRate = min($deficitHours * 2, 100); // 2 points per deficit hour.

            // 0-100 score; weights tuned for attendance + punctuality + productive hours + deficit penalty.
            $performanceScore = max(
                0,
                min(
                    100,
                    ($attendanceRate * 0.35)
                    + ($punctualityRate * 0.20)
                    + ($hoursRate * 0.15)
                    + ($activityRate * 0.30)
                    - ($deficitPenaltyRate * 0.15)
                )
            );

            // Perfect attendance = all expected weekdays either worked or excused, with no late and no absences.
            $hasPerfectAttendance = ($workedDays + $excusedCount) >= $expectedWeekdays && $lateCount === 0 && $absentCount === 0;

            $performanceData[] = [
                'user' => $user,
                'total_days' => $totalDays,
                'working_days' => $workedDays,
                'expected_weekdays' => $expectedWeekdays,
                'total_hours' => $totalHours,
                'avg_hours_per_day' => $avgHoursPerDay,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'half_day_count' => $halfDayCount,
                'excused_count' => $excusedCount,
                'absent_count' => $absentCount,
                'attendance_rate' => $attendanceRate,
                'punctuality_rate' => $punctualityRate,
                'hours_rate' => $hoursRate,
                'activity_rate' => $activityRate,
                'deficit_hours' => $deficitHours,
                'has_perfect_attendance' => $hasPerfectAttendance,
                'performance_score' => $performanceScore,
            ];
        }

        // Sort by performance score (descending)
        usort($performanceData, function($a, $b) {
            return $b['performance_score'] <=> $a['performance_score'];
        });

        // Get departments for filter
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Calculate summary statistics
        $totalUsers = count($performanceData);
        $perfectAttendanceCount = collect($performanceData)->where('has_perfect_attendance', true)->count();
        $avgPerformanceScore = $totalUsers > 0 ? collect($performanceData)->avg('performance_score') : 0;
        $totalHoursAll = collect($performanceData)->sum('total_hours');
        $avgDeficitHours = $totalUsers > 0 ? collect($performanceData)->avg('deficit_hours') : 0;
        $zeroDeficitCount = collect($performanceData)->where('deficit_hours', '<=', 0)->count();

        // Prepare chart data
        // Top 10 Performers
        $topPerformers = collect($performanceData)->take(10)->map(function($data) {
            return [
                'name' => $data['user']->name,
                'score' => round($data['performance_score'], 1)
            ];
        })->toArray();

        // Performance Score Distribution
        $scoreRanges = [
            '90-100' => 0,
            '80-89' => 0,
            '70-79' => 0,
            '60-69' => 0,
            '0-59' => 0
        ];
        foreach ($performanceData as $data) {
            $score = $data['performance_score'];
            if ($score >= 90) {
                $scoreRanges['90-100']++;
            } elseif ($score >= 80) {
                $scoreRanges['80-89']++;
            } elseif ($score >= 70) {
                $scoreRanges['70-79']++;
            } elseif ($score >= 60) {
                $scoreRanges['60-69']++;
            } else {
                $scoreRanges['0-59']++;
            }
        }

        // Department Performance Comparison
        $departmentPerformance = [];
        foreach ($performanceData as $data) {
            $deptName = $data['user']->department->name ?? 'No Department';
            if (!isset($departmentPerformance[$deptName])) {
                $departmentPerformance[$deptName] = [
                    'total_score' => 0,
                    'count' => 0
                ];
            }
            $departmentPerformance[$deptName]['total_score'] += $data['performance_score'];
            $departmentPerformance[$deptName]['count']++;
        }
        $departmentAvg = [];
        foreach ($departmentPerformance as $dept => $data) {
            $departmentAvg[$dept] = $data['count'] > 0 ? round($data['total_score'] / $data['count'], 1) : 0;
        }
        arsort($departmentAvg);

        // Attendance Breakdown
        $attendanceBreakdown = [
            'present' => collect($performanceData)->sum('present_count'),
            'late' => collect($performanceData)->sum('late_count'),
            'absent' => collect($performanceData)->sum('absent_count'),
            'half_day' => collect($performanceData)->sum('half_day_count'),
            'excused' => collect($performanceData)->sum('excused_count'),
        ];

        // Line Graph Data - Performance Trends Over Time (Daily)
        $dailyPerformance = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $dayDtrs = $dtrs->where('date', $dateStr);
            $presentCount = $dayDtrs->where('status', 'present')->count();
            $lateCount = $dayDtrs->where('status', 'late')->count();
            $halfDayCount = $dayDtrs->where('status', 'half_day')->count();
            $excusedCount = $dayDtrs->whereIn('status', self::EXCUSED_STATUSES)->count();
            $usersWithDtr = $dayDtrs->pluck('user_id')->unique()->count();
            $isWeekday = !$currentDate->isWeekend();
            $absentForDay = isset($approvedAbsentUsersByDate[$dateStr])
                ? count($approvedAbsentUsersByDate[$dateStr])
                : 0;

            $workedForDay = $presentCount + $lateCount + $halfDayCount;
            $dayExpected = $isWeekday ? max($totalUsers, 1) : 0;
            $attendanceRateForDay = $dayExpected > 0 ? (($workedForDay + $excusedCount) / $dayExpected) * 100 : 0;
            $punctualityRateForDay = $workedForDay > 0 ? (($workedForDay - $lateCount) / $workedForDay) * 100 : 100;
            $avgHours = $usersWithDtr > 0 ? ((float) $dayDtrs->sum('total_hours')) / $usersWithDtr : 0;
            $hoursRateForDay = min(($avgHours / 8) * 100, 100);
            $activityRateForDay = $dayDtrs
                ->pluck('activity_percentage')
                ->filter(fn($value) => $value !== null)
                ->whenEmpty(fn($collection) => $collection->push($hoursRateForDay))
                ->avg();
            $dailyScore = max(0, min(100, ($attendanceRateForDay * 0.35) + ($punctualityRateForDay * 0.20) + ($hoursRateForDay * 0.15) + ((float) $activityRateForDay * 0.30)));

            $dailyPerformance[$dateStr] = [
                'date' => $currentDate->format('M d'),
                'avg_hours' => round($avgHours, 2),
                'performance_score' => round($dailyScore, 1),
                'attendance_rate' => round($attendanceRateForDay, 1),
                'present' => $presentCount,
                'late' => $lateCount,
                'half_day' => $halfDayCount,
                'excused' => $excusedCount,
                'absent' => $absentForDay,
            ];
            
            $currentDate->addDay();
        }

        // Stacked Area Chart Data - Attendance breakdown over time
        $stackedAreaData = [];
        foreach ($dailyPerformance as $dateStr => $data) {
            $stackedAreaData[] = [
                'date' => $data['date'],
                'present' => $data['present'],
                'late' => $data['late'],
                'half_day' => $data['half_day'],
                'excused' => $data['excused'],
                'absent' => $data['absent']
            ];
        }

        // Radar Chart Data - Average performance across metrics (for top performer vs average)
        $topPerformer = collect($performanceData)->first();
        $avgMetrics = [
            'Attendance' => collect($performanceData)->avg('attendance_rate'),
            'Punctuality' => collect($performanceData)->avg('punctuality_rate'),
            'Hours Worked' => collect($performanceData)->avg('total_hours'),
            'Productivity' => collect($performanceData)->avg('activity_rate'),
            'Consistency' => collect($performanceData)->avg('working_days') / max(collect($performanceData)->avg('total_days'), 1) * 100,
            'Performance Score' => collect($performanceData)->avg('performance_score')
        ];
        
        $topPerformerMetrics = [];
        if ($topPerformer) {
            $topPerformerMetrics = [
                'Attendance' => $topPerformer['attendance_rate'],
                'Punctuality' => $topPerformer['punctuality_rate'],
                'Hours Worked' => $topPerformer['total_hours'],
                'Productivity' => $topPerformer['activity_rate'],
                'Consistency' => $topPerformer['total_days'] > 0 ? ($topPerformer['working_days'] / $topPerformer['total_days'] * 100) : 0,
                'Performance Score' => $topPerformer['performance_score']
            ];
        }

        // Normalize radar chart data (scale to 0-100 for better visualization)
        $normalizedAvgMetrics = [];
        $normalizedTopMetrics = [];
        $maxValues = [
            'Attendance' => max(max($avgMetrics['Attendance'], $topPerformerMetrics['Attendance'] ?? 0), 1),
            'Punctuality' => 100,
            'Hours Worked' => max(max($avgMetrics['Hours Worked'], $topPerformerMetrics['Hours Worked'] ?? 0), 1),
            'Productivity' => 100,
            'Consistency' => 100,
            'Performance Score' => 100
        ];

        // Additional charts
        $dailyAttendanceRateData = array_values(array_map(fn($d) => $d['attendance_rate'], $dailyPerformance));
        $dailyLateData = array_values(array_map(fn($d) => $d['late'], $dailyPerformance));
        $dailyHalfDayData = array_values(array_map(fn($d) => $d['half_day'], $dailyPerformance));

        $departmentHours = [];
        foreach ($performanceData as $data) {
            $deptName = $data['user']->department->name ?? 'No Department';
            if (!isset($departmentHours[$deptName])) {
                $departmentHours[$deptName] = 0;
            }
            $departmentHours[$deptName] += (float) $data['total_hours'];
        }
        arsort($departmentHours);

        $workingDaysDistribution = [
            '0-20%' => 0,
            '21-40%' => 0,
            '41-60%' => 0,
            '61-80%' => 0,
            '81-100%' => 0,
        ];
        foreach ($performanceData as $data) {
            $ratio = $data['expected_weekdays'] > 0 ? ($data['working_days'] / $data['expected_weekdays']) * 100 : 0;
            if ($ratio <= 20) {
                $workingDaysDistribution['0-20%']++;
            } elseif ($ratio <= 40) {
                $workingDaysDistribution['21-40%']++;
            } elseif ($ratio <= 60) {
                $workingDaysDistribution['41-60%']++;
            } elseif ($ratio <= 80) {
                $workingDaysDistribution['61-80%']++;
            } else {
                $workingDaysDistribution['81-100%']++;
            }
        }

        $topBottom = collect($performanceData)->sortByDesc('performance_score');
        $topBottomPerformers = [
            'top' => $topBottom->take(5)->map(fn($d) => ['name' => $d['user']->name, 'score' => round($d['performance_score'], 1)])->values()->all(),
            'bottom' => $topBottom->reverse()->take(5)->map(fn($d) => ['name' => $d['user']->name, 'score' => round($d['performance_score'], 1)])->values()->all(),
        ];

        $deficitByWeek = [];
        foreach ($deficits as $deficit) {
            $weekLabel = Carbon::parse($deficit->week_start_date)->format('M d');
            if (!isset($deficitByWeek[$weekLabel])) {
                $deficitByWeek[$weekLabel] = 0.0;
            }
            $deficitByWeek[$weekLabel] += (float) $deficit->deficit_hours;
        }
        ksort($deficitByWeek);
        $deficitTrendLabels = array_keys($deficitByWeek);
        $deficitTrendData = array_values($deficitByWeek);
        
        foreach ($avgMetrics as $key => $value) {
            $normalizedAvgMetrics[$key] = ($value / $maxValues[$key]) * 100;
            if (isset($topPerformerMetrics[$key])) {
                $normalizedTopMetrics[$key] = ($topPerformerMetrics[$key] / $maxValues[$key]) * 100;
            } else {
                $normalizedTopMetrics[$key] = 0;
            }
        }

        return view('admin.kpi.dashboard', compact(
            'performanceData',
            'departments',
            'dateFrom',
            'dateTo',
            'departmentId',
            'totalUsers',
            'perfectAttendanceCount',
            'avgPerformanceScore',
            'totalHoursAll',
            'avgDeficitHours',
            'zeroDeficitCount',
            'topPerformers',
            'scoreRanges',
            'departmentAvg',
            'attendanceBreakdown',
            'dailyPerformance',
            'stackedAreaData',
            'normalizedAvgMetrics',
            'normalizedTopMetrics',
            'topPerformer',
            'dailyAttendanceRateData',
            'dailyLateData',
            'dailyHalfDayData',
            'departmentHours',
            'workingDaysDistribution',
            'topBottomPerformers',
            'deficitTrendLabels',
            'deficitTrendData'
        ));
    }
}
