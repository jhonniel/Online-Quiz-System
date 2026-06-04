<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    private const PARSED_CACHE_KEY = 'settings.parsed';

    /** @var array<string, mixed>|null */
    private static ?array $requestParsedCache = null;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function get($key, $default = null)
    {
        $parsed = self::parsedSettings();

        if (! array_key_exists($key, $parsed)) {
            return $default;
        }

        $value = $parsed[$key];

        return $value === null ? $default : $value;
    }

    public static function set($key, $value, $type = 'text', $description = null)
    {
        // For string values (like seasonal_effects), store as plain string, not JSON
        // For array values, store as JSON
        $valueToStore = is_array($value) ? json_encode($value) : $value;

        $data = ['value' => $valueToStore];
        if ($type) {
            $data['type'] = $type;
        }
        if ($description) {
            $data['description'] = $description;
        }

        $setting = self::updateOrCreate(
            ['key' => $key],
            $data
        );

        self::clearCache();

        return $setting;
    }

    /**
     * Get all settings as an array keyed by setting key
     *
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        return self::parsedSettings();
    }

    /**
     * @return array<string, mixed>
     */
    public static function parsedSettings(): array
    {
        if (self::$requestParsedCache !== null) {
            return self::$requestParsedCache;
        }

        self::$requestParsedCache = Cache::remember(self::PARSED_CACHE_KEY, now()->addHour(), function () {
            $parsed = [];

            foreach (self::query()->get(['key', 'value']) as $setting) {
                $parsed[$setting->key] = self::parseRawValue($setting->getAttributes()['value'] ?? null);
            }

            return $parsed;
        });

        return self::$requestParsedCache;
    }

    public static function parseRawValue(mixed $rawValue): mixed
    {
        if ($rawValue === null) {
            return null;
        }

        // If value is JSON string (starts with { or [), decode it
        if (is_string($rawValue) && (str_starts_with(trim($rawValue), '{') || str_starts_with(trim($rawValue), '['))) {
            $decoded = json_decode($rawValue, true);

            return $decoded !== null ? $decoded : $rawValue;
        }

        // If value is a JSON-encoded string (starts with "), decode it
        if (is_string($rawValue) && str_starts_with(trim($rawValue), '"') && str_ends_with(trim($rawValue), '"')) {
            $decoded = json_decode($rawValue, true);
            if ($decoded === null) {
                return trim($rawValue, '"');
            }

            // Handle double-encoded JSON (e.g. "\"[\\\"a\\\",\\\"b\\\"]\"")
            if (is_string($decoded) && (str_starts_with(trim($decoded), '{') || str_starts_with(trim($decoded), '['))) {
                $decodedTwice = json_decode($decoded, true);

                return $decodedTwice !== null ? $decodedTwice : $decoded;
            }

            return $decoded;
        }

        return $rawValue;
    }

    /**
     * Clear all setting-related cache entries
     */
    public static function clearCache(): void
    {
        self::$requestParsedCache = null;

        try {
            Cache::forget(self::PARSED_CACHE_KEY);

            foreach (self::query()->pluck('key') as $key) {
                Cache::forget("setting.{$key}");
            }

            try {
                Cache::tags(['settings'])->flush();
            } catch (\Exception $e) {
                // Cache driver doesn't support tags, ignore
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear setting cache', ['error' => $e->getMessage()]);
        }
    }
}
