@props([
    'title',
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/5']) }}>
    <div class="border-b border-slate-200/80 bg-gradient-to-b from-slate-50 to-white px-5 py-4 sm:px-6 sm:py-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Overview</p>
                <h2 class="mt-0.5 text-lg font-semibold tracking-tight text-slate-900">{{ $title }}</h2>
                @if($description)
                    <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
                @endif
            </div>
            @if(isset($actions))
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
    <div class="space-y-5 p-5 sm:space-y-6 sm:p-6">
        {{ $slot }}
    </div>
</section>
