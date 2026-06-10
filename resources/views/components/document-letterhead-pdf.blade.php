@props([
    'companyAddress' => null,
])

@php
    use App\Support\DocumentLetterhead;

    $letterheadDataUri = DocumentLetterhead::dataUri();
@endphp

@if($letterheadDataUri !== '')
    <div style="text-align: center; margin: 0 0 20px 0;">
        <img src="{{ $letterheadDataUri }}"
             alt="Mini Clean Business Solutions"
             style="display: block; width: 100%; max-width: 420px; height: auto; margin: 0 auto;">
    </div>
@endif
