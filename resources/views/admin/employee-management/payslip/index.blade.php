@extends('layouts.admin')

@section('title', 'Payslip')

@section('page-title', 'Payslip')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-500 text-sm">Employee Management</span>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">Payslip</span>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="payslipLinkModal()">
    <div class="border-b border-indigo-800/20 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 rounded-lg shadow-sm px-4 sm:px-6 py-5">
        <h1 class="text-xl sm:text-2xl font-bold text-white">Payslip Management</h1>
        <p class="mt-1 text-sm text-indigo-100">Upload employee payslips via CSV. Duplicate rows (same employee and cut-off period) are skipped. Position comes from profile department; date hired from profile.</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ session('error') }}</div>
    @endif
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
            <p class="font-semibold mb-2">Import errors</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('import_warnings') && count(session('import_warnings')) > 0)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-semibold mb-2">Import notes</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach(session('import_warnings') as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/80">
            <h2 class="text-base font-semibold text-gray-900">Upload CSV</h2>
            <p class="text-sm text-gray-500 mt-0.5">Import payslips in bulk using the standard template format.</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-5">
            <form action="{{ route('admin.payslip.import') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="xl:col-span-3 p-5 sm:p-6 border-b xl:border-b-0 xl:border-r border-gray-100 space-y-5"
                  x-data="{ fileLabel: '' }">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CSV file <span class="text-red-500" aria-hidden="true">*</span></label>
                    <label class="relative flex flex-col items-center justify-center w-full min-h-[9rem] rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/50 hover:border-indigo-400 hover:bg-indigo-50/30 transition-colors cursor-pointer group">
                        <input type="file"
                               name="csv_file"
                               id="csv_file"
                               accept=".csv,text/csv"
                               required
                               class="sr-only"
                               @change="fileLabel = $event.target.files[0]?.name || ''">
                        <svg class="w-9 h-9 text-gray-400 group-hover:text-indigo-500 mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-800">Click to choose CSV file</span>
                        <span class="mt-1 text-xs text-gray-500 px-4 text-center" x-text="fileLabel || '.csv format · use the template for correct columns'"></span>
                    </label>
                    @error('csv_file')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.payslip.template') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download template
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import payslips
                    </button>
                </div>
            </form>

            <aside class="xl:col-span-2 p-5 sm:p-6 bg-gray-50/60 space-y-4">
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 px-4 py-3">
                    <p class="text-sm font-medium text-indigo-900">Employee linking</p>
                    <p class="mt-1 text-xs text-indigo-800/90 leading-relaxed">
                        Include <strong>employee_email</strong> so rows match employee accounts. Position on the payslip comes from each employee's profile department. Unlinked rows can be connected manually below.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <p class="text-sm font-medium text-gray-900">Auto-calculated</p>
                    <p class="mt-1 text-xs text-gray-500 leading-relaxed">
                        Total Deduction is computed from all deduction columns. CA, Gov't Loans, and Loans are company loan deductions.
                    </p>
                </div>

                <details id="payslip-csv-columns" class="group rounded-xl border border-gray-200 bg-white overflow-hidden">
                    <summary class="flex items-center justify-between gap-2 px-4 py-3 cursor-pointer list-none select-none hover:bg-gray-50 transition-colors">
                        <span class="text-sm font-medium text-gray-900">Required CSV columns</span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="px-4 pb-4 pt-1 space-y-3 border-t border-gray-100">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Period & employee</p>
                            <p class="text-xs text-gray-600 leading-relaxed">cutt_off_start, cutt_off_end, employee_name, employee_email (recommended). Use the employee's exact login email so the row links to their profile. Do not include position or date hired — when a row matches an employee account, <strong>Position</strong> comes from the employee's assigned department position and <strong>Date Hired</strong> from their profile. Always start from the <strong>Download template</strong> link — do not rename columns; Excel “Save As CSV” is fine (comma or semicolon).</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Earnings</p>
                            <p class="text-xs text-gray-600 leading-relaxed">rate_per_day, total_working_days, overtime_pay, holiday_pay, allowances, thirteenth_month_pay, gross_pay, net_pay</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Deductions</p>
                            <p class="text-xs text-gray-600 leading-relaxed">sss, phic, hdmf, late_hours, absences_days, withholding_tax, ca, govt_loans, loans</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1.5">Sign-off</p>
                            <p class="text-xs text-gray-600 leading-relaxed">prepared_by, approved_by</p>
                        </div>
                    </div>
                </details>
            </aside>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Payslip Records</h2>
                    <p class="text-sm text-gray-500">{{ number_format($totalCount) }} record(s) — grouped by year, month, and cut-off period</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 bg-gray-100 rounded-md cursor-pointer">
                        <input type="checkbox" id="payslip-select-all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Select all
                    </label>
                    <button type="button" id="payslip-bulk-print" disabled
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span id="payslip-bulk-print-label">Print selected</span>
                    </button>
                    <button type="button" id="payslip-bulk-delete" disabled
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span id="payslip-bulk-delete-label">Delete selected</span>
                    </button>
                    <button type="button" id="payslip-expand-all" class="px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-50 rounded-md hover:bg-indigo-100">
                        Expand all
                    </button>
                    <button type="button" id="payslip-collapse-all" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Collapse all
                    </button>
                </div>
            </div>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employee..."
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <select name="year" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All years</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ (string) request('year') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                <input type="date" name="period_start" value="{{ request('period_start') }}" placeholder="Period from"
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <input type="date" name="period_end" value="{{ request('period_end') }}" placeholder="Period to"
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <x-admin-filter-button />
            </form>
        </div>

        @if(count($groupedPayslips) === 0)
            <div class="px-6 py-12 text-center text-sm text-gray-500">No payslips uploaded yet.</div>
        @else
            <div id="payslip-groups" class="divide-y divide-gray-200">
                @foreach($groupedPayslips as $yearGroup)
                    <details class="payslip-group payslip-year-group group" data-payslip-group-key="year:{{ $yearGroup['key'] }}" {{ $loop->first ? 'open' : '' }}>
                        <summary class="flex items-center justify-between gap-3 px-6 py-4 cursor-pointer list-none bg-gray-50 hover:bg-gray-100 select-none">
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="h-5 w-5 text-gray-500 transition-transform group-open:rotate-90 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="text-base font-semibold text-gray-900">{{ $yearGroup['label'] }}</span>
                                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">{{ $yearGroup['count'] }} payslip(s)</span>
                            </div>
                        </summary>

                        <div class="border-t border-gray-200 bg-white">
                            @foreach($yearGroup['months'] as $monthGroup)
                                <details class="payslip-group payslip-month-group group border-b border-gray-100 last:border-b-0" data-payslip-group-key="month:{{ $monthGroup['key'] }}" {{ $loop->parent->first && $loop->first ? 'open' : '' }}>
                                    <summary class="flex items-center justify-between gap-3 px-6 py-3 pl-10 cursor-pointer list-none bg-white hover:bg-gray-50 select-none">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-90 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            <span class="text-sm font-semibold text-gray-800">{{ $monthGroup['label'] }}</span>
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $monthGroup['count'] }} payslip(s)</span>
                                        </div>
                                    </summary>

                                    <div class="bg-gray-50/50">
                                        @foreach($monthGroup['cutoffs'] as $cutoffGroup)
                                            <details class="payslip-group payslip-cutoff-group group border-t border-gray-100" data-payslip-group-key="cutoff:{{ $cutoffGroup['key'] }}" {{ $loop->parent->parent->first && $loop->parent->first && $loop->first ? 'open' : '' }}>
                                                <summary class="flex items-center justify-between gap-3 px-6 py-3 pl-16 cursor-pointer list-none hover:bg-gray-100/80 select-none">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-90 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                        <span class="text-sm font-medium text-gray-800">Cut-off: {{ $cutoffGroup['label'] }}</span>
                                                        <span class="inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-800">{{ $cutoffGroup['count'] }} employee(s)</span>
                                                    </div>
                                                </summary>

                                                <div class="overflow-x-auto border-t border-gray-200 bg-white">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-4 py-2.5 text-left">
                                                                    <input type="checkbox" class="payslip-select-all rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" title="Select all in this cut-off">
                                                                </th>
                                                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Employee</th>
                                                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Gross Pay</th>
                                                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Net Pay</th>
                                                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Employee Signed</th>
                                                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Uploaded</th>
                                                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-200 bg-white">
                                                            @include('admin.employee-management.payslip.partials.rows', ['payslips' => $cutoffGroup['payslips'], 'employees' => $employees])
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </details>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </div>

    @include('admin.employee-management.payslip.partials.link-modal', ['employees' => $employees])
