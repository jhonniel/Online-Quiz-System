@props([
    'user' => null,
    'name' => null,
    'verified' => null,
    'size' => 18,
    'badgeClass' => 'verified-badge--sm',
    'as' => 'span',
])

@php
    $displayName = $name ?? ($user?->name ?? '');
    $isVerified = $verified;
    if ($isVerified === null && $user instanceof \App\Models\User) {
        $isVerified = $user->hasVerifiedBadge();
    }
    $isVerified = (bool) $isVerified;
    $tag = in_array($as, ['span', 'div', 'p', 'h1', 'h2', 'h3', 'h4'], true) ? $as : 'span';
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 min-w-0 max-w-full align-middle']) }}>
    <span class="truncate">{{ $displayName }}</span>
    @if($isVerified)
        <x-verified-badge :size="(int) $size" class="{{ $badgeClass }}" />
    @endif
</{{ $tag }}>
