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

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.04]']) }}>
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
