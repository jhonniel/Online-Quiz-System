@props([
    'label',
    'for',
    'col' => 'default',
])

@php
    $wrapperClass = $col === 'actions' ? 'user-portal-filter__actions' : 'user-portal-filter__field';
@endphp

<div {{ $attributes->merge(['class' => $wrapperClass]) }}>
    @if($col !== 'actions')
        <label for="{{ $for }}" class="user-portal-filter__label">{{ $label }}</label>
    @else
        <span class="sr-only">{{ $label }}</span>
    @endif
    <div>
        {{ $slot }}
    </div>
</div>
