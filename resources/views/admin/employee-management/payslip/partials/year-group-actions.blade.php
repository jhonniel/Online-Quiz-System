@props([
    'year',
])

<div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
    <a href="{{ route('admin.payslip.yearly-summary', ['year' => $year]) }}"
       onclick="event.stopPropagation()"
       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-md hover:bg-emerald-100">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Summary
    </a>
    @include('admin.employee-management.payslip.partials.group-print-link', [
        'href' => route('admin.payslip.yearly-summary', ['year' => $year, 'print' => 1]),
    ])
    <a href="{{ route('admin.payslip.yearly-summary.csv', ['year' => $year]) }}"
       onclick="event.stopPropagation()"
       class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        CSV
    </a>
</div>
