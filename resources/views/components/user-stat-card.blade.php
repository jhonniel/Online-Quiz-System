@props([
    'label',
    'value' => null,
    'hint' => null,
    'tone' => 'default',
    'size' => 'default',
    'class' => '',
])

@php
    $borderClass = match ($tone) {
        'indigo' => 'border-indigo-100',
        'emerald' => 'border-emerald-100',
        'amber' => 'border-amber-100',
        'rose' => 'border-rose-100',
        'sky' => 'border-sky-100',
        default => 'border-slate-200/90',
    };
    $compactAccent = match ($tone) {
        'amber' => 'border-l-amber-500',
        'emerald' => 'border-l-emerald-500',
        'rose' => 'border-l-rose-500',
        'sky' => 'border-l-sky-500',
        'indigo' => 'border-l-indigo-500',
        default => 'border-l-slate-300',
    };
    $iconClass = match ($tone) {
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'sky' => 'bg-sky-50 text-sky-600',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

@php
    $isCompact = $size === 'compact';
@endphp

<div {{ $attributes->merge([
    'class' => trim(($isCompact
        ? 'border-l-4 bg-white p-3.5 shadow-sm '.$compactAccent.' '
        : 'flex items-center gap-4 rounded-xl border bg-white p-4 shadow-sm ring-1 ring-slate-900/5 transition-shadow hover:shadow-md '
    ).($isCompact ? 'border border-slate-200/90 ' : '').(!$isCompact ? $borderClass.' ' : '').$class),
]) }}>
    @if(! $isCompact)
        @isset($icon)
            <div @class([
                'flex h-11 w-11 shrink-0 items-center justify-center rounded-lg',
                '[&>svg]:h-6 [&>svg]:w-6 [&>svg]:max-h-6 [&>svg]:max-w-6 [&>svg]:shrink-0',
                $iconClass,
            ])>
                {{ $icon }}
            </div>
        @endisset
    @endif
    <div @class(['min-w-0 flex-1', $isCompact ? '' : ''])>
        <p @class([
            'font-medium text-slate-500',
            $isCompact ? 'text-[11px] uppercase tracking-wide' : 'text-xs uppercase tracking-wide',
        ])>{{ $label }}</p>
        <p @class([
            'font-semibold tabular-nums text-slate-900',
            $isCompact ? 'mt-0.5 text-xl' : 'mt-1 text-2xl',
        ])>
            @if(isset($valueSlot))
                {{ $valueSlot }}
            @elseif($value !== null)
                {!! $value !!}
            @endif
        </p>
        @if($hint)
            <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
        @elseif(isset($hintSlot))
            <p class="mt-1 text-xs text-slate-500">{{ $hintSlot }}</p>
        @endif
    </div>
</div>
