<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SystemHealthMetrics
{
    private const RECENT_BUCKETS_KEY = 'syshealth:recent_buckets';
    private const BUCKET_TTL_MINUTES = 20;
    private const MAX_RECENT_BUCKETS = 20; // ~20 minutes (1 bucket per minute)
    private const MAX_TOP_IPS = 15;
    private const MAX_TOP_PATHS = 12;

    /**
     * Track lightweight traffic + suspicious activity signals.
     *
     * Notes:
     * - "Reads" = GET/HEAD
     * - "Writes" = POST/PUT/PATCH/DELETE
     * - "Suspicious" signals are heuristics (not definitive intrusion detection).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Avoid counting the metrics endpoint to prevent recursive self-noise.
        // Keep counting /admin/settings/health so activity is visible while viewing System Health.
        if ($request->is('admin/settings/health-metrics')) {
            return $response;
        }

        try {
            $bucket = now()->format('YmdHi'); // per-minute bucket
            $this->rememberBucket($bucket);

            $method = strtoupper($request->method());
            $status = method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : 200;

            $isRead = in_array($method, ['GET', 'HEAD'], true);
            $isWrite = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);

            if ($isRead) {
                $this->inc("syshealth:bucket:$bucket:reads");
            } elseif ($isWrite) {
                $this->inc("syshealth:bucket:$bucket:writes");
            } else {
                $this->inc("syshealth:bucket:$bucket:other");
            }

            $this->inc("syshealth:bucket:$bucket:requests");

            if ($status >= 500) {
                $this->inc("syshealth:bucket:$bucket:errors_5xx");
            } elseif ($status === 429) {
                $this->inc("syshealth:bucket:$bucket:rate_limited");
            } elseif ($status === 404) {
                $this->inc("syshealth:bucket:$bucket:not_found");
            } elseif ($status === 401 || $status === 403) {
                $this->inc("syshealth:bucket:$bucket:auth_denied");
            }

            $ip = (string) ($request->ip() ?? 'unknown');
            $path = '/' . ltrim((string) $request->path(), '/');

            if ($status === 404) {
                $this->bumpMap("syshealth:bucket:$bucket:ip_404", $ip, self::MAX_TOP_IPS);
                $this->bumpMap("syshealth:bucket:$bucket:path_404", $path, self::MAX_TOP_PATHS);
            }

            if ($status === 401 || $status === 403) {
                $this->bumpMap("syshealth:bucket:$bucket:ip_auth_denied", $ip, self::MAX_TOP_IPS);
            }

            if ($status === 429) {
                $this->bumpMap("syshealth:bucket:$bucket:ip_rate_limited", $ip, self::MAX_TOP_IPS);
            }
        } catch (\Throwable $e) {
            // Never break requests because of metrics.
        }

        return $response;
    }

    private function rememberBucket(string $bucket): void
    {
        $buckets = Cache::get(self::RECENT_BUCKETS_KEY, []);
        if (!is_array($buckets)) {
            $buckets = [];
        }

        if (empty($buckets) || end($buckets) !== $bucket) {
            $buckets[] = $bucket;
            if (count($buckets) > self::MAX_RECENT_BUCKETS) {
                $buckets = array_slice($buckets, -self::MAX_RECENT_BUCKETS);
            }
            Cache::put(self::RECENT_BUCKETS_KEY, $buckets, now()->addMinutes(self::BUCKET_TTL_MINUTES));
        }
    }

    private function inc(string $key, int $by = 1): void
    {
        $current = Cache::get($key, 0);
        $current = is_numeric($current) ? (int) $current : 0;
        Cache::put($key, $current + $by, now()->addMinutes(self::BUCKET_TTL_MINUTES));
    }

    /**
     * Store small "top N" maps in cache.
     *
     * @param string $key cache key
     * @param string $entry map key
     * @param int $max keep only top N by count
     */
    private function bumpMap(string $key, string $entry, int $max): void
    {
        $map = Cache::get($key, []);
        if (!is_array($map)) {
            $map = [];
        }

        $map[$entry] = (int) (($map[$entry] ?? 0) + 1);
        arsort($map);
        if (count($map) > $max) {
            $map = array_slice($map, 0, $max, true);
        }

        Cache::put($key, $map, now()->addMinutes(self::BUCKET_TTL_MINUTES));
    }
}

