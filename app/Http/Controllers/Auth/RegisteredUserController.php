<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\HiringApplication;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $application = null;
        $email = $request->input('email');
        $token = $request->input('token');

        // If token is provided, validate it and pre-fill email
        if ($token) {
            $application = HiringApplication::where('acceptance_token', $token)
                ->where('status', 'accepted')
                ->first();

            if ($application && $application->isTokenValid()) {
                $email = $application->email;
            } else {
                $application = null; // Invalid token
            }
        }

        return view('auth.register', compact('application', 'email', 'token'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
            // Role is not validated from user input - only admins can set roles
            // 'role' => ['nullable', 'string', 'in:student,employee,applicant'],
            'university_id' => ['required'],
            'new_university_name' => ['nullable', 'string', 'max:255'],
            'course' => ['nullable', 'string', 'max:255'],
        ], [
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'password.confirmed' => 'Password confirmation does not match.',
            'email.unique' => 'This email address is already registered.',
            'university_id.required' => 'Please select a university/school.',
        ]);

        // Handle university assignment
        $universityId = null;

        if ($request->university_id === 'new') {
            // Validate new university name
            $request->validate([
                'new_university_name' => ['required', 'string', 'max:255', 'unique:universities,name'],
            ]);

            // Create new university
            $university = \App\Models\University::create([
                'name' => $request->new_university_name,
                'is_active' => true,
            ]);
            $universityId = $university->id;
        } else {
            // Use existing university
            $universityId = $request->university_id;
        }

        // Check if this is a hiring application registration
        $application = null;
        $isHiringApplication = false;

        if ($request->has('acceptance_token')) {
            $application = HiringApplication::where('acceptance_token', $request->acceptance_token)
                ->where('email', $request->email)
                ->where('status', 'accepted')
                ->first();

            if ($application && $application->isTokenValid()) {
                $isHiringApplication = true;
            }
        }

        // Determine role - users cannot set their own role, only admins can
        // Default to 'student' for regular registrations
        $role = 'student';
        
        // If it's a hiring application, set role to 'applicant'
        if ($isHiringApplication) {
            $role = 'applicant';
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'is_active' => true,
            'is_approved' => $isHiringApplication, // Auto-approve if from hiring application
            'university_id' => $universityId,
            'course' => $role === 'student' ? trim((string) $request->input('course', '')) ?: null : null,
        ]);

        // Link application to user if it's a hiring application
        if ($isHiringApplication && $application) {
            $application->update([
                'user_id' => $user->id,
            ]);
        }

        event(new Registered($user));

        if ($isHiringApplication) {
            // Auto-login for hiring applications
            Auth::login($user);
            return redirect('/home')
                ->with('success', 'Welcome! Your account has been created and you can now proceed to the interview stage.');
        } else {
            // Don't auto-login, wait for approval
            return redirect('/login')
                ->with('success', 'Registration successful! Your account is pending admin approval. You will be notified once approved.');
        }
    }
}
