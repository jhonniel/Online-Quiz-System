<?php

namespace App\Support;

use App\Models\ContactMessage;
use App\Models\DtrTimeRequest;
use App\Models\EmployeeFileRequest;
use App\Models\HiringApplication;
use App\Models\LeaveRequest;
use App\Models\TicketReport;
use App\Models\User;
use Illuminate\Http\Request;

class AdminHrDashboardCharts
{
    public function __construct(
        private AdminScopedDashboardCharts $scopedCharts,
    ) {}

    /**
     * @return array{
     *     chartPeriod: string,
     *     chartFrom: string,
     *     chartTo: string,
     *     payload: array<string, mixed>,
     *     sections: list<string>
     * }
     */
    public function forUser(User $user, Request $request): array
    {
        $meta = $this->scopedCharts->resolvePeriodMeta($request);
        $ranges = $meta['ranges'];
        $labels = array_column($ranges, 'label');
        $zeros = array_fill(0, max(count($labels), 1), 0);

        $periodStart = $ranges[0]['start'] ?? now()->subDays(7);
        $periodEnd = end($ranges)['end'] ?? now();

        $payload = ['trendLabels' => $labels];
        $sections = ['workforce'];

        $payload['workforceLabels'] = ['Employees', 'Students', 'HR'];
        $payload['workforceData'] = [
            User::query()->where('role', 'employee')->count(),
            User::query()->where('role', 'student')->count(),
            User::query()->where('role', 'hr')->count(),
        ];

        $pendingLabels = [];
        $pendingData = [];

        if ($user->canAccessEmployeeFeature('leave_requests')) {
            $pendingLabels[] = 'Employee leave';
            $pendingData[] = LeaveRequest::query()
                ->where('status', 'pending')
                ->whereHas('user', fn ($q) => $q->where('role', 'employee'))
                ->whereIn('type', LeaveRequest::hrViewableTypes())
                ->count();
        }

        if ($user->canAccessStudentFeature('student_leave_requests')) {
            $pendingLabels[] = 'Student leave';
            $pendingData[] = LeaveRequest::query()
                ->where('status', 'pending')
                ->whereHas('user', fn ($q) => $q->where('role', 'student'))
                ->count();
        }

        if ($user->canAccessEmployeeFeature('time_report')) {
            $pendingLabels[] = 'Time requests';
            $pendingData[] = DtrTimeRequest::query()->where('status', 'pending')->count();
        }

        if ($user->canAccessEmployeeFeature('file_request')) {
            $pendingLabels[] = 'File requests';
            $pendingData[] = EmployeeFileRequest::query()->where('status', 'pending')->count();
        }

        if ($user->canAccessHiringFeature('applications')) {
            $pendingLabels[] = 'Applications';
            $pendingData[] = HiringApplication::query()->where('status', 'pending')->count();
        }

        if ($user->canAccessCommunicationFeature('contact_messages')) {
            $pendingLabels[] = 'Unread messages';
            $pendingData[] = ContactMessage::query()->where('status', 'new')->count();
        }

        if ($user->canAccessCommunicationFeature('tickets')) {
            $pendingLabels[] = 'Open tickets';
            $pendingData[] = TicketReport::query()->where('status', 'open')->count();
        }

        if ($pendingLabels !== []) {
            $sections[] = 'pending_workload';
            $payload['pendingLabels'] = $pendingLabels;
            $payload['pendingData'] = $pendingData;
        }

        if ($user->canAccessEmployeeFeature('leave_requests')) {
            $sections[] = 'employee_leave';

            $employeeLeaveQuery = fn () => LeaveRequest::query()
                ->whereHas('user', fn ($q) => $q->where('role', 'employee'))
                ->whereIn('type', LeaveRequest::hrViewableTypes());

            $leaveTrendData = [];
            $statusTrendPending = [];
            $statusTrendApproved = [];
            $statusTrendRejected = [];
            foreach ($ranges as $range) {
                $leaveTrendData[] = (clone $employeeLeaveQuery())
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
                $statusTrendPending[] = (clone $employeeLeaveQuery())
                    ->where('status', 'pending')
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
                $statusTrendApproved[] = (clone $employeeLeaveQuery())
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
                $statusTrendRejected[] = (clone $employeeLeaveQuery())
                    ->where('status', 'rejected')
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
            }

            $payload['employeeLeaveTrendData'] = $leaveTrendData;
            $payload['employeeLeaveStatusLabels'] = ['Pending', 'For More Verification', 'Approved', 'Rejected'];
            $payload['employeeLeaveStatusData'] = [
                (clone $employeeLeaveQuery())->where('status', 'pending')->count(),
                (clone $employeeLeaveQuery())->where('status', 'for_more_verification')->count(),
                (clone $employeeLeaveQuery())->where('status', 'approved')->count(),
                (clone $employeeLeaveQuery())->where('status', 'rejected')->count(),
            ];

            $typeLabels = [];
            $typeData = [];
            foreach (LeaveRequest::hrViewableTypes() as $type) {
                $typeLabels[] = LeaveRequest::labelForType($type);
                $typeData[] = (clone $employeeLeaveQuery())
                    ->where('type', $type)
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->count();
            }
            $payload['employeeLeaveTypeLabels'] = $typeLabels;
            $payload['employeeLeaveTypeData'] = $typeData;
            $payload['employeeLeaveStatusTrendPending'] = $statusTrendPending;
            $payload['employeeLeaveStatusTrendApproved'] = $statusTrendApproved;
            $payload['employeeLeaveStatusTrendRejected'] = $statusTrendRejected;
        }

        if ($user->canAccessStudentFeature('student_leave_requests')) {
            $sections[] = 'student_leave';

            $studentLeaveQuery = fn () => LeaveRequest::query()
                ->whereHas('user', fn ($q) => $q->where('role', 'student'));

            $studentLeaveTrendData = [];
            foreach ($ranges as $range) {
                $studentLeaveTrendData[] = (clone $studentLeaveQuery())
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
            }

            $payload['studentLeaveTrendData'] = $studentLeaveTrendData;
            $payload['studentLeaveStatusLabels'] = ['Pending', 'For More Verification', 'Approved', 'Rejected'];
            $payload['studentLeaveStatusData'] = [
                (clone $studentLeaveQuery())->where('status', 'pending')->count(),
                (clone $studentLeaveQuery())->where('status', 'for_more_verification')->count(),
                (clone $studentLeaveQuery())->where('status', 'approved')->count(),
                (clone $studentLeaveQuery())->where('status', 'rejected')->count(),
            ];
        }

        if ($user->canAccessHiringFeature('applications')) {
            $sections[] = 'hiring';

            $hiringPending = [];
            $hiringAccepted = [];
            $hiringRejected = [];
            foreach ($ranges as $range) {
                $hiringPending[] = HiringApplication::query()
                    ->whereIn('status', ['pending', 'screening', 'interview_scheduled'])
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
                $hiringAccepted[] = HiringApplication::query()
                    ->whereIn('status', ['accepted', 'hired', 'done_interview'])
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
                $hiringRejected[] = HiringApplication::query()
                    ->where('status', 'rejected')
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
            }

            $payload['hiringPendingData'] = $hiringPending;
            $payload['hiringAcceptedData'] = $hiringAccepted;
            $payload['hiringRejectedData'] = $hiringRejected;
            $payload['hiringStatusLabels'] = ['In progress', 'Accepted / Hired', 'Rejected'];
            $payload['hiringStatusData'] = [
                HiringApplication::query()->whereIn('status', ['pending', 'screening', 'interview_scheduled', 'done_interview'])->count(),
                HiringApplication::query()->whereIn('status', ['accepted', 'hired'])->count(),
                HiringApplication::query()->where('status', 'rejected')->count(),
            ];
        }

        if ($user->canAccessCommunicationFeature('tickets')) {
            $sections[] = 'tickets';

            $ticketsOpen = [];
            $ticketsClosed = [];
            foreach ($ranges as $range) {
                $ticketsOpen[] = TicketReport::query()
                    ->whereIn('status', ['open', 'processing', 'needs_investigation'])
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
                $ticketsClosed[] = TicketReport::query()
                    ->whereIn('status', ['resolved', 'closed'])
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
            }

            $payload['ticketsOpenData'] = $ticketsOpen;
            $payload['ticketsClosedData'] = $ticketsClosed;
        }

        if ($user->canAccessCommunicationFeature('contact_messages')) {
            $sections[] = 'contact';

            $contactTrendData = [];
            foreach ($ranges as $range) {
                $contactTrendData[] = ContactMessage::query()
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
            }

            $payload['contactTrendData'] = $contactTrendData;
        }

        if ($user->canAccessEmployeeFeature('time_report') || $user->canAccessStudentFeature('time_requests')) {
            $sections[] = 'time_requests';

            $timeRequestBase = function () use ($user) {
                $query = DtrTimeRequest::query();

                if ($user->canAccessEmployeeFeature('time_report') && ! $user->canAccessStudentFeature('time_requests')) {
                    $query->whereHas('user', fn ($q) => $q->where('role', 'employee'));
                } elseif ($user->canAccessStudentFeature('time_requests') && ! $user->canAccessEmployeeFeature('time_report')) {
                    $query->whereHas('user', fn ($q) => $q->where('role', 'student'));
                }

                return $query;
            };

            $timePending = [];
            $timeApproved = [];
            $timeRejected = [];
            foreach ($ranges as $range) {
                $timePending[] = $timeRequestBase()
                    ->where('status', 'pending')
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
                $timeApproved[] = $timeRequestBase()
                    ->where('status', 'approved')
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
                $timeRejected[] = $timeRequestBase()
                    ->where('status', 'rejected')
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();
            }

            $payload['timeRequestPendingData'] = $timePending;
            $payload['timeRequestApprovedData'] = $timeApproved;
            $payload['timeRequestRejectedData'] = $timeRejected;
        }

        if ($sections === ['workforce']) {
            $payload['employeeLeaveTrendData'] = $zeros;
            $payload['studentLeaveTrendData'] = $zeros;
        }

        return array_merge($meta, [
            'payload' => $payload,
            'sections' => $sections,
        ]);
    }
}
