<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
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
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }
        
        // Get raw value from database to avoid array casting issues
        $rawValue = $setting->getAttributes()['value'] ?? null;
        
        if ($rawValue === null) {
            return $default;
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
        
        return self::updateOrCreate(
            ['key' => $key],
            $data
        );
    }

    /**
     * Get all settings as an array keyed by setting key
     * 
     * @return array
     */
    public static function getAll(): array
    {
        $settingsCollection = self::all()->keyBy('key');
        $settings = [];
        foreach ($settingsCollection as $key => $setting) {
            $settings[$key] = $setting->value;
        }
        return $settings;
    }

    /**
     * Clear all setting-related cache entries
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        try {
            // Clear all setting cache entries
            $settings = self::all();
            foreach ($settings as $setting) {
                \Illuminate\Support\Facades\Cache::forget("setting.{$setting->key}");
            }
            
            // Try to clear cache tags if supported (Redis, Memcached)
            try {
                \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
            } catch (\Exception $e) {
                // Cache driver doesn't support tags, ignore
            }
        } catch (\Exception $e) {
            // Log error but don't throw
            Log::warning('Failed to clear setting cache', ['error' => $e->getMessage()]);
        }
    }
}
