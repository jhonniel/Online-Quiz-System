<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAnalyticsFeature
{
    /**
     * @param  string  $feature  analytics|error_logs|user_activity|students_review
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if (! $user->relationLoaded('adminPermission')) {
            $user->load('adminPermission');
        }

        if (! $user->canAccessAnalyticsFeature($feature)) {
            abort(403, 'Access denied. You do not have permission to access this analytics area.');
        }

        return $next($request);
    }
}
