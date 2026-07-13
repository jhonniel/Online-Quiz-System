<?php

namespace App\Support;

use App\Models\AdminPermission;
use App\Models\User;

final class AdminFeatureNavLinks
{
    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    public static function sectionsFor(User $user): array
    {
        $sections = [];

        foreach (self::contentLinks($user) as $links) {
            $sections[] = $links;
        }

        if ($user->canAccessConfession()) {
            $sections[] = [
                'area' => 'Confession (Say-it)',
                'links' => [
                    ['label' => 'Contents', 'url' => url('/admin/confession'), 'description' => 'Manage confession posts'],
                    ['label' => 'Dashboard', 'url' => url('/admin/confession/dashboard'), 'description' => 'Confession overview'],
                    ['label' => 'Banned words', 'url' => url('/admin/confession/banned-words'), 'description' => 'Moderation word list'],
                    ['label' => 'Topics', 'url' => url('/admin/confession/topics'), 'description' => 'Confession topics'],
                ],
            ];
        }

        foreach (self::analyticsLinks($user) as $links) {
            $sections[] = $links;
        }

        foreach (self::employeeLinks($user) as $links) {
            $sections[] = $links;
        }

        foreach (self::studentLinks($user) as $links) {
            $sections[] = $links;
        }

        foreach (self::userManagementLinks($user) as $links) {
            $sections[] = $links;
        }

        foreach (self::hiringLinks($user) as $links) {
            $sections[] = $links;
        }

        foreach (self::communicationLinks($user) as $links) {
            $sections[] = $links;
        }

        foreach (self::subscriptionLinks($user) as $links) {
            $sections[] = $links;
        }

        if ($user->canAccessFiles()) {
            $sections[] = [
                'area' => 'File Storage',
                'links' => [
                    ['label' => 'Files', 'url' => url('/admin/files'), 'description' => 'Admin file storage'],
                ],
            ];
        }

        foreach (self::systemLinks($user) as $links) {
            $sections[] = $links;
        }

        if ($user->canAccessTasks()) {
            $taskLinks = [];
            if ($user->canAccessTaskFeature('task_dashboard')) {
                $taskLinks[] = ['label' => 'Task Dashboard', 'url' => url('/admin/tasks/dashboard'), 'description' => 'Task analytics overview'];
            }
            if ($user->canAccessTaskFeature('personal_tasks')) {
                $taskLinks[] = ['label' => 'My Tasks', 'url' => url('/admin/tasks?type=personal'), 'description' => 'Personal task board'];
            }
            if ($user->canAccessTaskFeature('group_tasks')) {
                $taskLinks[] = ['label' => 'Group Tasks', 'url' => url('/admin/tasks?type=group'), 'description' => 'Shared team tasks'];
            }
            if ($taskLinks !== []) {
                $sections[] = [
                    'area' => 'Task To Do',
                    'links' => $taskLinks,
                ];
            }
        }

        return array_values(array_filter($sections, fn (array $section): bool => ($section['links'] ?? []) !== []));
    }

    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    private static function contentLinks(User $user): array
    {
        if (! $user->canAccessAnyAdminSubFeature('content_management')) {
            return [];
        }

        $map = [
            'quizzes' => [url('/admin/quizzes'), 'Create and manage quizzes'],
            'manual_grading' => [url('/admin/manual-grading'), 'Grade open-ended quiz answers'],
            'forum' => [url('/admin/forum'), 'Forum moderation and posts'],
            'news' => [url('/admin/news'), 'News articles'],
            'evaluations' => [url('/admin/evaluations'), 'Evaluation questions'],
            'announcements' => [route('admin.system-announcements.index'), 'System announcements'],
        ];

        $links = self::buildLinks($user, 'canAccessContentFeature', AdminPermissionAreas::CONTENT_FEATURES, $map);

        return $links === [] ? [] : [['area' => 'Content Management', 'links' => $links]];
    }

    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    private static function analyticsLinks(User $user): array
    {
        if (! $user->canAccessAnyAnalyticsFeature()) {
            return [];
        }

        $map = [
            'analytics' => [url('/admin/analytics'), 'Analytics overview'],
            'error_logs' => [url('/admin/analytics/error-logs'), 'Application error logs'],
            'user_activity' => [url('/admin/user-activity'), 'User activity tracking'],
            'students_review' => [url('/admin/analytics/students-review'), 'Student review analytics'],
            'employee_records' => [url('/admin/employee-records'), 'Leave credits, overtime, and offset tracking'],
        ];

        $links = [];
        foreach ($map as $feature => [$url, $description]) {
            if ($user->canAccessAnalyticsFeature($feature)) {
                $links[] = [
                    'label' => AdminPermission::ANALYTICS_FEATURES[$feature] ?? ucfirst(str_replace('_', ' ', $feature)),
                    'url' => $url,
                    'description' => $description,
                ];
            }
        }

        if ($user->canAccessEmployeeFeature('kpi_dashboard')) {
            $links[] = [
                'label' => 'KPI Dashboard',
                'url' => url('/admin/kpi/dashboard'),
                'description' => 'Employee KPI metrics',
            ];
        }

        return $links === [] ? [] : [['area' => 'Analytics & Reports', 'links' => $links]];
    }

    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    private static function employeeLinks(User $user): array
    {
        if (! $user->canAccessAnyAdminSubFeature('employee_management')) {
            return [];
        }

        $map = [
            'employee_dashboard' => [url('/admin/employee-dashboard'), 'Employee overview'],
            'file_request' => [url('/admin/file-request'), 'Employee file requests'],
            'payslip' => [url('/admin/payslip'), 'Employee payslips'],
            'employee_nda' => [url('/admin/employee-documents/nda'), 'Employee NDA documents'],
            'employee_contract' => [url('/admin/employee-documents/contract'), 'Employment agreements'],
            'employee_policy' => [url('/admin/employee-documents/policy'), 'Company policy documents'],
            'employee_handbook' => [url('/admin/employee-documents/handbook'), 'Employee handbook'],
            'dtr' => [url('/admin/dtr'), 'Daily time records'],
            'time_report' => [url('/admin/time-report'), 'Time adjustment reports'],
            'leave_requests' => [url('/admin/leave-requests'), 'Employee leave requests'],
            'leave_calendar' => [url('/admin/leave-calendar'), 'Employee leave calendar'],
        ];

        $links = self::buildLinks($user, 'canAccessEmployeeFeature', AdminPermissionAreas::EMPLOYEE_FEATURES, $map);

        if ($user->canAccessEmployeeFeature('employee_signatures')) {
            $links[] = [
                'label' => 'E-Signatures',
                'url' => url('/admin/employee-documents/signatures'),
                'description' => 'Employee e-signature files',
            ];
        }

        return $links === [] ? [] : [['area' => 'Employee Management', 'links' => $links]];
    }

    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    private static function studentLinks(User $user): array
    {
        if (! $user->canAccessAnyAdminSubFeature('student_management')) {
            return [];
        }

        $map = [
            'students' => [url('/admin/student-management/students'), 'Student roster'],
            'student_dashboard' => [url('/admin/student-management/dashboard'), 'Student time dashboard'],
            'student_dtr' => [url('/admin/student-dtr'), 'Student DTR records'],
            'student_leave_requests' => [url('/admin/student-leave-requests'), 'Student leave requests'],
            'student_leave_calendar' => [url('/admin/student-leave-calendar'), 'Student leave calendar'],
            'student_nda_files' => [url('/admin/student-management/nda-files'), 'Student NDA files'],
            'time_requests' => [url('/admin/time-requests'), 'Student time requests'],
        ];

        $links = self::buildLinks($user, 'canAccessStudentFeature', AdminPermissionAreas::STUDENT_FEATURES, $map);

        return $links === [] ? [] : [['area' => 'Student Management', 'links' => $links]];
    }

    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    private static function userManagementLinks(User $user): array
    {
        if (! $user->canAccessAnyAdminSubFeature('user_management')) {
            return [];
        }

        $map = [
            'users' => [url('/admin/users'), 'User accounts'],
            'teachers' => [url('/admin/teachers-management/teachers'), 'Teacher accounts'],
            'teacher_invites' => [url('/admin/teachers-management/invite-links'), 'Teacher invite links'],
            'teacher_moa' => [url('/admin/teachers-management/moa'), 'Teacher MOA uploads'],
            'teacher_excused' => [url('/admin/teachers-management/teacher-excused-requests'), 'Teacher excused requests'],
            'universities' => [url('/admin/universities'), 'Universities'],
            'departments' => [url('/admin/departments'), 'Departments'],
            'user_maps' => [url('/admin/user-maps'), 'User location maps'],
        ];

        $links = self::buildLinks($user, 'canAccessUserManagementFeature', AdminPermissionAreas::USER_MANAGEMENT_FEATURES, $map);

        return $links === [] ? [] : [['area' => 'User Management', 'links' => $links]];
    }

    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    private static function hiringLinks(User $user): array
    {
        if (! $user->canAccessAnyAdminSubFeature('hiring_process')) {
            return [];
        }

        $map = [
            'overview' => [url('/admin/hiring-process'), 'Hiring overview'],
            'positions' => [url('/admin/hiring-positions'), 'Open positions'],
            'applications' => [url('/admin/hiring-applications'), 'Applications inbox'],
            'hired_applicants' => [url('/admin/hiring-process/applicants?view=hired').'#hired-applicants', 'Hired applicants'],
            'interview_calendar' => [url('/admin/hiring-applications/calendar'), 'Interview calendar'],
        ];

        $links = self::buildLinks($user, 'canAccessHiringFeature', AdminPermissionAreas::HIRING_FEATURES, $map);

        return $links === [] ? [] : [['area' => 'Hiring Process', 'links' => $links]];
    }

    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    private static function communicationLinks(User $user): array
    {
        if (! $user->canAccessAnyAdminSubFeature('communication')) {
            return [];
        }

        $map = [
            'contact_messages' => [url('/admin/contact-messages'), 'Contact form messages'],
            'live_chat' => [url('/admin/live-chat'), 'Live chat inbox'],
            'tickets' => [url('/admin/tickets'), 'Support tickets'],
            'feedback' => [url('/admin/feedback'), 'User feedback'],
        ];

        $links = self::buildLinks($user, 'canAccessCommunicationFeature', AdminPermissionAreas::COMMUNICATION_FEATURES, $map);

        return $links === [] ? [] : [['area' => 'Communication', 'links' => $links]];
    }

    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    private static function subscriptionLinks(User $user): array
    {
        if (! $user->hasAdminPermission('linked_accounts') && ! $user->hasAdminPermission('billing')) {
            return [];
        }

        $links = [];

        if ($user->hasAdminPermission('linked_accounts')) {
            if ($user->canAccessSubscriptionFeature('dashboard')) {
                $links[] = ['label' => 'Subscriptions Dashboard', 'url' => url('/admin/linked-accounts'), 'description' => 'Starlinks accounts overview'];
            }
            if ($user->canAccessSubscriptionFeature('starlinks')) {
                $links[] = ['label' => 'Starlinks', 'url' => url('/admin/starlinks'), 'description' => 'Starlink accounts'];
            }
            if ($user->canAccessSubscriptionFeature('omadas')) {
                $links[] = ['label' => 'Omada', 'url' => url('/admin/omadas'), 'description' => 'Omada controllers'];
            }
            if ($user->canAccessSubscriptionFeature('plan_types')) {
                $links[] = ['label' => 'Plan Types', 'url' => url('/admin/subscription-plan-types'), 'description' => 'Subscription plan types'];
            }
        }

        if ($user->hasAdminPermission('billing') && $user->canAccessSubscriptionFeature('billing')) {
            $links[] = ['label' => 'Billing', 'url' => url('/admin/billing'), 'description' => 'Billing and statements'];
        }

        return $links === [] ? [] : [['area' => 'Starlinks Accounts', 'links' => $links]];
    }

