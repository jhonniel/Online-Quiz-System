<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\University;
use App\Models\LeaveBalance;
use App\Mail\UserCredentials;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('university')
            ->orderBy('is_approved', 'asc') // Show pending users first
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function api(Request $request)
    {
        $users = User::where('is_active', true)
            ->where('role', '!=', 'admin') // Exclude admins from assignment
            ->with('university')
            ->select('id', 'name', 'email', 'university_id', 'role')
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
        return view('admin.users.create', compact('universities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,user,student,employee,applicant',
            'university_id' => 'nullable',
            'new_university_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'required_training_hours' => 'nullable|numeric|min:0',
            'vacation_allowance' => 'nullable|numeric|min:0|max:365',
            'sick_allowance' => 'nullable|numeric|min:0|max:365',
        ]);

        // Custom validation for new university
        if ($request->university_id === 'new' && !$request->filled('new_university_name')) {
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
            'is_active' => $request->has('is_active'),
            'required_training_hours' => $request->required_training_hours,
        ]);

        // Handle leave balances for employees
        if ($request->role === 'employee' && ($request->filled('vacation_allowance') || $request->filled('sick_allowance'))) {
            $currentYear = now()->year;
            $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 0);
            $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 0);

            LeaveBalance::create([
                'user_id' => $user->id,
                'year' => $currentYear,
                'vacation_allowance' => $request->filled('vacation_allowance') ? (float) $request->vacation_allowance : $defaultVacation,
                'sick_allowance' => $request->filled('sick_allowance') ? (float) $request->sick_allowance : $defaultSick,
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        // Only admins should access this profile view via middleware/routes,
        // but we also guard here for safety.
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can view employee profiles.');
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

            $leaveBalance = \App\Models\LeaveBalance::firstOrCreate(
                ['user_id' => $user->id, 'year' => $currentYear],
                [
                    'vacation_allowance' => $defaultVacation,
                    'sick_allowance' => $defaultSick,
                ]
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
            ];

            $overtimeQuery = \App\Models\Dtr::where('user_id', $user->id);
            if ($months === 12) {
                $overtimeQuery->whereYear('date', $currentYear);
            } else {
                $overtimeQuery->whereDate('date', '>=', $fromDate->toDateString());
            }
            $totalOvertimeHours = $overtimeQuery->sum('overtime_hours');

            // Add overtime coming from approved overtime leave requests (HH:MM in reason)
            $approvedOvertimeRequestsQuery = \App\Models\LeaveRequest::where('user_id', $user->id)
                ->where('type', 'overtime')
                ->where('status', 'approved');

            if ($months === 12) {
                $approvedOvertimeRequestsQuery->whereYear('start_date', $currentYear);
            } else {
                $approvedOvertimeRequestsQuery->whereDate('start_date', '>=', $fromDate->toDateString());
            }

            $approvedOvertimeRequests = $approvedOvertimeRequestsQuery->get();

            $overtimeFromLeavesMinutes = 0;
            foreach ($approvedOvertimeRequests as $otRequest) {
                $raw = $otRequest->reason ?? '';
                if (preg_match('/Total Overtime Hours:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                    [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                    $overtimeFromLeavesMinutes += $h * 60 + $mPart;
                }
            }

            $totalOvertimeHours += $overtimeFromLeavesMinutes / 60;

            // Subtract deficit hours from overtime balance (allow negative values)
            $deficitQuery = \App\Models\DtrDeficit::where('user_id', $user->id)
                ->where('is_applied', true);

            if ($months === 12) {
                $deficitQuery->whereYear('week_start_date', $currentYear);
            } else {
                $deficitQuery->whereDate('week_start_date', '>=', $fromDate->toDateString());
            }

            $totalDeficitHours = $deficitQuery->sum('deficit_hours');
            $totalOvertimeHours = $totalOvertimeHours - $totalDeficitHours;

            // Format total deficit hours for display
            $totalDeficitMinutes = (int) round($totalDeficitHours * 60);
            $deficitHoursPart = intdiv($totalDeficitMinutes, 60);
            $deficitMinutesPart = $totalDeficitMinutes % 60;
            $totalDeficitFormatted = sprintf('%02d:%02d', $deficitHoursPart, $deficitMinutesPart);

            $approvedOffsetQuery = \App\Models\LeaveRequest::where('user_id', $user->id)
                ->where('type', 'offset')
                ->where('status', 'approved');

            if ($months === 12) {
                $approvedOffsetQuery->whereYear('start_date', $currentYear);
            } else {
                $approvedOffsetQuery->whereDate('start_date', '>=', $fromDate->toDateString());
            }

            $approvedOffsetRequests = $approvedOffsetQuery->get();

            // Parse offset hours from reason field (each offset may have different hours)
            $offsetHoursUsed = 0;
            foreach ($approvedOffsetRequests as $offsetRequest) {
                $raw = $offsetRequest->reason ?? '';
                if (preg_match('/Hours to Deduct:\s*([0-9]{2}:[0-9]{2})/', $raw, $m)) {
                    [$h, $mPart] = array_map('intval', explode(':', $m[1]));
                    $offsetHoursUsed += $h + ($mPart / 60);
                } else {
                    // Fallback: if format not found, use 8 hours (for old records)
                    $offsetHoursUsed += 8;
                }
            }

            $netOvertimeHours = $totalOvertimeHours - $offsetHoursUsed;

            // Format overtime (handle negative values)
            $isNegative = $netOvertimeHours < 0;
            $absOvertimeMinutes = (int) round(abs($netOvertimeHours) * 60);
            $overtimeHoursPart = intdiv($absOvertimeMinutes, 60);
            $overtimeMinutesPart = $absOvertimeMinutes % 60;
            $overtimeFormatted = ($isNegative ? '-' : '') . sprintf('%02d:%02d', $overtimeHoursPart, $overtimeMinutesPart);
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

        return redirect()->route('users.show', $user)
            ->with('success', 'Overtime credited window updated for all employees.');
    }

    public function updateLeaveBalance(Request $request, User $user)
    {
        $request->validate([
            'vacation_allowance' => 'required|numeric|min:0|max:365',
            'sick_allowance' => 'required|numeric|min:0|max:365',
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

        $leaveBalance->update([
            'vacation_allowance' => $request->vacation_allowance,
            'sick_allowance' => $request->sick_allowance,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', "Leave balances updated for {$year}.");
    }

    public function edit(User $user)
    {
        $universities = University::active()->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'universities'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:admin,user,student,employee,applicant',
            'university_id' => 'nullable',
            'new_university_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'required_training_hours' => 'nullable|numeric|min:0',
            'vacation_allowance' => 'nullable|numeric|min:0|max:365',
            'sick_allowance' => 'nullable|numeric|min:0|max:365',
        ]);

        // Custom validation for new university
        if ($request->university_id === 'new' && !$request->filled('new_university_name')) {
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

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'university_id' => $universityId,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->filled('required_training_hours')) {
            $data['required_training_hours'] = $request->required_training_hours;
        } else {
            $data['required_training_hours'] = null;
        }

        $user->update($data);

        // Handle leave balances for employees
        if ($request->role === 'employee' && ($request->has('vacation_allowance') || $request->has('sick_allowance'))) {
            $currentYear = now()->year;
            $defaultVacation = (float) \App\Models\Setting::get('default_vacation_balance', 0);
            $defaultSick = (float) \App\Models\Setting::get('default_sick_leave_balance', 0);

            $leaveBalance = LeaveBalance::firstOrCreate(
                ['user_id' => $user->id, 'year' => $currentYear],
                [
                    'vacation_allowance' => $defaultVacation,
                    'sick_allowance' => $defaultSick,
                ]
            );

            // Update only if values are provided
            $updateData = [];
            if ($request->has('vacation_allowance')) {
                $updateData['vacation_allowance'] = $request->filled('vacation_allowance') ? (float) $request->vacation_allowance : $defaultVacation;
            }
            if ($request->has('sick_allowance')) {
                $updateData['sick_allowance'] = $request->filled('sick_allowance') ? (float) $request->sick_allowance : $defaultSick;
            }

            if (!empty($updateData)) {
                $leaveBalance->update($updateData);
            }
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

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
            'role' => 'required|string|in:admin,user,student,employee,applicant',
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

        $updated = $query->update(['role' => $role]);

        if ($updated > 0) {
            $roleLabel = match($role) {
                'admin' => 'Administrator',
                'student' => 'Student',
                'employee' => 'Employee',
                'applicant' => 'Applicant',
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
                'user_id' => $user->id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to send credentials email: ' . $e->getMessage());
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
                        'user_email' => $user->email
                    ]);
                }
            }

            $message = "Credentials email sent to {$successCount} user(s).";
            if ($failCount > 0) {
                $message .= " Failed to send to {$failCount} user(s): " . implode(', ', $failedUsers);
            }

            return redirect()->back()
                ->with($failCount > 0 ? 'warning' : 'success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to send bulk credentials emails', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to send credentials emails: ' . $e->getMessage());
        }
    }
}
