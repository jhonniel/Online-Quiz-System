@props([
    'label',
    'value',
    'valueClass' => 'text-slate-900',
    'featured' => false,
])

<div {{ $attributes->merge(['class' => trim('min-w-0 bg-white px-3 py-3.5 sm:px-5 sm:py-5 '.($featured ? 'flex flex-col justify-center sm:min-h-[8.5rem]' : ''))]) }}>
    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-[11px]">{{ $label }}</p>
    <p @class([
        'mt-1 font-semibold tabular-nums tracking-tight',
        'text-xl sm:mt-1.5 sm:text-2xl' => ! $featured,
        'text-3xl sm:mt-2 sm:text-4xl' => $featured,
        $valueClass,
    ])>{{ $value }}</p>
    @if(isset($hint))
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
