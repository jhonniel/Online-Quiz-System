<?php

namespace App\Http\Controllers;

use App\Http\Controllers\User\AccountTerminatedController;
use App\Models\User;

class RedirectController extends Controller
{
    public function home()
    {
        if (auth()->check()) {
            $user = auth()->user();

            // HR users land on the HR dashboard.
            if ($user instanceof User && $user->isHr()) {
                return redirect('/admin/hr-dashboard');
            }

            // Any admin role user should always land on admin dashboard.
            if ($user instanceof User && $user->isAdmin()) {
                return redirect('/admin/dashboard');
            }

            if ($user->role === 'student' && (bool) $user->student_terminated) {
                return redirect()->to(AccountTerminatedController::url());
            }

            return redirect('/dashboard');
        }

        return redirect('/login');
    }
}
