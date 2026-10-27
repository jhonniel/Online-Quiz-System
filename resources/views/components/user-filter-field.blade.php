@props([
    'label',
    'for',
    'col' => 'default',
])

@if($col === 'actions')
    <div {{ $attributes->merge(['class' => 'user-portal-filter__actions']) }}>
        <span class="sr-only">{{ $label }}</span>
        {{ $slot }}
    </div>
@else
    <div {{ $attributes->merge(['class' => 'user-portal-filter__field']) }}>
        <label for="{{ $for }}" class="user-portal-filter__label">{{ $label }}</label>
        {{ $slot }}
    </div>
@endif
