<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\DtrTimeRequest;
use App\Models\EmployeeFileRequest;
use App\Models\HiringApplication;
use App\Models\LeaveRequest;
use App\Models\TicketReport;
use App\Models\User;
use App\Support\AdminFeatureNavLinks;
use App\Support\AdminHrDashboardCharts;
use Illuminate\Http\Request;

class HrDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->usesHrDashboard(), 403);

        $sections = AdminFeatureNavLinks::sectionsFor($user);
        $stats = $this->statsFor($user);
        $chartBundle = app(AdminHrDashboardCharts::class)->forUser($user, $request);

        return view('admin.hr-dashboard', [
            'user' => $user,
            'sections' => $sections,
            'stats' => $stats,
            'totalLinks' => collect($sections)->sum(fn (array $section): int => count($section['links'] ?? [])),
            'chartPeriod' => $chartBundle['chartPeriod'],
            'chartFrom' => $chartBundle['chartFrom'],
            'chartTo' => $chartBundle['chartTo'],
            'hrChartPayload' => $chartBundle['payload'],
            'hrChartSections' => $chartBundle['sections'],
        ]);
    }

    /**
     * @return list<array{label: string, value: int|string, url: string|null, tone: string}>
     */
    private function statsFor(User $user): array
    {
        $stats = [];

        if ($user->canAccessEmployeeFeature('leave_requests')) {
            $query = LeaveRequest::query()
                ->where('status', 'pending')
                ->whereHas('user', fn ($q) => $q->where('role', 'employee'));

            if ($user->isHr()) {
                $query->whereIn('type', LeaveRequest::hrViewableTypes());
            }

            $count = $query->count();
            $stats[] = [
                'label' => 'Pending employee leave',
                'value' => $count,
                'url' => url('/admin/leave-requests'),
                'tone' => $count > 0 ? 'amber' : 'gray',
            ];
        }

        if ($user->canAccessStudentFeature('student_leave_requests')) {
            $count = LeaveRequest::query()
                ->where('status', 'pending')
                ->whereHas('user', fn ($q) => $q->where('role', 'student'))
                ->count();

            $stats[] = [
                'label' => 'Pending student leave',
                'value' => $count,
                'url' => url('/admin/student-leave-requests'),
                'tone' => $count > 0 ? 'amber' : 'gray',
            ];
        }

        if ($user->canAccessEmployeeFeature('time_report')) {
            $count = DtrTimeRequest::query()->where('status', 'pending')->count();
            $stats[] = [
                'label' => 'Pending time requests',
                'value' => $count,
                'url' => url('/admin/time-report'),
                'tone' => $count > 0 ? 'amber' : 'gray',
            ];
        }

        if ($user->canAccessEmployeeFeature('file_request')) {
            $count = EmployeeFileRequest::query()->where('status', 'pending')->count();
            $stats[] = [
                'label' => 'Pending file requests',
                'value' => $count,
                'url' => url('/admin/file-request'),
                'tone' => $count > 0 ? 'amber' : 'gray',
            ];
        }

        if ($user->canAccessHiringFeature('applications')) {
            $count = HiringApplication::query()->where('status', 'pending')->count();
            $stats[] = [
                'label' => 'Pending applications',
                'value' => $count,
                'url' => url('/admin/hiring-applications'),
                'tone' => $count > 0 ? 'blue' : 'gray',
            ];
        }

        if ($user->canAccessCommunicationFeature('contact_messages')) {
            $count = ContactMessage::query()->where('status', 'new')->count();
            $stats[] = [
                'label' => 'Unread messages',
                'value' => $count,
                'url' => url('/admin/contact-messages'),
                'tone' => $count > 0 ? 'indigo' : 'gray',
            ];
        }

        if ($user->canAccessCommunicationFeature('tickets')) {
            $count = TicketReport::query()->where('status', 'open')->count();
            $stats[] = [
                'label' => 'Open tickets',
                'value' => $count,
                'url' => url('/admin/tickets/open'),
                'tone' => $count > 0 ? 'red' : 'gray',
            ];
        }

        if ($user->canAccessStudentFeature('time_requests')) {
            $count = DtrTimeRequest::query()
                ->where('status', 'pending')
                ->whereHas('user', fn ($q) => $q->where('role', 'student'))
                ->count();

            $stats[] = [
                'label' => 'Pending student time requests',
                'value' => $count,
                'url' => url('/admin/time-requests'),
                'tone' => $count > 0 ? 'amber' : 'gray',
            ];
        }

        return $stats;
    }
}
