<?php

namespace App\Http\Middleware;

use App\Models\ApiEndpointMetric;
use App\Models\ApiEndpointMetricPoint;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackApiEndpointMetrics
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->headers->get('X-Api-Monitor-Probe') === '1') {
            return $next($request);
        }

        $startedAt = microtime(true);
        $response = $next($request);

        $route = $request->route();
        if (! $route) {
            return $response;
        }

        $uri = trim((string) $route->uri(), '/');
        if (str_starts_with($uri, 'admin/system/api-monitoring')) {
            return $response;
        }

        $routeName = $route->getName();
        $methods = array_values(array_diff($route->methods() ?? [], ['HEAD', 'OPTIONS']));
        $method = strtoupper($methods[0] ?? $request->method());

        $isApiRoute = $this->shouldTrackEndpoint($uri, $routeName, $method);

        if (! $isApiRoute) {
            return $response;
        }

        $routeKey = $method.' '.$uri;
        $statusCode = $response->getStatusCode();
        $durationMs = (microtime(true) - $startedAt) * 1000;
        $isFailure = $statusCode >= 500;

        $metric = ApiEndpointMetric::query()->firstOrNew(['route_key' => $routeKey]);
        $metric->method = $method;
        $metric->uri = $uri;
        $metric->route_name = $routeName;
        $metric->request_count = (int) $metric->request_count + 1;
        $metric->success_count = (int) $metric->success_count + ($isFailure ? 0 : 1);
        $metric->failure_count = (int) $metric->failure_count + ($isFailure ? 1 : 0);
        $metric->last_status_code = $statusCode;
        $metric->last_response_at = now();

        if ($isFailure) {
            $metric->last_failure_at = now();
        }

        $previousCount = max(((int) $metric->request_count) - 1, 0);
        $previousAverage = (float) $metric->avg_response_time_ms;
        $metric->avg_response_time_ms = $previousCount === 0
            ? $durationMs
            : (($previousAverage * $previousCount) + $durationMs) / ($previousCount + 1);

        $metric->save();

        ApiEndpointMetricPoint::query()->create([
            'route_key' => $routeKey,
            'is_success' => ! $isFailure,
            'status_code' => $statusCode,
            'response_time_ms' => round($durationMs, 2),
            'recorded_at' => now(),
        ]);

        return $response;
    }

    private function shouldTrackEndpoint(string $uri, ?string $routeName, string $method): bool
    {
        if (str_starts_with($uri, '_debugbar')
            || str_starts_with($uri, '_ignition')
            || str_starts_with($uri, 'livewire')
            || $uri === 'sanctum/csrf-cookie'
            || str_starts_with($uri, 'admin/system/api-monitoring')) {
            return false;
        }
        
        return true;
    }
}
