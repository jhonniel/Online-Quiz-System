{{--
    Standard admin filter/search form submit button.
    Use for Filter, Apply Filters, Apply, Search, etc. on admin list pages.
--}}
@props([
    'fullWidth' => false,
    'size' => 'md',
])

@php
    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
    ];

    $classes = implode(' ', array_filter([
        'inline-flex items-center justify-center font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-colors whitespace-nowrap',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $fullWidth ? 'w-full' : null,
    ]));
@endphp

<button type="submit" {{ $attributes->merge(['class' => $classes]) }}>
    @if(trim($slot) !== '')
        {{ $slot }}
    @else
        Filter
    @endif
</button>
