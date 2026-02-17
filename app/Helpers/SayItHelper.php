<?php

namespace App\Helpers;

class SayItHelper
{
    /** Font Awesome 6 Free animal icon classes (fas). Same codename = same animal. */
    protected static array $animalIcons = [
        'fa-dog',
        'fa-cat',
        'fa-dove',
        'fa-fish',
        'fa-frog',
        'fa-horse',
        'fa-hippo',
        'fa-crow',
        'fa-otter',
        'fa-paw',
        'fa-dragon',
        'fa-kiwi-bird',
        'fa-spider',
        'fa-feather',
        'fa-dragon',
        'fa-horse',
    ];

    /** Pastel/vibrant avatar colors (bg and text). Same codename = same color. */
    protected static array $avatarColorClasses = [
        'bg-violet-100 text-violet-600',
        'bg-sky-100 text-sky-600',
        'bg-emerald-100 text-emerald-600',
        'bg-amber-100 text-amber-600',
        'bg-rose-100 text-rose-600',
        'bg-teal-100 text-teal-600',
        'bg-fuchsia-100 text-fuchsia-600',
        'bg-indigo-100 text-indigo-600',
        'bg-cyan-100 text-cyan-600',
        'bg-pink-100 text-pink-600',
        'bg-blue-100 text-blue-600',
        'bg-lime-100 text-lime-600',
        'bg-orange-100 text-orange-600',
        'bg-slate-100 text-slate-600',
    ];

    /**
     * Return a deterministic animal icon class (e.g. "fas fa-dog") for a codename.
     * Same codename always gets the same animal.
     */
    public static function animalIconForCodename(?string $codename): string
    {
        $seed = $codename ?? '?';
        $index = abs(crc32(mb_strtolower($seed))) % count(self::$animalIcons);
        return 'fas ' . self::$animalIcons[$index];
    }

    /**
     * Return deterministic pastel/vibrant avatar classes for a codename (e.g. "bg-sky-100 text-sky-600").
     * Same codename always gets the same color. Colors are soft, not harsh.
     */
    public static function avatarColorClassesForCodename(?string $codename): string
    {
        $seed = $codename ?? '?';
        $index = abs(crc32(mb_strtolower($seed))) % count(self::$avatarColorClasses);
        return self::$avatarColorClasses[$index];
    }

    /** Format vote/comment count for display (e.g. 8200 → "8.2K", 198 → "198"). */
    public static function formatCount(int $count): string
    {
        $count = (int) $count;
        if ($count >= 1000) {
            $k = $count / 1000;
            return rtrim(rtrim(number_format($k, 1, '.', ''), '0'), '.') . 'K';
        }
        return (string) $count;
    }
}
