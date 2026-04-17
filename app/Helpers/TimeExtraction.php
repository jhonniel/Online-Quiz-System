<?php

namespace App\Helpers;

/**
 * SmartTimeParser / TimeExtraction
 *
 * Extract time duration from free text (e.g. remarks) and return as decimal hours.
 * Used for DTR "Added Time From Note" from remarks.
 *
 * Supports patterns like:
 * - 1h, 1hr, 1 hour
 * - 1h30m, 2h 15m, 1hr 30min, 1 hour 30 minutes
 * - 1:30, 01:30 (H:MM or HH:MM)
 * - 90 min, 90 minutes, 90m, 45m
 * - 1.5 hours, 1.5h
 * - 30 mins, 2 hrs
 * - half hour, quarter hour
 * - one hr, two hours, three mins
 * - about 30 mins, around 2 hrs
 */
class TimeExtraction
{
    /**
     * Word-to-number mapping for time parsing.
     */
    private static array $wordNumbers = [
        'zero' => 0,
        'one' => 1,
        'two' => 2,
        'three' => 3,
        'four' => 4,
        'five' => 5,
        'six' => 6,
        'seven' => 7,
        'eight' => 8,
        'nine' => 9,
        'ten' => 10,
        'eleven' => 11,
        'twelve' => 12,
        'thirteen' => 13,
        'fourteen' => 14,
        'fifteen' => 15,
        'sixteen' => 16,
        'seventeen' => 17,
        'eighteen' => 18,
        'nineteen' => 19,
        'twenty' => 20,
        'thirty' => 30,
        'forty' => 40,
        'fifty' => 50,
        'sixty' => 60,
        'seventy' => 70,
        'eighty' => 80,
        'ninety' => 90,
    ];

    /**
     * Fractional word mappings (in minutes for hours context, or as fraction).
     */
    private static array $fractionWords = [
        'half' => 0.5,
        'quarter' => 0.25,
        'a half' => 0.5,
        'a quarter' => 0.25,
    ];

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

        // Normalize: lowercase, convert common separators to spaces, remove extra whitespace.
        // Keep ":" and "." so values like 1:30 and 1.5 remain parseable.
        $text = strtolower($text);
        $text = preg_replace('/[-_,;]+/', ' ', $text);
        $text = trim(preg_replace('/\s+/', ' ', $text));

        // Remove filler words: "about", "around", "approximately", "roughly", "nearly"
        $text = preg_replace('/\b(about|around|approximately|roughly|nearly)\s+/i', '', $text);

        $totalMinutes = 0.0;
        $matched = [];

        // Build word pattern for reuse
        $wordPattern = '(?:' . implode('|', array_keys(self::$wordNumbers)) . ')';

        // 0. "one and a half hr", "two and half hours", "one and half hr" (word number + and + half/quarter + hour)
        if (preg_match_all('/\b(' . $wordPattern . ')\s+and\s+(?:a\s+)?(half|quarter)\s*(?:h(?:(?:ou)?r)?s?)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $wholeNum = self::$wordNumbers[strtolower($m[1])] ?? 0;
                $frac = self::$fractionWords[$m[2]] ?? 0;
                $totalMinutes += ($wholeNum + $frac) * 60;
                $matched[] = $m[0];
            }
        }

        // 0b. Numeric "1 and a half hr", "2 and half hours"
        if (preg_match_all('/\b(\d+)\s+and\s+(?:a\s+)?(half|quarter)\s*(?:h(?:(?:ou)?r)?s?)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $wholeNum = (int) $m[1];
                $frac = self::$fractionWords[$m[2]] ?? 0;
                $totalMinutes += ($wholeNum + $frac) * 60;
                $matched[] = $m[0];
            }
        }

