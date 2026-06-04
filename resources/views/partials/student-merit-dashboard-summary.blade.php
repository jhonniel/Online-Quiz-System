@php
    $details = $studentMeritDetails ?? null;
    if (! is_array($details)) {
        return;
    }
    $b = $details['breakdown'] ?? [];
    $absence = $details['absence'] ?? [];
    $notices = $details['notices'] ?? [];
    $thresholds = $details['thresholds'] ?? [];
    $meritTotal = (int) ($b['total'] ?? 0);
    $meritTotalHigh = $meritTotal >= (int) ($thresholds['final'] ?? 3);
    $undertimeFilings = $details['undertime_filings'] ?? [];
    $absentRequests = $details['absent_requests'] ?? [];
@endphp
<div class="bg-white border border-amber-200 rounded-lg p-4 shadow-sm" role="region" aria-label="Merits and conduct">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Merits &amp; conduct</h3>
            <p class="text-xs text-gray-500 mt-0.5">
                Under-time filings, approved absences, and administrator actions can add merits. Review your TOR for rules.
            </p>
        </div>
        <a href="{{ route('user.tor') }}"
           class="inline-flex items-center justify-center px-3 py-1.5 rounded-md text-xs font-medium text-amber-900 bg-amber-50 border border-amber-200 hover:bg-amber-100 shrink-0">
            View TOR
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2">
            <p class="text-xs text-gray-500">Under-time merits</p>
            <p class="text-xl font-bold text-gray-900 tabular-nums">{{ (int) ($b['undertime'] ?? 0) }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2">
            <p class="text-xs text-gray-500">Excess absences</p>
            <p class="text-xl font-bold text-gray-900 tabular-nums">{{ (int) ($b['excess_absence'] ?? 0) }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2">
            <p class="text-xs text-gray-500">Manual</p>
            <p class="text-xl font-bold text-gray-900 tabular-nums">{{ (int) ($b['manual'] ?? 0) }}</p>
        </div>
        <div class="rounded-lg border px-3 py-2 {{ $meritTotalHigh ? 'border-red-300 bg-red-100/50' : 'border-amber-300 bg-amber-100/50' }}">
            <p class="text-xs font-medium {{ $meritTotalHigh ? 'text-red-900' : 'text-amber-900' }}">Total merits</p>
            <p class="text-2xl font-bold tabular-nums {{ $meritTotalHigh ? 'text-red-900' : 'text-amber-900' }}">{{ $meritTotal }}</p>
        </div>
    </div>

    <p class="text-xs text-gray-500 mb-3">
        System notices: rules warning at <strong>{{ (int) ($thresholds['warning'] ?? 1) }}+</strong> merits;
        final notice at <strong>{{ (int) ($thresholds['final'] ?? 3) }}+</strong> merits.
    </p>

    @php
        $activeNotices = [];
        if (! empty($notices['rules_warning'])) {
            $activeNotices[] = 'Rules violation warning (active)';
        }
        if (! empty($notices['final_notice'])) {
            $activeNotices[] = 'Final notice (active)';
        }
        if (! empty($notices['merit_automation_disabled'])) {
            $activeNotices[] = 'Automatic merit notices paused by administration';
        }
        if (! empty($notices['student_terminated'])) {
            $activeNotices[] = 'Account terminated';
        }
    @endphp
    <div class="rounded-lg border border-gray-200 p-3 mb-4 {{ count($activeNotices) > 0 ? 'bg-amber-50/40' : 'bg-gray-50/50' }}">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Rules notices on your account</p>
        @if(count($activeNotices) > 0)
            <ul class="text-sm text-gray-800 list-disc list-inside space-y-0.5">
                @foreach($activeNotices as $line)
                    <li>{{ $line }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-600">No active rules notices from merits at this time.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-2">Absence balance &amp; merits</h4>
            <dl class="grid grid-cols-2 gap-2 text-sm">
                <div>
                    <dt class="text-gray-500">Allowance</dt>
                    <dd class="font-semibold tabular-nums">{{ number_format((float) ($absence['allowable'] ?? 0), 2) }} days</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Approved absent</dt>
                    <dd class="font-semibold tabular-nums">{{ (int) ($absence['approved_days'] ?? 0) }} days</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Remaining</dt>
                    <dd class="font-semibold tabular-nums">{{ number_format((float) ($absence['remaining_balance'] ?? 0), 2) }} days</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Excess absence merits</dt>
                    <dd class="font-semibold tabular-nums">{{ (int) ($absence['excess_merits'] ?? 0) }}</dd>
                </div>
            </dl>
            @if(! empty($details['rules']['excess_absence']))
                <p class="mt-2 text-xs text-gray-500">{{ $details['rules']['excess_absence'] }}</p>
            @endif
        </div>
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-1">How merits are counted</h4>
            <ul class="text-xs text-gray-600 space-y-1.5 list-disc list-inside">
                @if(! empty($details['rules']['undertime']))
                    <li>{{ $details['rules']['undertime'] }}</li>
                @endif
                @if(! empty($details['rules']['manual']))
                    <li>{{ $details['rules']['manual'] }}</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="space-y-4">
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-2">
                Under-time time requests
                <span class="font-normal text-gray-500">({{ count($undertimeFilings) }})</span>
            </h4>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Filed</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse($undertimeFilings as $row)
                            <tr class="border-t border-gray-100">
                                <td class="px-3 py-2 whitespace-nowrap">{{ $row['date'] ?? '—' }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $row['hours_label'] ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    @php $st = strtolower((string) ($row['status'] ?? '')); @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $st === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($st === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">
                                        {{ $row['status'] ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-gray-500 text-center">No under-time filings below 08:00.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-2">
                Approved absent leave
                <span class="font-normal text-gray-500">({{ count($absentRequests) }})</span>
            </h4>
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-3 py-2">Period</th>
                            <th class="px-3 py-2">Days</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse($absentRequests as $row)
                            <tr class="border-t border-gray-100">
                                <td class="px-3 py-2">{{ $row['range'] ?? '—' }}</td>
                                <td class="px-3 py-2 tabular-nums">{{ $row['days'] ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                        {{ $row['status'] ?? 'approved' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-gray-500 text-center">No approved absent leave requests yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
