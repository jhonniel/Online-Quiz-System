<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissionAreas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminSubFeature
{
    /**
     * @param  string  $areaKey  content_management|employee_management|...
     * @param  string  $feature  sub-feature key within the area
     */
    public function handle(Request $request, Closure $next, string $areaKey, string $feature): Response
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if (! $user->relationLoaded('adminPermission')) {
            $user->load('adminPermission');
        }

        if (! $user->canAccessAdminSubFeature($areaKey, $feature)) {
            abort(403, 'Access denied. You do not have permission to access this area.');
        }

        if (! array_key_exists($feature, AdminPermissionAreas::area($areaKey)['features'] ?? [])) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
