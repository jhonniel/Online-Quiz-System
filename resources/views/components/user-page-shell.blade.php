@props([
    'wide' => false,
    'fluid' => false,
])

@php
    $widthClass = match (true) {
        $fluid => 'w-full max-w-none',
        $wide => 'w-full max-w-[88rem]',
        default => 'w-full max-w-7xl',
    };
@endphp

<div {{ $attributes->merge(['class' => $widthClass.' mx-auto flex min-h-0 flex-col gap-5 sm:gap-6']) }}>
    {{ $slot }}
</div>