        // 1. "half hour", "quarter hour", "half an hour"
        if (preg_match_all('/\b(half|quarter)(?:\s+an?)?\s+hour/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (!self::alreadyMatched($m[0], $matched)) {
                    $frac = self::$fractionWords[$m[1]] ?? 0;
                    $totalMinutes += $frac * 60;
                    $matched[] = $m[0];
                }
            }
        }

        // 2. Hours:minutes colon format (e.g. 1:30, 01:45)
        if (preg_match_all('/\b(\d{1,2}):(\d{1,2})\b/', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $h = (int) $m[1];
                $min = (int) $m[2];
                $totalMinutes += $h * 60 + $min;
                $matched[] = $m[0];
            }
        }

        // 3. Compact format: 1h30m, 2h15m (no space)
        if (preg_match_all('/\b(\d+)\s*h\s*(\d+)\s*m(?:in(?:ute)?s?)?\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $totalMinutes += (int) $m[1] * 60 + (int) $m[2];
                $matched[] = $m[0];
            }
        }

        // 4. Hours with optional decimals (e.g. 1.5h, 1.5 hours, 2 hrs, 1h)
        if (preg_match_all('/\b(\d+(?:\.\d+)?)\s*(?:h(?:(?:ou)?r)?s?)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                // Avoid double-counting if already matched in compact format
                if (!self::alreadyMatched($m[0], $matched)) {
                    $totalMinutes += (float) $m[1] * 60;
                    $matched[] = $m[0];
                }
            }
        }

        // 5. Minutes (e.g. 30m, 30 min, 45 minutes, 90 minutes)
        if (preg_match_all('/\b(\d+(?:\.\d+)?)\s*(?:m(?:in(?:ute)?s?)?)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (!self::alreadyMatched($m[0], $matched)) {
                    $totalMinutes += (float) $m[1];
                    $matched[] = $m[0];
                }
            }
        }

        // 6. Combined word hours and minutes: "one hr three mins", "two hours fifteen minutes"
        if (preg_match_all('/\b((' . $wordPattern . ')(?:\s+' . $wordPattern . ')?)\s*(?:h(?:(?:ou)?r)?s?)\s+((' . $wordPattern . ')(?:\s+' . $wordPattern . ')?)\s*(?:m(?:in(?:ute)?s?)?)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (!self::alreadyMatched($m[0], $matched)) {
                    $hrs = self::parseWordNumberPhrase($m[1]);
                    $mins = self::parseWordNumberPhrase($m[3]);
                    $totalMinutes += $hrs * 60 + $mins;
                    $matched[] = $m[0];
                }
            }
        }

        // 7. Word-based hours: "one hour", "two hrs", "three hours"
        if (preg_match_all('/\b((' . $wordPattern . ')(?:\s+' . $wordPattern . ')?)\s*(?:h(?:(?:ou)?r)?s?)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (!self::alreadyMatched($m[0], $matched)) {
                    $num = self::parseWordNumberPhrase($m[1]);
                    $totalMinutes += $num * 60;
                    $matched[] = $m[0];
                }
            }
        }

        // 8. Word-based minutes: "thirty mins", "fifteen minutes", "three mins"
        if (preg_match_all('/\b((' . $wordPattern . ')(?:\s+' . $wordPattern . ')?)\s*(?:m(?:in(?:ute)?s?)?)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (!self::alreadyMatched($m[0], $matched)) {
                    $num = self::parseWordNumberPhrase($m[1]);
                    $totalMinutes += $num;
                    $matched[] = $m[0];
                }
            }
        }

        // 9. "an hour", "a minute"
        if (preg_match_all('/\b(?:an?|one)\s+(hour|hr|minute|min)\b/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                if (!self::alreadyMatched($m[0], $matched)) {
                    $unit = strtolower($m[1]);
                    $totalMinutes += str_starts_with($unit, 'h') ? 60 : 1;
                    $matched[] = $m[0];
                }
            }
        }

        return round($totalMinutes / 60, 4);
    }

    /**
     * Check if a match string is already contained in a previously matched string.
     */
    private static function alreadyMatched(string $needle, array $matched): bool
    {
        foreach ($matched as $m) {
            if (str_contains($m, $needle) || str_contains($needle, $m)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Parse number phrases like "five", "twenty", "seventy five".
     */
    private static function parseWordNumberPhrase(string $phrase): int
    {
        $phrase = strtolower(trim(preg_replace('/\s+/', ' ', $phrase)));
        if ($phrase === '') {
            return 0;
        }

        if (isset(self::$wordNumbers[$phrase])) {
            return self::$wordNumbers[$phrase];
        }

        $parts = explode(' ', $phrase);
        if (count($parts) === 2) {
            $first = self::$wordNumbers[$parts[0]] ?? null;
            $second = self::$wordNumbers[$parts[1]] ?? null;
            if ($first !== null && $second !== null) {
                return $first + $second;
            }
        }

        return 0;
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
