@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'user-page-header']) }}>
    <div class="user-page-header__main">
        @isset($icon)
            <div class="user-page-header__icon" aria-hidden="true">
                {{ $icon }}
            </div>
        @endisset
        <div class="min-w-0 flex-1">
            <h1 class="user-page-header__title">{{ $title }}</h1>
            @if($subtitle)
                <p class="user-page-header__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if(isset($actions))
        <div class="user-page-header__actions">
            {{ $actions }}
        </div>
    @endif
</div>
