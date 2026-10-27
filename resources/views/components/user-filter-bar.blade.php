@props([
    'action',
    'method' => 'GET',
])

<form method="{{ $method }}" action="{{ $action }}" {{ $attributes->merge(['class' => 'user-filter-bar']) }}>
    <div class="user-filter-bar__grid">
        {{ $slot }}
    </div>
</form>
