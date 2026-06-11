<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class TomTomService
{
    public static function apiKey(): ?string
    {
        $key = trim((string) Setting::get('tomtom_api_key', ''));

        return $key !== '' ? $key : null;
    }

    public static function hasApiKey(): bool
    {
        return self::apiKey() !== null;
    }

    public static function mapStyleUrl(): ?string
    {
        $key = self::apiKey();
        if ($key === null) {
            return null;
        }

        return 'https://api.tomtom.com/style/1/style/22.2.1-9?'
            .http_build_query([
                'key' => $key,
                'map' => '2/basic_street-light',
            ]);
    }

    /**
     * @return array{lat: float, lng: float, label: string}|null
     */
    public static function geocodeIp(string $ip): ?array
    {
        $ip = trim($ip);
        if ($ip === '' || self::isPrivateIp($ip)) {
            return null;
        }

        $cacheKey = 'user-map:ip:'.hash('sha256', $ip);
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached === false ? null : $cached;
        }

        $result = self::fetchIpGeolocation($ip);
        Cache::put(
            $cacheKey,
            $result ?? false,
            now()->addDays($result !== null ? 7 : 1)
        );

        return $result;
    }

    /**
     * @return array{lat: float, lng: float, label: string}|null
     */
    private static function fetchIpGeolocation(string $ip): ?array
    {
        $providers = [
            fn () => self::geocodeIpViaIpWhoIs($ip),
            fn () => self::geocodeIpViaIpApiCo($ip),
            fn () => self::geocodeIpViaIpApiCom($ip),
        ];

        foreach ($providers as $provider) {
            try {
                $result = $provider();
                if ($result !== null) {
                    return $result;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    /**
     * @return array{lat: float, lng: float, label: string}|null
     */
    private static function geocodeIpViaIpWhoIs(string $ip): ?array
    {
        $response = Http::connectTimeout(3)
            ->timeout(4)
            ->acceptJson()
            ->get('https://ipwho.is/'.rawurlencode($ip));

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        if (($data['success'] ?? false) !== true) {
            return null;
        }

        return self::normalizeIpGeocodeResult(
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            [
                $data['city'] ?? null,
                $data['region'] ?? null,
                $data['country'] ?? null,
            ],
            $ip
        );
    }

    /**
     * @return array{lat: float, lng: float, label: string}|null
     */
    private static function geocodeIpViaIpApiCo(string $ip): ?array
    {
        $response = Http::connectTimeout(3)
            ->timeout(4)
            ->acceptJson()
            ->get('https://ipapi.co/'.rawurlencode($ip).'/json/');

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        if (isset($data['error']) || isset($data['reason'])) {
            return null;
        }

        return self::normalizeIpGeocodeResult(
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            [
                $data['city'] ?? null,
                $data['region'] ?? null,
                $data['country_name'] ?? null,
            ],
            $ip
        );
    }

    /**
     * @return array{lat: float, lng: float, label: string}|null
     */
    private static function geocodeIpViaIpApiCom(string $ip): ?array
    {
        $response = Http::connectTimeout(2)
            ->timeout(4)
            ->acceptJson()
            ->get('http://ip-api.com/json/'.rawurlencode($ip), [
                'fields' => 'status,message,lat,lon,city,regionName,country',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        if (($data['status'] ?? '') !== 'success') {
            return null;
        }

        return self::normalizeIpGeocodeResult(
            $data['lat'] ?? null,
            $data['lon'] ?? null,
            [
                $data['city'] ?? null,
                $data['regionName'] ?? null,
                $data['country'] ?? null,
            ],
            $ip
        );
    }

    /**
     * @param  list<mixed>  $labelParts
     * @return array{lat: float, lng: float, label: string}|null
     */
    private static function normalizeIpGeocodeResult(mixed $lat, mixed $lng, array $labelParts, string $ip): ?array
    {
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        $parts = array_values(array_filter($labelParts, fn ($part) => is_string($part) && trim($part) !== ''));

        return [
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'label' => $parts !== [] ? implode(', ', $parts) : $ip,
        ];
    }

    /**
     * @return array{lat: float, lng: float, label: string}|null
     */
    public static function geocodeQuery(string $query): ?array
    {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $cacheKey = 'tomtom:query:'.hash('sha256', Str::lower($query));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($query) {
            $key = self::apiKey();
            if ($key === null) {
                return null;
            }

            $response = Http::timeout(8)->get(
                'https://api.tomtom.com/search/2/geocode/'.rawurlencode($query).'.json',
                [
                    'key' => $key,
                    'limit' => 1,
                ]
            );

            if (! $response->successful()) {
                return null;
            }

            $result = data_get($response->json(), 'results.0');
            if (! is_array($result)) {
                return null;
            }

            $lat = data_get($result, 'position.lat');
            $lng = data_get($result, 'position.lon', data_get($result, 'position.lng'));
            if (! is_numeric($lat) || ! is_numeric($lng)) {
                return null;
            }

            return [
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'label' => (string) data_get($result, 'address.freeformAddress', $query),
            ];
        });
    }

    /**
     * @return array{lat: float, lng: float, label: string}|null
     */
    public static function reverseGeocode(float $lat, float $lng): ?array
    {
        $cacheKey = 'tomtom:reverse:'.round($lat, 5).':'.round($lng, 5);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($lat, $lng) {
            $key = self::apiKey();
            if ($key === null) {
                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'label' => sprintf('%.5f, %.5f', $lat, $lng),
                ];
            }

            $response = Http::timeout(8)->get(
                sprintf('https://api.tomtom.com/search/2/reverseGeocode/%.6f,%.6f.json', $lat, $lng),
                ['key' => $key]
            );

            if (! $response->successful()) {
                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'label' => sprintf('%.5f, %.5f', $lat, $lng),
                ];
            }

            $result = data_get($response->json(), 'addresses.0');
            if (! is_array($result)) {
                return [
                    'lat' => $lat,
                    'lng' => $lng,
                    'label' => sprintf('%.5f, %.5f', $lat, $lng),
                ];
            }

            return [
                'lat' => $lat,
                'lng' => $lng,
                'label' => (string) data_get($result, 'address.freeformAddress', sprintf('%.5f, %.5f', $lat, $lng)),
            ];
        });
    }

    public static function isPrivateIp(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }

        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
