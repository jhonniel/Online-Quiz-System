<?php

namespace App\Services\SayItImageGeneration;

interface SayItImageGeneratorContract
{
    /**
     * @throws SayItImageGenerationException
     */
    public function generate(string $prompt): string;
}
