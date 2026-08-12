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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ApiMonitoringController extends Controller
{
    /** Rolling window for uptime sparklines (daily buckets). */
    private const UPTIME_MONITOR_DAYS = 30;

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
            'newApiKeyPlainText' => session('new_external_api_key') ?: old('generated_api_key'),
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
            'key_prefix' => substr($plainKey, 0, 12),
            'key_hash' => hash('sha256', $plainKey),
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.system.api-monitoring.index', request()->only('scope'))
            ->with('success', 'External API key generated. Copy it now — it will not be shown again.')
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
        $uptimeSince = now()->subDays(self::UPTIME_MONITOR_DAYS - 1)->startOfDay();
        $dayBucketSql = $this->dailyBucketSqlExpression();

        $dailyBucketsByRoute = ApiEndpointMetricPoint::query()
            ->where('recorded_at', '>=', $uptimeSince)
            ->select([
                'route_key',
                DB::raw("{$dayBucketSql} as day_bucket"),
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN is_success THEN 1 ELSE 0 END) as success_count'),
            ])
            ->groupBy('route_key', DB::raw($dayBucketSql))
            ->get()
            ->groupBy('route_key')
            ->map(function ($rows) {
                $bucket = [];
                foreach ($rows as $row) {
                    $dayValue = $row->day_bucket ?? null;
                    $dayKey = $dayValue ? \Illuminate\Support\Carbon::parse($dayValue)->startOfDay()->format('Y-m-d 00:00:00') : '';
                    if ($dayKey === '') {
                        continue;
                    }

                    $bucket[$dayKey] = [
                        'total' => (int) ($row->total_count ?? 0),
                        'success' => (int) ($row->success_count ?? 0),
                    ];
                }

                return $bucket;
            });

        $dayKeys = collect(range(self::UPTIME_MONITOR_DAYS - 1, 0))->map(function (int $daysAgo) {
            return now()->subDays($daysAgo)->startOfDay()->format('Y-m-d 00:00:00');
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
                'uptime_points' => $this->buildDailyUptimePoints($dailyBucketsByRoute->get($routeKey, []), $dayKeys),
                'uptime_stats' => $this->buildDailyUptimeStats($dailyBucketsByRoute->get($routeKey, []), $dayKeys),
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
     * SQL expression to bucket recorded_at by calendar day (driver-specific).
     */
    private function dailyBucketSqlExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "DATE_TRUNC('day', recorded_at)",
            'sqlite' => 'date(recorded_at)',
            default => 'DATE(recorded_at)',
        };
    }

    /**
     * @param  array<string, array{total: int, success: int}>  $daily
     * @param  array<int, string>  $dayKeys
     * @return array<int, float|null>
     */
    private function buildDailyUptimePoints(array $daily, array $dayKeys): array
    {
        return collect($dayKeys)->map(function (string $dayKey) use ($daily) {
            if (! isset($daily[$dayKey]) || $daily[$dayKey]['total'] === 0) {
                return null;
            }

            return round(($daily[$dayKey]['success'] / $daily[$dayKey]['total']) * 100, 2);
        })->all();
    }

    /**
     * @param  array<string, array{total: int, success: int}>  $daily
     * @param  array<int, string>  $dayKeys
     * @return array<string, float|int|null>
     */
    private function buildDailyUptimeStats(array $daily, array $dayKeys): array
    {
        $uptimePoints = $this->buildDailyUptimePoints($daily, $dayKeys);
        $valid = collect($uptimePoints)->filter(fn ($value) => $value !== null)->values();

        if ($valid->isEmpty()) {
            return [
                'avg' => null,
                'min' => null,
                'max' => null,
                'days_with_traffic' => 0,
            ];
        }

        return [
            'avg' => round((float) $valid->avg(), 2),
            'min' => round((float) $valid->min(), 2),
            'max' => round((float) $valid->max(), 2),
            'days_with_traffic' => $valid->count(),
        ];
    }
}
