<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiEndpointMetric;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ExternalApiMonitoringController extends Controller
{
    public function endpoints(): JsonResponse
    {
        $rows = $this->buildApiRows();

        return response()->json([
            'summary' => $this->buildSummary($rows),
            'rows' => $rows,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildApiRows(): array
    {
        $metrics = ApiEndpointMetric::query()->get()->keyBy('route_key');
        $allowedRouteKeys = $this->getAllowedRouteKeys();
        $rows = [];

        foreach (Route::getRoutes() as $route) {
            $uri = trim((string) $route->uri(), '/');
            $name = $route->getName();
            $methods = array_values(array_diff($route->methods() ?? [], ['HEAD', 'OPTIONS']));
            $method = strtoupper($methods[0] ?? 'GET');

            if (! $this->isApiRoute($uri, $name, $method)) {
                continue;
            }

            $routeKey = $method.' '.$uri;
            if (! in_array($routeKey, $allowedRouteKeys, true)) {
                continue;
            }

            $metric = $metrics->get($routeKey);
            $lastStatusCode = $metric->last_status_code ?? null;

            $status = 'unknown';
            if ($lastStatusCode !== null) {
                $status = $lastStatusCode >= 500 ? 'failing' : 'healthy';
            }

            $requestCount = (int) ($metric->request_count ?? 0);
            $successCount = (int) ($metric->success_count ?? 0);

            $rows[] = [
                'route_key' => $routeKey,
                'method' => $method,
                'uri' => '/'.$uri,
                'name' => $name,
                'status' => $status,
                'request_count' => $requestCount,
                'success_count' => $successCount,
                'failure_count' => (int) ($metric->failure_count ?? 0),
                'uptime_percent' => $requestCount > 0 ? round(($successCount / $requestCount) * 100, 2) : null,
                'last_status_code' => $lastStatusCode,
                'avg_response_time_ms' => round((float) ($metric->avg_response_time_ms ?? 0), 2),
                'last_response_at' => optional($metric?->last_response_at)->toDateTimeString(),
                'last_failure_at' => optional($metric?->last_failure_at)->toDateTimeString(),
            ];
        }

        usort($rows, fn (array $a, array $b): int => [$a['uri'], $a['method']] <=> [$b['uri'], $b['method']]);

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function buildSummary(array $rows): array
    {
        $summary = [
            'total' => count($rows),
            'healthy' => 0,
            'failing' => 0,
            'unknown' => 0,
            'total_requests' => 0,
        ];

        foreach ($rows as $row) {
            $status = (string) $row['status'];
            if (array_key_exists($status, $summary)) {
                $summary[$status]++;
            }
            $summary['total_requests'] += (int) $row['request_count'];
        }

        return $summary;
    }

    private function isApiRoute(string $uri, ?string $name, string $method): bool
    {
        if (Str::startsWith($uri, ['_debugbar', '_ignition', 'livewire', 'sanctum/csrf-cookie', 'admin/system/api-monitoring'])) {
            return false;
        }

        if (Str::startsWith($uri, 'api/')
            || Str::contains($uri, '/api/')
            || Str::endsWith($uri, '/api')
            || (($name !== null) && Str::contains($name, '.api'))) {
            return true;
        }

        if ($name === null) {
            return false;
        }

        return Str::contains($name, [
            '.data',
            '.stats',
            '.metrics',
            '.health',
            '.count',
            '.unread',
            '.toggle',
            '.mark',
            '.upload',
            '.download',
            '.import',
            '.export',
            '.presign',
            '.messages',
            '.typing',
            '.details',
            '.history',
            '.preview',
            '.calendar',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function getAllowedRouteKeys(): array
    {
        $stored = Setting::get('external_api_allowed_route_keys', []);

        if (is_array($stored)) {
            return array_values(array_filter($stored, fn ($item) => is_string($item) && trim($item) !== ''));
        }

        return [];
    }
}
