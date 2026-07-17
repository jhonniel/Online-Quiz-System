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

    /**
     * Post body backgrounds (Tailwind gradient classes). Keys are stored on confession_posts.card_background.
     * Keep keys and full class strings here so the Tailwind scanner includes them.
     *
     * @var array<string, string>
     */
    protected static array $cardContentBackgroundClasses = [
        'white' => 'bg-white',
        'lilac' => 'bg-gradient-to-br from-violet-100 via-fuchsia-50 to-purple-50',
        'mint' => 'bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50',
        'peach' => 'bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50',
        'sky' => 'bg-gradient-to-br from-sky-50 via-blue-50 to-indigo-50',
        'rose' => 'bg-gradient-to-br from-rose-50 via-pink-50 to-fuchsia-50',
        'sand' => 'bg-gradient-to-br from-stone-100 via-amber-50 to-orange-50',
        'seafoam' => 'bg-gradient-to-br from-cyan-100 via-teal-50 to-emerald-50',
        'butter' => 'bg-gradient-to-br from-yellow-50 via-lime-50 to-emerald-50',
        'lavender' => 'bg-gradient-to-br from-indigo-50 via-violet-100 to-purple-100',
        'coral' => 'bg-gradient-to-br from-red-50 via-rose-100 to-orange-50',
    ];

    /** @var array<string, string> */
    protected static array $cardBackgroundLabels = [
        'white' => 'White',
        'lilac' => 'Lilac',
        'mint' => 'Mint',
        'peach' => 'Peach',
        'sky' => 'Sky',
        'rose' => 'Rose',
        'sand' => 'Sand',
        'seafoam' => 'Seafoam',
        'butter' => 'Butter',
        'lavender' => 'Lavender',
        'coral' => 'Coral',
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

        return 'fas '.self::$animalIcons[$index];
    }

    /**
     * Deterministic placeholder icon for a chat room without a profile photo.
     */
    public static function roomPlaceholderIcon(?string $seed): string
    {
        $icons = [
            'fa-comments',
            'fa-comment-dots',
            'fa-users',
            'fa-user-friends',
            'fa-hashtag',
            'fa-bolt',
            'fa-star',
            'fa-heart',
            'fa-fire',
            'fa-moon',
            'fa-sun',
            'fa-leaf',
            'fa-coffee',
            'fa-music',
            'fa-gamepad',
            'fa-ghost',
            'fa-rocket',
            'fa-puzzle-piece',
            'fa-lightbulb',
            'fa-globe',
        ];
        $key = mb_strtolower((string) ($seed ?: '?'));
        $index = abs(crc32($key)) % count($icons);

        return 'fas '.$icons[$index];
    }

    /**
     * Deterministic pastel background/text classes for a room placeholder icon.
     */
    public static function roomPlaceholderColorClasses(?string $seed): string
    {
        return self::avatarColorClassesForCodename($seed);
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

    /** @return list<string> */
    public static function cardBackgroundKeys(): array
    {
        return array_keys(self::$cardContentBackgroundClasses);
    }

    /** Tailwind classes for the main post content area (above the action bar). */
    public static function cardContentBackgroundClasses(?string $key): string
    {
        if ($key === null || $key === '') {
            return '';
        }

        return self::$cardContentBackgroundClasses[$key] ?? '';
    }

    /**
     * @return list<array{key: string, label: string, classes: string}>
     */
    public static function cardBackgroundFormOptions(): array
    {
        $out = [];
        foreach (self::$cardContentBackgroundClasses as $key => $classes) {
            $out[] = [
                'key' => $key,
                'label' => self::$cardBackgroundLabels[$key] ?? $key,
                'classes' => $classes,
            ];
        }

        return $out;
    }

    /**
     * Random soft mesh gradient (stacked radial gradients). Used for Say-it "Random" post backgrounds.
     * Output is server-generated only (safe for HTML style attribute via Blade escaping).
     */
    public static function randomMeshBackgroundStyle(): string
    {
        /** @var list<array{0: int, 1: int, 2: int}> */
        $palette = [
            [255, 140, 125],
            [255, 205, 170],
            [110, 220, 255],
            [195, 165, 255],
            [235, 75, 175],
            [255, 115, 95],
            [255, 175, 85],
            [155, 125, 255],
            [255, 195, 215],
            [95, 205, 195],
            [255, 160, 200],
            [180, 130, 255],
        ];

        $pickRgb = static function () use ($palette): array {
            return $palette[random_int(0, count($palette) - 1)];
        };

        /** Pull channels toward white so washes stay airy */
        $washRgb = static function () use ($pickRgb): array {
            $c = $pickRgb();
            $toward = 255;
            $mix = random_int(62, 82) / 100;

            return [
                (int) round($c[0] + ($toward - $c[0]) * $mix + random_int(-18, 18)),
                (int) round($c[1] + ($toward - $c[1]) * $mix + random_int(-18, 18)),
                (int) round($c[2] + ($toward - $c[2]) * $mix + random_int(-18, 18)),
            ];
        };

        // Near-white base (low saturation) so real white reads clearly
        $baseH = random_int(260, 320);
        $baseS = random_int(6, 18);
        $baseL = random_int(98, 100);
        $baseColor = "hsl({$baseH},{$baseS}%,{$baseL}%)";

        $layers = [];

        // Soft white glow regions so white is visibly present
        $layers[] = 'radial-gradient(ellipse 120% 90% at 50% 45%,rgba(255,255,255,0.92),transparent 55%)';
        $layers[] = 'radial-gradient(ellipse 95% 70% at '.random_int(10, 90).'% '.random_int(15, 85).'%,rgba(255,255,255,0.55),transparent 48%)';

        $n = random_int(4, 6);
        for ($i = 0; $i < $n; $i++) {
            $c = $washRgb();
            $r = max(200, min(255, $c[0]));
            $g = max(200, min(255, $c[1]));
            $b = max(200, min(255, $c[2]));
            $alpha = round(random_int(18, 48) / 100, 2);
            $xw = random_int(85, 150);
            $yh = random_int(70, 130);
            $xp = random_int(0, 100);
            $yp = random_int(0, 100);
            $stop = random_int(28, 46);
            $layers[] = "radial-gradient(ellipse {$xw}% {$yh}% at {$xp}% {$yp}%,rgba({$r},{$g},{$b},{$alpha}),transparent {$stop}%)";
        }

        shuffle($layers);

        return 'background-color:'.$baseColor.';background-image:'.implode(',', $layers).';background-repeat:no-repeat;background-size:100% 100%;';
    }

    /** Format vote/comment count for display (e.g. 8200 → "8.2K", 198 → "198"). */
    public static function formatCount(int $count): string
    {
        $count = (int) $count;
        if ($count >= 1000) {
            $k = $count / 1000;

            return rtrim(rtrim(number_format($k, 1, '.', ''), '0'), '.').'K';
        }

        return (string) $count;
    }

    /**
     * Disk for Say-it confession images: S3/Spaces (digitalocean, spaces, s3) or local (public, local).
     * When a remote disk is chosen but not configured (no key/secret/bucket), local/testing falls back to public.
     */
    public static function confessionStorageDisk(): string
    {
        $preferred = (string) config('filesystems.confessions_storage_disk', 'digitalocean');
        $allowed = ['digitalocean', 'spaces', 's3', 'public', 'local'];
        if (! in_array($preferred, $allowed, true)) {
            $preferred = 'digitalocean';
        }

        if (in_array($preferred, ['public', 'local'], true)) {
            return $preferred;
        }

        $cfg = config("filesystems.disks.{$preferred}", []);
        $remoteOk = ! empty($cfg['key'])
            && ! empty($cfg['secret'])
            && ! empty($cfg['bucket'] ?? null);

        if ($remoteOk) {
            return $preferred;
        }

        if (app()->environment('local', 'testing')) {
            return 'public';
        }

        return $preferred;
    }

    public static function isConfessionImageStorageConfigured(): bool
    {
        $disk = self::confessionStorageDisk();

        if (in_array($disk, ['public', 'local'], true)) {
            return true;
        }

        $cfg = config("filesystems.disks.{$disk}", []);

        return ! empty($cfg['key'])
            && ! empty($cfg['secret'])
            && ! empty($cfg['bucket'] ?? null);
    }

    /**
     * Object key prefix in the bucket (same root as other uploads when DIGITALOCEAN_SPACES_ROOT_PATH is set).
     */
    public static function confessionsStoragePathPrefix(): string
    {
        $root = trim((string) env('DIGITALOCEAN_SPACES_ROOT_PATH', ''), '/');
        if ($root === '') {
            return 'confessions';
        }

        return $root.'/confessions';
    }
}
