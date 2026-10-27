@props([
    'label',
    'value',
    'valueClass' => 'text-slate-900',
])

<div {{ $attributes->merge(['class' => 'min-w-0 bg-white px-3 py-3.5 sm:px-5 sm:py-5']) }}>
    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-[11px]">{{ $label }}</p>
    <p @class(['mt-1 text-xl font-semibold tabular-nums tracking-tight sm:mt-1.5 sm:text-2xl', $valueClass])>{{ $value }}</p>
    @if(isset($hint))
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
