<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RoleLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/home');
        }
        
        return view('landing.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        $existing = User::where('email', $request->input('email'))->first();
        if ($existing && $existing->role === 'student' && (bool) $existing->student_terminated) {
            throw ValidationException::withMessages([
                'email' => [
                    'Your student account has been terminated. You cannot sign in. Contact the administration if you need assistance.',
                ],
            ]);
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Force admin-role users to admin URL after login.
            $user = Auth::user();
            if ($user instanceof User && $user->isAdmin()) {
                return redirect('/admin/dashboard');
            }

            return redirect()->intended('/home');
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
        
        return redirect('/login');
    }
}
