@props([
    'title',
    'subtitle' => null,
])

@php
    $subtitleIsSlot = isset($subtitle) && is_object($subtitle) && method_exists($subtitle, 'isEmpty') && ! $subtitle->isEmpty();
@endphp

<div {{ $attributes->merge(['class' => 'user-page-header-elevated relative isolate flex flex-col gap-3 overflow-hidden rounded-xl border border-indigo-900/25 bg-gradient-to-br from-indigo-600 via-indigo-700 to-indigo-900 px-3 py-3.5 shadow-lg shadow-indigo-900/20 ring-1 ring-white/10 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-6 sm:py-5']) }}>
    <div class="relative z-[1] flex min-w-0 items-start gap-2.5 sm:gap-3">
        @isset($icon)
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white sm:h-11 sm:w-11 [&>svg]:h-5 [&>svg]:w-5 [&>svg]:max-h-5 [&>svg]:max-w-5 sm:[&>svg]:h-6 sm:[&>svg]:w-6 sm:[&>svg]:max-h-6 sm:[&>svg]:max-w-6" aria-hidden="true">
                {{ $icon }}
            </div>
        @endisset
        <div class="min-w-0 flex-1">
            <h1 class="text-base font-semibold leading-snug tracking-tight text-white break-words sm:text-xl sm:leading-tight line-clamp-3 sm:line-clamp-none">{{ $title }}</h1>
            @if(isset($subtitleSlot))
                <div class="mt-1 text-xs leading-relaxed text-indigo-100/95 sm:text-sm [&_a]:font-medium [&_a]:text-white [&_a]:underline-offset-2 [&_a]:hover:underline">{{ $subtitleSlot }}</div>
            @elseif($subtitleIsSlot)
                <div class="mt-1 text-xs leading-relaxed text-indigo-100/95 sm:text-sm [&_a]:font-medium [&_a]:text-white">{{ $subtitle }}</div>
            @elseif(filled($subtitle))
                <p class="mt-1 text-xs leading-relaxed text-indigo-100/95 sm:text-sm">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if(isset($actions))
        <div class="user-page-header__actions relative z-[1] grid w-full min-w-0 grid-cols-1 gap-2 min-[480px]:grid-cols-2 sm:flex sm:w-auto sm:flex-row sm:flex-wrap sm:items-center">
            {{ $actions }}
        </div>
    @endif
</div>
