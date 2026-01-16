<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RoleLoginController extends Controller
{
    public function showLoginForm()
    {
        // If user is already authenticated, redirect to their dashboard
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is active and approved
            if ($user->is_active && $user->is_approved) {
                // Load adminPermission relationship to check permissions
                if (!$user->relationLoaded('adminPermission')) {
                    $user->load('adminPermission');
                }

                // Redirect to admin dashboard if user is admin or has admin permissions
                if ($user->isAdmin() || $user->hasAnyAdminPermission()) {
                    return redirect()->route('admin.dashboard');
                } else {
                    return redirect()->route('user.dashboard');
                }
            }
        }

        // Get system settings
        $settings = [
            'system_name' => \App\Models\Setting::get('system_name', 'System'),
            'system_logo' => \App\Models\Setting::get('system_logo'),
        ];

        return view('landing.login', [
            'errors' => new \Illuminate\Support\MessageBag(),
            'settings' => $settings
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Check if user exists before attempting authentication
        $user = \App\Models\User::where('email', $request->email)->first();

        if ($user) {
            // Check if user has a hiring application
            $hiringApplication = \App\Models\HiringApplication::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->latest()
                ->first();

            // If user has a hiring application, check if they are hired
            if ($hiringApplication && $hiringApplication->status !== 'hired') {
                $message = 'Your application is still under review. You will be able to login once you are hired.';
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'type' => 'error'
                    ], 403);
                }
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['login' => [$message]]);
            }

            // If user exists but is not approved, show approval message
            if (!$user->is_approved) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Contact admin to activate your account',
                        'type' => 'error'
                    ], 403);
                }
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['login' => ['Contact admin to activate your account']]);
            }
        }

        // Attempt authentication
        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account has been disabled. Please contact an administrator.',
                        'type' => 'error'
                    ], 403);
                }
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['login' => ['Your account has been disabled. Please contact an administrator.']]);
            }

            $request->session()->regenerate();

            // Load adminPermission relationship to check permissions
            if (!$user->relationLoaded('adminPermission')) {
                $user->load('adminPermission');
            }

            // Redirect based on user's role and permissions
            // Admins always go to admin dashboard
            // Users with admin permissions (any role) go to admin dashboard
            // Other users go to user dashboard
            if ($user->isAdmin() || $user->hasAnyAdminPermission()) {
                $redirectUrl = route('admin.dashboard');
            } else {
                $redirectUrl = route('user.dashboard');
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful! Redirecting...',
                    'type' => 'success',
                    'redirect_url' => $redirectUrl
                ]);
            }

            return redirect()->intended($redirectUrl);
        }

        // Credentials don't match
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Email and password is not match',
                'type' => 'error'
            ], 422);
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['login' => ['Email and password is not match']]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing.index');
    }
}
