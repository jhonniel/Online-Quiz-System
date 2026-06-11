<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Builder;
final class UserMapService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     markers: list<array<string, mixed>>,
     *     stats: array{total_users: int, mapped: int, unmapped: int, online: int},
     *     filters: array{departments: list<array{id: int, name: string}>, universities: list<array{id: int, name: string}>}
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

        $query = User::query()
            ->where('is_active', true)
            ->with([
                'department:id,name',
                'university:id,name,location',
            ])
            ->orderBy('name');

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

        if (! empty($filters['online_only'])) {
            $query->whereIn('id', $onlineUserIds ?: [0]);
        }

        $users = $query->get(['id', 'name', 'email', 'role', 'department_id', 'university_id', 'profile_picture']);

        $userIds = $users->pluck('id')->all();

        $latestSessionIps = self::latestSessionIpsFor($userIds);
        $latestActivityIps = self::latestActivityIpsFor($userIds);

        $locationBuckets = [];
        $markers = [];
        $mapped = 0;

        foreach ($users as $user) {
            $coords = self::resolveCoordinates(
                $user,
                $latestSessionIps[$user->id] ?? null,
                $latestActivityIps[$user->id] ?? null
            );

            if ($coords === null) {
                continue;
            }

            $bucketKey = round($coords['lat'], 3).':'.round($coords['lng'], 3);
            $locationBuckets[$bucketKey] = ($locationBuckets[$bucketKey] ?? 0) + 1;
            $offsetIndex = $locationBuckets[$bucketKey] - 1;
            [$lat, $lng] = self::applyJitter($coords['lat'], $coords['lng'], $offsetIndex);

            $mapped++;
            $markers[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_label' => $user->getRoleLabel(),
                'department' => $user->department?->name,
                'university' => $user->university?->name,
                'lat' => $lat,
                'lng' => $lng,
                'location_label' => $coords['label'],
                'location_source' => $coords['source'],
                'is_online' => isset($onlineSet[$user->id]),
                'profile_picture_url' => $user->profile_picture ? $user->getProfilePictureUrl() : null,
                'initials' => $user->getInitials(),
                'admin_url' => url('/admin/users/'.$user->id),
            ];
        }

        return [
            'markers' => $markers,
            'stats' => [
                'total_users' => $users->count(),
                'mapped' => $mapped,
                'unmapped' => max(0, $users->count() - $mapped),
                'online' => $users->where(fn (User $user) => isset($onlineSet[$user->id]))->count(),
            ],
            'filters' => [
                'departments' => self::departmentOptions(),
                'universities' => self::universityOptions(),
            ],
        ];
    }

    /**
     * @return array{lat: float, lng: float, label: string, source: string}|null
     */
    private static function resolveCoordinates(User $user, ?string $sessionIp, ?string $activityIp): ?array
    {
        foreach ([$sessionIp, $activityIp] as $ip) {
            if (! is_string($ip) || $ip === '') {
                continue;
            }

            $resolved = TomTomService::geocodeIp($ip);
            if ($resolved !== null) {
                return [
                    'lat' => $resolved['lat'],
                    'lng' => $resolved['lng'],
                    'label' => $resolved['label'],
                    'source' => 'ip',
                ];
            }
        }

        $universityLocation = trim((string) ($user->university?->location ?? ''));
        if ($universityLocation !== '') {
            $resolved = TomTomService::geocodeQuery($universityLocation);
            if ($resolved !== null) {
                return [
                    'lat' => $resolved['lat'],
                    'lng' => $resolved['lng'],
                    'label' => $resolved['label'],
                    'source' => 'university',
                ];
            }
        }

        return null;
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, string>
     */
    private static function latestSessionIpsFor(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return UserSession::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('ip_address')
            ->orderByDesc('last_activity_at')
            ->get(['user_id', 'ip_address'])
            ->unique('user_id')
            ->mapWithKeys(fn ($session) => [(int) $session->user_id => (string) $session->ip_address])
            ->all();
    }

    /**
     * @param  list<int>  $userIds
     * @return array<int, string>
     */
    private static function latestActivityIpsFor(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return UserActivity::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('ip_address')
            ->orderByDesc('created_at')
            ->get(['user_id', 'ip_address'])
            ->unique('user_id')
            ->mapWithKeys(fn ($activity) => [(int) $activity->user_id => (string) $activity->ip_address])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private static function departmentOptions(): array
    {
        return User::query()
            ->where('is_active', true)
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
            ->where('is_active', true)
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
