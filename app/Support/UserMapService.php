<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserGeoLocation;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class UserMapService
{
    private const MAX_IPS = 500;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     markers: list<array<string, mixed>>,
     *     stats: array{total_ips: int, mapped: int, unmapped: int, online: int, private_skipped: int},
     *     filters: array{
     *         departments: list<array{id: int, name: string}>,
     *         universities: list<array{id: int, name: string}>,
     *         activity_types: list<string>
     *     }
     * }
     */
    public static function build(array $filters = []): array
    {
        $onlineUserIds = UserSession::query()
            ->where('status', 'active')
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->pluck('user_id')
            ->unique()
            ->all();

        $onlineSet = array_fill_keys($onlineUserIds, true);

        $baseQuery = self::activityQuery($filters);

        $ipRows = (clone $baseQuery)
            ->selectRaw('ip_address, COUNT(*) as hit_count, MAX(created_at) as last_seen_at')
            ->groupBy('ip_address')
            ->orderByDesc('last_seen_at')
            ->get();

        $privateSkipped = 0;
        $ipRows = $ipRows->filter(function ($row) use (&$privateSkipped) {
            $ip = trim((string) $row->ip_address);
            if ($ip === '' || TomTomService::isPrivateIp($ip)) {
                $privateSkipped++;

                return false;
            }

            return true;
        })->values();

        if (! empty($filters['online_only'])) {
            $onlineIps = (clone $baseQuery)
                ->whereNotNull('user_id')
                ->whereIn('user_id', $onlineUserIds ?: [0])
                ->distinct()
                ->pluck('ip_address')
                ->map(fn ($ip) => trim((string) $ip))
                ->filter()
                ->all();

            $onlineIpSet = array_fill_keys($onlineIps, true);
            $ipRows = $ipRows->filter(fn ($row) => isset($onlineIpSet[trim((string) $row->ip_address)]))->values();
        }

        $totalIps = $ipRows->count();
        $ipRows = $ipRows->take(self::MAX_IPS);

        $ipAddresses = $ipRows->pluck('ip_address')->map(fn ($ip) => trim((string) $ip))->all();

        $usersByIp = self::usersByIp($ipAddresses, $onlineSet);
        $gpsMarkers = self::buildGpsMarkers($filters, $onlineSet);
        $gpsUserIds = collect($gpsMarkers)
            ->flatMap(fn (array $marker) => collect($marker['users'] ?? [])->pluck('id'))
            ->unique()
            ->all();
        $locationBuckets = [];
        $markers = [];
        $mapped = 0;
        $onlinePins = 0;

        foreach ($ipRows as $row) {
            $ip = trim((string) $row->ip_address);
            $coords = TomTomService::geocodeIp($ip);
            if ($coords === null) {
                continue;
            }

            $users = $usersByIp[$ip] ?? [];
            if ($users !== [] && collect($users)->every(fn (array $user) => in_array($user['id'], $gpsUserIds, true))) {
                continue;
            }
            $isOnline = collect($users)->contains(fn (array $user) => $user['is_online']);

            $bucketKey = round($coords['lat'], 3).':'.round($coords['lng'], 3);
            $locationBuckets[$bucketKey] = ($locationBuckets[$bucketKey] ?? 0) + 1;
            $offsetIndex = $locationBuckets[$bucketKey] - 1;
            [$lat, $lng] = self::applyJitter($coords['lat'], $coords['lng'], $offsetIndex);

            $mapped++;
            if ($isOnline) {
                $onlinePins++;
            }

            $primaryUser = $users[0] ?? null;

            $markers[] = [
                'id' => 'ip:'.md5($ip),
                'ip_address' => $ip,
                'name' => $primaryUser['name'] ?? ('IP '.$ip),
                'email' => $primaryUser['email'] ?? null,
                'role' => $primaryUser['role'] ?? null,
                'role_label' => $primaryUser['role_label'] ?? 'Activity log',
                'department' => $primaryUser['department'] ?? null,
                'university' => $primaryUser['university'] ?? null,
                'lat' => $lat,
                'lng' => $lng,
                'location_label' => $coords['label'],
                'location_source' => 'activity_log',
                'is_online' => $isOnline,
                'hit_count' => (int) $row->hit_count,
                'last_seen_at' => Carbon::parse($row->last_seen_at)->toIso8601String(),
                'last_seen_human' => Carbon::parse($row->last_seen_at)->diffForHumans(),
                'users' => $users,
                'user_count' => count($users),
                'activity_log_url' => url('/admin/user-activity?ip_address='.urlencode($ip)),
                'admin_url' => $primaryUser['admin_url'] ?? null,
            ];
        }

        $onlineGpsPins = collect($gpsMarkers)->filter(fn (array $marker) => (bool) ($marker['is_online'] ?? false))->count();

        return [
            'markers' => array_merge($gpsMarkers, $markers),
            'stats' => [
                'total_ips' => $totalIps,
                'mapped' => $mapped + count($gpsMarkers),
                'unmapped' => max(0, $totalIps - $mapped),
                'online' => $onlinePins + $onlineGpsPins,
                'gps_pins' => count($gpsMarkers),
                'private_skipped' => $privateSkipped,
                'capped' => $totalIps > self::MAX_IPS,
            ],
            'filters' => [
                'departments' => self::departmentOptions(),
                'universities' => self::universityOptions(),
                'activity_types' => self::activityTypeOptions(),
            ],
        ];
    }

  /**
     * @param  array<string, mixed>  $filters
     */
    private static function activityQuery(array $filters): Builder
    {
        $query = UserActivity::query()
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '');

        if (! empty($filters['activity_type'])) {
            $query->where('activity_type', $filters['activity_type']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['ip_address'])) {
            $query->where('ip_address', 'like', '%'.trim((string) $filters['ip_address']).'%');
        }

        $hasUserFilters = ! empty($filters['search'])
            || ! empty($filters['role'])
            || ! empty($filters['department_id'])
            || ! empty($filters['university_id']);

        if ($hasUserFilters) {
            $query->whereHas('user', function (Builder $userQuery) use ($filters) {
                self::applyUserFilters($userQuery, $filters);
            });
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private static function applyUserFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', (int) $filters['department_id']);
        }

        if (! empty($filters['university_id'])) {
            $query->where('university_id', (int) $filters['university_id']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
    }

    /**
     * @param  list<string>  $ipAddresses
     * @param  array<int, bool>  $onlineSet
     * @return array<string, list<array<string, mixed>>>
     */
    private static function usersByIp(array $ipAddresses, array $onlineSet): array
    {
        if ($ipAddresses === []) {
            return [];
        }

        $pairs = UserActivity::query()
            ->whereIn('ip_address', $ipAddresses)
            ->whereNotNull('user_id')
            ->selectRaw('ip_address, user_id, MAX(created_at) as last_seen_at')
            ->groupBy('ip_address', 'user_id')
            ->orderByDesc('last_seen_at')
            ->get();

        $userIds = $pairs->pluck('user_id')->unique()->filter()->values()->all();

        $users = User::query()
            ->whereIn('id', $userIds)
            ->with([
                'department:id,name',
                'university:id,name',
            ])
            ->get(['id', 'name', 'email', 'role', 'department_id', 'university_id'])
            ->keyBy('id');

        $map = [];

        foreach ($pairs as $pair) {
            $ip = trim((string) $pair->ip_address);
            $user = $users->get((int) $pair->user_id);
            if (! $user instanceof User) {
                continue;
            }

            $map[$ip] ??= [];

            if (collect($map[$ip])->contains(fn (array $entry) => $entry['id'] === $user->id)) {
                continue;
            }

            $map[$ip][] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $user->getRoleLabel(),
                'department' => $user->department?->name,
                'university' => $user->university?->name,
                'is_online' => isset($onlineSet[$user->id]),
                'admin_url' => url('/admin/users/'.$user->id),
            ];
        }

        return $map;
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private static function departmentOptions(): array
    {
        return User::query()
            ->whereIn('id', UserActivity::query()->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->whereNotNull('department_id')
            ->with('department:id,name')
            ->get(['department_id'])
            ->pluck('department')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->map(fn ($department) => ['id' => (int) $department->id, 'name' => (string) $department->name])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private static function universityOptions(): array
    {
        return User::query()
            ->whereIn('id', UserActivity::query()->whereNotNull('user_id')->distinct()->pluck('user_id'))
            ->whereNotNull('university_id')
            ->with('university:id,name')
            ->get(['university_id'])
            ->pluck('university')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->map(fn ($university) => ['id' => (int) $university->id, 'name' => (string) $university->name])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private static function activityTypeOptions(): array
    {
        return UserActivity::query()
            ->whereNotNull('ip_address')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int, bool>  $onlineSet
     * @return list<array<string, mixed>>
     */
    private static function buildGpsMarkers(array $filters, array $onlineSet): array
    {
        $query = UserGeoLocation::query()
            ->with([
                'user:id,name,email,role,department_id,university_id',
                'user.department:id,name',
                'user.university:id,name',
            ])
            ->whereHas('user', function (Builder $userQuery) use ($filters) {
                self::applyUserFilters($userQuery, $filters);
            });

        if (! empty($filters['date_from'])) {
            $query->whereDate('captured_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('captured_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['online_only'])) {
            $onlineIds = array_keys(array_filter($onlineSet));
            $query->whereIn('user_id', $onlineIds ?: [0]);
        }

        $latestByUser = [];
        foreach ($query->orderByDesc('captured_at')->get() as $location) {
            $latestByUser[$location->user_id] ??= $location;
        }

        $markers = [];

        foreach ($latestByUser as $location) {
            $user = $location->user;
            if (! $user instanceof User) {
                continue;
            }

            $lat = (float) $location->latitude;
            $lng = (float) $location->longitude;
            $reverse = TomTomService::reverseGeocode($lat, $lng);
            $isOnline = isset($onlineSet[$user->id]);

            $markers[] = [
                'id' => 'gps:'.$user->id,
                'ip_address' => $location->ip_address,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $user->getRoleLabel(),
                'department' => $user->department?->name,
                'university' => $user->university?->name,
                'lat' => $lat,
                'lng' => $lng,
                'location_label' => $reverse['label'] ?? sprintf('%.5f, %.5f', $lat, $lng),
                'location_source' => 'browser_gps',
                'accuracy_meters' => $location->accuracy,
                'is_online' => $isOnline,
                'hit_count' => 1,
                'last_seen_at' => $location->captured_at?->toIso8601String(),
                'last_seen_human' => $location->captured_at?->diffForHumans(),
                'users' => [[
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_label' => $user->getRoleLabel(),
                    'department' => $user->department?->name,
                    'university' => $user->university?->name,
                    'is_online' => $isOnline,
                    'admin_url' => url('/admin/users/'.$user->id),
                ]],
                'user_count' => 1,
                'activity_log_url' => url('/admin/user-activity?search='.urlencode($user->email)),
                'admin_url' => url('/admin/users/'.$user->id),
            ];
        }

        return $markers;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function applyJitter(float $lat, float $lng, int $index): array
    {
        if ($index === 0) {
            return [$lat, $lng];
        }

        $angle = ($index * 137.508) * (M_PI / 180);
        $radius = 0.012 * min($index, 6);

        return [
            $lat + ($radius * cos($angle)),
            $lng + ($radius * sin($angle)),
        ];
    }
}
