@props([
    'action',
    'method' => 'GET',
])

<form method="{{ $method }}" action="{{ $action }}" {{ $attributes->merge(['class' => 'user-portal-filter']) }}>
    <div class="user-portal-filter__grid">
        {{ $slot }}
    </div>
</form>
