{{-- CSS ghost "Boo" (adapted from a Codepen-style floating ghost; social block omitted) --}}
@php
    $size = $size ?? 'md';
    $sizeClass = match ($size) {
        'sm' => 'boo-scene--sm',
        'lg' => 'boo-scene--lg',
        default => '',
    };
@endphp
<div class="boo-scene {{ $sizeClass }}" @if(!empty($delayClass)) data-boo-delay="{{ $delayClass }}" @endif aria-hidden="true">
    <div class="boo-scene__figure">
        <div class="boo-ghost">
            <div class="boo-ghost__eyes"></div>
            <div class="boo-ghost__dimples"></div>
            <div class="boo-ghost__feet">
                <div class="boo-ghost__feet-foot"></div>
                <div class="boo-ghost__feet-foot"></div>
                <div class="boo-ghost__feet-foot"></div>
                <div class="boo-ghost__feet-foot"></div>
            </div>
        </div>
        <div class="boo-shadow"></div>
    </div>
</div>
