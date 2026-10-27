@props([
    'label',
    'value' => null,
    'hint' => null,
    'tone' => 'default',
    'class' => '',
])

@php
    $tones = [
        'default' => 'user-stat-card--default',
        'indigo' => 'user-stat-card--indigo',
        'emerald' => 'user-stat-card--emerald',
        'amber' => 'user-stat-card--amber',
        'rose' => 'user-stat-card--rose',
        'sky' => 'user-stat-card--sky',
    ];
    $toneClass = $tones[$tone] ?? $tones['default'];
@endphp

<div {{ $attributes->merge(['class' => 'user-stat-card '.$toneClass.' '.$class]) }}>
    @isset($icon)
        <div class="user-stat-card__icon">{{ $icon }}</div>
    @endisset
    <div class="user-stat-card__body min-w-0">
        <p class="user-stat-card__label">{{ $label }}</p>
        <p class="user-stat-card__value">
            @if(isset($valueSlot))
                {{ $valueSlot }}
            @elseif($value !== null)
                {!! $value !!}
            @endif
        </p>
        @if($hint)
            <p class="user-stat-card__hint">{{ $hint }}</p>
        @elseif(isset($hintSlot))
            <p class="user-stat-card__hint">{{ $hintSlot }}</p>
        @endif
    </div>
</div>
