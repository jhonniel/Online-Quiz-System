<?php

namespace Illuminate\Support {

    if (! function_exists('Illuminate\Support\php_binary')) {
        function php_binary(): string
        {
            return (new \Symfony\Component\Process\PhpExecutableFinder())->find(false) ?: 'php';
        }
    }
}
