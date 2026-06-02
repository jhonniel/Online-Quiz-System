<?php

namespace App\Support;

use App\Models\Dtr;
use App\Models\DtrTimeRequest;
use App\Models\LeaveRequest;
use App\Models\QuizAttempt;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminScopedDashboardCharts
{
    /**
     * @param  list<int>  $employeeIds
     * @return array<string, mixed>
     */
    public function forEmployees(array $employeeIds, Request $request): array
    {
        $meta = $this->resolvePeriodMeta($request);
        $ranges = $meta['ranges'];
        $ids = array_values(array_filter(array_map('intval', $employeeIds)));

        if ($ids === []) {
            return array_merge($meta, ['payload' => $this->emptyEmployeePayload($ranges)]);
        }

        $periodStart = $ranges[0]['start'] ?? now()->subDays(7);
        $periodEnd = end($ranges)['end'] ?? now();

        $leaveTrendLabels = [];
        $leaveTrendData = [];
        $dtrTrendData = [];
        foreach ($ranges as $r) {
            $leaveTrendLabels[] = $r['label'];
            $leaveTrendData[] = LeaveRequest::whereIn('user_id', $ids)
                ->whereBetween('created_at', [$r['start'], $r['end']])
                ->count();
            $dtrTrendData[] = Dtr::whereIn('user_id', $ids)
                ->whereBetween('date', [$r['start'], $r['end']])
                ->count();
        }

        $leaveStatusLabels = ['Pending', 'For More Verification', 'Approved', 'Rejected'];
        $leaveStatusData = [
            LeaveRequest::whereIn('user_id', $ids)->where('status', 'pending')->count(),
            LeaveRequest::whereIn('user_id', $ids)->where('status', 'for_more_verification')->count(),
            LeaveRequest::whereIn('user_id', $ids)->where('status', 'approved')->count(),
            LeaveRequest::whereIn('user_id', $ids)->where('status', 'rejected')->count(),
        ];

        $employeeLeaveTypes = ['vacation_leave', 'sick_leave', 'overtime', 'offset', 'work_from_home', 'absent'];
        $leaveByTypeLabels = [];
        $leaveByTypeData = [];
        foreach ($employeeLeaveTypes as $type) {
            $leaveByTypeLabels[] = LeaveRequest::labelForType($type);
            $leaveByTypeData[] = LeaveRequest::whereIn('user_id', $ids)
                ->where('type', $type)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->count();
        }

        $statusTrendLabels = $leaveTrendLabels;
        $statusTrendPending = [];
        $statusTrendApproved = [];
        $statusTrendRejected = [];
        foreach ($ranges as $r) {
            $statusTrendPending[] = LeaveRequest::whereIn('user_id', $ids)
                ->where('status', 'pending')
                ->whereBetween('created_at', [$r['start'], $r['end']])
                ->count();
            $statusTrendApproved[] = LeaveRequest::whereIn('user_id', $ids)
                ->where('status', 'approved')
                ->whereBetween('created_at', [$r['start'], $r['end']])
                ->count();
            $statusTrendRejected[] = LeaveRequest::whereIn('user_id', $ids)
                ->where('status', 'rejected')
                ->whereBetween('created_at', [$r['start'], $r['end']])
                ->count();
        }

        return array_merge($meta, [
            'payload' => [
                'leaveTrendLabels' => $leaveTrendLabels,
                'leaveTrendData' => $leaveTrendData,
                'dtrTrendLabels' => $leaveTrendLabels,
                'dtrTrendData' => $dtrTrendData,
                'leaveStatusLabels' => $leaveStatusLabels,
                'leaveStatusData' => $leaveStatusData,
                'leaveByTypeLabels' => $leaveByTypeLabels,
                'leaveByTypeData' => $leaveByTypeData,
                'statusTrendLabels' => $statusTrendLabels,
                'statusTrendPending' => $statusTrendPending,
                'statusTrendApproved' => $statusTrendApproved,
                'statusTrendRejected' => $statusTrendRejected,
            ],
        ]);
    }

    /**
     * @param  list<int>  $studentIds
     * @return array<string, mixed>
     */
    public function forStudents(array $studentIds, Request $request): array
    {
        $meta = $this->resolvePeriodMeta($request);
        $ranges = $meta['ranges'];
        $ids = array_values(array_filter(array_map('intval', $studentIds)));

        if ($ids === []) {
            return array_merge($meta, ['payload' => $this->emptyStudentPayload($ranges)]);
        }

        $periodStart = $ranges[0]['start'] ?? now()->subDays(7);
        $periodEnd = end($ranges)['end'] ?? now();

        $leaveTrendLabels = [];
        $leaveTrendData = [];
        $dtrTrendData = [];
        foreach ($ranges as $r) {
            $leaveTrendLabels[] = $r['label'];
            $leaveTrendData[] = LeaveRequest::whereIn('user_id', $ids)
                ->whereBetween('created_at', [$r['start'], $r['end']])
                ->count();
            $dtrTrendData[] = Dtr::whereIn('user_id', $ids)
                ->whereBetween('date', [$r['start'], $r['end']])
                ->count();
        }

        $incompleteOvertime = LeaveRequest::query()
            ->whereIn('user_id', $ids)
            ->awaitingAttendanceOvertimeCompletion()
            ->count();
        $pendingStudentLeave = LeaveRequest::whereIn('user_id', $ids)->where('status', 'pending')->count();

        $leaveStatusLabels = ['Pending', 'Incomplete Details', 'For More Verification', 'Approved', 'Rejected'];
        $leaveStatusData = [
            max(0, $pendingStudentLeave - $incompleteOvertime),
            $incompleteOvertime,
            LeaveRequest::whereIn('user_id', $ids)->where('status', 'for_more_verification')->count(),
            LeaveRequest::whereIn('user_id', $ids)->where('status', 'approved')->count(),
            LeaveRequest::whereIn('user_id', $ids)->where('status', 'rejected')->count(),
        ];

        $studentLeaveTypes = ['overtime', 'additional_time', 'absent', 'other'];
        $leaveByTypeLabels = [];
        $leaveByTypeData = [];
        foreach ($studentLeaveTypes as $type) {
            $leaveByTypeLabels[] = LeaveRequest::labelForType($type);
            $leaveByTypeData[] = LeaveRequest::whereIn('user_id', $ids)
                ->where('type', $type)
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->count();
        }

        $timeRequestLabels = $leaveTrendLabels;
        $timeRequestPending = [];
        $timeRequestApproved = [];
        $timeRequestRejected = [];
        foreach ($ranges as $r) {
            $timeRequestPending[] = DtrTimeRequest::whereIn('user_id', $ids)
                ->where('status', 'pending')
                ->whereBetween('created_at', [$r['start'], $r['end']])
                ->count();
            $timeRequestApproved[] = DtrTimeRequest::whereIn('user_id', $ids)
                ->where('status', 'approved')
                ->whereBetween('created_at', [$r['start'], $r['end']])
                ->count();
            $timeRequestRejected[] = DtrTimeRequest::whereIn('user_id', $ids)
                ->where('status', 'rejected')
                ->whereBetween('created_at', [$r['start'], $r['end']])
                ->count();
        }

        $quizLabels = $leaveTrendLabels;
        $quizData = [];
        foreach ($ranges as $r) {
            $quizData[] = QuizAttempt::whereIn('user_id', $ids)
                ->whereBetween('created_at', [$r['start'], $r['end']])
                ->count();
        }

        return array_merge($meta, [
            'payload' => [
                'leaveTrendLabels' => $leaveTrendLabels,
                'leaveTrendData' => $leaveTrendData,
                'dtrTrendLabels' => $leaveTrendLabels,
                'dtrTrendData' => $dtrTrendData,
                'leaveStatusLabels' => $leaveStatusLabels,
                'leaveStatusData' => $leaveStatusData,
                'leaveByTypeLabels' => $leaveByTypeLabels,
                'leaveByTypeData' => $leaveByTypeData,
                'timeRequestLabels' => $timeRequestLabels,
                'timeRequestPending' => $timeRequestPending,
                'timeRequestApproved' => $timeRequestApproved,
                'timeRequestRejected' => $timeRequestRejected,
                'quizLabels' => $quizLabels,
                'quizData' => $quizData,
                'pendingTimeRequests' => DtrTimeRequest::whereIn('user_id', $ids)->where('status', 'pending')->count(),
                'incompleteOvertime' => $incompleteOvertime,
            ],
        ]);
    }

    /**
     * @return array{chartPeriod: string, chartFrom: string, chartTo: string, ranges: list<array<string, mixed>>}
     */
    public function resolvePeriodMeta(Request $request): array
    {
        $chartPeriod = $this->getChartPeriod($request);
        $customRange = $this->getCustomChartRange($request);
        if ($customRange !== null) {
            $chartPeriod = 'custom';
        } elseif ($chartPeriod === 'custom') {
            $chartPeriod = 'week';
        }

        return [
            'chartPeriod' => $chartPeriod,
            'chartFrom' => $request->input('chart_from', now()->subDays(6)->format('Y-m-d')),
            'chartTo' => $request->input('chart_to', now()->format('Y-m-d')),
            'ranges' => $this->getChartDateRanges($chartPeriod, $request),
        ];
    }

    public function getChartPeriod(Request $request): string
    {
        $period = $request->input('chart_period', 'week');

        return in_array($period, ['day', 'week', 'month', 'year', 'custom'], true) ? $period : 'week';
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    public function getCustomChartRange(Request $request): ?array
    {
        $from = $request->input('chart_from');
        $to = $request->input('chart_to');
        if (empty($from) || empty($to)) {
            return null;
        }

        try {
            $fromDate = Carbon::parse($from)->startOfDay();
            $toDate = Carbon::parse($to)->endOfDay();
            if ($fromDate->gt($toDate)) {
                return null;
            }

            return [$fromDate, $toDate];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getChartDateRanges(string $period, Request $request): array
    {
        $customRange = $this->getCustomChartRange($request);
        if ($customRange !== null) {
            [$fromDate, $toDate] = $customRange;
            $daysDiff = $fromDate->diffInDays($toDate) + 1;
            $ranges = [];

            if ($daysDiff <= 31) {
                for ($date = $fromDate->copy(); $date->lte($toDate); $date->addDay()) {
                    $ranges[] = [
                        'label' => $date->format('M j'),
                        'start' => $date->copy()->startOfDay(),
                        'end' => $date->copy()->endOfDay(),
                    ];
                }
            } else {
                $current = $fromDate->copy()->startOfMonth();
                while ($current->lte($toDate)) {
                    $monthEnd = $current->copy()->endOfMonth();
                    $end = $monthEnd->gt($toDate) ? $toDate->copy() : $monthEnd;
                    $ranges[] = [
                        'label' => $current->format('M Y'),
                        'start' => $current->copy()->startOfMonth(),
                        'end' => $end,
                    ];
                    $current->addMonth()->startOfMonth();
                }
            }

            return $ranges;
        }

        $ranges = [];
        if ($period === 'day') {
            for ($h = 23; $h >= 0; $h--) {
                $dt = now()->subHours($h);
                $ranges[] = [
                    'label' => $dt->format('g a'),
                    'start' => $dt->copy()->startOfHour(),
                    'end' => $dt->copy()->endOfHour(),
                ];
            }
        } elseif ($period === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $ranges[] = [
                    'label' => $date->format('M j'),
                    'start' => $date->copy()->startOfDay(),
                    'end' => $date->copy()->endOfDay(),
                ];
            }
        } elseif ($period === 'month') {
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $ranges[] = [
                    'label' => $date->format('M j'),
                    'start' => $date->copy()->startOfDay(),
                    'end' => $date->copy()->endOfDay(),
                ];
            }
        } else {
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $ranges[] = [
                    'label' => $date->format('M Y'),
                    'start' => $date->copy()->startOfMonth(),
                    'end' => $date->copy()->endOfMonth(),
                ];
            }
        }

        return $ranges;
    }

    /**
     * @param  list<array<string, mixed>>  $ranges
     * @return array<string, mixed>
     */
    private function emptyEmployeePayload(array $ranges): array
    {
        $labels = array_column($ranges, 'label');
        $zeros = array_fill(0, max(count($labels), 1), 0);

        return [
            'leaveTrendLabels' => $labels,
            'leaveTrendData' => $zeros,
            'dtrTrendLabels' => $labels,
            'dtrTrendData' => $zeros,
            'leaveStatusLabels' => ['Pending', 'For More Verification', 'Approved', 'Rejected'],
            'leaveStatusData' => [0, 0, 0, 0],
            'leaveByTypeLabels' => [],
            'leaveByTypeData' => [],
            'statusTrendLabels' => $labels,
            'statusTrendPending' => $zeros,
            'statusTrendApproved' => $zeros,
            'statusTrendRejected' => $zeros,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $ranges
     * @return array<string, mixed>
     */
    private function emptyStudentPayload(array $ranges): array
    {
        $labels = array_column($ranges, 'label');
        $zeros = array_fill(0, max(count($labels), 1), 0);

        return [
            'leaveTrendLabels' => $labels,
            'leaveTrendData' => $zeros,
            'dtrTrendLabels' => $labels,
            'dtrTrendData' => $zeros,
            'leaveStatusLabels' => ['Pending', 'Incomplete Details', 'For More Verification', 'Approved', 'Rejected'],
            'leaveStatusData' => [0, 0, 0, 0, 0],
            'leaveByTypeLabels' => [],
            'leaveByTypeData' => [],
            'timeRequestLabels' => $labels,
            'timeRequestPending' => $zeros,
            'timeRequestApproved' => $zeros,
            'timeRequestRejected' => $zeros,
            'quizLabels' => $labels,
            'quizData' => $zeros,
            'pendingTimeRequests' => 0,
            'incompleteOvertime' => 0,
        ];
    }
}
