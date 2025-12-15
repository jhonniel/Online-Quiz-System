<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminPermission;
use App\Models\Department;
use Illuminate\Http\Request;

class AdminPermissionController extends Controller
{
    /**
     * Display a listing of users who have been given permissions.
     */
    public function index(Request $request)
    {
        // Only show users who have been assigned permissions (have adminPermission record)
        $query = User::whereIn('role', ['admin', 'employee'])
            ->whereHas('adminPermission')
            ->with('adminPermission');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
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
        $perPage = $request->get('per_page', 20);

        $users = $query->orderBy('role')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.admin-permissions.index', compact('users'));
    }

    /**
     * Show form to add a new employee to permissions management.
     */
    public function create()
    {
        // Get all employees that don't have permissions yet
        $employees = User::where('role', 'employee')
            ->whereDoesntHave('adminPermission')
            ->orderBy('name')
            ->get();

        return view('admin.admin-permissions.create', compact('employees'));
    }

    /**
     * Store a new permission assignment for an employee.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Only allow assigning to employees
        if (!$user->isEmployee()) {
            return redirect()->route('admin.admin-permissions.create')
                ->with('error', 'Permissions can only be assigned to employee users.');
        }

        // Check if user already has permissions
        if ($user->adminPermission) {
            return redirect()->route('admin.admin-permissions.edit', $user)
                ->with('info', 'This employee already has permissions. You can edit them here.');
        }

        // Create empty permission record (all false by default)
        $user->adminPermission()->create([
            'content_management' => false,
            'analytics_reports' => false,
            'employee_management' => false,
            'student_management' => false,
            'hiring_process' => false,
            'communication' => false,
            'user_management' => false,
            'system' => false,
        ]);

        // Refresh the user's relationship
        $user->refresh();
        $user->load('adminPermission');

        return redirect()->route('admin.admin-permissions.edit', $user)
            ->with('success', 'Employee added to permissions management. Please configure their access. The employee may need to refresh their browser or log out and log back in to see the changes.');
    }

    /**
     * Show the form for editing the specified user's permissions.
     */
    public function edit(User $user)
    {
        // Only allow editing permissions for admin and employee users
        if (!$user->isAdmin() && !$user->isEmployee()) {
            return redirect()->route('admin.admin-permissions.index')
                ->with('error', 'Permissions can only be assigned to admin or employee users.');
        }

        $permission = $user->adminPermission;
        $departments = Department::active()->orderBy('name')->get();

        return view('admin.admin-permissions.edit', compact('user', 'permission', 'departments'));
    }

    /**
     * Update the specified user's permissions in storage.
     */
    public function update(Request $request, User $user)
    {
        // Only allow updating permissions for admin and employee users
        if (!$user->isAdmin() && !$user->isEmployee()) {
            return redirect()->route('admin.admin-permissions.index')
                ->with('error', 'Permissions can only be assigned to admin or employee users.');
        }

        $validated = $request->validate([
            'content_management' => 'boolean',
            'analytics_reports' => 'boolean',
            'employee_management' => 'boolean',
            'allowed_departments' => 'nullable|array',
            'allowed_departments.*' => 'exists:departments,id',
            'student_management' => 'boolean',
            'hiring_process' => 'boolean',
            'communication' => 'boolean',
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

        // Update or create permission record
        $user->adminPermission()->updateOrCreate(
            ['user_id' => $user->id],
            $permissions
        );

        // Refresh the user's relationship to ensure it's loaded
        $user->refresh();
        $user->load('adminPermission');

        $roleLabel = $user->isAdmin() ? 'Admin' : 'Employee';
        return redirect()->route('admin.admin-permissions.index')
            ->with('success', "{$roleLabel} permissions updated successfully. The user may need to refresh their browser to see the changes.");
    }

    /**
     * Remove permissions from a user (making them a super admin/employee with full access).
     */
    public function destroy(User $user)
    {
        if (!$user->isAdmin() && !$user->isEmployee()) {
            return redirect()->route('admin.admin-permissions.index')
                ->with('error', 'Only admin or employee users can have permissions.');
        }

        $user->adminPermission()->delete();

        $roleLabel = $user->isAdmin() ? 'admin' : 'employee';
        return redirect()->route('admin.admin-permissions.index')
            ->with('success', "Permissions removed. User now has full {$roleLabel} access.");
    }
}
