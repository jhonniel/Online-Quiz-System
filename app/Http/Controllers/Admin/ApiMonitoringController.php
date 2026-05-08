<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiEndpointMetric;
use App\Models\ApiEndpointMetricPoint;
use App\Models\ExternalApiKey;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ApiMonitoringController extends Controller
{
    public function index()
    {
        $scope = request()->query('scope', 'all');
        if (! in_array($scope, ['api_like', 'all'], true)) {
            $scope = 'all';
        }

        $rows = $this->buildApiRows($scope);

        return view('admin.system.api-monitoring', [
            'rows' => $rows,
            'summary' => $this->buildSummary($rows),
            'scope' => $scope,
            'externalApiEnabled' => Setting::get('external_api_enabled', 'disabled') === 'enabled',
            'externalApiKeys' => ExternalApiKey::query()->latest()->get(),
            'newApiKeyPlainText' => session('new_external_api_key'),
            'externalAllowedRouteKeys' => $this->getExternalAllowedRouteKeys(),
        ]);
    }

    public function metrics(): JsonResponse
    {
        $scope = request()->query('scope', 'all');
        if (! in_array($scope, ['api_like', 'all'], true)) {
            $scope = 'all';
        }

        $rows = $this->buildApiRows($scope);

        return response()->json([
            'summary' => $this->buildSummary($rows),
            'rows' => $rows,
            'scope' => $scope,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function updateExternalAccess(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'external_api_enabled' => ['required', 'in:enabled,disabled'],
        ]);

        Setting::set(
            'external_api_enabled',
            $validated['external_api_enabled'],
            'text',
            'Enable or disable external API access'
        );

        return back()->with('success', 'External API access setting updated.');
    }

    public function createApiKey(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $plainKey = 'oqs_'.Str::random(48);
        ExternalApiKey::query()->create([
            'name' => $validated['name'],
            'key_hash' => hash('sha256', $plainKey),
            'is_active' => true,
        ]);

        return back()
            ->with('success', 'External API key generated.')
            ->with('new_external_api_key', $plainKey);
    }

    public function revokeApiKey(ExternalApiKey $key): RedirectResponse
    {
        $key->forceFill(['is_active' => false])->save();

        return back()->with('success', 'API key revoked.');
    }

    public function updateExternalAllowedApis(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'allowed_route_keys' => ['nullable', 'array'],
            'allowed_route_keys.*' => ['string', 'max:255'],
        ]);

        $allowed = collect($validated['allowed_route_keys'] ?? [])
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn (string $value) => trim($value))
            ->unique()
            ->values()
            ->all();

        Setting::set(
            'external_api_allowed_route_keys',
            $allowed,
            'json',
            'Allowed API route keys for external API access'
        );

        return back()->with('success', 'Allowed external APIs updated.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildApiRows(string $scope = 'api_like'): array
    {
        $metrics = ApiEndpointMetric::query()->get()->keyBy('route_key');
        $hourlyPointsByRoute = ApiEndpointMetricPoint::query()
            ->where('recorded_at', '>=', now()->subDay())
            ->orderByDesc('recorded_at')
            ->limit(4000)
            ->get()
            ->groupBy('route_key');

        $hourKeys = collect(range(23, 0))->map(function (int $hoursAgo) {
            return now()->subHours($hoursAgo)->format('Y-m-d H:00:00');
        })->values()->all();
        $rows = [];
        foreach (Route::getRoutes() as $route) {
            $uri = trim((string) $route->uri(), '/');
            $name = $route->getName();
            $methods = array_values(array_diff($route->methods() ?? [], ['HEAD', 'OPTIONS']));
            $method = strtoupper($methods[0] ?? 'GET');

            if (! $this->shouldIncludeRoute($uri, $name, $method, $scope)) {
                continue;
            }

            $routeKey = $method.' '.$uri;
            $metric = $metrics->get($routeKey);

            $requestCount = (int) ($metric->request_count ?? 0);
            $successCount = (int) ($metric->success_count ?? 0);
            $failureCount = (int) ($metric->failure_count ?? 0);
            $lastStatusCode = $metric->last_status_code ?? null;

            $status = 'unknown';
            $effectiveStatusCode = $lastStatusCode;
            if ($effectiveStatusCode !== null) {
                $status = $effectiveStatusCode >= 500 ? 'failing' : 'healthy';
            }

            $rows[] = [
                'route_key' => $routeKey,
                'method' => $method,
                'uri' => '/'.$uri,
                'name' => $name,
                'status' => $status,
                'request_count' => $requestCount,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'uptime_percent' => $requestCount > 0 ? round(($successCount / $requestCount) * 100, 2) : null,
                'last_status_code' => $effectiveStatusCode,
                'avg_response_time_ms' => round((float) ($metric->avg_response_time_ms ?? 0), 2),
                'last_response_at' => optional($metric?->last_response_at)->toDateTimeString(),
                'last_failure_at' => optional($metric?->last_failure_at)->toDateTimeString(),
                'uptime_points' => $this->buildHourlyUptimePoints($hourlyPointsByRoute->get($routeKey, collect()), $hourKeys),
                'uptime_stats' => $this->buildHourlyUptimeStats($hourlyPointsByRoute->get($routeKey, collect()), $hourKeys),
            ];
        }

        usort($rows, function (array $a, array $b): int {
            return [$a['uri'], $a['method']] <=> [$b['uri'], $b['method']];
        });

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

    private function shouldIncludeRoute(string $uri, ?string $name, string $method, string $scope): bool
    {
        if (Str::startsWith($uri, ['_debugbar', '_ignition', 'livewire', 'sanctum/csrf-cookie', 'admin/system/api-monitoring'])) {
            return false;
        }

        if ($scope === 'all') {
            return true;
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
    private function getExternalAllowedRouteKeys(): array
    {
        $stored = Setting::get('external_api_allowed_route_keys', []);

        if (is_array($stored)) {
            return array_values(array_filter($stored, fn ($item) => is_string($item) && trim($item) !== ''));
        }

        return [];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ApiEndpointMetricPoint>  $points
     * @param  array<int, string>  $hourKeys
     * @return array<int, float|null>
     */
    private function buildHourlyUptimePoints($points, array $hourKeys): array
    {
        $hourly = [];
        foreach ($points as $point) {
            $hourKey = optional($point->recorded_at)->format('Y-m-d H:00:00');
            if (! $hourKey) {
                continue;
            }

            if (! isset($hourly[$hourKey])) {
                $hourly[$hourKey] = ['total' => 0, 'success' => 0];
            }

            $hourly[$hourKey]['total']++;
            if ($point->is_success) {
                $hourly[$hourKey]['success']++;
            }
        }

        return collect($hourKeys)->map(function (string $hourKey) use ($hourly) {
            if (! isset($hourly[$hourKey]) || $hourly[$hourKey]['total'] === 0) {
                return null;
            }

            return round(($hourly[$hourKey]['success'] / $hourly[$hourKey]['total']) * 100, 2);
        })->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ApiEndpointMetricPoint>  $points
     * @param  array<int, string>  $hourKeys
     * @return array<string, float|int|null>
     */
    private function buildHourlyUptimeStats($points, array $hourKeys): array
    {
        $uptimePoints = $this->buildHourlyUptimePoints($points, $hourKeys);
        $valid = collect($uptimePoints)->filter(fn ($value) => $value !== null)->values();

        if ($valid->isEmpty()) {
            return [
                'avg' => null,
                'min' => null,
                'max' => null,
                'hours_with_traffic' => 0,
            ];
        }

        return [
            'avg' => round((float) $valid->avg(), 2),
            'min' => round((float) $valid->min(), 2),
            'max' => round((float) $valid->max(), 2),
            'hours_with_traffic' => $valid->count(),
        ];
    }
}
