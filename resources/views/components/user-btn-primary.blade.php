@props(['tag' => 'a'])

@php
    $classes = 'user-portal-btn-primary inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 touch-manipulation sm:w-auto';
@endphp

@if($tag === 'button')
    <button {{ $attributes->merge(['class' => $classes, 'type' => $attributes->get('type', 'button')]) }}>{{ $slot }}</button>
@else
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@endif