</div>

<style>
    [x-cloak] { display: none !important; }
    .payslip-group > summary::-webkit-details-marker,
    details.group > summary::-webkit-details-marker { display: none; }
    .payslip-group > summary::marker,
    details.group > summary::marker { display: none; content: ''; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('payslip-groups');
    const expandAll = document.getElementById('payslip-expand-all');
    const collapseAll = document.getElementById('payslip-collapse-all');
    const bulkPrintBtn = document.getElementById('payslip-bulk-print');
    const bulkPrintLabel = document.getElementById('payslip-bulk-print-label');
    const bulkDeleteBtn = document.getElementById('payslip-bulk-delete');
    const bulkDeleteLabel = document.getElementById('payslip-bulk-delete-label');
    const globalSelectAll = document.getElementById('payslip-select-all');

    function payslipCheckboxes() {
        return Array.from(document.querySelectorAll('.payslip-checkbox'));
    }

    function selectedPayslipIds() {
        return payslipCheckboxes()
            .filter(function (cb) { return cb.checked; })
            .map(function (cb) { return cb.value; });
    }

    function submitPayslipBulkForm(action, target) {
        const selectedIds = selectedPayslipIds();

        if (selectedIds.length === 0) {
            return false;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;

        if (target) {
            form.target = target;
        }

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = @json(csrf_token());
        form.appendChild(csrfToken);

        selectedIds.forEach(function (id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'payslip_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        form.remove();

        return true;
    }

    function updateBulkDeleteState() {
        const selectedCount = selectedPayslipIds().length;
        if (bulkPrintBtn) {
            bulkPrintBtn.disabled = selectedCount === 0;
        }
        if (bulkPrintLabel) {
            bulkPrintLabel.textContent = selectedCount > 0
                ? 'Print selected (' + selectedCount + ')'
                : 'Print selected';
        }
        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = selectedCount === 0;
        }
        if (bulkDeleteLabel) {
            bulkDeleteLabel.textContent = selectedCount > 0
                ? 'Delete selected (' + selectedCount + ')'
                : 'Delete selected';
        }
        if (globalSelectAll) {
            const all = payslipCheckboxes();
            globalSelectAll.checked = all.length > 0 && all.every(function (cb) { return cb.checked; });
            globalSelectAll.indeterminate = selectedCount > 0 && selectedCount < all.length;
        }
    }

    payslipCheckboxes().forEach(function (checkbox) {
        checkbox.addEventListener('change', updateBulkDeleteState);
    });

    document.querySelectorAll('.payslip-select-all').forEach(function (selectAll) {
        selectAll.addEventListener('change', function () {
            const table = selectAll.closest('table');
            const checkboxes = table
                ? table.querySelectorAll('.payslip-checkbox')
                : payslipCheckboxes();

            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });

            document.querySelectorAll('.payslip-select-all').forEach(function (other) {
                if (other !== selectAll) {
                    other.checked = selectAll.checked;
                }
            });

            updateBulkDeleteState();
        });
    });

    globalSelectAll?.addEventListener('change', function () {
        const checked = globalSelectAll.checked;
        payslipCheckboxes().forEach(function (checkbox) {
            checkbox.checked = checked;
        });
        document.querySelectorAll('.payslip-select-all').forEach(function (selectAll) {
            selectAll.checked = checked;
        });
        updateBulkDeleteState();
    });

    bulkPrintBtn?.addEventListener('click', function () {
        const selectedIds = selectedPayslipIds();

        if (selectedIds.length === 0) {
            alert('Please select at least one payslip to print.');
            return;
        }

        submitPayslipBulkForm(@json(route('admin.payslip.bulk-print')), '_blank');
    });

    bulkDeleteBtn?.addEventListener('click', function () {
        const selectedIds = selectedPayslipIds();

        if (selectedIds.length === 0) {
            alert('Please select at least one payslip to delete.');
            return;
        }

        if (!confirm('Delete ' + selectedIds.length + ' selected payslip(s)? This cannot be undone.')) {
            return;
        }

        submitPayslipBulkForm(@json(route('admin.payslip.bulk-destroy')));
    });

    const PAYSLIP_GROUP_STORAGE_KEY = 'admin-payslip-group-states';
    const PAYSLIP_CSV_COLUMNS_KEY = 'admin-payslip-csv-columns-open';

    function loadPayslipGroupStates() {
        try {
            const raw = localStorage.getItem(PAYSLIP_GROUP_STORAGE_KEY);
            return raw ? JSON.parse(raw) : {};
        } catch (error) {
            return {};
        }
    }

    function savePayslipGroupStates() {
        if (!root) {
            return;
        }

        const states = loadPayslipGroupStates();

        root.querySelectorAll('details.payslip-group[data-payslip-group-key]').forEach(function (el) {
            states[el.dataset.payslipGroupKey] = el.open;
        });

        localStorage.setItem(PAYSLIP_GROUP_STORAGE_KEY, JSON.stringify(states));
    }

    function restorePayslipGroupStates() {
        if (!root) {
            return;
        }

        const states = loadPayslipGroupStates();

        root.querySelectorAll('details.payslip-group[data-payslip-group-key]').forEach(function (el) {
            const key = el.dataset.payslipGroupKey;
            if (Object.prototype.hasOwnProperty.call(states, key)) {
                el.open = states[key];
            }
        });
    }

    function bindPayslipGroupPersistence() {
        if (!root) {
            return;
        }

        root.querySelectorAll('details.payslip-group[data-payslip-group-key]').forEach(function (el) {
            el.addEventListener('toggle', savePayslipGroupStates);
        });
    }

    const csvColumns = document.getElementById('payslip-csv-columns');
    if (csvColumns) {
        const savedCsvOpen = localStorage.getItem(PAYSLIP_CSV_COLUMNS_KEY);
        if (savedCsvOpen !== null) {
            csvColumns.open = savedCsvOpen === 'true';
        }

        csvColumns.addEventListener('toggle', function () {
            localStorage.setItem(PAYSLIP_CSV_COLUMNS_KEY, csvColumns.open ? 'true' : 'false');
        });
    }

    restorePayslipGroupStates();
    bindPayslipGroupPersistence();

    expandAll?.addEventListener('click', function () {
        if (!root) {
            return;
        }

        root.querySelectorAll('details.payslip-group').forEach(function (el) {
            el.open = true;
        });
        savePayslipGroupStates();
    });

    collapseAll?.addEventListener('click', function () {
        if (!root) {
            return;
        }

        root.querySelectorAll('details.payslip-group').forEach(function (el) {
            el.open = false;
        });
        savePayslipGroupStates();
    });
});
</script>
@endsection
