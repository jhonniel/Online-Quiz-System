@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
    'embedded' => false,
])

<div {{ $attributes->merge(['class' => $embedded
    ? 'overflow-hidden rounded-lg border border-slate-200/80 bg-white'
    : 'overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/5']) }}>
    @if($title || $subtitle || isset($actions))
        <div @class([
            'relative z-10 flex flex-col gap-3 border-b px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-4',
            $embedded ? 'border-slate-200/80 bg-white' : 'border-slate-100 bg-slate-50/95 sm:px-5 sm:py-4',
        ])>
            <div class="min-w-0">
                @if($title)
                    <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($actions))
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endif
        </div>
    @endif
    @if($padding)
        <div class="user-panel__body relative z-0 min-h-0 overflow-hidden p-4 sm:p-5">
            {{ $slot }}
        </div>
    @else
        <div class="user-panel__body user-panel__body--flush relative z-0 min-h-0 overflow-hidden">
            {{ $slot }}
        </div>
    @endif
</div>
