@props([
    'label',
    'for',
    'col' => 'default',
])

@php
    $colClass = match ($col) {
        'wide' => 'min-w-0 w-full lg:col-span-4',
        'actions' => 'flex w-full min-w-0 flex-col gap-2 sm:col-span-2 sm:flex-row sm:flex-wrap lg:col-span-3 lg:justify-end',
        default => 'min-w-0 w-full sm:col-span-1 lg:col-span-3',
    };
@endphp

<div {{ $attributes->merge(['class' => $colClass]) }}>
    <label for="{{ $for }}" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</label>
    <div @class([
        'mt-1.5',
        'flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center' => $col === 'actions',
    ])>
        {{ $slot }}
    </div>
</div>
