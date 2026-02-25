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

            // Super admins and users with any admin permission go to admin dashboard; everyone else goes to user dashboard
            if ($user->isSuperAdmin() || $user->hasAnyAdminPermission()) {
                return redirect('/admin/dashboard');
            }
            return redirect('/dashboard');
        }

        return redirect('/login');
    }
}
