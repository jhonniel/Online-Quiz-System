<?php

namespace App\Support;

final class SystemAnnouncementFormatter
{
    public static function toDisplayHtml(string $content): string
    {
        $content = preg_replace('#<\s*script\b[^>]*>.*?</script>#is', '', $content) ?? '';

        if (self::containsListHtml($content)) {
            return $content;
        }

        if (strip_tags($content) !== $content) {
            return $content;
        }

        return self::plainTextToHtml($content);
    }

    private static function containsListHtml(string $content): bool
    {
        return (bool) preg_match('/<(ul|ol)\b/i', $content);
    }

    private static function plainTextToHtml(string $content): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $html = '';
        $inUl = false;
        $inOl = false;

        $closeLists = function () use (&$html, &$inUl, &$inOl): void {
            if ($inUl) {
                $html .= '</ul>';
                $inUl = false;
            }
            if ($inOl) {
                $html .= '</ol>';
                $inOl = false;
            }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $closeLists();

                continue;
            }

            if (preg_match('/^[-•*]\s+(.+)$/', $trimmed, $matches)) {
                if ($inOl) {
                    $html .= '</ol>';
                    $inOl = false;
                }
                if (! $inUl) {
                    $html .= '<ul class="announcement-list">';
                    $inUl = true;
                }
                $html .= '<li>'.e($matches[1]).'</li>';

                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $matches)) {
                if ($inUl) {
                    $html .= '</ul>';
                    $inUl = false;
                }
                if (! $inOl) {
                    $html .= '<ol class="announcement-list announcement-list-ordered">';
                    $inOl = true;
                }
                $html .= '<li>'.e($matches[1]).'</li>';

                continue;
            }

            $closeLists();
            $html .= '<p>'.e($trimmed).'</p>';
        }

        $closeLists();

        return $html !== '' ? $html : '<p>'.e(trim($content)).'</p>';
    }
}
