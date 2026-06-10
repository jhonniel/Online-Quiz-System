@props([
    'companyAddress' => null,
])

@php
    use App\Support\DocumentLetterhead;

    $letterheadUrl = DocumentLetterhead::assetUrl();
@endphp

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    <img src="{{ $letterheadUrl }}"
         alt="Mini Clean Business Solutions"
         class="mx-auto h-auto w-full max-w-md object-contain"
         width="480"
         height="120">
</div>
