@extends('layouts.admin')

@section('title', 'Payslip Cut-off Summary')

@section('page-title', 'Payslip Cut-off Summary')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <a href="{{ route('admin.payslip.index') }}" class="text-gray-500 text-sm hover:text-gray-700">Payslip</a>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">{{ $periodLabel }} Summary</span>
</li>
@endsection

@section('content')
@php
    $rows = $summary['rows'];
    $cutoffQuery = [
        'period_start' => $validated['period_start'],
        'period_end' => $validated['period_end'],
    ];
@endphp

<div class="space-y-6">
    <div class="print:hidden flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.payslip.index', request()->only('search', 'year', 'period_start', 'period_end')) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to payslip records</a>
            <h1 class="mt-2 text-xl sm:text-2xl font-bold text-gray-900">Payslip Cut-off Summary — {{ $periodLabel }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Payroll sheet for this cut-off period.
                {{ number_format($summary['employee_count']) }} employee(s) · {{ number_format($summary['payslip_count']) }} payslip record(s).
            </p>
        </div>

        @if(count($rows) > 0)
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
                <a href="{{ route('admin.payslip.cutoff-summary.csv', $cutoffQuery) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-emerald-800 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download CSV
                </a>
            </div>
        @endif
    </div>

    @include('admin.employee-management.payslip.partials.payroll-summary-sheet', [
        'summary' => $summary,
        'companyName' => $companyName,
        'sheetTitle' => $sheetTitle,
        'emptyMessage' => 'No payslip records found for '.$periodLabel.'.',
        'autoPrint' => request()->boolean('print'),
    ])
</div>
@endsection
