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
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Time Records</h2>
                    <p class="text-sm text-gray-600 mt-1">Total records: {{ $totalRecords }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(auth()->user()->role === 'student')
                        <button onclick="openRecordAttendanceModal()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Record Attendance
                        </button>
                    @endif
                    <a href="{{ route('user.dtr.export-pdf', request()->query()) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generate PDF
                    </a>
                </div>
            </div>
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
                                                @php
                                                    // Determine status first
                                                    if ($dtr->status === 'absent') {
                                                        $statusLabel = 'Absent';
                                                        $statusClass = 'bg-red-100 text-red-800';
                                                        $isCompleted = false;
                                                    } elseif ($dtr->status === 'on_leave') {
                                                        $statusLabel = 'Leave';
                                                        $statusClass = 'bg-purple-100 text-purple-800';
                                                        $isCompleted = false;
                                                    } elseif ($dtr->status === 'travel') {
                                                        $statusLabel = 'Travel';
                                                        $statusClass = 'bg-blue-100 text-blue-800';
                                                        $isCompleted = false;
                                                    } else {
                                                        $totalMinutesForStatus = (int) round(($dtr->total_hours ?? 0) * 60);
                                                        if ($totalMinutesForStatus < 480) {
                                                            $statusLabel = 'Under Time';
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                            $isCompleted = false;
                                                        } else {
                                                            $statusLabel = 'Completed';
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                            $isCompleted = true;
                                                        }
                                                    }
                                                    
                                                    // Calculate time values
                                                    $workedHours = max(($dtr->total_hours ?? 0) - ($dtr->added_time_from_note ?? 0), 0);
                                                    $workedMinutes = (int) round($workedHours * 60);
                                                    $workedH = intdiv($workedMinutes, 60);
                                                    $workedM = $workedMinutes % 60;
                                                    $workedFormatted = sprintf('%02d:%02d', $workedH, $workedM);
                                                    $workedOver8Hours = $workedMinutes > 480; // 8 hours = 480 minutes
                                                    
                                                    $extraMinutes = (int) round(($dtr->added_time_from_note ?? 0) * 60);
                                                    
                                                    $totalMinutes = (int) round(($dtr->total_hours ?? 0) * 60);
                                                    $totalH = intdiv($totalMinutes, 60);
                                                    $totalM = $totalMinutes % 60;
                                                    $totalFormatted = sprintf('%02d:%02d', $totalH, $totalM);
                                                    $totalOver8Hours = $totalMinutes > 480; // 8 hours = 480 minutes
                                                    
                                                    $otMinutes = (int) round(($dtr->overtime_hours ?? 0) * 60);
                                                @endphp
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ $dtr->date->format('M d, Y') }}</div>
                                                        <div class="text-xs text-gray-500">{{ $dtr->date->format('l') }}</div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            @if($isCompleted || $workedOver8Hours)
                                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                </svg>
                                                            @else
                                                                {{ $workedMinutes > 0 ? $workedFormatted : '00:00' }}
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            @if($isCompleted || $extraMinutes > 0)
                                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                </svg>
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            @if($isCompleted || $totalOver8Hours)
                                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                </svg>
                                                            @else
                                                                {{ $totalMinutes > 0 ? $totalFormatted : '00:00' }}
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-orange-600">
                                                            @if($isCompleted || $otMinutes > 0)
                                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                </svg>
                                                            @else
                                                                <span class="text-gray-400">-</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
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
                                                $isWeekComplete = $deficitMinutes == 0;
                                            @endphp
                                            <tr>
                                                <td colspan="3" class="px-3 sm:px-6 py-3 text-right text-sm font-bold text-gray-900">Weekly Total:</td>
                                                <td class="px-3 sm:px-6 py-3 text-sm font-bold text-gray-900">
                                                    @if($isWeekComplete)
                                                        <svg class="w-5 h-5 text-green-600 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @else
                                                        {{ $weeklyTotalFormatted }}
                                                    @endif
                                                </td>
                                                <td class="px-3 sm:px-6 py-3 text-sm font-bold text-orange-600">
                                                    @if($isWeekComplete)
                                                        <svg class="w-5 h-5 text-green-600 inline-block" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    @else
                                                        {{ $weeklyOvertimeFormatted }}
                                                    @endif
                                                </td>
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

@if(auth()->user()->role === 'student')
<!-- Record Attendance Modal -->
<div id="record-attendance-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Record Attendance</h3>
            <button onclick="closeRecordAttendanceModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="{{ route('user.dtr-time-requests.store') }}" method="POST" id="record-attendance-form">
            @csrf
            <input type="hidden" name="filter_date_from" value="{{ request('date_from') }}">
            <input type="hidden" name="filter_date_to" value="{{ request('date_to') }}">

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="attendance_date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" 
                               name="date_from" 
                               id="attendance_date_from" 
                               required
                               min="{{ request('date_from') ?: '' }}"
                               max="{{ request('date_to') ?: '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('date_from')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="attendance_date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" 
                               name="date_to" 
                               id="attendance_date_to" 
                               required
                               min="{{ request('date_from') ?: '' }}"
                               max="{{ request('date_to') ?: '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Date range must be within the selected filter range</p>
                        @error('date_to')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hours per Day</label>
                    <div id="days-container" class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
                        <p class="text-sm text-gray-500 text-center">Select a date range to see days</p>
                    </div>
                </div>

                <div>
                    <label for="attendance_remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks (Optional)</label>
                    <textarea name="remarks" 
                              id="attendance_remarks" 
                              rows="3"
                              maxlength="1000"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Add any additional notes about this attendance</p>
                    @error('remarks')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" 
                        onclick="closeRecordAttendanceModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRecordAttendanceModal() {
    const modal = document.getElementById('record-attendance-modal');
    if (modal) {
        modal.classList.remove('hidden');
        updateDaysList();
    }
}

