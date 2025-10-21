<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    public function create(): View
    {
        return view('auth.register');
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
            'university_id' => ['required'],
            'new_university_name' => ['nullable', 'string', 'max:255'],
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true,
            'is_approved' => false, // Requires admin approval
            'university_id' => $universityId,
        ]);

        event(new Registered($user));

        // Don't auto-login, wait for approval
        return redirect()->route('login')
            ->with('success', 'Registration successful! Your account is pending admin approval. You will be notified once approved.');
    }
}
