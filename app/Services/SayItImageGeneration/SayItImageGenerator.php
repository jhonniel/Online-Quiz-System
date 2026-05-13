<?php

namespace App\Services\SayItImageGeneration;

final class SayItImageGenerator
{
    public static function isConfigured(): bool
    {
        return SayItImageSettings::isConfigured();
    }

    /** Backend ready and admin allows the composer “Generate image” feature. */
    public static function isComposerGenerateImageAvailable(): bool
    {
        return SayItImageSettings::isComposerGenerateImageFeatureEnabled() && self::isConfigured();
    }

    public static function make(): SayItImageGeneratorContract
    {
        return match (SayItImageSettings::driver()) {
            'sdwebui' => new SdWebUiImageGenerator,
            'comfyui' => new ComfyUiImageGenerator,
            default => new NullSayItImageGenerator,
        };
    }
}
