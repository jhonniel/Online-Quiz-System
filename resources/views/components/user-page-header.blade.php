@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 rounded-xl border border-indigo-700/20 bg-gradient-to-r from-indigo-600 to-indigo-700 px-4 py-4 shadow-md sm:flex-row sm:items-center sm:justify-between sm:px-5 sm:py-5']) }}>
    <div class="flex min-w-0 items-start gap-3">
        @isset($icon)
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white [&>svg]:h-6 [&>svg]:w-6 [&>svg]:max-h-6 [&>svg]:max-w-6" aria-hidden="true">
                {{ $icon }}
            </div>
        @endisset
        <div class="min-w-0 flex-1">
            <h1 class="truncate text-lg font-semibold tracking-tight text-white sm:text-xl">{{ $title }}</h1>
            @if(isset($subtitleSlot))
                <div class="mt-0.5 text-sm leading-relaxed text-indigo-100">{{ $subtitleSlot }}</div>
            @elseif($subtitle)
                <p class="mt-0.5 text-sm leading-relaxed text-indigo-100">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if(isset($actions))
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
