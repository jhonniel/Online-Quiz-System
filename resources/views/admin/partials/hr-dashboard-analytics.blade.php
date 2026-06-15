@php
    $chartFormAction = $chartFormAction ?? route('admin.hr-dashboard');
    $chartPeriod = $chartPeriod ?? 'week';
    $chartFrom = $chartFrom ?? now()->subDays(6)->format('Y-m-d');
    $chartTo = $chartTo ?? now()->format('Y-m-d');
    $hrChartPayload = $hrChartPayload ?? [];
    $hrChartSections = $hrChartSections ?? ['workforce'];
    $preserveQuery = $preserveQuery ?? request()->except(['chart_period', 'chart_from', 'chart_to']);
@endphp

<div class="space-y-4">
    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Analytics</h2>
                <p class="text-sm text-gray-500 mt-0.5">Charts for your assigned HR workflows</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Period:</span>
                @foreach(['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'] as $periodKey => $periodLabel)
                    <a href="{{ $chartFormAction }}?{{ http_build_query(array_merge($preserveQuery, ['chart_period' => $periodKey])) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $chartPeriod === $periodKey ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        {{ $periodLabel }}
                    </a>
                @endforeach
            </div>
        </div>
        <form method="GET" action="{{ $chartFormAction }}" class="mt-4 flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4">
            @foreach($preserveQuery as $key => $value)
                @if(is_array($value))
                    @foreach($value as $item)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <input type="hidden" name="chart_period" value="custom">
            <label class="text-xs text-gray-600">
                From
                <input type="date" name="chart_from" value="{{ $chartFrom }}" class="mt-1 block rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
            </label>
            <label class="text-xs text-gray-600">
                To
                <input type="date" name="chart_to" value="{{ $chartTo }}" class="mt-1 block rounded-lg border-gray-300 text-sm focus:ring-teal-500 focus:border-teal-500">
            </label>
            <x-admin-filter-button>Apply range</x-admin-filter-button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @if(in_array('workforce', $hrChartSections, true))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Workforce overview</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Current headcount by role</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrWorkforceChart"></canvas></div>
            </div>
        @endif

        @if(in_array('pending_workload', $hrChartSections, true))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Pending workload</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Items awaiting your action</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrPendingWorkloadChart"></canvas></div>
            </div>
        @endif

        @if(in_array('employee_leave', $hrChartSections, true))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Employee leave filed</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Vacation and sick leave per period</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrEmployeeLeaveTrendChart"></canvas></div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Employee leave by status</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Current totals</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrEmployeeLeaveStatusChart"></canvas></div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Employee leave by type</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Filed in selected period</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrEmployeeLeaveTypeChart"></canvas></div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden lg:col-span-2 xl:col-span-2">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Employee leave decisions</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pending, approved, and rejected per period</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrEmployeeLeaveStatusTrendChart"></canvas></div>
            </div>
        @endif

        @if(in_array('student_leave', $hrChartSections, true))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Student leave filed</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Per period</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrStudentLeaveTrendChart"></canvas></div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Student leave by status</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Current totals</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrStudentLeaveStatusChart"></canvas></div>
            </div>
        @endif

        @if(in_array('hiring', $hrChartSections, true))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Hiring pipeline</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Current application totals</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrHiringStatusChart"></canvas></div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden lg:col-span-2">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Hiring applications over time</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Filed per period by outcome</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrHiringTrendChart"></canvas></div>
            </div>
        @endif

        @if(in_array('tickets', $hrChartSections, true))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden lg:col-span-2">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Support tickets</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Opened vs resolved per period</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrTicketsTrendChart"></canvas></div>
            </div>
        @endif

        @if(in_array('contact', $hrChartSections, true))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Contact messages</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Received per period</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrContactTrendChart"></canvas></div>
            </div>
        @endif

        @if(in_array('time_requests', $hrChartSections, true))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden lg:col-span-2">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">DTR time requests</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Filed per period by status</p>
                </div>
                <div class="p-5 h-52"><canvas id="hrTimeRequestChart"></canvas></div>
            </div>
        @endif
    </div>

    @if(count($hrChartSections) === 1)
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 text-center">
            <p class="text-sm text-gray-600">More charts will appear here once admin features are assigned to your HR account.</p>
        </div>
    @endif
