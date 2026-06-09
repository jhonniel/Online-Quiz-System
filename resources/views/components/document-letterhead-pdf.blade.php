@props([
    'companyAddress' => null,
])

@php
    $companyName = trim((string) \App\Models\Setting::get('system_name', config('app.name', 'Laravel')));
    $companyAddress = $companyAddress ?? trim((string) \App\Models\Setting::get('contact_address', ''));
@endphp

<div style="text-align: center; margin-bottom: 20px;">
    <p style="margin: 0 0 6px 0; font-family: 'DejaVu Serif', 'Times New Roman', serif; font-size: 14pt; color: #6b8e23; text-transform: uppercase;">
        {{ $companyName }}
    </p>
    @if($companyAddress !== '')
        <p style="margin: 0; font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 9pt; color: #000000;">
            {{ $companyAddress }}
        </p>
    @endif
</div>
