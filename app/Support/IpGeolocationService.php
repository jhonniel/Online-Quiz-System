<?php

namespace App\Support;

use App\Jobs\ResolveIpGeolocationBatchJob;
use App\Models\IpGeolocation;
use App\Models\UserActivity;
use Illuminate\Support\Collection;

final class IpGeolocationService
{
    private const MAX_SYNC_LOOKUPS_PER_REQUEST = 50;

    /**
     * Store coordinates on the activity row and in ip_geolocations when the IP is known.
     */
    public static function attachCoordinatesToActivity(UserActivity $activity): void
    {
        $ip = trim((string) ($activity->ip_address ?? ''));
        if ($ip === '' || TomTomService::isPrivateIp($ip)) {
            return;
        }

        if ($activity->latitude !== null && $activity->longitude !== null) {
            return;
        }

        $stored = IpGeolocation::query()->where('ip_address', $ip)->first();
        if ($stored instanceof IpGeolocation) {
            self::updateActivityCoordinates($activity, self::coordsFromModel($stored));

            return;
        }

        ResolveIpGeolocationBatchJob::dispatch([$ip]);
    }

    /**
     * @return array{lat: float, lng: float, label: string}|null
     */
    public static function resolve(string $ip): ?array
    {
        $ip = trim($ip);
        if ($ip === '' || TomTomService::isPrivateIp($ip)) {
            return null;
        }

        $stored = IpGeolocation::query()->where('ip_address', $ip)->first();
        if ($stored instanceof IpGeolocation) {
            return self::coordsFromModel($stored);
        }

        $coords = TomTomService::lookupIpCoordinates($ip);
        if ($coords === null) {
            return null;
        }

        self::persist($ip, $coords);

        return $coords;
    }

    /**
     * Resolve many IPs using stored coordinates first, then live lookups for missing public IPs.
     *
     * @param  list<string>  $ips
     * @return array{
     *     coordinates: array<string, array{lat: float, lng: float, label: string}>,
     *     queued: int
     * }
     */
    public static function resolveMany(array $ips, int $maxNewLookups = self::MAX_SYNC_LOOKUPS_PER_REQUEST): array
    {
        $ips = collect($ips)
            ->map(fn ($ip) => trim((string) $ip))
            ->filter()
            ->unique()
            ->values();

        if ($ips->isEmpty()) {
            return [
                'coordinates' => [],
                'queued' => 0,
            ];
        }

        $publicIps = $ips
            ->filter(fn (string $ip) => ! TomTomService::isPrivateIp($ip))
            ->values();

        $stored = IpGeolocation::query()
            ->whereIn('ip_address', $publicIps->all())
            ->get()
            ->keyBy('ip_address');

        $results = [];
        foreach ($stored as $ip => $row) {
            $results[$ip] = self::coordsFromModel($row);
        }

        $missing = $publicIps
            ->reject(fn (string $ip) => isset($results[$ip]))
            ->values();

        if ($missing->isEmpty()) {
            return [
                'coordinates' => $results,
                'queued' => 0,
            ];
        }

        $syncBatch = $missing->take($maxNewLookups);
        $deferred = $missing->slice($maxNewLookups)->values();

        foreach ($syncBatch as $ip) {
            $coords = self::resolve($ip);
            if ($coords !== null) {
                $results[$ip] = $coords;
            }
        }

        if ($deferred->isNotEmpty()) {
            ResolveIpGeolocationBatchJob::dispatch($deferred->all());
        }

        return [
            'coordinates' => $results,
            'queued' => $deferred->count(),
        ];
    }

    /**
     * Queue background resolution for IPs seen in activity logs.
     *
     * @param  list<string>  $ips
     */
    public static function queueUnresolved(array $ips): void
    {
        $ips = collect($ips)
            ->map(fn ($ip) => trim((string) $ip))
            ->filter(fn (string $ip) => $ip !== '' && ! TomTomService::isPrivateIp($ip))
            ->unique()
            ->values();

        if ($ips->isEmpty()) {
            return;
        }

        $known = IpGeolocation::query()
            ->whereIn('ip_address', $ips->all())
            ->pluck('ip_address')
            ->all();

        $missing = $ips->reject(fn (string $ip) => in_array($ip, $known, true))->values()->all();

        if ($missing !== []) {
            ResolveIpGeolocationBatchJob::dispatch($missing);
        }
    }

    /**
     * @return array{lat: float, lng: float, label: string}
     */
    private static function coordsFromModel(IpGeolocation $row): array
    {
        return [
            'lat' => (float) $row->latitude,
            'lng' => (float) $row->longitude,
            'label' => (string) ($row->location_label ?: $row->ip_address),
        ];
    }

    /**
     * @param  array{lat: float, lng: float, label: string, source?: string}  $coords
     */
    private static function persist(string $ip, array $coords): void
    {
        IpGeolocation::query()->updateOrCreate(
            ['ip_address' => $ip],
            [
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],
                'location_label' => $coords['label'],
                'source' => $coords['source'] ?? 'ip_lookup',
                'resolved_at' => now(),
            ]
        );

        self::backfillActivitiesForIp($ip, $coords);
    }

    /**
     * @param  array{lat: float, lng: float, label: string}  $coords
     */
    private static function updateActivityCoordinates(UserActivity $activity, array $coords): void
    {
        if ($activity->latitude !== null && $activity->longitude !== null) {
            return;
        }

        $activity->updateQuietly([
            'latitude' => $coords['lat'],
            'longitude' => $coords['lng'],
            'location_label' => $coords['label'],
        ]);
    }

    /**
     * @param  array{lat: float, lng: float, label: string}  $coords
     */
    private static function backfillActivitiesForIp(string $ip, array $coords): void
    {
        UserActivity::query()
            ->where('ip_address', $ip)
            ->where(function ($query) {
                $query->whereNull('latitude')
                    ->orWhereNull('longitude');
            })
            ->update([
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],
                'location_label' => $coords['label'],
            ]);
    }

    /**
     * @return Collection<int, string>
     */
    public static function distinctActivityIpsMissingCoordinates(int $limit = 1000): Collection
    {
        return UserActivity::query()
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->distinct()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->pluck('ip_address')
            ->map(fn ($ip) => trim((string) $ip))
            ->filter(fn (string $ip) => $ip !== '' && ! TomTomService::isPrivateIp($ip))
            ->unique()
            ->filter(function (string $ip) {
                return ! IpGeolocation::query()->where('ip_address', $ip)->exists();
            })
            ->values();
    }

    /**
     * Copy stored IP coordinates onto activity rows that are still missing them.
     */
    public static function backfillActivityCoordinatesFromStored(): int
    {
        $updated = 0;

        IpGeolocation::query()
            ->orderBy('ip_address')
            ->chunkById(100, function ($rows) use (&$updated) {
                foreach ($rows as $row) {
                    if (! $row instanceof IpGeolocation) {
                        continue;
                    }

                    $updated += UserActivity::query()
                        ->where('ip_address', $row->ip_address)
                        ->where(function ($query) {
                            $query->whereNull('latitude')
                                ->orWhereNull('longitude');
                        })
                        ->update([
                            'latitude' => $row->latitude,
                            'longitude' => $row->longitude,
                            'location_label' => $row->location_label,
                        ]);
                }
            });

        return $updated;
    }
}
