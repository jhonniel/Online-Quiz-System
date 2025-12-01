<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $errors = new \Illuminate\Support\MessageBag(); // Empty MessageBag
        return view('admin.users.create', compact('universities', 'errors'));
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

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'university_id' => $universityId,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
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

        $user->update($data);

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
}
