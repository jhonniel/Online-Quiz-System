@extends('layouts.admin')

@section('title', 'API Monitoring')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 xl:px-8 space-y-6">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">API Monitoring</h1>
                <p class="text-sm text-gray-500">Live status and request usage for discovered API endpoints.</p>
            </div>
            <div class="text-xs text-gray-500">
                Auto-refresh every 10 seconds
            </div>
        </div>

        <div class="p-6 space-y-5"
             x-data='apiMonitor({
                 metricsUrl: "{{ url('/admin/system/api-monitoring/metrics') }}",
                 initialRows: @json($rows),
                 initialSummary: @json($summary),
                 initialScope: @json($scope ?? 'all')
             })'>
            @if(session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if($newApiKeyPlainText)
                <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 space-y-2">
                    <p class="text-sm font-semibold text-indigo-800">New API key generated (copy now)</p>
                    <p class="text-xs text-indigo-700 font-mono break-all">{{ $newApiKeyPlainText }}</p>
                    <p class="text-xs text-indigo-700">For security, this key is shown only once.</p>
                </div>
            @endif

            <div class="rounded-xl border border-gray-200 p-4 space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">External API Access</h2>
                        <p class="text-xs text-gray-500">Allow outside projects to request API monitoring data using API keys.</p>
                    </div>
                    <form method="POST" action="{{ url('/admin/system/api-monitoring/external-access') }}" class="flex items-center gap-2">
                        @csrf
                        <select name="external_api_enabled" class="h-9 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="enabled" {{ $externalApiEnabled ? 'selected' : '' }}>Enabled</option>
                            <option value="disabled" {{ $externalApiEnabled ? '' : 'selected' }}>Disabled</option>
                        </select>
                        <button type="submit" class="h-9 inline-flex items-center px-3 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                            Save
                        </button>
                    </form>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <p class="text-xs font-semibold text-gray-700">External endpoint</p>
                    <p class="mt-1 text-xs font-mono text-gray-700 break-all">{{ url('/api/monitoring/endpoints') }}</p>
                    <p class="mt-2 text-xs text-gray-600">Send API key in header: <span class="font-mono">X-API-Key: your_key_here</span></p>
                </div>

                <form method="POST" action="{{ url('/admin/system/api-monitoring/external-allowed-apis') }}" class="rounded-lg border border-gray-200 p-3 space-y-3">
                    @csrf
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Allowed APIs for External Access</p>
                            <p class="text-[11px] text-gray-500">Only checked endpoints will be returned to outside projects.</p>
                        </div>
                        <button type="submit" class="h-8 inline-flex items-center px-3 rounded-md bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                            Save Allowed APIs
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2 max-h-56 overflow-y-auto border border-gray-100 rounded-md p-2">
                        @forelse($rows as $row)
                            <label class="flex items-start gap-2 text-xs text-gray-700">
                                <input type="checkbox"
                                       name="allowed_route_keys[]"
                                       value="{{ $row['route_key'] }}"
                                       {{ in_array($row['route_key'], $externalAllowedRouteKeys ?? [], true) ? 'checked' : '' }}
                                       class="mt-0.5 h-3.5 w-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="truncate">{{ $row['route_key'] }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-gray-500">No API endpoints available for selection.</p>
                        @endforelse
                    </div>
                </form>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <form method="POST" action="{{ url('/admin/system/api-monitoring/keys') }}" class="rounded-lg border border-gray-200 p-3 space-y-2">
                        @csrf
                        <label class="block text-xs font-semibold text-gray-700" for="api_key_name">Create API Key</label>
                        <input id="api_key_name" name="name" type="text" required placeholder="Integration name (e.g. HR Dashboard)"
                               class="w-full h-9 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit" class="h-9 inline-flex items-center px-3 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                            Generate Key
                        </button>
                    </form>

                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs font-semibold text-gray-700 mb-2">Current API Keys</p>
                        <div class="space-y-2 max-h-44 overflow-y-auto">
                            @forelse($externalApiKeys as $apiKey)
                                <div class="flex items-center justify-between gap-2 border border-gray-100 rounded-md px-2 py-1.5">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-gray-800 truncate">{{ $apiKey->name }}</p>
                                        <p class="text-[11px] text-gray-500">Last used: {{ optional($apiKey->last_used_at)->diffForHumans() ?? 'Never' }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-semibold {{ $apiKey->is_active ? 'text-emerald-700' : 'text-rose-700' }}">
                                            {{ $apiKey->is_active ? 'Active' : 'Revoked' }}
                                        </span>
                                        @if($apiKey->is_active)
                                            <form method="POST" action="{{ url('/admin/system/api-monitoring/keys/'.$apiKey->id.'/revoke') }}">
                                                @csrf
                                                <button type="submit" class="text-[11px] font-semibold text-rose-700 hover:text-rose-800">Revoke</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-500">No API keys yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <p class="text-xs text-gray-500">Route scope controls how many endpoints are listed.</p>
                <div class="inline-flex rounded-md border border-gray-200 bg-white p-1">
                    <button type="button"
                            @click="setScope('api_like')"
                            :class="scope === 'api_like' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
                            class="px-2.5 py-1 text-xs font-semibold rounded-md transition-colors">
                        API-like
                    </button>
                    <button type="button"
                            @click="setScope('all')"
                            :class="scope === 'all' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-50'"
                            class="px-2.5 py-1 text-xs font-semibold rounded-md transition-colors">
                        All Endpoints
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total APIs</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900" x-text="summary.total"></p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Healthy</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700" x-text="summary.healthy"></p>
                </div>
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Failing</p>
                    <p class="mt-1 text-2xl font-bold text-rose-700" x-text="summary.failing"></p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Unknown</p>
                    <p class="mt-1 text-2xl font-bold text-amber-700" x-text="summary.unknown"></p>
                </div>
                <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Total Requests</p>
                    <p class="mt-1 text-2xl font-bold text-indigo-700" x-text="summary.total_requests"></p>
                </div>
            </div>

            <div class="text-xs text-gray-500">
                Last refreshed: <span x-text="lastRefreshedText"></span>
            </div>

            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Method</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Endpoint</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Route Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Requests</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Failures</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Uptime</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Uptime Trend</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Avg Response (ms)</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Last Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <template x-for="row in rows" :key="row.route_key">
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-4 py-3 text-xs font-semibold text-gray-700" x-text="row.method"></td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-900" x-text="row.uri"></td>
                                <td class="px-4 py-3 text-xs text-gray-600" x-text="row.name || '—'"></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                          :class="statusBadgeClass(row.status)"
                                          x-text="formatStatus(row.status)">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900" x-text="row.request_count"></td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-rose-700" x-text="row.failure_count"></td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-800" x-text="row.uptime_percent === null ? '—' : (row.uptime_percent + '%')"></td>
                                <td class="px-4 py-3">
                                    <template x-if="row.uptime_points && row.uptime_points.length > 1">
                                        <div class="w-28 h-10">
                                            <svg class="w-full h-full" viewBox="0 0 112 40" preserveAspectRatio="none" aria-hidden="true">
                                                <polyline
                                                    :points="sparklinePoints(row.uptime_points)"
                                                    fill="none"
                                                    stroke="#22c55e"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                </polyline>
                                            </svg>
                                        </div>
                                    </template>
                                    <template x-if="!row.uptime_points || row.uptime_points.length <= 1">
                                        <span class="text-xs text-gray-400">No trend yet</span>
                                    </template>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-indigo-700" x-text="row.avg_response_time_ms"></td>
                                <td class="px-4 py-3 text-xs text-gray-600" x-text="row.last_status_code ?? '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0">
                            <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">No API endpoints discovered yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function apiMonitor(config) {
        return {
            rows: config.initialRows || [],
            summary: config.initialSummary || { total: 0, healthy: 0, failing: 0, unknown: 0, total_requests: 0 },
            lastRefreshedText: new Date().toLocaleString(),
            timerId: null,
            metricsUrl: config.metricsUrl,
            scope: config.initialScope || 'api_like',
            init() {
                this.timerId = setInterval(() => this.refresh(), 10000);
            },
            setScope(scope) {
                this.scope = scope;
                this.refresh();
            },
            async refresh() {
                try {
                    const url = `${this.metricsUrl}?scope=${encodeURIComponent(this.scope)}`;
                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    this.rows = data.rows || [];
                    this.summary = data.summary || this.summary;
                    this.lastRefreshedText = new Date().toLocaleString();
                } catch (error) {
                    console.error('API monitor refresh failed', error);
                }
            },
            statusBadgeClass(status) {
                if (status === 'healthy') {
                    return 'bg-emerald-100 text-emerald-700';
                }
                if (status === 'failing') {
                    return 'bg-rose-100 text-rose-700';
                }
                return 'bg-amber-100 text-amber-700';
            },
            formatStatus(status) {
                if (status === 'healthy') {
                    return 'Healthy';
                }
                if (status === 'failing') {
                    return 'Failing';
                }
                return 'Unknown';
            },
            sparklinePoints(values) {
                const width = 112;
                const height = 40;
                const count = values.length;
                if (count <= 1) {
                    return '';
                }

                const stepX = width / (count - 1);
                return values.map((value, index) => {
                    const clamped = Math.max(0, Math.min(100, Number(value) || 0));
                    const x = (index * stepX).toFixed(2);
                    const y = (height - ((clamped / 100) * (height - 4)) - 2).toFixed(2);
                    return `${x},${y}`;
                }).join(' ');
            }
        };
    }
</script>
@endsection
