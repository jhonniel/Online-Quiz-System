@props([
    'wide' => false,
])

@php
    $maxClass = $wide ? 'max-w-[88rem]' : 'max-w-7xl';
@endphp

<div {{ $attributes->merge(['class' => 'mx-auto w-full '.$maxClass.' flex min-h-0 flex-col gap-5 sm:gap-6']) }}>
    {{ $slot }}
</div>
