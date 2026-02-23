<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminPermission;
use App\Models\Department;
use App\Models\HiringPosition;
use Illuminate\Http\Request;

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
            $query->where(function($q) use ($search) {
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
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
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
            $users = User::where('role', 'employee')
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
        if (!$isFullAccessAdmin && !$user->isEmployee()) {
            return redirect('/admin/admin-permissions/create')
                ->with('error', 'Only full-access admins can assign permissions to non-employee users.');
        }

        // Check if user already has permissions
        if ($user->adminPermission) {
            return redirect('/admin/admin-permissions/' . $user->id . '/edit')
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
            'billing' => false,
            'files' => false,
            'confession' => false,
            'feedback' => false,
            'user_management' => false,
            'system' => false,
        ]);

        // Refresh the user's relationship
        $user->refresh();
        $user->load('adminPermission');

        $roleLabel = $user->isEmployee() ? 'Employee' : ucfirst($user->role);
        return redirect('/admin/admin-permissions/' . $user->id . '/edit')
            ->with('success', "{$roleLabel} added to permissions management. Please configure their access. The user may need to refresh their browser or log out and log back in to see the changes.");
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
        if (!$isFullAccessAdmin && !$user->isEmployee() && !$user->isAdmin()) {
            return redirect('/admin/admin-permissions')
                ->with('error', 'Only full-access admins can edit permissions for non-employee users.');
        }

        $permission = $user->adminPermission;
        $departments = Department::active()->orderBy('name')->get();
        $hiringPositions = HiringPosition::where('is_active', true)->orderBy('title')->get();

        return view('admin.admin-permissions.edit', compact('user', 'permission', 'departments', 'hiringPositions', 'isFullAccessAdmin'));
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
        if (!$isFullAccessAdmin && !$user->isEmployee() && !$user->isAdmin()) {
            return redirect('/admin/admin-permissions')
                ->with('error', 'Only full-access admins can update permissions for non-employee users.');
        }

        $validated = $request->validate([
            'content_management' => 'boolean',
            'analytics_reports' => 'boolean',
            'employee_management' => 'boolean',
            'allowed_departments' => 'nullable|array',
            'allowed_departments.*' => 'exists:departments,id',
            'student_management' => 'boolean',
            'hiring_process' => 'boolean',
            'allowed_positions' => 'nullable|array',
            'allowed_positions.*' => 'exists:hiring_positions,id',
            'communication' => 'boolean',
            'billing' => 'boolean',
            'files' => 'boolean',
            'confession' => 'boolean',
            'feedback' => 'boolean',
            'user_management' => 'boolean',
            'system' => 'boolean',
        ]);

        // Convert checkboxes to boolean (they may not be present in request)
        $permissions = [
            'content_management' => $request->has('content_management'),
            'analytics_reports' => $request->has('analytics_reports'),
            'employee_management' => $request->has('employee_management'),
            'student_management' => $request->has('student_management'),
            'hiring_process' => $request->has('hiring_process'),
            'communication' => $request->has('communication'),
            'billing' => $request->has('billing'),
            'files' => $request->has('files'),
            'confession' => $request->has('confession'),
            'feedback' => $request->has('feedback'),
            'user_management' => $request->has('user_management'),
            'system' => $request->has('system'),
        ];

        // Handle allowed departments - only set if employee_management is enabled
        if ($request->has('employee_management')) {
            $permissions['allowed_departments'] = $request->input('allowed_departments', []);
        } else {
            // If employee_management is disabled, clear allowed_departments
            $permissions['allowed_departments'] = null;
        }

        // Handle allowed positions - only set if hiring_process is enabled
        if ($request->has('hiring_process')) {
            $permissions['allowed_positions'] = $request->input('allowed_positions', []);
        } else {
            // If hiring_process is disabled, clear allowed_positions
            $permissions['allowed_positions'] = null;
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
            ->with('success', "{$roleLabel} permissions updated successfully. The user may need to refresh their browser to see the changes.");
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
        if (!$isFullAccessAdmin && !$user->isEmployee() && !$user->isAdmin()) {
            return redirect('/admin/admin-permissions')
                ->with('error', 'Only full-access admins can remove permissions from non-employee users.');
        }

        $user->adminPermission()->delete();

        $roleLabel = $user->isAdmin() ? 'admin' : strtolower($user->role);
        return redirect('/admin/admin-permissions')
            ->with('success', "Permissions removed. User now has full {$roleLabel} access.");
    }
}
