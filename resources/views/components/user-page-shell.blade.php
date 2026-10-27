@props([
    'wide' => false,
])

@php
    $maxClass = $wide ? 'max-w-[90rem]' : 'max-w-7xl';
@endphp

<div {{ $attributes->merge(['class' => 'user-page-shell mx-auto w-full '.$maxClass.' flex flex-col min-h-0 gap-4 sm:gap-5']) }}>
    {{ $slot }}
</div>
