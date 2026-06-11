<?php

namespace App\Support;

final class BrandNameFit
{
    public static function fontSizeRem(?string $text, float $baseRem = 1.125, float $minRem = 0.625): string
    {
        $length = mb_strlen(trim((string) $text));

        if ($length <= 20) {
            return $baseRem.'rem';
        }

        $reduction = min($baseRem - $minRem, ($length - 20) * 0.022);

        return round(max($minRem, $baseRem - $reduction), 3).'rem';
    }
}
