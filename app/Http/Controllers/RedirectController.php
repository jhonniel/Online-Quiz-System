<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function home()
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Load adminPermission relationship to check permissions
            if (!$user->relationLoaded('adminPermission')) {
                $user->load('adminPermission');
            }

            // Only super admins go to admin dashboard; everyone else (including users with admin permissions) goes to user dashboard
            if ($user->isSuperAdmin()) {
                return redirect('/admin/dashboard');
            }
            return redirect('/dashboard');
        }

        return redirect('/login');
    }
}
