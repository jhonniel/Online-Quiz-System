@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm']) }}>
    @if($title || $subtitle || isset($actions))
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
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
    <div @class(['p-4 sm:p-5' => $padding])>
        {{ $slot }}
    </div>
</div>
