<?php

namespace App\Services;

use App\Models\ConfessionComment;
use App\Models\ConfessionPost;
use App\Models\Setting;
use App\Models\User;

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
        $source = self::pickNameSource();
        if ($source !== 'default_codename') {
            $role = substr($source, 5);
            $roleBasedName = self::randomNameByRole($role);
            if ($roleBasedName !== null) {
                return self::makeUniqueLabel($roleBasedName);
            }
        }

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

    protected static function pickNameSource(): string
    {
        $configured = Setting::get('confession_anon_name_sources', ['default_codename']);
        if (!is_array($configured) || empty($configured)) {
            return 'default_codename';
        }

        $availableRoles = User::query()
            ->whereNotNull('role')
            ->select('role')
            ->distinct()
            ->pluck('role')
            ->filter(fn ($role) => is_string($role) && $role !== '')
            ->values()
            ->all();

        $allowed = collect(['default_codename'])
            ->merge(collect($availableRoles)->map(fn ($role) => 'role_' . $role))
            ->all();

        $selected = array_values(array_unique(array_filter(
            $configured,
            fn ($value) => is_string($value) && in_array($value, $allowed, true)
        )));

        if (empty($selected)) {
            return 'default_codename';
        }

        return $selected[array_rand($selected)];
    }

    protected static function randomNameByRole(string $role): ?string
    {
        $users = User::query()
            ->where('role', $role)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->inRandomOrder()
            ->limit(24)
            ->get(['name']);

        if ($users->isEmpty()) {
            return null;
        }

        $firstNames = [];
        $lastNames = [];

        foreach ($users as $user) {
            $tokens = preg_split('/\s+/', trim((string) $user->name)) ?: [];
            $tokens = array_values(array_filter($tokens, fn ($token) => is_string($token) && $token !== ''));
            if ($tokens === []) {
                continue;
            }

            $firstNames[] = (string) $tokens[0];
            // Only use a real last/middle token — never duplicate the first name.
            if (count($tokens) >= 2) {
                $lastNames[] = (string) $tokens[array_key_last($tokens)];
            }
        }

        $firstNames = array_values(array_unique($firstNames));
        $lastNames = array_values(array_unique($lastNames));

        if ($firstNames === []) {
            return null;
        }

        // Need at least one last-name pool that can differ from the chosen first name.
        if ($lastNames === []) {
            // Fall back: mix two different first names as "First Last".
            if (count($firstNames) < 2) {
                return null;
            }

            return self::pickDistinctPair($firstNames, $firstNames);
        }

        return self::pickDistinctPair($firstNames, $lastNames);
    }

    /**
     * Pick first + last so the two labels are not the same (case-insensitive).
     */
    protected static function pickDistinctPair(array $firstPool, array $lastPool): ?string
    {
        $maxAttempts = 40;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $first = (string) $firstPool[array_rand($firstPool)];
            $last = (string) $lastPool[array_rand($lastPool)];

            if (mb_strtolower(trim($first)) === mb_strtolower(trim($last))) {
                continue;
            }

            $label = trim($first.' '.$last);
            if ($label !== '') {
                return $label;
            }
        }

        // Exhaustive fallback: find any unequal pair.
        foreach ($firstPool as $first) {
            foreach ($lastPool as $last) {
                if (mb_strtolower(trim((string) $first)) === mb_strtolower(trim((string) $last))) {
                    continue;
                }

                $label = trim($first.' '.$last);
                if ($label !== '') {
                    return $label;
                }
            }
        }

        return null;
    }

    protected static function makeUniqueLabel(string $label): string
    {
        $cleaned = trim($label);
        if ($cleaned === '') {
            return self::randomCodename();
        }

        if (self::isUnique($cleaned)) {
            return $cleaned;
        }

        for ($i = 0; $i < 30; $i++) {
            $mixedCase = self::applyMixedCase($cleaned);
            if (self::isUnique($mixedCase)) {
                return $mixedCase;
            }
        }

        for ($i = 0; $i < 50; $i++) {
            $candidate = self::applyMixedCase($cleaned) . '_' . random_int(10, 9999);
            if (self::isUnique($candidate)) {
                return $candidate;
            }
        }

        return self::randomCodename() . '_' . random_int(10, 9999);
    }

    protected static function applyMixedCase(string $value): string
    {
        $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $result = '';

        foreach ($chars as $char) {
            if (!preg_match('/[a-zA-Z]/', $char)) {
                $result .= $char;
                continue;
            }

            $result .= random_int(0, 1) === 1
                ? mb_strtoupper($char)
                : mb_strtolower($char);
        }

        return $result;
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
        $normalized = trim($codename);

        return ! ConfessionPost::query()
            ->whereRaw('TRIM(codename) = ?', [$normalized])
            ->exists()
            && ! ConfessionComment::query()
                ->whereRaw('TRIM(codename) = ?', [$normalized])
                ->exists();
    }
}
