<?php

/**
 * Polyfill for array_first() and array_last().
 * Required for Laravel 10.x on PHP 8.4 when using framework code that still expects these global helpers.
 */

if (! function_exists('array_first')) {
    function array_first(array $array, ?callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? value($default) : reset($array);
        }
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return value($default);
    }
}

if (! function_exists('array_last')) {
    function array_last(array $array, ?callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($array) ? value($default) : end($array);
        }
        return array_first(array_reverse($array, true), $callback, $default);
    }
}
