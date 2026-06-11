@php
    use App\Support\PayslipYearlySummary;

    $formatMoney = fn ($amount) => PayslipYearlySummary::formatAmount($amount);
    $rows = $summary['rows'];
    $totals = $summary['totals'];
    $autoPrint = $autoPrint ?? false;
@endphp

@if(count($rows) === 0)
    <div class="rounded-lg border border-gray-200 bg-white px-6 py-12 text-center text-sm text-gray-500">
        {{ $emptyMessage }}
    </div>
@else
    <div id="payslip-summary-print-area" class="payslip-summary-print-area rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden print:overflow-visible">
        <div class="overflow-x-auto print:overflow-visible payslip-summary-table-wrap">
            <table class="min-w-full border-collapse text-xs sm:text-sm payslip-summary-table">
                <thead class="payslip-summary-table-head">
                    @include('admin.employee-management.payslip.partials.summary-sheet-header', [
                        'companyName' => $companyName,
                        'sheetTitle' => $sheetTitle,
                    ])
                    <tr class="payslip-summary-columns-header-row bg-[#1e3a8a] text-white">
                        <th rowspan="2" class="border border-[#1e40af] px-3 py-2 text-left font-bold italic whitespace-nowrap">Employee Name</th>
                        <th rowspan="2" class="border border-[#1e40af] px-3 py-2 text-left font-bold italic whitespace-nowrap">Date Hired</th>
                        <th rowspan="2" class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">Total Basic Salary</th>
                        <th colspan="4" class="border border-[#1e40af] px-3 py-2 text-center font-bold italic uppercase tracking-wide">Allowable Deductions</th>
                        <th rowspan="2" class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">Regular OT</th>
                        <th rowspan="2" class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">Holiday</th>
                        <th rowspan="2" class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">Gross Salary</th>
                        <th rowspan="2" class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">Taxable Income</th>
                        <th rowspan="2" class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">Tax Withheld</th>
                        <th colspan="3" class="border border-[#1e40af] px-3 py-2 text-center font-bold italic uppercase tracking-wide">Company Loans</th>
                        <th rowspan="2" class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">Net Pay</th>
                    </tr>
                    <tr class="payslip-summary-columns-header-row bg-[#1e3a8a] text-white">
                        <th class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">SSS</th>
                        <th class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">HDMF</th>
                        <th class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">PHIC</th>
                        <th class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">13th Month</th>
                        <th class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">CA</th>
                        <th class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">Gov't Loans</th>
                        <th class="border border-[#1e40af] px-3 py-2 text-right font-bold italic whitespace-nowrap">Loans</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($rows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-200 px-3 py-2 font-medium text-gray-900 whitespace-nowrap">{{ $row['employee_name'] }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-gray-700 whitespace-nowrap">
                                {{ ($row['date_hired'] ?? null)?->format('M d, Y') ?: '—' }}
                            </td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['total_basic_salary']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['sss']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['hdmf']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['phic']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['thirteenth_month']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['regular_ot']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['holiday']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['gross_salary']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['taxable_income']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['tax_withheld']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['ca']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['govt_loans']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right text-gray-900 tabular-nums">{{ $formatMoney($row['loans']) }}</td>
                            <td class="border border-gray-200 px-3 py-2 text-right font-semibold text-gray-900 tabular-nums payslip-summary-net-pay-cell">
                                {{ $formatMoney($row['net_pay']) }}
                                @if(! empty($row['signature_data_uri']))
                                    <img src="{{ $row['signature_data_uri'] }}"
                                         alt=""
                                         class="payslip-summary-signature-img">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-bold text-gray-900">
                        <td class="border border-gray-300 px-3 py-2" colspan="2">TOTAL</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['total_basic_salary']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['sss']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['hdmf']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['phic']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['thirteenth_month']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['regular_ot']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['holiday']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['gross_salary']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['taxable_income']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['tax_withheld']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['ca']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['govt_loans']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['loans']) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right tabular-nums">{{ $formatMoney($totals['net_pay']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif

@include('admin.employee-management.payslip.partials.payroll-summary-print-styles')

@if($autoPrint && count($rows) > 0)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.print();
    });
</script>
@endif
