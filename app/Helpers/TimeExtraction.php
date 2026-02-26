<?php

namespace App\Helpers;

/**
 * Extract time duration from free text (e.g. remarks) and return as decimal hours.
 * Used for DTR "Added Time From Note" from remarks.
 *
 * Supports patterns like:
 * - 1h 30m, 1hr 30min, 1 hour 30 minutes
 * - 1:30, 01:30 (H:MM or HH:MM)
 * - 90 min, 90 minutes, 90m
 * - 1.5 hours, 1.5h
 * - 30 mins, 2 hrs
 */
class TimeExtraction
{
    /**
     * Extract total time duration from text and return as decimal hours.
     *
     * @param string|null $text
     * @return float Decimal hours (e.g. 1.5 for 1h 30m)
     */
    public static function fromText(?string $text): float
    {
        if ($text === null || trim($text) === '') {
            return 0.0;
        }

        $totalMinutes = 0.0;

        // Hours:minutes colon format (e.g. 1:30, 01:45)
        if (preg_match_all('/\b(\d{1,2}):(\d{1,2})\b/', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $h = (int) $m[1];
                $min = (int) $m[2];
                $totalMinutes += $h * 60 + $min;
            }
        }

        // Hours with optional decimals (e.g. 1.5h, 1.5 hours, 2 hrs)
        if (preg_match_all('/\b(\d+(?:\.\d+)?)\s*(?:h(?:r)?s?|hours?)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $totalMinutes += (float) $m[1] * 60;
            }
        }

        // Minutes (e.g. 30m, 30 min, 45 minutes)
        if (preg_match_all('/\b(\d+(?:\.\d+)?)\s*(?:m(?:in)?s?|minutes?)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $totalMinutes += (float) $m[1];
            }
        }

        // Standalone "h" or "hr" after number (e.g. 1 h, 2 hr) - avoid double count with "1:30"
        if (preg_match_all('/\b(\d+(?:\.\d+)?)\s*(?<![:\d])h(?:r)?\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $totalMinutes += (float) $m[1] * 60;
            }
        }

        // Standalone "m" after number - minutes (e.g. 45 m) - be careful not to match "1:30" min part
        if (preg_match_all('/\b(\d+(?:\.\d+)?)\s*m(?!s\b|in)/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $totalMinutes += (float) $m[1];
            }
        }

        return round($totalMinutes / 60, 4);
    }

    /**
     * Convert decimal hours to HH:MM string for form display.
     *
     * @param float $decimalHours
     * @return string e.g. "01:30"
     */
    public static function decimalToHhMm(float $decimalHours): string
    {
        if ($decimalHours <= 0) {
            return '00:00';
        }
        $totalMinutes = (int) round($decimalHours * 60);
        $h = (int) floor($totalMinutes / 60);
        $m = $totalMinutes % 60;
        return sprintf('%02d:%02d', $h, $m);
    }
}
