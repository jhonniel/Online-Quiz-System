@props([
    'variant' => 'line',
])

@php
    $base = 'animate-pulse bg-gray-200 rounded';
    $classes = match ($variant) {
        'circle' => $base.' rounded-full',
        'block' => $base,
        'card' => $base.' rounded-xl',
        default => $base,
    };
@endphp

<div {{ $attributes->merge(['class' => $classes, 'aria-hidden' => 'true']) }}></div>
