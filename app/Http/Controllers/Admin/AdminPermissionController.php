<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPermission;
use App\Models\Department;
use App\Models\HiringPosition;
use App\Models\User;
use App\Support\AdminPermissionAreas;
use App\Support\UserRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminPermissionController extends Controller
{
    /**
     * Display a listing of users who have been given permissions.
     */
    public function index(Request $request)
    {
        // Show all users who have been assigned permissions (have adminPermission record)
        $query = User::whereHas('adminPermission')
            ->with('adminPermission');

        $search = trim((string) $request->input('search', ''));

        // Search functionality
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Get per_page from request or default to 20
        $perPage = (int) $request->get('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $users = $query->orderBy('role')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.admin-permissions.index', compact('users'));
    }

    /**
     * Show form to add a new user to permissions management.
     * Full-access admins can add any user (all roles).
     * Regular admins can only add employees.
     */
    public function create()
    {
        $currentUser = auth()->user();
        $isFullAccessAdmin = $currentUser->isSuperAdmin();

        if ($isFullAccessAdmin) {
            // Full-access admins can add any user (all roles) that don't have permissions yet
            $users = User::whereDoesntHave('adminPermission')
                ->orderBy('role')
                ->orderBy('name')
                ->get();
        } else {
            // Regular admins can only add employees
            $users = User::whereIn('role', UserRoles::STAFF)
                ->whereDoesntHave('adminPermission')
                ->orderBy('name')
                ->get();
        }

        return view('admin.admin-permissions.create', compact('users', 'isFullAccessAdmin'));
    }

    /**
     * Store a new permission assignment for a user.
     * Full-access admins can assign to any non-employee user.
     * Regular admins can only assign to employees.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUser = auth()->user();
        $isFullAccessAdmin = $currentUser->isSuperAdmin();
        $user = User::findOrFail($request->user_id);

        // Access control: Only full-access admins can assign to non-employee users
        if (! $isFullAccessAdmin && ! $user->isStaffMember()) {
            return redirect('/admin/admin-permissions/create')
                ->with('error', 'Only full-access admins can assign permissions to non-employee users.');
        }

        // Check if user already has permissions
        if ($user->adminPermission) {
            return redirect('/admin/admin-permissions/'.$user->id.'/edit')
                ->with('info', 'This user already has permissions. You can edit them here.');
        }

        // Create empty permission record (all false by default)
        $user->adminPermission()->create([
            'content_management' => false,
            'analytics_reports' => false,
            'employee_management' => false,
            'student_management' => false,
            'hiring_process' => false,
            'communication' => false,
            'linked_accounts' => false,
            'billing' => false,
            'files' => false,
            'confession' => false,
            'tasks' => false,
            'feedback' => false,
            'user_management' => false,
            'system' => false,
            'qr_code' => false,
        ]);

        // Refresh the user's relationship
        $user->refresh();
        $user->load('adminPermission');

        $roleLabel = $user->getRoleLabel();

        return redirect('/admin/admin-permissions/'.$user->id.'/edit')
            ->with('success', "{$roleLabel} added to permissions management. Please configure their access. The user may need to refresh their browser or log out and log back in to see the new items in their sidebar.");
    }

    /**
     * Show the form for editing the specified user's permissions.
     * Full-access admins can edit any user's permissions.
     * Regular admins can only edit employee permissions.
     */
    public function edit(User $user)
    {
        $currentUser = auth()->user();
        $isFullAccessAdmin = $currentUser->isSuperAdmin();

        // Access control: Only full-access admins can edit non-employee permissions
        if (! $isFullAccessAdmin && ! $user->isStaffMember() && ! $user->isAdmin()) {
            return redirect('/admin/admin-permissions')
                ->with('error', 'Only full-access admins can edit permissions for non-employee users.');
        }

        $permission = $user->adminPermission;
        $departments = Department::active()->orderBy('name')->get();
        $hiringPositions = HiringPosition::where('is_active', true)->orderBy('title')->get();

        $permissionAreas = AdminPermissionAreas::areas();

        return view('admin.admin-permissions.edit', compact('user', 'permission', 'departments', 'hiringPositions', 'isFullAccessAdmin', 'permissionAreas'));
    }

    /**
     * Update the specified user's permissions in storage.
     * Full-access admins can update any user's permissions.
     * Regular admins can only update employee permissions.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        $isFullAccessAdmin = $currentUser->isSuperAdmin();

        // Access control: Only full-access admins can update non-employee permissions
        if (! $isFullAccessAdmin && ! $user->isStaffMember() && ! $user->isAdmin()) {
            return redirect('/admin/admin-permissions')
                ->with('error', 'Only full-access admins can update permissions for non-employee users.');
        }

        $hasAllowedEmployeeDepartments = Schema::hasColumn('admin_permissions', 'allowed_employee_departments');
        $hasAllowedStudentDepartments = Schema::hasColumn('admin_permissions', 'allowed_student_departments');
        $hasAllowedDepartments = Schema::hasColumn('admin_permissions', 'allowed_departments');

        $rules = [
            'content_management' => 'boolean',
            'analytics_reports' => 'boolean',
            'employee_management' => 'boolean',
            'student_management' => 'boolean',
            'hiring_process' => 'boolean',
            'allowed_positions' => 'nullable|array',
            'allowed_positions.*' => 'exists:hiring_positions,id',
            'allowed_analytics_features' => 'nullable|array',
            'allowed_analytics_features.*' => 'in:'.implode(',', array_keys(AdminPermission::ANALYTICS_FEATURES)),
            'communication' => 'boolean',
            'linked_accounts' => 'boolean',
            'billing' => 'boolean',
            'files' => 'boolean',
            'confession' => 'boolean',
            'tasks' => 'boolean',
            'feedback' => 'boolean',
            'user_management' => 'boolean',
            'system' => 'boolean',
            'qr_code' => 'boolean',
        ];

        // Validate these inputs only when the DB supports them.
        if ($hasAllowedEmployeeDepartments) {
            $rules['allowed_employee_departments'] = 'nullable|array';
            $rules['allowed_employee_departments.*'] = 'exists:departments,id';
        }

        if ($hasAllowedStudentDepartments) {
            $rules['allowed_student_departments'] = 'nullable|array';
            $rules['allowed_student_departments.*'] = 'exists:departments,id';
        }

        foreach (AdminPermissionAreas::areas() as $areaKey => $area) {
            if (! Schema::hasColumn('admin_permissions', $area['column'])) {
                continue;
            }
            $parentField = $areaKey === 'subscriptions'
                ? 'subscriptions'
                : $area['parent_flag'];
            if ($areaKey === 'subscriptions') {
                continue;
            }
            $rules[$area['column']] = 'nullable|array';
            $rules[$area['column'].'.*'] = 'in:'.implode(',', array_keys($area['features']));
        }

        if (Schema::hasColumn('admin_permissions', 'allowed_subscription_features')) {
            $rules['allowed_subscription_features'] = 'nullable|array';
            $rules['allowed_subscription_features.*'] = 'in:'.implode(',', array_keys(AdminPermissionAreas::SUBSCRIPTION_FEATURES));
        }

        $request->validate($rules);

        // Convert checkboxes to boolean (they may not be present in request)
        $permissions = [
            'content_management' => $request->has('content_management'),
            'analytics_reports' => $request->has('analytics_reports'),
            'employee_management' => $request->has('employee_management'),
            'student_management' => $request->has('student_management'),
            'hiring_process' => $request->has('hiring_process'),
            'communication' => $request->has('communication'),
            'linked_accounts' => $request->has('linked_accounts'),
            'billing' => $request->has('billing'),
            'files' => $request->has('files'),
            'confession' => $request->has('confession'),
            'tasks' => $request->has('tasks'),
            'feedback' => $request->has('feedback'),
            'user_management' => $request->has('user_management'),
            'system' => $request->has('system'),
            'qr_code' => $request->has('qr_code'),
        ];

        // Handle department scopes for both new and legacy schemas.
        $employeeDepartments = $request->has('employee_management')
            ? $request->input('allowed_employee_departments', [])
            : [];
        $studentDepartments = $request->has('student_management')
            ? $request->input('allowed_student_departments', [])
            : [];

        if ($hasAllowedEmployeeDepartments) {
            $permissions['allowed_employee_departments'] = $request->has('employee_management')
                ? array_values(array_unique(array_map('intval', $employeeDepartments)))
                : null;
        }

        if ($hasAllowedStudentDepartments) {
            $permissions['allowed_student_departments'] = $request->has('student_management')
                ? array_values(array_unique(array_map('intval', $studentDepartments)))
                : null;
        }

        if ($hasAllowedDepartments) {
            $mergedDepartments = array_values(array_unique(array_map('intval', array_merge($employeeDepartments, $studentDepartments))));
            $permissions['allowed_departments'] = (! empty($mergedDepartments) && ($request->has('employee_management') || $request->has('student_management')))
                ? $mergedDepartments
                : null;
        }

        // Handle allowed positions - only set if hiring_process is enabled
        if ($request->has('hiring_process')) {
            $permissions['allowed_positions'] = $request->input('allowed_positions', []);
        } else {
            // If hiring_process is disabled, clear allowed_positions
            $permissions['allowed_positions'] = null;
        }

        if (Schema::hasColumn('admin_permissions', 'allowed_analytics_features')) {
            if ($request->has('analytics_reports')) {
                $permissions['allowed_analytics_features'] = $request->input('allowed_analytics_features', []);
            } else {
                $permissions['allowed_analytics_features'] = null;
            }
        }

        $hasSubscriptions = $request->has('subscriptions') || $request->has('linked_accounts') || $request->has('billing');
        if ($hasSubscriptions) {
            $permissions['linked_accounts'] = $request->has('linked_accounts') || $request->has('subscriptions');
            $permissions['billing'] = $request->has('billing') || $request->has('subscriptions');
        }

        foreach (AdminPermissionAreas::areas() as $areaKey => $area) {
            if ($areaKey === 'subscriptions' || ! Schema::hasColumn('admin_permissions', $area['column'])) {
                continue;
            }
            $parentField = $area['parent_flag'];
            if ($areaKey === 'communication' && $request->has('feedback') && ! $request->has('communication')) {
                if ($area['column'] === 'allowed_communication_features') {
                    $feedbackOnly = array_values(array_intersect(
                        $request->input('allowed_communication_features', []),
                        ['feedback']
                    ));
                    $permissions[$area['column']] = $feedbackOnly ?: null;
                }

                continue;
            }
            if ($request->has($parentField)) {
                $permissions[$area['column']] = $request->input($area['column'], []);
                if ($areaKey === 'communication' && in_array('feedback', $permissions[$area['column']] ?? [], true)) {
                    $permissions['feedback'] = true;
                }
            } else {
                $permissions[$area['column']] = null;
            }
        }

        if (Schema::hasColumn('admin_permissions', 'allowed_subscription_features')) {
            if ($hasSubscriptions) {
                $permissions['allowed_subscription_features'] = $request->input('allowed_subscription_features', []);
            } else {
                $permissions['allowed_subscription_features'] = null;
            }
        }

        // Update or create permission record
        $user->adminPermission()->updateOrCreate(
            ['user_id' => $user->id],
            $permissions
        );

        // Refresh the user's relationship to ensure it's loaded
        $user->refresh();
        $user->load('adminPermission');

        $roleLabel = ucfirst($user->role);

        return redirect('/admin/admin-permissions')
            ->with('success', "{$roleLabel} permissions updated successfully. The user may need to refresh their browser to see the new items in their sidebar.");
    }

    /**
     * Remove permissions from a user.
     * Full-access admins can remove permissions from any user.
     * Regular admins can only remove permissions from employees.
     */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        $isFullAccessAdmin = $currentUser->isSuperAdmin();

        // Access control: Only full-access admins can remove permissions from non-employee users
        if (! $isFullAccessAdmin && ! $user->isStaffMember() && ! $user->isAdmin()) {
            return redirect('/admin/admin-permissions')
                ->with('error', 'Only full-access admins can remove permissions from non-employee users.');
        }

        $user->adminPermission()->delete();

        $roleLabel = $user->isAdmin() ? 'admin' : strtolower($user->role);

        return redirect('/admin/admin-permissions')
            ->with('success', "Permissions removed. User now has full {$roleLabel} access.");
    }

    /**
     * Show the current user's assigned permissions (read-only).
     * Visible to any user who has admin access so they can see what they are allowed to do.
     */
    public function myPermissions()
    {
        $user = auth()->user();
        $user->load('adminPermission');
        $permission = $user->adminPermission;
        $isSuperAdmin = $user->isSuperAdmin();

        $departments = $isSuperAdmin ? collect() : Department::orderBy('name')->get();
        $hiringPositions = $isSuperAdmin ? collect() : HiringPosition::orderBy('title')->get();

        $permissionAreas = AdminPermissionAreas::areas();

        return view('admin.admin-permissions.my-permissions', compact('user', 'permission', 'isSuperAdmin', 'departments', 'hiringPositions', 'permissionAreas'));
    }
}
