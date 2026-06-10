<?php

namespace App\Support;

final class DocumentLetterhead
{
    public const IMAGE_PATH = 'images/employee-documents/miniclean-letterhead.png';

    public static function assetUrl(): string
    {
        return asset(self::IMAGE_PATH);
    }

    public static function dataUri(): string
    {
        $path = public_path(self::IMAGE_PATH);
        if (! is_file($path)) {
            return '';
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/jpeg',
        };

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }

    public static function embedInHtml(string $html): string
    {
        $dataUri = self::dataUri();
        if ($dataUri === '') {
            return $html;
        }

        $filename = basename(self::IMAGE_PATH);

        return preg_replace(
            '~(<img\b[^>]*\bsrc=["\'])(?:[^"\']*[/\\\\])?'.preg_quote($filename, '~').'(?:\?[^"\']*)?(["\'])~i',
            '$1'.$dataUri.'$2',
            $html
        ) ?? $html;
    }

    public static function repairBlockInHtml(string $html): string
    {
        if (! preg_match('/<div class="document-page-letterhead">/i', $html, $match, PREG_OFFSET_CAPTURE)) {
            return self::embedInHtml($html);
        }

        $start = $match[0][1];
        $position = $start + strlen($match[0][0]);
        $depth = 1;
        $length = strlen($html);

        while ($position < $length && $depth > 0) {
            if (! preg_match('/<\/?div(?:\s[^>]*)?>/i', $html, $tag, PREG_OFFSET_CAPTURE, $position)) {
                return self::embedInHtml($html);
            }

            $tagText = $tag[0][0];
            $position = $tag[0][1] + strlen($tagText);
            $depth += str_starts_with($tagText, '</') ? -1 : 1;
        }

        if ($depth !== 0) {
            return self::embedInHtml($html);
        }

        $letterhead = view('user.employee-documents.partials.document-page-letterhead')->render();

        return substr_replace($html, $letterhead, $start, $position - $start);
    }
}
