{{-- Ghosto — ghost with witch hat + pumpkin (scoped CSS) --}}
@php
    $size = $size ?? 'md';
    $sizeClass = match ($size) {
        'sm' => 'ghosto-wrap--sm',
        'lg' => 'ghosto-wrap--lg',
        default => '',
    };
@endphp
<div class="ghosto-wrap {{ $sizeClass }}" aria-hidden="true">
    <div class="ghosto-backg">
        <div class="ghosto-body">
            <div class="ghosto-hat">
                <div class="ghosto-hat1">
                    <div class="ghosto-s1"></div>
                </div>
            </div>
            <div class="ghosto-face">
                <div class="ghosto-eye-l">
                    <div class="ghosto-dot1"></div>
                    <div class="ghosto-dot2"></div>
                </div>
                <div class="ghosto-eye-r">
                    <div class="ghosto-dot1"></div>
                    <div class="ghosto-dot2"></div>
                </div>
                <div class="ghosto-blsh-l"></div>
                <div class="ghosto-blsh-r"></div>
                <div class="ghosto-mouth"></div>
            </div>
            <div class="ghosto-hand-l"></div>
            <div class="ghosto-hand-r"></div>
            <div class="ghosto-pumpkin">
                <div class="ghosto-handle"></div>
                <div class="ghosto-p1"></div>
                <div class="ghosto-p2"></div>
                <div class="ghosto-p3"></div>
                <div class="ghosto-p4"></div>
                <div class="ghosto-e-l"></div>
                <div class="ghosto-e-r"></div>
                <div class="ghosto-nose"></div>
                <div class="ghosto-m">
                    <div class="ghosto-t1"></div>
                    <div class="ghosto-t2"></div>
                    <div class="ghosto-t3"></div>
                    <div class="ghosto-t4"></div>
                </div>
            </div>
        </div>
    </div>
</div>
