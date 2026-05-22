<?php

namespace App\Support;

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
}
