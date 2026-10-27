@props([
    'columns' => 'grid-cols-2 sm:grid-cols-3 xl:grid-cols-5',
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm']) }}>
    <div @class(['grid divide-x divide-y divide-slate-100', $columns, 'sm:divide-y-0'])>
        {{ $slot }}
    </div>
</div>
