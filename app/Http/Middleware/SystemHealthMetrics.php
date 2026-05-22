<?php

namespace App\Http\Middleware;

use App\Support\SystemHealthMetricsStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemHealthMetrics
{
    /**
     * Record traffic metrics after the response is sent so page loads are not blocked.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('admin/settings/health-metrics')) {
            return $response;
        }

        $request->attributes->set('syshealth_metrics_pending', true);

        app()->terminating(static function () use ($request, $response): void {
            if (! $request->attributes->get('syshealth_metrics_pending')) {
                return;
            }

            try {
                SystemHealthMetricsStore::recordRequest($request, $response);
            } catch (\Throwable $e) {
                // Never break requests because of metrics.
            }
        });

        return $response;
    }
}
