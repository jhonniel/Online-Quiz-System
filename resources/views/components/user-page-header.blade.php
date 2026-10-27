@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 rounded-xl border border-indigo-800/30 bg-gradient-to-br from-indigo-600 via-indigo-600 to-indigo-800 px-3 py-3.5 shadow-md ring-1 ring-black/10 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-6 sm:py-5']) }}>
    <div class="flex min-w-0 items-start gap-2.5 sm:gap-3">
        @isset($icon)
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white sm:h-11 sm:w-11 [&>svg]:h-5 [&>svg]:w-5 [&>svg]:max-h-5 [&>svg]:max-w-5 sm:[&>svg]:h-6 sm:[&>svg]:w-6 sm:[&>svg]:max-h-6 sm:[&>svg]:max-w-6" aria-hidden="true">
                {{ $icon }}
            </div>
        @endisset
        <div class="min-w-0 flex-1">
            <h1 class="text-base font-semibold leading-snug tracking-tight text-white break-words sm:text-xl sm:leading-tight line-clamp-3 sm:line-clamp-none">{{ $title }}</h1>
            @if(isset($subtitleSlot))
                <div class="mt-0.5 text-xs leading-relaxed text-indigo-100 sm:text-sm [&_a]:break-words">{{ $subtitleSlot }}</div>
            @elseif($subtitle)
                <p class="mt-0.5 text-xs leading-relaxed text-indigo-100 sm:text-sm">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if(isset($actions))
        <div class="user-page-header__actions grid w-full min-w-0 grid-cols-1 gap-2 min-[480px]:grid-cols-2 sm:flex sm:w-auto sm:flex-row sm:flex-wrap sm:items-center">
            {{ $actions }}
        </div>
    @endif
</div>
