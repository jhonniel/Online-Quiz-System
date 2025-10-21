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
        // Get system settings
        $settings = [
            'system_name' => \App\Models\Setting::get('system_name', 'Online Quiz System'),
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

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account has been disabled. Please contact an administrator.',
                        'type' => 'error'
                    ], 403);
                }
                throw ValidationException::withMessages([
                    'email' => ['Your account has been disabled. Please contact an administrator.'],
                ]);
            }

            // Check if user is approved
            if (!$user->is_approved) {
                Auth::logout();
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account is pending admin approval. Please wait for approval before logging in.',
                        'type' => 'warning'
                    ], 403);
                }
                throw ValidationException::withMessages([
                    'email' => ['Your account is pending admin approval. Please wait for approval before logging in.'],
                ]);
            }

            $request->session()->regenerate();

            // Redirect based on user's actual role
            if ($request->ajax()) {
                $redirectUrl = $user->isAdmin() ? route('admin.dashboard') : route('user.dashboard');
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful! Redirecting...',
                    'type' => 'success',
                    'redirect_url' => $redirectUrl
                ]);
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('user.dashboard'));
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
                'type' => 'error'
            ], 422);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing.index');
    }
}
