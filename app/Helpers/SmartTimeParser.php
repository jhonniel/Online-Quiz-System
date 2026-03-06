<?php

namespace App\Helpers;

/**
 * SmartTimeParser - Intelligent time duration parser.
 *
 * Parses various natural language time expressions and returns decimal hours.
 * This is an alias/extension of TimeExtraction for semantic clarity.
 *
 * Supported formats:
 * - Numeric compact: 1h, 1hr, 1h30m, 2h 15m, 45m
 * - Colon format: 1:30, 01:45
 * - Decimal hours: 1.5 hours, 1.5h
 * - Word-based: one hour, two hrs, thirty mins
 * - Combined words: one hr three mins, two hours fifteen minutes
 * - Fractional: half hour, quarter hour, half an hour
 * - Natural language: about 30 mins, around 2 hrs, approximately 1 hour
 */
class SmartTimeParser extends TimeExtraction
{
    /**
     * Parse time duration from natural language text.
     *
     * @param string|null $text
     * @return float Decimal hours
     */
    public static function parse(?string $text): float
    {
        return parent::fromText($text);
    }

    /**
     * Parse time and return as HH:MM formatted string.
     *
     * @param string|null $text
     * @return string HH:MM format
     */
    public static function parseToHhMm(?string $text): string
    {
        $decimalHours = self::parse($text);
        return parent::decimalToHhMm($decimalHours);
    }

    /**
     * Parse time and return as total minutes.
     *
     * @param string|null $text
     * @return int Total minutes
     */
    public static function parseToMinutes(?string $text): int
    {
        $decimalHours = self::parse($text);
        return (int) round($decimalHours * 60);
    }
}
