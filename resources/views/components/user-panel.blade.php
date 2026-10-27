@props([
    'title' => null,
    'subtitle' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'user-panel']) }}>
    @if($title || $subtitle || isset($actions))
        <div class="user-panel__head">
            <div class="min-w-0">
                @if($title)
                    <h2 class="user-panel__title">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="user-panel__subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($actions))
                <div class="user-panel__actions">{{ $actions }}</div>
            @endif
        </div>
    @endif
    <div @class(['user-panel__body' => $padding])>
        {{ $slot }}
    </div>
</div>
