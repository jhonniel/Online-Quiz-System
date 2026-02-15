<?php

namespace App\Services;

use App\Models\ConfessionBannedWord;

class ConfessionCensorService
{
    /**
     * Replace banned text in content. Matches banned strings even inside other words (substring).
     * Display style per word: full = all asterisks, first_last = t**t, end_only = te**.
     */
    public static function censor(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        $entries = ConfessionBannedWord::listWordsWithStyle();
        if ($entries->isEmpty()) {
            return $text;
        }
        foreach ($entries as $entry) {
            $word = trim($entry->word);
            if ($word === '') {
                continue;
            }
            $pattern = '/' . preg_quote($word, '/') . '/iu';
            $style = $entry->display_style ?? 'full';
            $text = preg_replace_callback($pattern, function (array $m) use ($style) {
                return self::mask($m[0], $style);
            }, $text);
        }
        return $text;
    }

    /**
     * Apply display style to the matched substring (same length as input).
     * - full: ****
     * - first_last: t**t (first and last character visible)
     * - end_only: te** (first 2 chars visible, rest asterisks)
     */
    protected static function mask(string $matched, string $style): string
    {
        $len = mb_strlen($matched);
        if ($len === 0) {
            return '';
        }
        if ($style === ConfessionBannedWord::DISPLAY_FIRST_LAST) {
            if ($len <= 1) {
                return str_repeat('*', $len);
            }
            return mb_substr($matched, 0, 1) . str_repeat('*', $len - 2) . mb_substr($matched, -1, 1);
        }
        if ($style === ConfessionBannedWord::DISPLAY_END_ONLY) {
            $visible = min(2, $len);
            return mb_substr($matched, 0, $visible) . str_repeat('*', $len - $visible);
        }
        return str_repeat('*', $len);
    }
}
