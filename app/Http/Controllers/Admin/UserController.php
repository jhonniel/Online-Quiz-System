<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StudentRulesNoticeMail;
use App\Mail\UserCredentials;
use App\Models\Department;
use App\Support\StudentMeritNoticeSettings;
use App\Support\StudentMeritRulesNotice;
use App\Support\StudentViolationCounter;
use App\Models\LeaveBalance;
use App\Models\University;
use App\Models\User;
use App\Services\MailConfigService;
use App\Support\DocumentExportPdfBranding;
use App\Support\EmployeeProfileCsvImporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function teachersManagement(Request $request)
    {
        $request->merge(['role' => 'teacher']);

        return $this->index($request);
    }

    public function teachersManagementExportPdf(Request $request)
    {
        $request->merge(['role' => 'teacher']);

        return $this->exportPdf($request);
    }

    public function index(Request $request)
    {
        $context = $this->usersIndexContext($request);
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $users = $this->filteredUsersQuery($request, $context)
            ->paginate($perPage)
            ->appends($request->query());

        return view('admin.users.index', [
            'users' => $users,
            'search' => $context['search'],
            'perPage' => $perPage,
            'schools' => $context['schools'],
            'schoolId' => $context['schoolId'],
            'roleFilter' => $context['roleFilter'],
            'departmentFilter' => $context['departmentFilter'],
            'departments' => $context['departments'],
            'isTeachersManagement' => $context['isTeachersManagement'],
        ]);
    }

    public function exportPdf(Request $request)
    {
        $context = $this->usersIndexContext($request);

        $userIds = collect((array) $request->input('user_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($userIds !== []) {
            $request->validate([
                'user_ids' => ['required', 'array', 'min:1'],
                'user_ids.*' => ['integer', 'exists:users,id'],
            ]);

            $isTeachersManagement = $context['isTeachersManagement'];
            $with = $isTeachersManagement ? ['university'] : ['university', 'department'];

            $users = User::query()
                ->with($with)
                ->whereIn('id', $userIds)
                ->when($isTeachersManagement, fn (Builder $q) => $q->where('role', 'teacher'))
                ->orderBy('name')
                ->get();
        } else {
            $users = $this->filteredUsersQuery($request, $context)->get();
        }

        $exportMeta = $this->usersExportMeta($context, $userIds !== [] ? count($userIds) : null);
        $branding = DocumentExportPdfBranding::forPdf();

        $pdf = Pdf::loadView('admin.users.export-pdf', [
            'users' => $users,
            'exportMeta' => $exportMeta,
            'branding' => $branding,
            'isTeachersManagement' => $context['isTeachersManagement'],
        ])->setPaper('a4', 'landscape');

        $prefix = $context['isTeachersManagement'] ? 'teachers' : 'users';
        $filename = $prefix.'_export_'.now()->format('Y-m-d_His').'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * @return array{
     *     isTeachersManagement: bool,
     *     search: string,
     *     schoolId: mixed,
     *     roleFilter: string,
     *     departmentFilter: string,
     *     schools: \Illuminate\Support\Collection<int, University>,
     *     departments: \Illuminate\Support\Collection<int, Department>
     * }
     */
    private function usersIndexContext(Request $request): array
    {
        $isTeachersManagement = $request->routeIs('admin.teachers-management.*');
        $schoolId = $request->input('school');

        return [
            'isTeachersManagement' => $isTeachersManagement,
            'search' => trim((string) $request->input('search', '')),
            'schoolId' => $schoolId,
            'roleFilter' => $isTeachersManagement ? 'teacher' : trim((string) $request->input('role', '')),
            'departmentFilter' => $isTeachersManagement ? '' : trim((string) $request->input('department', '')),
            'schools' => University::active()->orderBy('name')->get(),
            'departments' => $isTeachersManagement
                ? collect()
                : Department::active()->orderBy('name')->get(),
        ];
    }

    /**
     * @param  array{
     *     isTeachersManagement: bool,
     *     search: string,
     *     schoolId: mixed,
     *     roleFilter: string,
     *     departmentFilter: string
     * }  $context
     */
    private function filteredUsersQuery(Request $request, array $context): Builder
    {
        $search = $context['search'];
        $schoolId = $context['schoolId'];
        $roleFilter = $context['roleFilter'];
        $departmentFilter = $context['departmentFilter'];
        $isTeachersManagement = $context['isTeachersManagement'];
        $dbDriver = DB::connection()->getDriverName();
        $idLikeSql = $dbDriver === 'pgsql' ? 'CAST(id AS TEXT) LIKE ?' : 'CAST(id AS CHAR) LIKE ?';
        $with = $isTeachersManagement ? ['university'] : ['university', 'department'];

        $query = User::query()
            ->with($with)
            ->orderBy('is_approved', 'asc')
            ->orderBy('created_at', 'desc');

        if ($schoolId !== null && $schoolId !== '') {
            $query->where('university_id', (int) $schoolId);
        }

        if ($roleFilter !== '') {
            $query->where('role', $roleFilter);
        }

        if ($roleFilter === 'employee' && $departmentFilter !== '') {
            if ($departmentFilter === 'unassigned') {
                $query->whereNull('department_id');
            } else {
                $query->where('department_id', (int) $departmentFilter);
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search, $idLikeSql, $isTeachersManagement) {
                $q->orWhereRaw($idLikeSql, ["%{$search}%"])
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");

                if (! $isTeachersManagement) {
                    $q->orWhereHas('department', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
                }

                $q->orWhereHas('university', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            });
        }

        return $query;
    }

    /**
     * @param  array{
     *     isTeachersManagement: bool,
     *     search: string,
     *     schoolId: mixed,
     *     roleFilter: string,
     *     departmentFilter: string,
     *     schools: \Illuminate\Support\Collection<int, University>,
     *     departments: \Illuminate\Support\Collection<int, Department>
     * }  $context
     * @return array<string, string|null>
     */
    private function usersExportMeta(array $context, ?int $selectedCount = null): array
    {
        $schoolName = null;
        if ($context['schoolId'] !== null && $context['schoolId'] !== '') {
            $schoolName = University::query()->whereKey($context['schoolId'])->value('name');
        }

        $roleLabel = null;
        if ($context['roleFilter'] !== '') {
            $roleLabel = (new User(['role' => $context['roleFilter']]))->getRoleLabel();
        }

        $departmentLabel = null;
        if ($context['roleFilter'] === 'employee' && $context['departmentFilter'] !== '') {
            if ($context['departmentFilter'] === 'unassigned') {
                $departmentLabel = 'Unassigned';
            } else {
                $departmentLabel = Department::query()->whereKey($context['departmentFilter'])->value('name');
            }
        }

        return [
            'title' => $context['isTeachersManagement'] ? 'Teachers List' : 'Users List',
            'search' => $context['search'] !== '' ? $context['search'] : null,
            'school' => $schoolName,
            'role' => $roleLabel,
            'department' => $departmentLabel,
            'selected_count' => $selectedCount,
        ];
    }

    public function api(Request $request)
    {
        $users = User::where('is_active', true)
            ->where('role', '!=', 'admin') // Exclude admins from assignment
            ->with(['university', 'department'])
            ->select('id', 'name', 'email', 'university_id', 'department_id', 'role')
            ->get();

        // If quiz_id is provided, include assignment information
        if ($request->has('quiz_id')) {
            $quizId = $request->quiz_id;
            $assignedUserIds = \App\Models\QuizAssignment::where('quiz_id', $quizId)
                ->pluck('user_id')
                ->toArray();

            $users = $users->map(function ($user) use ($assignedUserIds) {
                $user->is_assigned = in_array($user->id, $assignedUserIds);

                return $user;
            });
        }

        return response()->json(['users' => $users]);
    }

    public function create()
    {
        $universities = University::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();

        return view('admin.users.create', compact('universities', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,user,student,employee,teacher,applicant,technician',
            'university_id' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return $request->role === 'teacher';
                }),
            ],
            'new_university_name' => 'nullable|string|max:255',
            'department_id' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return $request->role === 'employee';
                }),
                'exists:departments,id',
            ],
            'is_active' => 'boolean',
            'required_training_hours' => 'nullable|numeric|min:0',
            'ojt_target_end_date' => 'nullable|date',
            'student_absence_allowance' => 'nullable|numeric|min:0|max:365',
            'leave_allowance' => 'nullable|numeric|min:0|max:365',
            'vacation_allowance' => 'nullable|numeric|min:0|max:365',
            'sick_allowance' => 'nullable|numeric|min:0|max:365',
        ]);

        // Custom validation for new university
        if ($request->university_id === 'new' && ! $request->filled('new_university_name')) {
            return back()->withErrors(['new_university_name' => 'Please enter a university name when adding a new university.'])->withInput();
        }

        // Handle university assignment
        $universityId = null;

        if ($request->university_id === 'new' && $request->filled('new_university_name')) {
            // Create new university
            $university = University::create([
                'name' => $request->new_university_name,
                'is_active' => true,
            ]);
            $universityId = $university->id;
        } elseif ($request->university_id && $request->university_id !== 'new') {
            // Use existing university
            $universityId = $request->university_id;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'university_id' => $universityId,
            'department_id' => in_array($request->role, ['employee', 'student'], true) ? $request->department_id : null,
            'is_active' => $request->has('is_active'),
            'required_training_hours' => $request->required_training_hours,
            'ojt_target_end_date' => $request->role === 'student' && $request->filled('ojt_target_end_date')
                ? $request->ojt_target_end_date
                : null,
            'student_absence_allowance' => $request->role === 'student'
                ? User::normalizedStudentAbsenceAllowance($request->input('student_absence_allowance'))
                : 0,
        ]);

        // Handle leave balances for employees
        if ($request->role === 'employee' && ($request->filled('leave_allowance') || $request->filled('vacation_allowance') || $request->filled('sick_allowance'))) {
            $currentYear = now()->year;
            $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 0);
            $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 0);
            $combinedDefault = $defaultVacation + $defaultSick;
            $combined = $request->filled('leave_allowance') ? (float) $request->leave_allowance : null;

            LeaveBalance::create([
                'user_id' => $user->id,
                'year' => $currentYear,
                'vacation_allowance' => $combined !== null ? $combined : ($request->filled('vacation_allowance') ? (float) $request->vacation_allowance : $defaultVacation),
                'sick_allowance' => $combined !== null ? 0 : ($request->filled('sick_allowance') ? (float) $request->sick_allowance : $defaultSick),
            ]);
        }

        return redirect('/admin/users')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        // Access is controlled by admin.permission:user_management middleware,
        // but we also guard here for safety to ensure user has user_management permission.
        if (! auth()->check() || ! auth()->user()->canAccessUserManagement()) {
            abort(403, 'You do not have permission to view user profiles.');
        }

        $balances = null;
        $overtimeFormatted = null;
        $overtimeWindowLabel = null;
        $totalDeficitFormatted = null;
        $totalDeficitHours = 0;

        if ($user->role === 'employee') {
            $currentYear = now()->year;
            $months = $user->overtime_months_credited ?? 12;

            if ($months === 12) {
                $fromDate = now()->copy()->startOfYear();
                $overtimeWindowLabel = 'Current Year';
            } else {
                $fromDate = now()->copy()->subMonths($months)->startOfDay();
                $overtimeWindowLabel = "Last {$months} month(s)";
            }

            $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 15);
            $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 10);

            $leaveBalance = \App\Models\LeaveBalance::firstOrCreateWithCarryover(
                (int) $user->id,
                (int) $currentYear,
                (float) $defaultVacation,
                (float) $defaultSick
            );

            $usedVacation = \App\Models\LeaveRequest::where('user_id', $user->id)
                ->where('type', 'vacation_leave')
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum->days;

            $usedSick = \App\Models\LeaveRequest::where('user_id', $user->id)
                ->where('type', 'sick_leave')
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum->days;

            $usedLeave = \App\Models\LeaveRequest::where('user_id', $user->id)
                ->whereIn('type', ['leave', 'vacation_leave', 'sick_leave'])
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get()
                ->sum->days;

            $combinedAllowance = (float) $leaveBalance->vacation_allowance + (float) $leaveBalance->sick_allowance;

            $balances = [
                'vacation' => [
                    'allowance' => (float) $leaveBalance->vacation_allowance,
                    'used' => $usedVacation,
                    'remaining' => max((float) $leaveBalance->vacation_allowance - $usedVacation, 0),
                ],
                'sick' => [
                    'allowance' => (float) $leaveBalance->sick_allowance,
                    'used' => $usedSick,
                    'remaining' => max((float) $leaveBalance->sick_allowance - $usedSick, 0),
                ],
                'leave' => [
                    'allowance' => $combinedAllowance,
                    'used' => $usedLeave,
                    'remaining' => max($combinedAllowance - $usedLeave, 0),
                ],
            ];

            // Overtime balance is ONLY based on approved overtime leave requests (DTR overtime is ignored)
            // Overtime Credited Window is ONLY used for expiration logic, NOT for counting
            $today = Carbon::today();
            $totalOvertimeHours = 0;

            // Get approved overtime leave requests for completed weeks only (count all, window only for expiration)
            $approvedOvertimeRequests = \App\Models\LeaveRequest::where('user_id', $user->id)
                ->where('type', 'overtime')
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today) // completed weeks only
                ->get();

            $overtimeFromLeavesMinutes = 0;
            foreach ($approvedOvertimeRequests as $otRequest) {
                $raw = $otRequest->reason ?? '';
                if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                    [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                    $overtimeFromLeavesMinutes += $h * 60 + $mPart;
                }
            }

            $totalOvertimeHours = $overtimeFromLeavesMinutes / 60;

            // Build set of weeks where overtime was earned (only from approved overtime leave requests)
            $overtimeWeekKeys = [];
            foreach ($approvedOvertimeRequests as $otRequest) {
                $weekStart = $otRequest->start_date->copy()->startOfWeek()->toDateString();
                $overtimeWeekKeys[$weekStart] = true;
            }

            // Get deficit hours for completed weeks starting from the user's first DTR week (for display only)
            $firstDtr = \App\Models\Dtr::where('user_id', $user->id)->orderBy('date', 'asc')->first();
            if ($firstDtr) {
                $firstWeekStart = $firstDtr->date->copy()->startOfWeek()->toDateString();
                $totalDeficitHours = \App\Models\DtrDeficit::where('user_id', $user->id)
                    ->where('is_applied', true)
                    ->where('week_end_date', '<', $today->toDateString()) // Only completed weeks
                    ->where('week_start_date', '>=', $firstWeekStart)
                    ->sum('deficit_hours');
            } else {
                $totalDeficitHours = 0;
            }

            // Format total deficit hours for display
            $totalDeficitMinutes = (int) round($totalDeficitHours * 60);
            $deficitHoursPart = intdiv($totalDeficitMinutes, 60);
            $deficitMinutesPart = $totalDeficitMinutes % 60;
            $totalDeficitFormatted = sprintf('%02d:%02d', $deficitHoursPart, $deficitMinutesPart);

            // Overtime balance is ONLY the total of approved overtime requests (no deductions)
            // Format overtime balance
            $absOvertimeMinutes = (int) round(abs($totalOvertimeHours) * 60);
            $overtimeHoursPart = intdiv($absOvertimeMinutes, 60);
            $overtimeMinutesPart = $absOvertimeMinutes % 60;
            $overtimeFormatted = sprintf('%02d:%02d', $overtimeHoursPart, $overtimeMinutesPart);
        }

        return view('admin.users.show', compact('user', 'balances', 'overtimeFormatted', 'overtimeWindowLabel', 'totalDeficitFormatted', 'totalDeficitHours'));
    }

    public function updateOvertimeWindow(Request $request, User $user)
    {
        $request->validate([
            'overtime_months_credited' => 'required|integer|in:12,9,6,3,1',
        ]);

        // Apply setting globally to all employees
        User::where('role', 'employee')->update([
            'overtime_months_credited' => $request->overtime_months_credited,
        ]);

        return redirect('/admin/users/'.$user->id)
            ->with('success', 'Overtime credited window updated for all employees.');
    }

    public function updateLeaveBalance(Request $request, User $user)
    {
        $request->validate([
            'leave_allowance' => 'required_without_all:vacation_allowance,sick_allowance|nullable|numeric|min:0|max:365',
            'vacation_allowance' => 'required_without:leave_allowance|nullable|numeric|min:0|max:365',
            'sick_allowance' => 'required_without:leave_allowance|nullable|numeric|min:0|max:365',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year = (int) $request->year;

        $leaveBalance = LeaveBalance::firstOrCreate(
            ['user_id' => $user->id, 'year' => $year],
            [
                'vacation_allowance' => 0,
                'sick_allowance' => 0,
            ]
        );

        if ($request->filled('leave_allowance')) {
            $leaveBalance->update([
                'vacation_allowance' => (float) $request->leave_allowance,
                'sick_allowance' => 0,
            ]);
        } else {
            $leaveBalance->update([
                'vacation_allowance' => $request->vacation_allowance,
                'sick_allowance' => $request->sick_allowance,
            ]);
        }

        return redirect('/admin/users/'.$user->id)
            ->with('success', "Leave balances updated for {$year}.");
    }

    public function edit(User $user)
    {
        $universities = University::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();

        $studentMeritBreakdown = $user->role === 'student'
            ? StudentViolationCounter::breakdownForUser((int) $user->id)
            : null;

        $studentMeritNoticeSettings = $user->role === 'student'
            ? StudentMeritNoticeSettings::thresholds()
            : null;

        return view('admin.users.edit', compact(
            'user',
            'universities',
            'departments',
            'studentMeritBreakdown',
            'studentMeritNoticeSettings'
        ));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,user,student,employee,teacher,applicant,technician',
            'university_id' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return $request->role === 'teacher';
                }),
            ],
            'new_university_name' => 'nullable|string|max:255',
            'department_id' => [
                'nullable',
                Rule::requiredIf(function () use ($request) {
                    return $request->role === 'employee';
                }),
                'exists:departments,id',
            ],
            'is_active' => 'boolean',
            'theme_color_enabled' => 'boolean',
            'required_training_hours' => 'nullable|numeric|min:0',
            'ojt_target_end_date' => 'nullable|date',
            'student_absence_allowance' => 'nullable|numeric|min:0|max:365',
            'leave_allowance' => 'nullable|numeric|min:0|max:365',
            'vacation_allowance' => 'nullable|numeric|min:0|max:365',
            'sick_allowance' => 'nullable|numeric|min:0|max:365',
            'student_rules_warning' => 'nullable|boolean',
            'student_rules_marquee_enabled' => 'nullable|boolean',
            'student_rules_notice_message' => [
                'nullable',
                'string',
                'max:5000',
                Rule::requiredIf(function () use ($request) {
                    return $request->role === 'student'
                        && ($request->boolean('student_rules_warning') || $request->boolean('student_rules_marquee_enabled'));
                }),
            ],
            'student_terminated' => 'nullable|boolean',
            'student_manual_merits' => 'nullable|integer|min:0|max:9999',
            'student_rules_allow_merit_automation' => 'nullable|boolean',
            'date_hired' => 'nullable|date',
            'tin' => 'nullable|string|max:50',
            'sss_number' => 'nullable|string|max:50',
            'hdmf_number' => 'nullable|string|max:50',
            'phic_number' => 'nullable|string|max:50',
        ]);

        // Custom validation for new university
        if ($request->university_id === 'new' && ! $request->filled('new_university_name')) {
            return back()->withErrors(['new_university_name' => 'Please enter a university name when adding a new university.'])->withInput();
        }

        if ($request->role === 'student'
            && $request->boolean('student_rules_warning')
            && $request->boolean('student_rules_marquee_enabled')) {
            return back()
                ->withErrors([
                    'student_rules_notices' => 'Rules violation warning and final notice cannot both be enabled. Choose one and save again.',
                ])
                ->withInput();
        }

        // Handle university assignment
        $universityId = null;

        if ($request->university_id === 'new' && $request->filled('new_university_name')) {
            // Create new university
            $university = University::create([
                'name' => $request->new_university_name,
                'is_active' => true,
            ]);
            $universityId = $university->id;
        } elseif ($request->university_id && $request->university_id !== 'new') {
            // Use existing university
            $universityId = $request->university_id;
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'university_id' => $universityId,
            'department_id' => in_array($request->role, ['employee', 'student'], true) ? $request->department_id : null,
            'is_active' => $request->has('is_active'),
            'theme_color_enabled' => $request->boolean('theme_color_enabled'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->filled('required_training_hours')) {
            $data['required_training_hours'] = $request->required_training_hours;
        } else {
            $data['required_training_hours'] = null;
        }

        if ($request->role === 'student') {
            $data['student_rules_warning'] = $request->boolean('student_rules_warning');
            $data['student_rules_marquee_enabled'] = $request->boolean('student_rules_marquee_enabled');
            $noticeMsg = trim((string) ($request->input('student_rules_notice_message') ?? ''));
            $data['student_rules_notice_message'] = $noticeMsg !== '' ? $noticeMsg : null;
            $data['student_terminated'] = $request->boolean('student_terminated');
            $data['student_absence_allowance'] = User::normalizedStudentAbsenceAllowance(
                $request->input('student_absence_allowance')
            );
            $data['ojt_target_end_date'] = $request->filled('ojt_target_end_date')
                ? $request->ojt_target_end_date
                : null;
            if (auth()->user()->isAdmin()) {
                $data['student_manual_merits'] = max(0, (int) $request->input('student_manual_merits', 0));
            }
        } else {
            $data['student_rules_warning'] = false;
            $data['student_rules_warning_manual'] = false;
            $data['student_rules_marquee_enabled'] = false;
            $data['student_rules_marquee_manual'] = false;
            $data['student_rules_merit_automation_disabled'] = false;
            $data['student_rules_notice_message'] = null;
            $data['student_terminated'] = false;
            $data['student_absence_allowance'] = 0;
            $data['student_manual_merits'] = 0;
            $data['ojt_target_end_date'] = null;
        }

        $prevStudentRulesWarning = (bool) ($user->student_rules_warning ?? false);
        $prevStudentRulesMarquee = (bool) ($user->student_rules_marquee_enabled ?? false);

        if ($request->role === 'student') {
            $newWarning = $request->boolean('student_rules_warning');
            if ($newWarning && ! $prevStudentRulesWarning) {
                $data['student_rules_warning_manual'] = true;
            } elseif (! $newWarning) {
                $data['student_rules_warning_manual'] = false;
            }

            $newMarquee = $request->boolean('student_rules_marquee_enabled');
            if ($newMarquee && ! $prevStudentRulesMarquee) {
                $data['student_rules_marquee_manual'] = true;
            } elseif (! $newMarquee) {
                $data['student_rules_marquee_manual'] = false;
            }

            $allowMeritAutomation = $request->boolean('student_rules_allow_merit_automation');
            if (! $newWarning && ! $newMarquee && ! $allowMeritAutomation) {
                $data['student_rules_merit_automation_disabled'] = true;
            } else {
                $data['student_rules_merit_automation_disabled'] = ! $allowMeritAutomation;
            }
        }

        if ($request->role === 'employee') {
            $data['date_hired'] = $request->filled('date_hired') ? $request->date_hired : null;
            $data['tin'] = $this->nullableProfileValue($request->input('tin'));
            $data['sss_number'] = $this->nullableProfileValue($request->input('sss_number'));
            $data['hdmf_number'] = $this->nullableProfileValue($request->input('hdmf_number'));
            $data['phic_number'] = $this->nullableProfileValue($request->input('phic_number'));
        } else {
            $data['date_hired'] = null;
            $data['tin'] = null;
            $data['sss_number'] = null;
            $data['hdmf_number'] = null;
            $data['phic_number'] = null;
        }

        $user->update($data);

        $user->refresh();
        if ($request->role === 'student' && ! StudentMeritRulesNotice::isMeritAutomationLocked($user)) {
            StudentMeritRulesNotice::syncForStudent($user);
        }

        if ($request->role === 'student' && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            $notifyViolation = ! empty($data['student_rules_warning']) && ! $prevStudentRulesWarning;
            $notifyFinal = ! empty($data['student_rules_marquee_enabled']) && ! $prevStudentRulesMarquee;
            if ($notifyViolation || $notifyFinal) {
                $noticeType = $notifyFinal ? 'final' : 'violation';
                try {
                    Mail::to($user->email)->send(new StudentRulesNoticeMail(
                        $user->fresh(),
                        $noticeType,
                        $data['student_rules_notice_message'] ?? null
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Student rules notice email failed: '.$e->getMessage());
                }
            }
        }

        // Handle leave balances for employees
        if ($request->role === 'employee' && ($request->has('leave_allowance') || $request->has('vacation_allowance') || $request->has('sick_allowance'))) {
            $currentYear = now()->year;
            $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 0);
            $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 0);

            $leaveBalance = LeaveBalance::firstOrCreateWithCarryover(
                (int) $user->id,
                (int) $currentYear,
                (float) $defaultVacation,
                (float) $defaultSick
            );

            // Update only if values are provided
            $updateData = [];
            if ($request->has('leave_allowance') && $request->filled('leave_allowance')) {
                $updateData['vacation_allowance'] = (float) $request->leave_allowance;
                $updateData['sick_allowance'] = 0;
            } else {
                if ($request->has('vacation_allowance')) {
                    $updateData['vacation_allowance'] = $request->filled('vacation_allowance') ? (float) $request->vacation_allowance : $defaultVacation;
                }
                if ($request->has('sick_allowance')) {
                    $updateData['sick_allowance'] = $request->filled('sick_allowance') ? (float) $request->sick_allowance : $defaultSick;
                }
            }

            if (! empty($updateData)) {
                $leaveBalance->update($updateData);
            }
        }

        return redirect('/admin/users')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect('/admin/users')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect('/admin/users')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "User {$status} successfully.");
    }

    public function approve(User $user)
    {
        $user->update(['is_approved' => true]);

        return redirect()->back()
            ->with('success', 'User approved successfully.');
    }

    public function disapprove(User $user)
    {
        $user->update(['is_approved' => false]);

        return redirect()->back()
            ->with('success', 'User disapproved successfully.');
    }

    public function bulkAssignRole(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required|string|in:admin,user,student,employee,teacher,applicant,technician',
        ], [
            'user_ids.required' => 'Please select at least one user.',
            'user_ids.array' => 'Invalid user selection format.',
            'user_ids.*.exists' => 'One or more selected users do not exist.',
            'role.required' => 'Please select a role to assign.',
            'role.in' => 'Invalid role selected.',
        ]);

        $userIds = $request->user_ids;
        $role = $request->role;

        // Prevent changing admin roles to non-admin (safety check)
        $adminUsers = User::whereIn('id', $userIds)->where('role', 'admin')->get();
        if ($adminUsers->count() > 0 && $role !== 'admin') {
            $adminNames = $adminUsers->pluck('name')->join(', ');

            return redirect()->back()
                ->with('error', "Cannot change role of administrator user(s): {$adminNames}. Please deselect admin users or keep them as administrators.");
        }

        // Prevent changing current user's role if they're an admin
        if (auth()->check() && in_array(auth()->id(), $userIds) && auth()->user()->isAdmin() && $role !== 'admin') {
            return redirect()->back()
                ->with('error', 'You cannot change your own role from administrator.');
        }

        // Update users (excluding current admin if trying to change their own role)
        $query = User::whereIn('id', $userIds);
        if (auth()->check() && auth()->user()->isAdmin() && $role !== 'admin') {
            $query = $query->where('id', '!=', auth()->id());
        }

        $payload = ['role' => $role];
        if (! in_array($role, ['employee', 'student'], true)) {
            $payload['department_id'] = null;
        }

        $updated = $query->update($payload);

        if ($updated > 0) {
            $roleLabel = match ($role) {
                'admin' => 'Administrator',
                'student' => 'Student',
                'employee' => 'Employee',
                'teacher' => 'Teacher',
                'applicant' => 'Applicant',
                'technician' => 'Technician',
                'user' => 'User',
                default => ucfirst($role),
            };

            return redirect()->back()
                ->with('success', "Successfully assigned role '{$roleLabel}' to {$updated} user(s).");
        } else {
            return redirect()->back()
                ->with('error', 'No users were updated. Please check your selection.');
        }
    }

    public function bulkAssignDepartment(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'department_id' => 'required|exists:departments,id',
        ], [
            'user_ids.required' => 'Please select at least one user.',
            'user_ids.array' => 'Invalid user selection format.',
            'user_ids.*.exists' => 'One or more selected users do not exist.',
            'department_id.required' => 'Please select a department to assign.',
            'department_id.exists' => 'Selected department does not exist.',
        ]);

        $userIds = $request->input('user_ids', []);
        $departmentId = (int) $request->input('department_id');

        // Limit department assignment to Employee and Student roles.
        $query = User::whereIn('id', $userIds)
            ->whereIn('role', ['employee', 'student']);

        $updated = $query->update(['department_id' => $departmentId]);
        $selectedCount = count($userIds);
        $skipped = max($selectedCount - $updated, 0);

        if ($updated <= 0) {
            return redirect()->back()
                ->with('error', 'No eligible users were updated. Only employees and students can be assigned a department.');
        }

        $department = Department::find($departmentId);
        $departmentName = $department ? $department->name : 'selected department';
        $message = "Assigned '{$departmentName}' department to {$updated} user(s).";

        if ($skipped > 0) {
            $message .= " Skipped {$skipped} user(s) because only employee/student roles support department assignment.";
        }

        return redirect()->back()->with('success', $message);
    }

    public function bulkAssignOjtTargetEndDate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'ojt_target_end_date' => 'required|date',
        ], [
            'user_ids.required' => 'Please select at least one user.',
            'user_ids.array' => 'Invalid user selection format.',
            'user_ids.*.exists' => 'One or more selected users do not exist.',
            'ojt_target_end_date.required' => 'Please provide an OJT target end date.',
            'ojt_target_end_date.date' => 'Invalid OJT target end date.',
        ]);

        $userIds = $request->input('user_ids', []);
        $targetDate = (string) $request->input('ojt_target_end_date');

        // Only students support OJT target end date / exit conference.
        $query = User::whereIn('id', $userIds)->where('role', 'student');
        $updated = $query->update(['ojt_target_end_date' => $targetDate]);
        $selectedCount = count($userIds);
        $skipped = max($selectedCount - $updated, 0);

        if ($updated <= 0) {
            return redirect()->back()
                ->with('error', 'No eligible users were updated. Only students can have an OJT target end date.');
        }

        $formattedDate = Carbon::parse($targetDate)->format('M j, Y');
        $message = "Set OJT target end date to {$formattedDate} for {$updated} student user(s).";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} user(s) because only students support this field.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Send credentials email to a single user.
     */
    public function sendCredentials(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        try {
            MailConfigService::configure();

            Mail::to($user->email)->send(
                new UserCredentials($user, $user->email, $request->password)
            );

            return redirect()->back()
                ->with('success', "Credentials email sent successfully to {$user->name}.");
        } catch (\Exception $e) {
            Log::error('Failed to send credentials email', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return redirect()->back()
                ->with('error', 'Failed to send credentials email: '.$e->getMessage());
        }
    }

    /**
     * Send credentials email to multiple users in bulk.
     */
    public function sendBulkCredentials(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'password' => 'required|string|min:8',
        ]);

        $userIds = $request->user_ids;
        $password = $request->password;
        $users = User::whereIn('id', $userIds)->get();

        if ($users->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No users selected.');
        }

        $successCount = 0;
        $failCount = 0;
        $failedUsers = [];

        try {
            MailConfigService::configure();

            foreach ($users as $user) {
                try {
                    Mail::to($user->email)->send(
                        new UserCredentials($user, $user->email, $password)
                    );
                    $successCount++;
                } catch (\Exception $e) {
                    $failCount++;
                    $failedUsers[] = $user->name;
                    Log::error('Failed to send credentials email to user', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                    ]);
                }
            }

            $message = "Credentials email sent to {$successCount} user(s).";
            if ($failCount > 0) {
                $message .= " Failed to send to {$failCount} user(s): ".implode(', ', $failedUsers);
            }

            return redirect()->back()
                ->with($failCount > 0 ? 'warning' : 'success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk credentials emails', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to send credentials emails: '.$e->getMessage());
        }
    }

    public function importEmployeeProfile(Request $request, EmployeeProfileCsvImporter $importer)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        try {
            $result = $importer->import($request->file('csv_file')->getRealPath());
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Failed to import employee profiles: '.$e->getMessage());
        }

        $message = "Updated {$result['updated']} employee profile(s).";
        if ($result['skipped'] > 0) {
            $message .= " Skipped {$result['skipped']}.";
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', $message)
            ->with('import_errors', $result['errors']);
    }

    public function downloadEmployeeProfileTemplate()
    {
        $rows = [
            EmployeeProfileCsvImporter::HEADERS,
            [
                'employee@example.com',
                'JHONNIEL R. YGAY',
                '2023-01-15',
                '123-456-789-000',
                '34-1234567-8',
                '1212-3456-7890',
                '12-345678901-2',
            ],
        ];

        $filename = 'employee_profile_import_template_'.date('Y-m-d').'.csv';
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function nullableProfileValue(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : null;
    }
}
