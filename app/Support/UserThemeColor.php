<?php

namespace App\Support;

final class UserThemeColor
{
    public const DEFAULT = '#4F46E5';

    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = strtoupper(trim($value));
        if (! preg_match('/^#[0-9A-F]{6}$/', $value)) {
            return null;
        }

        return $value;
    }

    public static function darken(string $hex, float $ratio = 0.82): string
    {
        $hex = ltrim(self::normalize($hex) ?? self::DEFAULT, '#');

        $r = max(0, min(255, (int) round(hexdec(substr($hex, 0, 2)) * $ratio)));
        $g = max(0, min(255, (int) round(hexdec(substr($hex, 2, 2)) * $ratio)));
        $b = max(0, min(255, (int) round(hexdec(substr($hex, 4, 2)) * $ratio)));

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    public static function lighten(string $hex, float $ratio = 1.12): string
    {
        $hex = ltrim(self::normalize($hex) ?? self::DEFAULT, '#');

        $r = max(0, min(255, (int) round(hexdec(substr($hex, 0, 2)) * $ratio)));
        $g = max(0, min(255, (int) round(hexdec(substr($hex, 2, 2)) * $ratio)));
        $b = max(0, min(255, (int) round(hexdec(substr($hex, 4, 2)) * $ratio)));

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
