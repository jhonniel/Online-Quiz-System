@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<section {{ $attributes->merge(['class' => 'user-dashboard-section min-w-0 overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm shadow-slate-900/[0.03] ring-1 ring-slate-900/[0.04]']) }}>
    <div class="border-b border-slate-200/70 bg-gradient-to-b from-slate-50/95 to-white px-3 py-3.5 sm:px-6 sm:py-5">
        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-end sm:justify-between sm:gap-3">
            <div class="min-w-0">
                @if(filled($eyebrow))
                    <p class="user-section-eyebrow">{{ $eyebrow }}</p>
                @endif
                <h2 class="mt-0.5 text-base font-semibold tracking-tight text-slate-900 break-words sm:text-lg">{{ $title }}</h2>
                @if($description)
                    <p class="mt-1 text-xs text-slate-600 sm:text-sm">{{ $description }}</p>
                @endif
            </div>
            @if(isset($actions))
                <div class="flex w-full flex-wrap items-stretch gap-2 sm:w-auto sm:items-center sm:shrink-0 [&_.user-header-btn-primary]:flex-1 sm:[&_.user-header-btn-primary]:flex-none">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
    <div class="space-y-4 p-3 sm:space-y-6 sm:p-6">
        {{ $slot }}
    </div>
</section>
