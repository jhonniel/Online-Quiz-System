@php
    $tone = $tone ?? 'halloween';
@endphp

<div @class([
    'login-welcome-copy',
    'login-welcome-copy--'.$tone,
])>
    <h1 @class([
        'login-welcome-copy__title font-black text-white text-center break-words leading-tight',
        'text-5xl xl:text-6xl mb-0 animate-float-gentle' => ($variant ?? '') === 'default',
        'text-2xl sm:text-3xl xl:text-[2rem] mb-0' => ($variant ?? '') !== 'default',
    ])>
        Welcome to {{ $settings['system_name'] }}
    </h1>

    @if(! empty($tagline))
        <p @class([
            'login-welcome-copy__tagline text-center mb-0',
            'text-xl xl:text-2xl font-semibold mt-4 animate-float-slow' => $tone === 'default',
            'text-base sm:text-lg xl:text-xl font-medium mt-3' => $tone !== 'default',
        ])>
            {{ $tagline }}
        </p>
    @endif
</div>