</div>

@push('scripts')
<script type="application/json" id="hr-dashboard-chart-payload">{!! json_encode($hrChartPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    var payload = {};
    try {
        var el = document.getElementById('hr-dashboard-chart-payload');
        if (el && el.textContent.trim()) payload = JSON.parse(el.textContent);
    } catch (e) {
        console.error('HR chart payload error', e);
        return;
    }

    var labels = payload.trendLabels || [];
    var doughnutColors = ['rgba(245, 158, 11, 0.85)', 'rgba(59, 130, 246, 0.85)', 'rgba(34, 197, 94, 0.85)', 'rgba(239, 68, 68, 0.85)', 'rgba(139, 92, 246, 0.85)', 'rgba(6, 182, 212, 0.85)', 'rgba(236, 72, 153, 0.85)'];
    var chartDefaults = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } };

    var workforceEl = document.getElementById('hrWorkforceChart');
    if (workforceEl) {
        new Chart(workforceEl, {
            type: 'bar',
            data: {
                labels: payload.workforceLabels || [],
                datasets: [{
                    label: 'Headcount',
                    data: payload.workforceData || [],
                    backgroundColor: ['rgba(20, 184, 166, 0.8)', 'rgba(99, 102, 241, 0.8)', 'rgba(245, 158, 11, 0.8)'],
                    borderWidth: 1
                }]
            },
            options: Object.assign({}, chartDefaults, { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } })
        });
    }

    var pendingEl = document.getElementById('hrPendingWorkloadChart');
    if (pendingEl) {
        var pendingLabels = payload.pendingLabels || [];
        new Chart(pendingEl, {
            type: 'doughnut',
            data: {
                labels: pendingLabels,
                datasets: [{
                    data: payload.pendingData || [],
                    backgroundColor: doughnutColors.slice(0, pendingLabels.length),
                    borderWidth: 1
                }]
            },
            options: chartDefaults
        });
    }

    var employeeLeaveTrendEl = document.getElementById('hrEmployeeLeaveTrendChart');
    if (employeeLeaveTrendEl) {
        new Chart(employeeLeaveTrendEl, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Leave requests',
                    data: payload.employeeLeaveTrendData || [],
                    borderColor: 'rgb(20, 184, 166)',
                    backgroundColor: 'rgba(20, 184, 166, 0.12)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: Object.assign({}, chartDefaults, { scales: { y: { beginAtZero: true } } })
        });
    }

    var employeeLeaveStatusEl = document.getElementById('hrEmployeeLeaveStatusChart');
    if (employeeLeaveStatusEl) {
        var statusLabels = payload.employeeLeaveStatusLabels || [];
        new Chart(employeeLeaveStatusEl, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: payload.employeeLeaveStatusData || [],
                    backgroundColor: doughnutColors.slice(0, statusLabels.length),
                    borderWidth: 1
                }]
            },
            options: chartDefaults
        });
    }

    var employeeLeaveTypeEl = document.getElementById('hrEmployeeLeaveTypeChart');
    if (employeeLeaveTypeEl) {
        new Chart(employeeLeaveTypeEl, {
            type: 'bar',
            data: {
                labels: payload.employeeLeaveTypeLabels || [],
                datasets: [{
                    label: 'Requests',
                    data: payload.employeeLeaveTypeData || [],
                    backgroundColor: ['rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)'],
                    borderWidth: 1
                }]
            },
            options: Object.assign({}, chartDefaults, { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } })
        });
    }

    var employeeLeaveStatusTrendEl = document.getElementById('hrEmployeeLeaveStatusTrendChart');
    if (employeeLeaveStatusTrendEl) {
        new Chart(employeeLeaveStatusTrendEl, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Pending', data: payload.employeeLeaveStatusTrendPending || [], backgroundColor: 'rgba(245, 158, 11, 0.85)' },
                    { label: 'Approved', data: payload.employeeLeaveStatusTrendApproved || [], backgroundColor: 'rgba(34, 197, 94, 0.85)' },
                    { label: 'Rejected', data: payload.employeeLeaveStatusTrendRejected || [], backgroundColor: 'rgba(239, 68, 68, 0.85)' }
                ]
            },
            options: Object.assign({}, chartDefaults, { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } })
        });
    }

    var studentLeaveTrendEl = document.getElementById('hrStudentLeaveTrendChart');
    if (studentLeaveTrendEl) {
        new Chart(studentLeaveTrendEl, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Leave requests',
                    data: payload.studentLeaveTrendData || [],
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.12)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: Object.assign({}, chartDefaults, { scales: { y: { beginAtZero: true } } })
        });
    }

    var studentLeaveStatusEl = document.getElementById('hrStudentLeaveStatusChart');
    if (studentLeaveStatusEl) {
        var studentStatusLabels = payload.studentLeaveStatusLabels || [];
        new Chart(studentLeaveStatusEl, {
            type: 'doughnut',
            data: {
                labels: studentStatusLabels,
                datasets: [{
                    data: payload.studentLeaveStatusData || [],
                    backgroundColor: doughnutColors.slice(0, studentStatusLabels.length),
                    borderWidth: 1
                }]
            },
            options: chartDefaults
        });
    }

    var hiringStatusEl = document.getElementById('hrHiringStatusChart');
    if (hiringStatusEl) {
        var hiringLabels = payload.hiringStatusLabels || [];
        new Chart(hiringStatusEl, {
            type: 'doughnut',
            data: {
                labels: hiringLabels,
                datasets: [{
                    data: payload.hiringStatusData || [],
                    backgroundColor: ['rgba(59, 130, 246, 0.85)', 'rgba(34, 197, 94, 0.85)', 'rgba(239, 68, 68, 0.85)'],
                    borderWidth: 1
                }]
            },
            options: chartDefaults
        });
    }

    var hiringTrendEl = document.getElementById('hrHiringTrendChart');
    if (hiringTrendEl) {
        new Chart(hiringTrendEl, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'In progress', data: payload.hiringPendingData || [], backgroundColor: 'rgba(59, 130, 246, 0.85)' },
                    { label: 'Accepted / Hired', data: payload.hiringAcceptedData || [], backgroundColor: 'rgba(34, 197, 94, 0.85)' },
                    { label: 'Rejected', data: payload.hiringRejectedData || [], backgroundColor: 'rgba(239, 68, 68, 0.85)' }
                ]
            },
            options: Object.assign({}, chartDefaults, { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } })
        });
    }

    var ticketsTrendEl = document.getElementById('hrTicketsTrendChart');
    if (ticketsTrendEl) {
        new Chart(ticketsTrendEl, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Open / In progress', data: payload.ticketsOpenData || [], backgroundColor: 'rgba(239, 68, 68, 0.85)' },
                    { label: 'Resolved / Closed', data: payload.ticketsClosedData || [], backgroundColor: 'rgba(34, 197, 94, 0.85)' }
                ]
            },
            options: Object.assign({}, chartDefaults, { scales: { y: { beginAtZero: true } } })
        });
    }

    var contactTrendEl = document.getElementById('hrContactTrendChart');
    if (contactTrendEl) {
        new Chart(contactTrendEl, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Messages',
                    data: payload.contactTrendData || [],
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.12)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: Object.assign({}, chartDefaults, { scales: { y: { beginAtZero: true } } })
        });
    }

    var timeRequestEl = document.getElementById('hrTimeRequestChart');
    if (timeRequestEl) {
        new Chart(timeRequestEl, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Pending', data: payload.timeRequestPendingData || [], backgroundColor: 'rgba(245, 158, 11, 0.85)' },
                    { label: 'Approved', data: payload.timeRequestApprovedData || [], backgroundColor: 'rgba(34, 197, 94, 0.85)' },
                    { label: 'Rejected', data: payload.timeRequestRejectedData || [], backgroundColor: 'rgba(239, 68, 68, 0.85)' }
                ]
            },
            options: Object.assign({}, chartDefaults, { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } })
        });
    }
});
</script>
@endpush
