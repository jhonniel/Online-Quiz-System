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
    $fluidGutter = $fluid
        ? '-mx-3 px-3 sm:-mx-4 sm:px-4 lg:-mx-6 lg:px-6 pb-4 sm:pb-6'
        : '';
@endphp

<div {{ $attributes->merge(['class' => trim($widthClass.' '.$fluidGutter.' mx-auto flex min-h-0 min-w-0 max-w-full flex-col gap-4 overflow-x-hidden sm:gap-5')]) }}>
    {{ $slot }}
</div>
