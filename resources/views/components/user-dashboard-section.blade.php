@props([
    'title',
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm']) }}>
    <div class="border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                @if($description)
                    <p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>
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
