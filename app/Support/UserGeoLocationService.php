<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserGeoLocation;

final class UserGeoLocationService
{
    public static function record(
        User $user,
        float $latitude,
        float $longitude,
        ?float $accuracy = null,
        string $source = 'browser',
        string $context = 'login',
    ): UserGeoLocation {
        $location = UserGeoLocation::create([
            'user_id' => $user->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'source' => $source,
            'context' => $context,
            'ip_address' => request()->ip(),
            'captured_at' => now(),
        ]);

        UserActivity::logActivity($user, 'geo_location', $context, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'source' => $source,
        ]);

        return $location;
    }
}
