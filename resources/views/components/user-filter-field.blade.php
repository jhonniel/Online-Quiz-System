@props([
    'label',
    'for',
    'col' => 'default',
])

@php
    $colClass = match ($col) {
        'wide' => 'user-filter-bar__field user-filter-bar__field--wide',
        'actions' => 'user-filter-bar__actions',
        default => 'user-filter-bar__field',
    };
@endphp

<div {{ $attributes->merge(['class' => $colClass]) }}>
    <label for="{{ $for }}" class="user-filter-bar__label">{{ $label }}</label>
    <div @class([
        'mt-1.5',
        'flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center' => $col === 'actions',
    ])>
        {{ $slot }}
    </div>
</div>