function closeRecordAttendanceModal() {
    const modal = document.getElementById('record-attendance-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('record-attendance-form').reset();
        document.getElementById('days-container').innerHTML = '<p class="text-sm text-gray-500 text-center">Select a date range to see days</p>';
    }
}

function updateDaysList() {
    const dateFrom = document.getElementById('attendance_date_from').value;
    const dateTo = document.getElementById('attendance_date_to').value;
    const container = document.getElementById('days-container');
    
    if (!dateFrom || !dateTo) {
        container.innerHTML = '<p class="text-sm text-gray-500 text-center">Please select both date from and date to</p>';
        return;
    }
    
    const from = new Date(dateFrom);
    const to = new Date(dateTo);
    
    if (from > to) {
        container.innerHTML = '<p class="text-sm text-red-500 text-center">Date From must be before Date To</p>';
        return;
    }
    
    // Get filter range
    const filterFrom = '{{ request("date_from") }}';
    const filterTo = '{{ request("date_to") }}';
    
    if (filterFrom && filterTo) {
        const filterFromDate = new Date(filterFrom);
        const filterToDate = new Date(filterTo);
        
        if (from < filterFromDate || to > filterToDate) {
            container.innerHTML = '<p class="text-sm text-red-500 text-center">Date range must be within the selected filter range</p>';
            return;
        }
    }
    
    // Generate days list
    let html = '';
    const currentDate = new Date(from);
    let dayIndex = 0;
    
    while (currentDate <= to) {
        const dateStr = currentDate.toISOString().split('T')[0];
        const dayName = currentDate.toLocaleDateString('en-US', { weekday: 'short' });
        const dayNum = currentDate.getDate();
        const monthName = currentDate.toLocaleDateString('en-US', { month: 'short' });
        
        html += `
            <div class="flex items-center gap-3 p-2 bg-gray-50 rounded border border-gray-200">
                <div class="w-24 text-sm font-medium text-gray-700">
                    ${dayName}, ${monthName} ${dayNum}
                </div>
                <input type="date" 
                       name="days[${dayIndex}][date]" 
                       value="${dateStr}" 
                       hidden>
                <input type="text" 
                       name="days[${dayIndex}][time]" 
                       value="08:00"
                       pattern="^([0-1][0-9]|2[0-3]):[0-5][0-9]$"
                       placeholder="08:00"
                       maxlength="5"
                       required
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       style="font-family: monospace; text-align: center;"
                       oninput="formatTimeInput(this)">
            </div>
        `;
        
        currentDate.setDate(currentDate.getDate() + 1);
        dayIndex++;
    }
    
    if (html === '') {
        container.innerHTML = '<p class="text-sm text-gray-500 text-center">No days in range</p>';
    } else {
        container.innerHTML = html;
    }
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    const dateFromInput = document.getElementById('attendance_date_from');
    const dateToInput = document.getElementById('attendance_date_to');
    
    if (dateFromInput) {
        dateFromInput.addEventListener('change', updateDaysList);
    }
    if (dateToInput) {
        dateToInput.addEventListener('change', updateDaysList);
    }
    
    // Set default dates if filter range exists
    const filterFrom = '{{ request("date_from") }}';
    const filterTo = '{{ request("date_to") }}';
    
    if (filterFrom && filterTo && dateFromInput && dateToInput) {
        const today = new Date().toISOString().split('T')[0];
        if (today >= filterFrom && today <= filterTo) {
            dateFromInput.value = today;
            dateToInput.value = today;
        } else {
            dateFromInput.value = filterFrom;
            dateToInput.value = filterFrom;
        }
    }
});

// Format time input to HH:MM format
function formatTimeInput(input) {
    let value = input.value.replace(/[^\d]/g, ''); // Remove non-digits
    
    if (value.length >= 2) {
        value = value.substring(0, 2) + ':' + value.substring(2, 4);
    }
    
    // Validate hours (00-23) and minutes (00-59)
    const parts = value.split(':');
    if (parts.length === 2) {
        let hours = parseInt(parts[0]) || 0;
        let minutes = parseInt(parts[1]) || 0;
        
        if (hours > 23) hours = 23;
        if (minutes > 59) minutes = 59;
        
        value = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
    }
    
    input.value = value;
}

// Close modal when clicking outside
document.getElementById('record-attendance-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRecordAttendanceModal();
    }
});
</script>
@endif
@endsection


