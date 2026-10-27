@props([
    'label',
    'value',
    'valueClass' => 'text-slate-900',
])

<div {{ $attributes->merge(['class' => 'bg-white px-4 py-4 sm:px-5 sm:py-5']) }}>
    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
    <p @class(['mt-1.5 text-2xl font-semibold tabular-nums tracking-tight', $valueClass])>{{ $value }}</p>
    @if(isset($hint))
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
