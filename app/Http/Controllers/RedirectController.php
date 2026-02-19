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

            // Redirect to admin dashboard if user is admin or has admin permissions
            // Full access (super admin) goes to main dashboard; partial admins go to users (or first available)
            if ($user->isAdmin() || $user->hasAnyAdminPermission()) {
                return $user->isSuperAdmin()
                    ? redirect('/admin/dashboard')
                    : redirect('/admin/users');
            } else {
                return redirect('/dashboard');
            }
        }

        return redirect('/login');
    }
}
