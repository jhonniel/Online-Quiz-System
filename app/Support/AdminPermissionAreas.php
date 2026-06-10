<?php

namespace App\Support;

/**
 * Admin permission areas and sidebar sub-features (empty allowed list = all features in that area).
 */
final class AdminPermissionAreas
{
    public const CONTENT_FEATURES = [
        'quizzes' => 'Quizzes',
        'manual_grading' => 'Manual Grading',
        'forum' => 'Forum',
        'news' => 'News',
        'evaluations' => 'Evaluation Questions',
        'announcements' => 'Announcement',
    ];

    public const EMPLOYEE_FEATURES = [
        'employee_dashboard' => 'Employee Dashboard',
        'file_request' => 'File Request',
        'payslip' => 'Payslip',
        'employee_nda' => 'NDA',
        'employee_contract' => 'Agreement',
        'employee_policy' => 'Policy',
        'employee_handbook' => 'Hand Book',
        'dtr' => 'DTR (Time Records)',
        'time_report' => 'Time Report',
        'leave_requests' => 'Leave Requests',
        'leave_calendar' => 'Leave Calendar',
    ];

    /** @var array<string, list<string>> */
    public const EMPLOYEE_FEATURE_GROUPS = [
        'Dashboard' => ['employee_dashboard'],
        'Employee Documents' => ['file_request', 'payslip', 'employee_nda', 'employee_contract', 'employee_policy', 'employee_handbook'],
        'Time & Attendance' => ['dtr', 'time_report'],
        'Leave' => ['leave_requests', 'leave_calendar'],
    ];

    public const STUDENT_FEATURES = [
        'students' => 'Students',
        'student_dashboard' => 'Student Time Dashboard',
        'student_dtr' => 'Student DTR',
        'student_leave_requests' => 'Student Leave Requests',
        'student_leave_calendar' => 'Student Leave Calendar',
        'student_nda_files' => 'NDA Files',
        'time_requests' => 'Time Requests',
    ];

    public const HIRING_FEATURES = [
        'overview' => 'Hiring Process',
        'positions' => 'Positions',
        'applications' => 'Applications',
        'hired_applicants' => 'Hired Applicants',
        'interview_calendar' => 'Calendar Interview',
    ];

    public const COMMUNICATION_FEATURES = [
        'contact_messages' => 'Messages',
        'live_chat' => 'Live Chat',
        'tickets' => 'Tickets',
        'feedback' => 'Feedback',
    ];

    public const SUBSCRIPTION_FEATURES = [
        'dashboard' => 'Subscriptions Dashboard',
        'starlinks' => 'Starlinks',
        'omadas' => 'Omada',
        'billing' => 'Billing',
        'plan_types' => 'Plan Types',
    ];

    public const USER_MANAGEMENT_FEATURES = [
        'users' => 'Users',
        'teachers' => 'Teachers',
        'teacher_invites' => 'Teacher Invite Links',
        'teacher_moa' => 'Teacher MOA',
        'teacher_excused' => 'Teacher Excused Requests',
        'universities' => 'Universities',
        'departments' => 'Departments',
    ];

    public const SYSTEM_FEATURES = [
        'calendar' => 'Calendar',
        'rules' => 'Rules',
        'settings' => 'Settings',
        'landing_page' => 'Landing Page',
        'stacks' => 'Stacks',
        'api_monitoring' => 'API Monitoring',
        'network_graph' => 'Network Graph',
        'admin_permissions' => 'Admin Permissions',
    ];

    /** @return array<string, array{label: string, parent_flag: string, column: string, features: array<string, string>, subscription_parent?: bool}> */
    public static function areas(): array
    {
        return [
            'content_management' => [
                'label' => 'Content Management',
                'parent_flag' => 'content_management',
                'column' => 'allowed_content_features',
                'features' => self::CONTENT_FEATURES,
            ],
            'employee_management' => [
                'label' => 'Employee Management',
                'parent_flag' => 'employee_management',
                'column' => 'allowed_employee_features',
                'features' => self::EMPLOYEE_FEATURES,
            ],
            'student_management' => [
                'label' => 'Student Management',
                'parent_flag' => 'student_management',
                'column' => 'allowed_student_features',
                'features' => self::STUDENT_FEATURES,
            ],
            'hiring_process' => [
                'label' => 'Hiring Process',
                'parent_flag' => 'hiring_process',
                'column' => 'allowed_hiring_features',
                'features' => self::HIRING_FEATURES,
            ],
            'communication' => [
                'label' => 'Communication',
                'parent_flag' => 'communication',
                'column' => 'allowed_communication_features',
                'features' => self::COMMUNICATION_FEATURES,
            ],
            'subscriptions' => [
                'label' => 'Subscriptions',
                'parent_flag' => 'subscriptions',
                'column' => 'allowed_subscription_features',
                'features' => self::SUBSCRIPTION_FEATURES,
                'subscription_parent' => true,
            ],
            'user_management' => [
                'label' => 'User Management',
                'parent_flag' => 'user_management',
                'column' => 'allowed_user_management_features',
                'features' => self::USER_MANAGEMENT_FEATURES,
            ],
            'system' => [
                'label' => 'System',
                'parent_flag' => 'system',
                'column' => 'allowed_system_features',
                'features' => self::SYSTEM_FEATURES,
            ],
        ];
    }

    public static function area(string $key): ?array
    {
        return self::areas()[$key] ?? null;
    }

    public const EMPLOYEE_DOCUMENT_FEATURES = [
        'file_request',
        'payslip',
        'employee_nda',
        'employee_contract',
        'employee_policy',
        'employee_handbook',
    ];

    public static function featureKeys(string $areaKey): array
    {
        $area = self::area($areaKey);

        return $area ? array_keys($area['features']) : [];
    }

    /**
     * Optional grouped layout for sub-feature checkboxes in Admin Permissions.
     *
     * @return array<string, list<string>>|null
     */
    public static function featureGroups(string $areaKey): ?array
    {
        return match ($areaKey) {
            'employee_management' => self::EMPLOYEE_FEATURE_GROUPS,
            default => null,
        };
    }

    public static function requestInputName(string $areaKey): string
    {
        $area = self::area($areaKey);

        return $area ? $area['column'] : '';
    }
}
