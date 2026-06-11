@php
    $payslipCompanyName = trim((string) \App\Models\Setting::get('system_name', config('app.name', '')));
    $payslipCompanyAddress = trim((string) \App\Models\Setting::get('contact_address', ''));
@endphp

@if($payslipCompanyName !== '' || $payslipCompanyAddress !== '')
    <div class="payslip-sheet-header px-6 pt-5 pb-3 text-center border-b border-gray-200">
        @if($payslipCompanyName !== '')
            <p class="font-serif text-base sm:text-lg font-bold uppercase tracking-wide text-[#6b8e23]">{{ $payslipCompanyName }}</p>
        @endif
        @if($payslipCompanyAddress !== '')
            <p class="mt-1 text-xs sm:text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $payslipCompanyAddress }}</p>
        @endif
    </div>
@endif
