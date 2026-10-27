@props([
    'action',
    'method' => 'GET',
])

<form method="{{ $method }}" action="{{ $action }}" {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-200/90 bg-slate-50/80 p-3 ring-1 ring-slate-900/[0.03] sm:p-4']) }}>
    <div class="grid w-full grid-cols-1 items-end gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:gap-4">
        {{ $slot }}
    </div>
</form>
