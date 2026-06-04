{{--
    Responsive data panel: mobile card list + desktop table.
    Usage:
    <x-responsive-data-panel>
        <x-slot:mobile>...</x-slot:mobile>
        <x-slot:desktop>...</x-slot:desktop>
    </x-responsive-data-panel>
--}}
@props([
    'breakpoint' => 'md',
])

@php
    $mobileHidden = $breakpoint === 'lg' ? 'lg:hidden' : ($breakpoint === 'sm' ? 'sm:hidden' : 'md:hidden');
    $desktopHidden = $breakpoint === 'lg' ? 'hidden lg:block' : ($breakpoint === 'sm' ? 'hidden sm:block' : 'hidden md:block');
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden']) }}>
    @isset($mobile)
        <div class="{{ $mobileHidden }} mobile-card-list">
            {{ $mobile }}
        </div>
    @endisset

    @isset($desktop)
        <div class="{{ $desktopHidden }} mobile-table-scroll scrollbar-thin-x">
            {{ $desktop }}
        </div>
    @endisset

    {{ $slot }}
</div>
