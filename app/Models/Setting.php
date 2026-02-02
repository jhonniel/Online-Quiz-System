<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
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
            \Log::warning('Failed to clear setting cache', ['error' => $e->getMessage()]);
        }
    }
}
