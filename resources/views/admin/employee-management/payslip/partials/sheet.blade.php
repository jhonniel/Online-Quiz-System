<div class="mx-auto max-w-3xl bg-white border border-gray-300 shadow-sm text-gray-900">
    <x-document-letterhead class="px-6 pt-5 pb-3" />

    <div class="border-y border-gray-900 bg-gray-200 px-6 py-1.5 text-center">
        <p class="text-sm font-bold uppercase tracking-wide text-gray-900">Payslip</p>
    </div>

    <div class="px-6 py-3 text-sm text-gray-900 border-b border-gray-300">
        <p class="font-bold">Period Covered: {{ $payslip->periodLabel() }}</p>
        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1">
            <div class="space-y-1">
                <p>Employee Name: <span class="font-semibold">{{ strtoupper($payslip->employee_name) }}</span></p>
                <p>Position: <span class="font-semibold">{{ strtoupper($payslip->position ?: '—') }}</span></p>
            </div>
            <div class="space-y-1">
                <p>Date Hired: <span class="font-semibold">{{ $payslip->date_hired?->format('F j, Y') ?: '—' }}</span></p>
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

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
            <div class="flex flex-col items-center">
                <p class="font-medium">Prepared by:</p>
                <div class="flex w-full flex-col items-center">
                    <div class="relative inline-block min-w-[11rem] max-w-[11rem] pt-4">
                        <p class="relative z-0 border-b border-gray-900 px-2 font-semibold">
                            {{ $payslip->prepared_by ?: ' ' }}
                        </p>
                    </div>
                    <p class="mt-2 min-h-[1.25rem]">Admin Officer</p>
                </div>
            </div>
            <div class="flex flex-col items-center">
                <p class="font-medium">Approved by:</p>
                <div class="flex w-full flex-col items-center">
                    <div class="relative inline-block min-w-[11rem] max-w-[11rem] pt-4">
                        <p class="relative z-0 border-b border-gray-900 px-2 font-semibold">
                            {{ $payslip->approved_by ?: ' ' }}
                        </p>
                    </div>
                    <p class="mt-2 min-h-[1.25rem]">Proprietor</p>
                </div>
            </div>
            <div class="flex flex-col items-center">
                <p class="font-medium">Received by:</p>
                <div class="flex w-full flex-col items-center">
                    @php($receivedBySignature = $payslip->isLinkedToEmployee() ? $payslip->receivedBySignatureDataUri() : null)
                    <div class="relative inline-block min-w-[11rem] max-w-[11rem] pt-4">
                        @if($receivedBySignature)
                            <img src="{{ $receivedBySignature }}"
                                 alt="Signature of {{ $payslip->employee_name }}"
                                 class="pointer-events-none absolute -top-3 left-1/2 z-10 h-14 w-full max-w-[11rem] -translate-x-1/2 object-contain">
                        @endif
                        <p class="relative z-0 border-b border-gray-900 px-2 font-semibold">
                            {{ $payslip->isLinkedToEmployee() ? $payslip->employee_name : ' ' }}
                        </p>
                    </div>
                    <p class="mt-2 min-h-[1.25rem]">{{ $payslip->isLinkedToEmployee() ? ($payslip->position ?: '—') : ' ' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
