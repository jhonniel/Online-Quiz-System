<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  The permission to check (e.g., 'content_management', 'analytics_reports')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Load adminPermission relationship if not already loaded
        if (!$user->relationLoaded('adminPermission')) {
            $user->load('adminPermission');
        }

        // Check if user has the specific permission
        // This method already handles checking for admin, employee, or any user with adminPermission
        if (!$user->hasAdminPermission($permission)) {
            abort(403, 'Access denied. You do not have permission to access this feature.');
        }

        return $next($request);
    }
}
