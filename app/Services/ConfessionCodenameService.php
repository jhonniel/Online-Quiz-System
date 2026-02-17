<?php

namespace App\Services;

use App\Models\ConfessionComment;
use App\Models\ConfessionPost;

class ConfessionCodenameService
{
    protected static array $animals = [
        'fox', 'wolf', 'bear', 'eagle', 'owl', 'hawk', 'raven', 'lynx',
        'otter', 'deer', 'hare', 'badger', 'seal', 'panda',
        'tiger', 'lion', 'panther', 'cobra', 'falcon', 'phoenix',
        'dragon', 'griffin', 'jaguar', 'leopard', 'moose',
        'elk', 'bison', 'coyote', 'hyena', 'mongoose', 'weasel',
    ];

    /**
     * Generate a codename that is unique across all posts and comments.
     * Codenames are never recycled.
     */
    public static function generate(): string
    {
        $maxAttempts = 50;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $codename = self::randomCodename();
            if (self::isUnique($codename)) {
                return $codename;
            }
        }
        // Fallback: add microtime to reduce collision chance
        return self::randomCodename() . '_' . substr(str_replace(['.', ' '], '', microtime()), -4);
    }

    protected static function randomCodename(): string
    {
        $animal = self::$animals[array_rand(self::$animals)];
        $number = random_int(1, 99999);
        return 'anonymous_' . $animal . '_' . $number;
    }

    /**
     * Check if codename has never been used (posts or comments).
     */
    public static function isUnique(string $codename): bool
    {
        return ! ConfessionPost::where('codename', $codename)->exists()
            && ! ConfessionComment::where('codename', $codename)->exists();
    }
}
