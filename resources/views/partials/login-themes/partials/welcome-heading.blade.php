@php
    $tone = $tone ?? 'halloween';
@endphp

<h1 @class([
    'font-black text-white drop-shadow-lg text-center',
    'text-5xl xl:text-6xl mb-0 animate-float-gentle' => ($variant ?? '') === 'default',
    'text-4xl xl:text-5xl mt-6 mb-0' => ($variant ?? '') !== 'default',
])>
    Welcome to {{ $settings['system_name'] }}
</h1>

@if(! empty($tagline))
    <p @class([
        'text-center drop-shadow-md mb-0',
        'text-xl xl:text-2xl font-semibold text-white/95 mt-6 animate-float-slow' => $tone === 'default',
        'text-lg xl:text-xl font-medium text-violet-100/95 mt-4' => $tone === 'halloween',
        'text-xl xl:text-2xl font-medium text-sky-100/95 mt-5' => $tone === 'christmas',
        'text-lg xl:text-xl font-medium text-red-50/95 mt-4' => $tone === 'hohoho',
    ])>
        {{ $tagline }}
    </p>
@endif
