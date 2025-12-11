@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Daily Time Record (DTR)</h1>
                    <p class="text-indigo-100 text-sm">View your time records</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg mx-2 sm:mx-3 lg:mx-4 xl:mx-6 mt-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg mx-2 sm:mx-3 lg:mx-4 xl:mx-6 mt-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mx-2 sm:mx-3 lg:mx-4 xl:mx-6 mt-4">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Hours ({{ $totalHoursLabel }})</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalHoursFormatted }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-orange-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Overtime ({{ $overtimeWindowLabel ?? 'This Year' }})</p>
                    <p class="text-2xl font-bold {{ str_starts_with($totalOvertimeFormatted, '-') ? 'text-red-600' : 'text-gray-900' }}">{{ $totalOvertimeFormatted }}</p>
                    @if(str_starts_with($totalOvertimeFormatted, '-'))
                        <p class="text-xs text-red-500 mt-1">Negative balance</p>
                    @endif

                    @if($expiringOvertimeTotal > 0 && $minDaysRemaining !== null)
                        <div class="mt-2 pt-2 border-t border-gray-200">
                            <p class="text-xs font-medium text-amber-600 mb-1">
                                <span class="inline-flex items-center">
                                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Expiring Balance: {{ $expiringOvertimeFormatted }}
                                </span>
                            </p>
                            <p class="text-xs text-amber-600">
                                <span class="inline-flex items-center">
                                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    @if($minDaysRemaining > 0)
                                        {{ $minDaysRemaining }} {{ $minDaysRemaining == 1 ? 'day' : 'days' }} remaining
                                    @else
                                        Expiring today
                                    @endif
                                </span>
                            </p>
                        </div>
                    @endif

                    @if($currentWeekDeficitHours > 0)
                        <div class="mt-2 pt-2 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                <span class="inline-flex items-center">
                                    <svg class="h-3 w-3 mr-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Current week deficit: {{ $currentWeekDeficitFormatted }} (not yet added)
                                </span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Absent Count ({{ $currentYear }})</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $absentCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 mx-2 sm:mx-3 lg:mx-4 xl:mx-6 mt-4">
        <form method="GET" action="{{ route('user.dtr.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="travel" {{ request('status') == 'travel' ? 'selected' : '' }}>Travel</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center px-4 sm:px-6 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('user.dtr.index') }}" class="inline-flex items-center px-4 sm:px-6 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DTR Table -->
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden flex-1 flex flex-col mx-2 sm:mx-3 lg:mx-4 xl:mx-6 mt-4 mb-4">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Time Records</h2>
            <p class="text-sm text-gray-600 mt-1">Total records: {{ $totalRecords }}</p>
        </div>

        <div class="overflow-x-auto flex-1" id="dtr-groups-root">
            @forelse($groupedDtrs as $monthKey => $month)
                <div class="border-b border-gray-200" data-month-group>
                    <button type="button"
                            class="w-full flex items-center justify-between px-4 sm:px-6 py-3 bg-gray-100 hover:bg-gray-200 transition text-left"
                            data-toggle="month">
                        <h3 class="text-md font-bold text-gray-900">{{ $month['label'] }}</h3>
                        <span class="ml-3 inline-flex items-center justify-center rounded-full bg-white/70 text-gray-700 text-xs px-2 py-0.5">
                            <span class="mr-1" data-month-chevron>+</span>
                            Toggle
                        </span>
                    </button>

                    <div class="border-t border-gray-200 hidden" data-month-content style="display: none;">
                        @foreach($month['weeks'] as $weekKey => $week)
                            <div class="border-b border-gray-200" data-week-group>
                                <button type="button"
                                        class="w-full flex items-center justify-between px-4 sm:px-6 py-2 bg-gray-50 hover:bg-gray-100 transition text-left"
                                        data-toggle="week">
                                    <h4 class="text-sm font-semibold text-gray-800">{{ $week['label'] }}</h4>
                                    <span class="ml-3 inline-flex items-center justify-center rounded-full bg-white/70 text-gray-700 text-xs px-2 py-0.5">
                                        <span class="mr-1" data-week-chevron>+</span>
                                        Toggle
                                    </span>
                                </button>

                                <div class="hidden" data-week-content style="display: none;">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worked Hours</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added Time</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($week['records'] as $dtr)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ $dtr->date->format('M d, Y') }}</div>
                                                        <div class="text-xs text-gray-500">{{ $dtr->date->format('l') }}</div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $workedHours = max(($dtr->total_hours ?? 0) - ($dtr->added_time_from_note ?? 0), 0);
                                                            $workedMinutes = (int) round($workedHours * 60);
                                                            $workedH = intdiv($workedMinutes, 60);
                                                            $workedM = $workedMinutes % 60;
                                                            $workedFormatted = sprintf('%02d:%02d', $workedH, $workedM);
                                                        @endphp
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $workedMinutes > 0 ? $workedFormatted : '00:00' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $extraMinutes = (int) round(($dtr->added_time_from_note ?? 0) * 60);
                                                            $extraH = intdiv($extraMinutes, 60);
                                                            $extraM = $extraMinutes % 60;
                                                            $extraFormatted = sprintf('%02d:%02d', $extraH, $extraM);
                                                        @endphp
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $extraMinutes > 0 ? $extraFormatted : '00:00' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $totalMinutes = (int) round(($dtr->total_hours ?? 0) * 60);
                                                            $totalH = intdiv($totalMinutes, 60);
                                                            $totalM = $totalMinutes % 60;
                                                            $totalFormatted = sprintf('%02d:%02d', $totalH, $totalM);
                                                        @endphp
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $totalMinutes > 0 ? $totalFormatted : '00:00' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            $otMinutes = (int) round(($dtr->overtime_hours ?? 0) * 60);
                                                            $otH = intdiv($otMinutes, 60);
                                                            $otM = $otMinutes % 60;
                                                            $otFormatted = sprintf('%02d:%02d', $otH, $otM);
                                                        @endphp
                                                        <div class="text-sm font-medium text-orange-600">
                                                            {{ $otMinutes > 0 ? $otFormatted : '00:00' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        @php
                                                            if ($dtr->status === 'travel') {
                                                                $statusLabel = 'Travel';
                                                                $statusClass = 'bg-blue-100 text-blue-800';
                                                            } else {
                                                                $totalMinutesForStatus = (int) round(($dtr->total_hours ?? 0) * 60);
                                                                if ($totalMinutesForStatus < 480) {
                                                                    $statusLabel = 'Under Time';
                                                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                                                } else {
                                                                    $statusLabel = 'Completed';
                                                                    $statusClass = 'bg-green-100 text-green-800';
                                                                }
                                                            }
                                                        @endphp
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                            {{ $statusLabel }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4">
                                                        <div class="text-sm text-gray-500 max-w-xs truncate" title="{{ $dtr->remarks }}">
                                                            {{ $dtr->remarks ?: '-' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gradient-to-r from-indigo-50 to-purple-50 border-t-2 border-indigo-300">
                                            @php
                                                $weeklyTotalHours = 0;
                                                $weeklyOvertimeHours = 0;
                                                foreach ($week['records'] as $dtr) {
                                                    $weeklyTotalHours += ($dtr->total_hours ?? 0);
                                                    $weeklyOvertimeHours += ($dtr->overtime_hours ?? 0);
                                                }
                                                $weeklyTotalMinutes = (int) round($weeklyTotalHours * 60);
                                                $weeklyTotalH = intdiv($weeklyTotalMinutes, 60);
                                                $weeklyTotalM = $weeklyTotalMinutes % 60;
                                                $weeklyTotalFormatted = sprintf('%02d:%02d', $weeklyTotalH, $weeklyTotalM);

                                                $weeklyOvertimeMinutes = (int) round($weeklyOvertimeHours * 60);
                                                $weeklyOvertimeH = intdiv($weeklyOvertimeMinutes, 60);
                                                $weeklyOvertimeM = $weeklyOvertimeMinutes % 60;
                                                $weeklyOvertimeFormatted = sprintf('%02d:%02d', $weeklyOvertimeH, $weeklyOvertimeM);

                                                $weeklyBaseMinutes = 40 * 60;
                                                $deficitMinutes = max(0, $weeklyBaseMinutes - $weeklyTotalMinutes);
                                                $deficitH = intdiv($deficitMinutes, 60);
                                                $deficitM = $deficitMinutes % 60;
                                                $deficitFormatted = sprintf('%02d:%02d', $deficitH, $deficitM);
                                            @endphp
                                            <tr>
                                                <td colspan="3" class="px-3 sm:px-6 py-3 text-right text-sm font-bold text-gray-900">Weekly Total:</td>
                                                <td class="px-3 sm:px-6 py-3 text-sm font-bold text-gray-900">{{ $weeklyTotalFormatted }}</td>
                                                <td class="px-3 sm:px-6 py-3 text-sm font-bold text-orange-600">{{ $weeklyOvertimeFormatted }}</td>
                                                <td colspan="2" class="px-3 sm:px-6 py-3 text-sm text-gray-600">
                                                    @if($deficitMinutes > 0)
                                                        Deficit: {{ $deficitFormatted }}
                                                    @else
                                                        <span class="text-green-600 font-semibold">Complete</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-12 px-4">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No DTR records found</h3>
                    <p class="mt-2 text-sm text-gray-500">Your time records will appear here once they are added.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Month toggle functionality
    document.querySelectorAll('[data-toggle="month"]').forEach(button => {
        button.addEventListener('click', function() {
            const monthGroup = this.closest('[data-month-group]');
            const content = monthGroup.querySelector('[data-month-content]');
            const chevron = monthGroup.querySelector('[data-month-chevron]');

            if (!content) return;

            const isHidden = content.classList.contains('hidden');
            if (isHidden) {
                content.classList.remove('hidden');
                content.style.display = '';
                chevron.textContent = '-';
            } else {
                content.classList.add('hidden');
                content.style.display = 'none';
                chevron.textContent = '+';
            }
        });
    });

    // Week toggle functionality
    document.querySelectorAll('[data-toggle="week"]').forEach(button => {
        button.addEventListener('click', function() {
            const weekGroup = this.closest('[data-week-group]');
            const content = weekGroup.querySelector('[data-week-content]');
            const chevron = weekGroup.querySelector('[data-week-chevron]');

            if (!content) return;

            const isHidden = content.classList.contains('hidden');
            if (isHidden) {
                content.classList.remove('hidden');
                content.style.display = '';
                chevron.textContent = '-';
            } else {
                content.classList.add('hidden');
                content.style.display = 'none';
                chevron.textContent = '+';
            }
        });
    });
});
</script>
@endsection

