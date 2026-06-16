@php
    $signatureUri = $signatory['e_signature_data_uri'] ?? null;
    $name = $signatory['name'] ?? '';
    $role = $signatory['role'] ?? '';
@endphp

<div class="signatory-block">
    @if(!empty($signatureUri))
        <img src="{{ $signatureUri }}" alt="E-Signature" class="signature-image">
    @endif
    <p class="signatory-name">{{ $name !== '' ? $name : '—' }}</p>
    <p class="signatory-role">{{ $role }}</p>
</div>
