<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Load adminPermission relationship if not already loaded
        if (!$user->relationLoaded('adminPermission')) {
            $user->load('adminPermission');
        }

        // Allow access if:
        // 1. User is an admin (always allowed)
        // 2. User is an employee with admin permissions
        // 3. User has any role but has been granted admin permissions via adminPermission record
        $hasAccess = false;

        if ($user->isAdmin()) {
            // Admins always have access
            $hasAccess = true;
        } elseif ($user->isEmployee()) {
            // Employees need at least one admin permission
            $hasAccess = $user->hasAnyAdminPermission();
        } elseif ($user->adminPermission) {
            // Other roles (students, applicants, etc.) can access if they have an adminPermission record
            // This means an admin has explicitly granted them access
            $hasAccess = $user->hasAnyAdminPermission();
        }

        if (!$hasAccess) {
            abort(403, 'Access denied. You do not have permission to access admin features.');
        }

        return $next($request);
    }
}
