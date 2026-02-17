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
            // Users with any role who have been granted admin permissions can access admin dashboard
            if ($user->isAdmin() || $user->hasAnyAdminPermission()) {
                return redirect('/admin/dashboard');
            } else {
                return redirect('/dashboard');
            }
        }

        return redirect('/login');
    }
}
