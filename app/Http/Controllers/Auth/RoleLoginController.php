<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\AccountTerminatedController;
use App\Models\User;
use App\Services\StudentOjtPostCompletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RoleLoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        if ($request->filled('redirect')) {
            $redirect = $request->query('redirect');
            if (is_string($redirect) && str_starts_with($redirect, url('/'))) {
                $request->session()->put('url.intended', $redirect);
            }
        }

        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof User && $user->role === 'student' && (bool) $user->student_terminated) {
                return redirect()->to(AccountTerminatedController::url());
            }

            if ($request->session()->has('url.intended')) {
                return redirect()->intended('/home');
            }

            if ($user instanceof User && $user->isAdmin()) {
                return redirect('/admin/dashboard');
            }

            if ($user instanceof User && $user->isHr()) {
                return redirect('/admin/hr-dashboard');
            }

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

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user instanceof User
                && $user->role === 'student'
                && Schema::hasColumn('users', 'ojt_requirement_met_at')) {
                app(StudentOjtPostCompletionService::class)->syncForStudentId((int) $user->id, false);
                $user->refresh();
            }

            if ($user instanceof User && $user->role === 'student' && (bool) $user->student_terminated) {
                return redirect()->to(AccountTerminatedController::url());
            }

            $fallback = '/home';
            if ($user instanceof User && $user->isAdmin()) {
                $fallback = '/admin/dashboard';
            } elseif ($user instanceof User && $user->isHr()) {
                $fallback = '/admin/hr-dashboard';
            }

            return redirect()->intended($fallback);
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
