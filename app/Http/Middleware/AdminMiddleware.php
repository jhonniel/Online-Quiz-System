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
            return redirect('/login');
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
        // 4. User is accessing task routes (students and employees can access tasks)
        $hasAccess = false;
        
        // Check if this is a task route that students/employees can access
        // Allow access to task index (My Tasks, Group Tasks) but not dashboard/analytics
        $isTaskIndexRoute = $request->routeIs('admin.tasks.index');
        $isTaskActionRoute = $request->routeIs('admin.tasks.store') || 
                            $request->routeIs('admin.tasks.update') || 
                            $request->routeIs('admin.tasks.destroy') ||
                            $request->routeIs('admin.tasks.reorder') ||
                            $request->routeIs('admin.tasks.update-order') ||
                            $request->routeIs('admin.tasks.add-comment') ||
                            $request->routeIs('admin.tasks.upload-attachment') ||
                            $request->routeIs('admin.tasks.delete-attachment') ||
                            $request->routeIs('admin.tasks.assign-users') ||
                            $request->routeIs('admin.tasks.*invitation*') ||
                            $request->routeIs('admin.tasks.join*') ||
                            $request->routeIs('admin.tasks.convert-to-group') ||
                            $request->routeIs('admin.tasks.custom-boards.*') ||
                            $request->routeIs('admin.tasks.task-lists.*') ||
                            $request->routeIs('admin.tasks.custom-priorities.*');
        
        $isAllowedTaskRoute = $isTaskIndexRoute || $isTaskActionRoute;

        if ($user->isAdmin()) {
            // Admins always have access
            $hasAccess = true;
        } elseif ($isAllowedTaskRoute && ($user->isEmployee() || $user->isStudent())) {
            // Students and employees can access task routes (My Tasks, Group Tasks, and related actions)
            $hasAccess = true;
        } elseif ($user->isEmployee()) {
            // Employees need at least one admin permission for other routes
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
