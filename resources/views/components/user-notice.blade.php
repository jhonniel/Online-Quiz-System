@props([
    'title' => null,
    'tone' => 'info',
])

@php
    $styles = match ($tone) {
        'success' => ['wrap' => 'border-emerald-200 bg-emerald-50/90', 'icon' => 'text-emerald-600', 'title' => 'text-emerald-900', 'body' => 'text-emerald-800'],
        'warning' => ['wrap' => 'border-amber-200 bg-amber-50/90', 'icon' => 'text-amber-600', 'title' => 'text-amber-900', 'body' => 'text-amber-800'],
        'danger' => ['wrap' => 'border-rose-200 bg-rose-50/90', 'icon' => 'text-rose-600', 'title' => 'text-rose-900', 'body' => 'text-rose-800'],
        default => ['wrap' => 'border-sky-200 bg-sky-50/90', 'icon' => 'text-sky-600', 'title' => 'text-sky-900', 'body' => 'text-sky-800'],
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 rounded-xl border px-4 py-3.5 shadow-sm ring-1 ring-black/[0.03] sm:flex-row sm:items-start '.$styles['wrap']]) }} role="status">
    @if(isset($icon))
        <div class="{{ $styles['icon'] }} mt-0.5 shrink-0 [&>svg]:h-5 [&>svg]:w-5 [&>svg]:max-h-5 [&>svg]:max-w-5">
            {{ $icon }}
        </div>
    @endif
    <div class="min-w-0 flex-1">
        @if($title)
            <p class="text-sm font-semibold {{ $styles['title'] }}">{{ $title }}</p>
        @endif
        <div @class(['text-sm leading-relaxed '.$styles['body'], $title ? 'mt-1' : ''])>
            {{ $slot }}
        </div>
    </div>
    @if(isset($actions))
        <div class="shrink-0 sm:self-center [&_a]:inline-flex [&_a]:min-h-[44px] [&_a]:items-center [&_button]:min-h-[44px]">
            {{ $actions }}
        </div>
    @endif
</div>