    /**
     * @return list<array{area: string, links: list<array{label: string, url: string, description: string}>}>
     */
    private static function systemLinks(User $user): array
    {
        if (! $user->canAccessAnyAdminSubFeature('system')) {
            return [];
        }

        $map = [
            'calendar' => [url('/admin/system/calendar'), 'Holiday calendar'],
            'rules' => [url('/admin/system/rules'), 'Rules and regulations'],
            'settings' => [url('/admin/settings'), 'System settings'],
            'landing_page' => [url('/admin/landing-page'), 'Landing page content'],
            'stacks' => [url('/admin/stacks'), 'Technology stacks'],
            'api_monitoring' => [url('/admin/system/api-monitoring'), 'API monitoring'],
            'network_graph' => [url('/admin/system/network-graph'), 'Network graph'],
            'admin_permissions' => [url('/admin/admin-permissions'), 'Admin permissions'],
        ];

        $links = self::buildLinks($user, 'canAccessSystemFeature', AdminPermissionAreas::SYSTEM_FEATURES, $map);

        return $links === [] ? [] : [['area' => 'System', 'links' => $links]];
    }

    /**
     * @param  array<string, string>  $featureLabels
     * @param  array<string, array{0: string, 1: string}>  $urlMap
     * @return list<array{label: string, url: string, description: string}>
     */
    private static function buildLinks(User $user, string $checker, array $featureLabels, array $urlMap): array
    {
        $links = [];

        foreach ($featureLabels as $feature => $label) {
            if (! $user->{$checker}($feature)) {
                continue;
            }

            [$url, $description] = $urlMap[$feature] ?? [url('/admin'), $label];

            $links[] = [
                'label' => $label,
                'url' => $url,
                'description' => $description,
            ];
        }

        return $links;
    }
}
