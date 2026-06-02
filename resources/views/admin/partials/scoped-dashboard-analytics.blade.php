@php
    $chartProfile = $chartProfile ?? 'employee';
    $chartFormAction = $chartFormAction ?? url()->current();
    $chartPeriod = $chartPeriod ?? 'week';
    $chartFrom = $chartFrom ?? now()->subDays(6)->format('Y-m-d');
    $chartTo = $chartTo ?? now()->format('Y-m-d');
    $scopedChartPayload = $scopedChartPayload ?? [];
    $preserveQuery = $preserveQuery ?? request()->except(['chart_period', 'chart_from', 'chart_to']);
@endphp

<div class="space-y-4">
    <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Analytics</h2>
                <p class="text-sm text-gray-500 mt-0.5">Charts for {{ $chartProfile === 'student' ? 'students in your scope' : 'employees in your scope' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Period:</span>
                @foreach(['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'] as $periodKey => $periodLabel)
                    <a href="{{ $chartFormAction }}?{{ http_build_query(array_merge($preserveQuery, ['chart_period' => $periodKey])) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $chartPeriod === $periodKey ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
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
                <input type="date" name="chart_from" value="{{ $chartFrom }}" class="mt-1 block rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </label>
            <label class="text-xs text-gray-600">
                To
                <input type="date" name="chart_to" value="{{ $chartTo }}" class="mt-1 block rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </label>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Apply range</button>
        </form>
    </div>

    @if($chartProfile === 'student')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white border border-orange-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-700">Pending time requests</p>
                <p class="mt-2 text-2xl font-bold text-orange-900">{{ number_format($scopedChartPayload['pendingTimeRequests'] ?? 0) }}</p>
            </div>
            <div class="bg-white border border-amber-200 rounded-xl p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Incomplete overtime</p>
                <p class="mt-2 text-2xl font-bold text-amber-900">{{ number_format($scopedChartPayload['incompleteOvertime'] ?? 0) }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-sm font-semibold text-gray-900">Leave requests filed</h3>
                <p class="text-xs text-gray-500 mt-0.5">Per period</p>
            </div>
            <div class="p-5 h-52"><canvas id="scopedLeaveTrendChart"></canvas></div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-sm font-semibold text-gray-900">Leave by status</h3>
                <p class="text-xs text-gray-500 mt-0.5">Current totals</p>
            </div>
            <div class="p-5 h-52"><canvas id="scopedLeaveStatusChart"></canvas></div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-sm font-semibold text-gray-900">{{ $chartProfile === 'student' ? 'Student' : 'Employee' }} Leave by Type</h3>
                <p class="text-xs text-gray-500 mt-0.5">Filed in selected period</p>
            </div>
            <div class="p-5 h-52"><canvas id="scopedLeaveByTypeChart"></canvas></div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-sm font-semibold text-gray-900">DTR records</h3>
                <p class="text-xs text-gray-500 mt-0.5">Logged per period</p>
            </div>
            <div class="p-5 h-52"><canvas id="scopedDtrTrendChart"></canvas></div>
        </div>

        @if($chartProfile === 'employee')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden lg:col-span-2">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Leave decisions over time</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Pending, approved, and rejected filings per period</p>
                </div>
                <div class="p-5 h-52"><canvas id="scopedLeaveStatusTrendChart"></canvas></div>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">DTR time requests</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Filed per period by status</p>
                </div>
                <div class="p-5 h-52"><canvas id="scopedTimeRequestChart"></canvas></div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-sm font-semibold text-gray-900">Quiz attempts</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Per period</p>
                </div>
                <div class="p-5 h-52"><canvas id="scopedQuizChart"></canvas></div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script type="application/json" id="scoped-dashboard-chart-payload">{!! json_encode($scopedChartPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    var payload = {};
    try {
        var el = document.getElementById('scoped-dashboard-chart-payload');
        if (el && el.textContent.trim()) payload = JSON.parse(el.textContent);
    } catch (e) {
        console.error('Scoped chart payload error', e);
        return;
    }

    var profile = @json($chartProfile);
    var doughnutColors = ['rgba(245, 158, 11, 0.85)', 'rgba(59, 130, 246, 0.85)', 'rgba(34, 197, 94, 0.85)', 'rgba(239, 68, 68, 0.85)', 'rgba(139, 92, 246, 0.85)'];
    var chartDefaults = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } };

    var leaveTrendEl = document.getElementById('scopedLeaveTrendChart');
    if (leaveTrendEl) {
        new Chart(leaveTrendEl, {
            type: 'line',
            data: {
                labels: payload.leaveTrendLabels || [],
                datasets: [{
                    label: 'Leave requests',
                    data: payload.leaveTrendData || [],
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.12)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: Object.assign({}, chartDefaults, { scales: { y: { beginAtZero: true } } })
        });
    }

    var leaveStatusEl = document.getElementById('scopedLeaveStatusChart');
    if (leaveStatusEl) {
        var statusLabels = payload.leaveStatusLabels || [];
        new Chart(leaveStatusEl, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: payload.leaveStatusData || [],
                    backgroundColor: doughnutColors.slice(0, statusLabels.length),
                    borderWidth: 1
                }]
            },
            options: chartDefaults
        });
    }

    var leaveByTypeEl = document.getElementById('scopedLeaveByTypeChart');
    if (leaveByTypeEl) {
        var typeBarColors = profile === 'employee'
            ? ['rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)', 'rgba(249, 115, 22, 0.8)', 'rgba(139, 92, 246, 0.8)', 'rgba(6, 182, 212, 0.8)', 'rgba(239, 68, 68, 0.8)']
            : ['rgba(249, 115, 22, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(239, 68, 68, 0.8)', 'rgba(107, 114, 128, 0.8)'];
        new Chart(leaveByTypeEl, {
            type: 'bar',
            data: {
                labels: payload.leaveByTypeLabels || [],
                datasets: [{
                    label: 'Requests',
                    data: payload.leaveByTypeData || [],
                    backgroundColor: typeBarColors.slice(0, (payload.leaveByTypeLabels || []).length),
                    borderWidth: 1
                }]
            },
            options: Object.assign({}, chartDefaults, {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true }, x: { ticks: { maxRotation: 45, minRotation: 20 } } }
            })
        });
    }

    var dtrTrendEl = document.getElementById('scopedDtrTrendChart');
    if (dtrTrendEl) {
        new Chart(dtrTrendEl, {
            type: 'bar',
            data: {
                labels: payload.dtrTrendLabels || [],
                datasets: [{
                    label: 'DTR records',
                    data: payload.dtrTrendData || [],
                    backgroundColor: 'rgba(16, 185, 129, 0.75)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1
                }]
            },
            options: Object.assign({}, chartDefaults, { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } })
        });
    }

    if (profile === 'employee') {
        var statusTrendEl = document.getElementById('scopedLeaveStatusTrendChart');
        if (statusTrendEl) {
            new Chart(statusTrendEl, {
                type: 'bar',
                data: {
                    labels: payload.statusTrendLabels || [],
                    datasets: [
                        { label: 'Pending', data: payload.statusTrendPending || [], backgroundColor: 'rgba(245, 158, 11, 0.85)' },
                        { label: 'Approved', data: payload.statusTrendApproved || [], backgroundColor: 'rgba(34, 197, 94, 0.85)' },
                        { label: 'Rejected', data: payload.statusTrendRejected || [], backgroundColor: 'rgba(239, 68, 68, 0.85)' }
                    ]
                },
                options: Object.assign({}, chartDefaults, { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } })
            });
        }
    } else {
        var timeRequestEl = document.getElementById('scopedTimeRequestChart');
        if (timeRequestEl) {
            new Chart(timeRequestEl, {
                type: 'bar',
                data: {
                    labels: payload.timeRequestLabels || [],
                    datasets: [
                        { label: 'Pending', data: payload.timeRequestPending || [], backgroundColor: 'rgba(245, 158, 11, 0.85)' },
                        { label: 'Approved', data: payload.timeRequestApproved || [], backgroundColor: 'rgba(34, 197, 94, 0.85)' },
                        { label: 'Rejected', data: payload.timeRequestRejected || [], backgroundColor: 'rgba(239, 68, 68, 0.85)' }
                    ]
                },
                options: Object.assign({}, chartDefaults, { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } })
            });
        }

        var quizEl = document.getElementById('scopedQuizChart');
        if (quizEl) {
            new Chart(quizEl, {
                type: 'line',
                data: {
                    labels: payload.quizLabels || [],
                    datasets: [{
                        label: 'Quiz attempts',
                        data: payload.quizData || [],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.12)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: Object.assign({}, chartDefaults, { scales: { y: { beginAtZero: true } } })
            });
        }
    }
});
</script>
@endpush
