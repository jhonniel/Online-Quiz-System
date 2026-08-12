@props([
    'user' => null,
    'name' => null,
    'verified' => null,
    'size' => 11,
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
    $resolvedSize = (int) $size;
    if ($resolvedSize <= 0) {
        $resolvedSize = 11;
    }
    $resolvedSize = max(10, min(12, $resolvedSize));
    // Keep CSS size classes aligned with the intended visual size.
    if ($badgeClass === 'verified-badge--sm' || $badgeClass === '') {
        $badgeClass = $resolvedSize <= 10
            ? 'verified-badge--xs'
            : ($resolvedSize >= 12 ? 'verified-badge--profile' : 'verified-badge--sm');
    }
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => 'inline-flex items-center gap-0 min-w-0 max-w-full align-middle']) }}>
    <span class="truncate">{{ $displayName }}</span>
    @if($isVerified)
        <x-verified-badge :size="$resolvedSize" class="{{ $badgeClass }}" />
    @endif
</{{ $tag }}>
