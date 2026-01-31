<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Dtr;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KpiController extends Controller
{
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

        // Get DTR records for the date range
        $dtrs = Dtr::whereIn('user_id', $users->pluck('id'))
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->toDateString())
            ->orderBy('date')
            ->get();

        // Calculate performance metrics for each user
        $performanceData = [];
        
        foreach ($users as $user) {
            $userDtrs = $dtrs->where('user_id', $user->id);
            
            // Calculate metrics
            $totalDays = $startDate->diffInDays($endDate) + 1;
            $workingDays = $userDtrs->count();
            $totalHours = $userDtrs->sum('total_hours');
            $avgHoursPerDay = $workingDays > 0 ? $totalHours / $workingDays : 0;
            
            // Count statuses
            $presentCount = $userDtrs->where('status', 'present')->count();
            $lateCount = $userDtrs->where('status', 'late')->count();
            $absentCount = $totalDays - $workingDays; // Days without DTR entry
            $completedCount = $userDtrs->where('status', 'completed')->count();
            $underTimeCount = $userDtrs->where('status', 'under_time')->count();
            
            // Calculate perfect attendance (all days present/completed, no late/absent)
            $perfectDays = $userDtrs->whereIn('status', ['present', 'completed'])->count();
            $hasPerfectAttendance = ($perfectDays === $totalDays) && ($lateCount === 0) && ($absentCount === 0);
            
            // Calculate performance score (0-100)
            $performanceScore = 0;
            if ($totalDays > 0) {
                $attendanceScore = ($presentCount + $completedCount) / $totalDays * 50; // 50% weight
                $punctualityScore = ($totalDays - $lateCount) / $totalDays * 30; // 30% weight
                $completionScore = $completedCount / max($workingDays, 1) * 20; // 20% weight
                $performanceScore = min(100, $attendanceScore + $punctualityScore + $completionScore);
            }

            $performanceData[] = [
                'user' => $user,
                'total_days' => $totalDays,
                'working_days' => $workingDays,
                'total_hours' => $totalHours,
                'avg_hours_per_day' => $avgHoursPerDay,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'absent_count' => $absentCount,
                'completed_count' => $completedCount,
                'under_time_count' => $underTimeCount,
                'perfect_days' => $perfectDays,
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
            'completed' => collect($performanceData)->sum('completed_count'),
            'under_time' => collect($performanceData)->sum('under_time_count')
        ];

        // Line Graph Data - Performance Trends Over Time (Daily)
        $dailyPerformance = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $dayDtrs = $dtrs->where('date', $dateStr);
            
            if ($dayDtrs->count() > 0) {
                $totalHours = $dayDtrs->sum('total_hours');
                $totalUsers = $dayDtrs->pluck('user_id')->unique()->count();
                $avgHours = $totalUsers > 0 ? $totalHours / $totalUsers : 0;
                
                $presentCount = $dayDtrs->where('status', 'present')->count();
                $completedCount = $dayDtrs->where('status', 'completed')->count();
                $lateCount = $dayDtrs->where('status', 'late')->count();
                
                // Calculate daily performance score
                $dailyScore = 0;
                if ($totalUsers > 0) {
                    $attendanceRate = ($presentCount + $completedCount) / $totalUsers;
                    $punctualityRate = ($totalUsers - $lateCount) / $totalUsers;
                    $dailyScore = ($attendanceRate * 0.6 + $punctualityRate * 0.4) * 100;
                }
                
                $dailyPerformance[$dateStr] = [
                    'date' => $currentDate->format('M d'),
                    'avg_hours' => round($avgHours, 2),
                    'performance_score' => round($dailyScore, 1),
                    'present' => $presentCount,
                    'completed' => $completedCount,
                    'late' => $lateCount,
                    'under_time' => $dayDtrs->where('status', 'under_time')->count(),
                    'absent' => $totalUsers - $dayDtrs->count()
                ];
            } else {
                $dailyPerformance[$dateStr] = [
                    'date' => $currentDate->format('M d'),
                    'avg_hours' => 0,
                    'performance_score' => 0,
                    'present' => 0,
                    'completed' => 0,
                    'late' => 0,
                    'under_time' => 0,
                    'absent' => 0
                ];
            }
            
            $currentDate->addDay();
        }

        // Stacked Area Chart Data - Attendance breakdown over time
        $stackedAreaData = [];
        foreach ($dailyPerformance as $dateStr => $data) {
            $stackedAreaData[] = [
                'date' => $data['date'],
                'present' => $data['present'],
                'completed' => $data['completed'],
                'late' => $data['late'],
                'under_time' => $data['under_time'],
                'absent' => $data['absent']
            ];
        }

        // Radar Chart Data - Average performance across metrics (for top performer vs average)
        $topPerformer = collect($performanceData)->first();
        $avgMetrics = [
            'Attendance' => collect($performanceData)->avg('present_count') + collect($performanceData)->avg('completed_count'),
            'Punctuality' => collect($performanceData)->avg('late_count') > 0 ? 100 - (collect($performanceData)->avg('late_count') / collect($performanceData)->avg('working_days') * 100) : 100,
            'Hours Worked' => collect($performanceData)->avg('total_hours'),
            'Completion Rate' => collect($performanceData)->avg('completed_count') / max(collect($performanceData)->avg('working_days'), 1) * 100,
            'Consistency' => collect($performanceData)->avg('working_days') / max(collect($performanceData)->avg('total_days'), 1) * 100,
            'Performance Score' => collect($performanceData)->avg('performance_score')
        ];
        
        $topPerformerMetrics = [];
        if ($topPerformer) {
            $topPerformerMetrics = [
                'Attendance' => $topPerformer['present_count'] + $topPerformer['completed_count'],
                'Punctuality' => $topPerformer['working_days'] > 0 ? 100 - ($topPerformer['late_count'] / $topPerformer['working_days'] * 100) : 100,
                'Hours Worked' => $topPerformer['total_hours'],
                'Completion Rate' => $topPerformer['working_days'] > 0 ? ($topPerformer['completed_count'] / $topPerformer['working_days'] * 100) : 0,
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
            'Completion Rate' => 100,
            'Consistency' => 100,
            'Performance Score' => 100
        ];
        
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
            'topPerformers',
            'scoreRanges',
            'departmentAvg',
            'attendanceBreakdown',
            'dailyPerformance',
            'stackedAreaData',
            'normalizedAvgMetrics',
            'normalizedTopMetrics',
            'topPerformer'
        ));
    }
}
