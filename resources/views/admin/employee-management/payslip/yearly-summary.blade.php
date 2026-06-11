@extends('layouts.admin')

@section('title', 'Payslip Yearly Summary')

@section('page-title', 'Payslip Yearly Summary')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <a href="{{ route('admin.payslip.index') }}" class="text-gray-500 text-sm hover:text-gray-700">Payslip</a>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">{{ $year }} Summary</span>
</li>
@endsection

@section('content')
@php
    use App\Support\PayslipYearlySummary;

    $formatMoney = fn ($amount) => PayslipYearlySummary::formatAmount($amount);
    $rows = $summary['rows'];
    $totals = $summary['totals'];
@endphp

<div class="space-y-6">
    <div class="print:hidden flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.payslip.index', request()->only('search', 'period_start', 'period_end')) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to payslip records</a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900">Payslip Yearly Summary — {{ $year }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Totals per employee for all payslips with period ending in {{ $year }}.
                {{ number_format($summary['employee_count']) }} employee(s) · {{ number_format($summary['payslip_count']) }} payslip record(s).
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <form method="GET" action="{{ route('admin.payslip.yearly-summary') }}" class="flex items-center gap-2">
                <label for="summary-year" class="text-sm font-medium text-gray-700 shrink-0">Year</label>
                <select id="summary-year" name="year" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @forelse($availableYears as $availableYear)
                        <option value="{{ $availableYear }}" {{ (int) $year === (int) $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                    @empty
                        <option value="{{ $year }}" selected>{{ $year }}</option>
                    @endforelse
                </select>
                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    View
                </button>
            </form>

            @if(count($rows) > 0)
                <button type="button"
                        onclick="printPayslipYearlySummary()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
                <a href="{{ route('admin.payslip.yearly-summary.csv', ['year' => $year]) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-emerald-800 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download CSV
                </a>
            @endif
        </div>
    </div>

    @if(count($rows) === 0)
        <div class="rounded-lg border border-gray-200 bg-white px-6 py-12 text-center text-sm text-gray-500">
            No payslip records found for {{ $year }}.
        </div>
    @else
        <div id="payslip-summary-print-area" class="payslip-summary-print-area rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="payslip-summary-print-header px-4 pt-4 pb-3 border-b border-gray-200">
                <h1 class="text-lg font-bold text-gray-900">Payslip Yearly Summary — {{ $year }}</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ number_format($summary['employee_count']) }} employee(s) · {{ number_format($summary['payslip_count']) }} payslip record(s)
                    · Generated {{ now()->format('M d, Y h:i A') }}
                </p>
            </div>
            <div class="overflow-x-auto print:overflow-visible">
                <table class="min-w-full border-collapse text-xs sm:text-sm payslip-summary-table">
                    <thead>
                        <tr class="bg-[#1e3a8a] text-white">
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
                        <tr class="bg-[#1e3a8a] text-white">
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
                                <td class="border border-gray-200 px-3 py-2 text-right font-semibold text-gray-900 tabular-nums">{{ $formatMoney($row['net_pay']) }}</td>
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
</div>

<style>
    .payslip-summary-print-header {
        display: none;
    }

    @media print {
        @page {
            size: legal landscape;
            margin: 8mm;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body * {
            visibility: hidden;
        }

        #payslip-summary-print-area,
        #payslip-summary-print-area * {
            visibility: visible;
        }

        #payslip-summary-print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none !important;
            box-shadow: none !important;
            overflow: visible !important;
        }

        .payslip-summary-print-header {
            display: block !important;
        }

        .payslip-summary-table {
            font-size: 9px !important;
            width: 100% !important;
        }

        .payslip-summary-table th,
        .payslip-summary-table td {
            padding: 4px 6px !important;
        }

        .payslip-summary-table thead tr {
            background-color: #1e3a8a !important;
            color: #fff !important;
        }

        .payslip-summary-table thead th {
            border-color: #1e40af !important;
        }

        .payslip-summary-table tbody tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .payslip-summary-table tfoot tr {
            background-color: #f3f4f6 !important;
            page-break-inside: avoid;
            break-inside: avoid;
        }
    }
</style>

<script>
    function printPayslipYearlySummary() {
        window.print();
    }

    @if(request()->boolean('print') && count($rows) > 0)
    document.addEventListener('DOMContentLoaded', function () {
        window.print();
    });
    @endif
</script>
@endsection
