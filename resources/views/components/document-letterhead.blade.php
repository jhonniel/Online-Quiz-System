@props([
    'companyAddress' => null,
])

@php
    $companyName = trim((string) \App\Models\Setting::get('system_name', config('app.name', 'Laravel')));
    $companyAddress = $companyAddress ?? trim((string) \App\Models\Setting::get('contact_address', ''));
@endphp

<div {{ $attributes->merge(['class' => 'text-center']) }}>
    <h2 class="text-lg uppercase tracking-wide text-[#6b8e23]" style="font-family: Georgia, 'Times New Roman', Times, serif;">
        {{ $companyName }}
    </h2>
    @if($companyAddress !== '')
        <p class="mt-1 text-xs text-gray-900 leading-snug">{{ $companyAddress }}</p>
    @endif
</div>
