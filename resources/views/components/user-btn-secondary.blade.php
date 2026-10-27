@props(['tag' => 'a'])

@php
    $classes = 'inline-flex min-h-[44px] items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 touch-manipulation';
@endphp

@if($tag === 'button')
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>{{ $slot }}</button>
@else
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@endif
