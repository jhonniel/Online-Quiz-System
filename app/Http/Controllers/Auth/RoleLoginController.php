<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\AccountTerminatedController;
use App\Models\User;
use App\Services\StudentOjtPostCompletionService;
use App\Support\UserGeoLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RoleLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof User && $user->role === 'student' && (bool) $user->student_terminated) {
                return redirect()->to(AccountTerminatedController::url());
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
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0|max:100000',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user instanceof User
                && $request->filled('latitude')
                && $request->filled('longitude')) {
                UserGeoLocationService::record(
                    $user,
                    (float) $request->input('latitude'),
                    (float) $request->input('longitude'),
                    $request->filled('accuracy') ? (float) $request->input('accuracy') : null,
                    'browser',
                    'login',
                );
            } elseif ($user instanceof User) {
                $request->session()->flash('request_geo_location', true);
            }

            if ($user instanceof User && $user->isAdmin()) {
                return redirect('/admin/dashboard');
            }

            if ($user instanceof User
                && $user->role === 'student'
                && Schema::hasColumn('users', 'ojt_requirement_met_at')) {
                app(StudentOjtPostCompletionService::class)->syncForStudentId((int) $user->id, false);
                $user->refresh();
            }

            if ($user instanceof User && $user->role === 'student' && (bool) $user->student_terminated) {
                return redirect()->to(AccountTerminatedController::url());
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
