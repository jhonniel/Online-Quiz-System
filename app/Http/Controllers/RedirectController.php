<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function home()
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Full-access admins (super admins) go directly to the admin dashboard
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return redirect('/admin/dashboard');
            }

            // Everyone else (employees, students, limited-permission admins, users) go to the user dashboard
            return redirect('/dashboard');
        }

        return redirect('/login');
    }
}
