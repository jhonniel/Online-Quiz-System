<?php

namespace App\Services\SayItImageGeneration;

final class NullSayItImageGenerator implements SayItImageGeneratorContract
{
    public function generate(string $prompt): string
    {
        throw new SayItImageGenerationException('AI image generation is not enabled.');
    }
}
