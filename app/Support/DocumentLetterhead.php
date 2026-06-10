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
}
