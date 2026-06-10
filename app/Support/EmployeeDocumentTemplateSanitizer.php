<?php

namespace App\Support;

final class EmployeeDocumentTemplateSanitizer
{
    public static function sanitize(string $html): string
    {
        $html = preg_replace('/<\s*script\b[^>]*>[\s\S]*?<\s*\/\s*script\s*>/i', '', $html) ?? '';
        $html = preg_replace('/<\s*style\b[^>]*>[\s\S]*?<\s*\/\s*style\s*>/i', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/i', '', $html) ?? '';
        $html = preg_replace('/\s(href|src)\s*=\s*(["\'])\s*javascript:.*?\2/i', '', $html) ?? '';

        return $html;
    }
}
