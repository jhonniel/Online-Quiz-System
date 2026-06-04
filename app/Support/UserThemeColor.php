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

    /** @return array{0: int, 1: int, 2: int} */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim(self::normalize($hex) ?? self::DEFAULT, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    public static function rgba(string $hex, float $alpha = 1.0): string
    {
        [$r, $g, $b] = self::hexToRgb($hex);
        $alpha = max(0, min(1, $alpha));

        return sprintf('rgba(%d, %d, %d, %.2F)', $r, $g, $b, $alpha);
    }

    /** @return array{primary: string, dark: string, mid: string, light: string, palette: list<string>, fill: string, grid: string} */
    public static function chartPalette(string $hex): array
    {
        $primary = self::normalize($hex) ?? self::DEFAULT;
        $dark = self::darken($primary, 0.82);
        $mid = self::lighten($primary, 0.95);
        $light = self::lighten($primary, 1.08);

        return [
            'primary' => $primary,
            'dark' => $dark,
            'mid' => $mid,
            'light' => $light,
            'palette' => [$primary, $dark, $mid, $light],
            'fill' => self::rgba($primary, 0.15),
            'grid' => self::rgba($primary, 0.12),
        ];
    }
}
