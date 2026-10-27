@props([
    'label',
    'value' => null,
    'hint' => null,
    'tone' => 'default',
    'class' => '',
])

@php
    $borderClass = match ($tone) {
        'indigo' => 'border-indigo-100',
        'emerald' => 'border-emerald-100',
        default => 'border-slate-200',
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

<div {{ $attributes->merge([
    'class' => trim('flex items-center gap-4 rounded-xl border bg-white p-4 shadow-sm transition-shadow hover:shadow-md '.$borderClass.' '.$class),
]) }}>
    @isset($icon)
        <div @class([
            'flex h-11 w-11 shrink-0 items-center justify-center rounded-lg',
            '[&>svg]:h-6 [&>svg]:w-6 [&>svg]:max-h-6 [&>svg]:max-w-6 [&>svg]:shrink-0',
            $iconClass,
        ])>
            {{ $icon }}
        </div>
    @endisset
    <div class="min-w-0 flex-1">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</p>
        <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">
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
