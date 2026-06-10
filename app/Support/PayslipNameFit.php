<?php

namespace App\Support;

final class PayslipNameFit
{
    public static function fontSizeRem(?string $text, float $baseRem = 0.875, float $minRem = 0.55): string
    {
        $length = mb_strlen(trim((string) $text));

        if ($length <= 20) {
            return $baseRem.'rem';
        }

        $reduction = min($baseRem - $minRem, ($length - 20) * 0.022);

        return round(max($minRem, $baseRem - $reduction), 3).'rem';
    }

    public static function fontSizePt(?string $text, float $basePt = 9.0, float $minPt = 5.5): string
    {
        $length = mb_strlen(trim((string) $text));

        if ($length <= 20) {
            return $basePt.'pt';
        }

        $reduction = min($basePt - $minPt, ($length - 20) * 0.22);

        return round(max($minPt, $basePt - $reduction), 2).'pt';
    }
}
