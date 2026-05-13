<?php

namespace App\Services\SayItImageGeneration;

final class SayItImageBinaryValidator
{
    public static function assertPngJpegWebp(string $binary): void
    {
        if (str_starts_with($binary, "\x89PNG\r\n\x1a\n")) {
            return;
        }
        if (str_starts_with($binary, "\xff\xd8\xff")) {
            return;
        }
        if (strlen($binary) > 12 && str_starts_with($binary, 'RIFF') && substr($binary, 8, 4) === 'WEBP') {
            return;
        }

        throw new SayItImageGenerationException('The generated file was not a valid PNG, JPEG, or WebP image.');
    }
}
