@php
    use App\Support\PayslipNameFit;
@endphp
<div class="mx-auto max-w-3xl overflow-hidden bg-white border border-gray-300 shadow-sm text-gray-900">
    <x-document-letterhead class="px-6 pt-5 pb-3" />

    <div class="border-y border-gray-900 bg-gray-200 px-6 py-1.5 text-center">
        <p class="text-sm font-bold uppercase tracking-wide text-gray-900">Payslip</p>
    </div>

    <div class="px-6 py-3 text-sm text-gray-900 border-b border-gray-300">
        <p class="font-bold">Period Covered: {{ $payslip->periodLabel() }}</p>
        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1">
            <div class="space-y-1">
                <p class="payslip-name-line grid grid-cols-[auto_minmax(0,1fr)] gap-x-1 items-baseline min-w-0">
                    <span class="shrink-0">Employee Name:</span>
                    <span class="payslip-fit-name-container min-w-0 overflow-hidden">
                        <span class="font-semibold payslip-employee-name payslip-fit-name block max-w-full"
                              style="font-size: {{ PayslipNameFit::fontSizeRem(strtoupper($payslip->employee_name), 0.875) }}">{{ strtoupper($payslip->employee_name) }}</span>
                    </span>
                </p>
                <p class="payslip-name-line grid grid-cols-[auto_minmax(0,1fr)] gap-x-1 items-baseline min-w-0">
                    <span class="shrink-0">Position:</span>
                    <span class="payslip-fit-name-container min-w-0 overflow-hidden">
                        <span class="font-semibold payslip-position-name payslip-fit-name block max-w-full"
                              style="font-size: {{ PayslipNameFit::fontSizeRem(strtoupper($payslip->displayPosition() ?: '—'), 0.875) }}">{{ strtoupper($payslip->displayPosition() ?: '—') }}</span>
                    </span>
                </p>
            </div>
            <div class="space-y-1">
                <p>Date Hired: <span class="font-semibold">{{ $payslip->displayDateHired()?->format('F j, Y') ?: '—' }}</span></p>
                <p>Rate per day: <span class="font-semibold">{{ $payslip->formatMoney($payslip->rate_per_day) }}</span></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-0 text-sm">
        <div class="border-b sm:border-b-0 sm:border-r border-gray-300">
            <div class="px-6 py-3 font-bold uppercase tracking-wide border-b border-gray-300 bg-gray-50">Deductions</div>
            <div class="px-6 py-4 space-y-2">
                <div class="flex justify-between gap-4"><span>SSS</span><span>{{ $payslip->formatMoney($payslip->sss) }}</span></div>
                <div class="flex justify-between gap-4"><span>PHIC</span><span>{{ $payslip->formatMoney($payslip->phic) }}</span></div>
                <div class="flex justify-between gap-4"><span>HDMF</span><span>{{ $payslip->formatMoney($payslip->hdmf) }}</span></div>
                <div class="flex justify-between gap-4"><span>Late/hrs</span><span>{{ $payslip->formatCount($payslip->late_hours) }}</span></div>
                <div class="flex justify-between gap-4"><span>Absences/day</span><span>{{ $payslip->formatCount($payslip->absences_days) }}</span></div>
                <div class="flex justify-between gap-4"><span>Withholding Tax</span><span>{{ $payslip->formatMoney($payslip->withholding_tax) }}</span></div>
                <div class="border-t border-gray-300 pt-2 mt-2">
                    <p class="text-xs font-bold uppercase tracking-wide text-center text-gray-900 py-1.5 bg-gray-100 border border-gray-200">Company Loans</p>
                </div>
                <div class="flex justify-between gap-4"><span>CA</span><span>{{ $payslip->formatMoney($payslip->ca) }}</span></div>
                <div class="flex justify-between gap-4"><span>Gov't Loans</span><span>{{ $payslip->formatMoney($payslip->govt_loans) }}</span></div>
                <div class="flex justify-between gap-4"><span>Loans</span><span>{{ $payslip->formatMoney($payslip->loans) }}</span></div>
                <div class="flex justify-between gap-4 font-bold border-t border-gray-200 pt-2"><span>Total Deduction</span><span>{{ $payslip->formatMoney($payslip->calculatedTotalDeductions()) }}</span></div>
            </div>
        </div>
        <div>
            <div class="px-6 py-3 font-bold uppercase tracking-wide border-b border-gray-300 bg-gray-50">Earnings</div>
            <div class="px-6 py-4 space-y-2">
                <div class="flex justify-between gap-4"><span>Total Working Days</span><span>{{ $payslip->total_working_days ?: '-' }}</span></div>
                <div class="flex justify-between gap-4"><span>Overtime pay</span><span>{{ $payslip->formatMoney($payslip->overtime_pay) }}</span></div>
                <div class="flex justify-between gap-4"><span>Holidays pay</span><span>{{ $payslip->formatMoney($payslip->holiday_pay) }}</span></div>
                <div class="flex justify-between gap-4"><span>Allowances</span><span>{{ $payslip->formatMoney($payslip->allowances) }}</span></div>
                <div class="flex justify-between gap-4"><span>13th Month</span><span>{{ $payslip->formatMoney($payslip->thirteenth_month_pay) }}</span></div>
                <div class="flex justify-between gap-4 font-bold border-t border-gray-200 pt-2"><span>Gross Pay</span><span>{{ $payslip->formatMoney($payslip->gross_pay) }}</span></div>
            </div>
        </div>
    </div>

    <div class="border-t border-gray-300 px-6 py-4 text-center">
        <p class="text-base font-bold uppercase tracking-wide">Net Pay: {{ $payslip->formatMoney($payslip->net_pay) }}</p>
    </div>

    <div class="border-t border-gray-300 px-6 py-6 text-sm text-gray-900">
        <p class="text-center leading-relaxed">
            I hereby declared that I received this payroll and I don't have any questions or further clarifications.
        </p>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 text-center payslip-signatures-grid">
            <div class="flex min-w-0 flex-col items-center">
                <p class="font-medium">Prepared by:</p>
                <div class="flex w-full min-w-0 flex-col items-center">
                    @php($preparedBySignature = $payslip->preparedBySignatureDataUri())
                    <div class="relative w-full min-w-0 max-w-full px-1 pt-4 payslip-sign-name-block payslip-fit-name-container">
                        @if($preparedBySignature)
                            <img src="{{ $preparedBySignature }}"
                                 alt="Signature of {{ $payslip->displayPreparedBy() }}"
                                 class="payslip-esign-float pointer-events-none absolute left-1/2 top-0 z-0 h-14 w-40 max-w-[90%] -translate-x-1/2 -translate-y-1 object-contain object-bottom">
                        @endif
                        <p class="relative z-10 border-b border-gray-900 px-1 font-semibold payslip-sign-name payslip-fit-name payslip-name-line text-center mx-auto max-w-full"
                           style="font-size: {{ PayslipNameFit::fontSizeRem($payslip->displayPreparedBy(), 0.875) }}">
                            {{ $payslip->displayPreparedBy() ?: ' ' }}
                        </p>
                    </div>
                    <p class="mt-2 min-h-[1.25rem]">Admin Officer</p>
                </div>
            </div>
            <div class="flex min-w-0 flex-col items-center">
                <p class="font-medium">Approved by:</p>
                <div class="flex w-full min-w-0 flex-col items-center">
                    @php($approvedBySignature = $payslip->approvedBySignatureDataUri())
                    <div class="relative w-full min-w-0 max-w-full px-1 pt-4 payslip-sign-name-block payslip-fit-name-container">
                        @if($approvedBySignature)
                            <img src="{{ $approvedBySignature }}"
                                 alt="Signature of {{ $payslip->displayApprovedBy() }}"
                                 class="payslip-esign-float pointer-events-none absolute left-1/2 top-0 z-0 h-14 w-40 max-w-[90%] -translate-x-1/2 -translate-y-1 object-contain object-bottom">
                        @endif
                        <p class="relative z-10 border-b border-gray-900 px-1 font-semibold payslip-sign-name payslip-fit-name payslip-name-line text-center mx-auto max-w-full"
                           style="font-size: {{ PayslipNameFit::fontSizeRem($payslip->displayApprovedBy(), 0.875) }}">
                            {{ $payslip->displayApprovedBy() ?: ' ' }}
                        </p>
                    </div>
                    <p class="mt-2 min-h-[1.25rem]">Proprietor</p>
                </div>
            </div>
            <div class="flex min-w-0 flex-col items-center">
                <p class="font-medium">Received by:</p>
                <div class="flex w-full min-w-0 flex-col items-center">
                    @php($receivedBySignature = $payslip->isLinkedToEmployee() ? $payslip->receivedBySignatureDataUri() : null)
                    <div class="relative w-full min-w-0 max-w-full px-1 pt-4 payslip-sign-name-block payslip-fit-name-container">
                        @if($receivedBySignature)
                            <img src="{{ $receivedBySignature }}"
                                 alt="Signature of {{ $payslip->employee_name }}"
                                 class="payslip-esign-float pointer-events-none absolute left-1/2 top-0 z-0 h-14 w-40 max-w-[90%] -translate-x-1/2 -translate-y-1 object-contain object-bottom">
                        @endif
                        <p class="relative z-10 border-b border-gray-900 px-1 font-semibold payslip-sign-name payslip-fit-name payslip-name-line text-center mx-auto max-w-full"
                           style="font-size: {{ PayslipNameFit::fontSizeRem($payslip->isLinkedToEmployee() ? $payslip->employee_name : '', 0.875) }}">
                            {{ $payslip->isLinkedToEmployee() ? $payslip->employee_name : ' ' }}
                        </p>
                    </div>
                    <div class="mt-2 min-h-[1.25rem] w-full min-w-0 max-w-full px-1 payslip-fit-name-container">
                        <p class="payslip-name-line payslip-position-name payslip-fit-name max-w-full mx-auto text-center"
                           style="font-size: {{ PayslipNameFit::fontSizeRem($payslip->isLinkedToEmployee() ? ($payslip->displayPosition() ?: '—') : '', 0.8125) }}">{{ $payslip->isLinkedToEmployee() ? ($payslip->displayPosition() ?: '—') : ' ' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
