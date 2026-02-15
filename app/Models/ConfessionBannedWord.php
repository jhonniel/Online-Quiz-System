<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ConfessionBannedWord extends Model
{
    protected $fillable = ['word', 'display_style'];

    public const DISPLAY_FULL = 'full';
    public const DISPLAY_FIRST_LAST = 'first_last';
    public const DISPLAY_END_ONLY = 'end_only';

    /**
     * Get all banned words with display style (cached for 5 minutes).
     * @return \Illuminate\Support\Collection<int, ConfessionBannedWord>
     */
    public static function listWordsWithStyle()
    {
        return Cache::remember('confession_banned_words_with_style', 300, function () {
            return static::orderBy('word')->get();
        });
    }

    /**
     * Clear the banned-words cache (call after create/update/delete).
     */
    public static function clearCache(): void
    {
        Cache::forget('confession_banned_words');
        Cache::forget('confession_banned_words_with_style');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }
}
