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

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg mx-2 sm:mx-3 lg:mx-4 xl:mx-6 mt-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif


    <!-- Filter Form -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 mx-2 sm:mx-3 lg:mx-4 xl:mx-6 mt-4">
        <form method="GET" action="{{ url('/dtr') }}" class="space-y-4">
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
                        <option value="travel" {{ request('status') == 'travel' ? 'selected' : '' }}>TRAVEL</option>
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
                <a href="{{ url('/dtr') }}" class="inline-flex items-center px-4 sm:px-6 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Pending Time Requests (Students Only) -->
    @if(auth()->user()->role === 'student' && isset($pendingTimeRequests) && $pendingTimeRequests->count() > 0)
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mx-2 sm:mx-3 lg:mx-4 xl:mx-6 mt-4 mb-4">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-yellow-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Pending Time Requests</h2>
                    <p class="text-sm text-gray-600 mt-1">Time requests awaiting approval ({{ $pendingTimeRequests->count() }})</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingTimeRequests as $request)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $request->date->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $hours = (int) $request->hours;
                                $minutes = (int) round(($request->hours - $hours) * 60);
                            @endphp
                            {{ sprintf('%02d:%02d', $hours, $minutes) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $request->getStatusBadgeClass() }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $request->remarks ? \Illuminate\Support\Str::limit($request->remarks, 50) : '—' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $request->created_at->format('M d, Y g:i A') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

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
                                                        $isCompleted = true; // Show checkmark for leave entries
                                                    } elseif ($dtr->status === 'travel') {
                                                        $statusLabel = 'TRAVEL';
                                                        $statusClass = 'bg-blue-100 text-blue-800';
                                                        $isCompleted = true; // Show checkmark for travel entries
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
                                                @endphp
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ $dtr->date->format('M d, Y') }}</div>
                                                        <div class="text-xs text-gray-500">{{ $dtr->date->format('l') }}</div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            @if($isCompleted)
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
                                                            @if($isCompleted)
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
                                                            @if($isCompleted)
                                                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                </svg>
                                                            @else
                                                                {{ $totalMinutes > 0 ? $totalFormatted : '00:00' }}
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
                                                foreach ($week['records'] as $dtr) {
                                                    $weeklyTotalHours += ($dtr->total_hours ?? 0);
                                                }
                                                $weeklyTotalMinutes = (int) round($weeklyTotalHours * 60);
                                                $weeklyTotalH = intdiv($weeklyTotalMinutes, 60);
                                                $weeklyTotalM = $weeklyTotalMinutes % 60;
                                                $weeklyTotalFormatted = sprintf('%02d:%02d', $weeklyTotalH, $weeklyTotalM);

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

        <form action="{{ url('/dtr-time-requests') }}" method="POST" id="record-attendance-form" onsubmit="return validateAttendanceForm(event)">
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
                               max="{{ date('Y-m-d') }}"
                               min="{{ request('date_from') ?: '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Cannot select future dates</p>
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
                               max="{{ date('Y-m-d') }}"
                               min="{{ request('date_from') ?: '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Date range must be within the selected filter range and cannot be in the future</p>
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
                    @error('days')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @if(is_array($message))
                            <ul class="mt-1 list-disc list-inside text-sm text-red-600">
                                @foreach($message as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @enderror
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
    
    // Parse dates using local timezone to avoid timezone issues
    const fromParts = dateFrom.split('-');
    const toParts = dateTo.split('-');
    const fromDate = new Date(parseInt(fromParts[0]), parseInt(fromParts[1]) - 1, parseInt(fromParts[2]));
    const toDate = new Date(parseInt(toParts[0]), parseInt(toParts[1]) - 1, parseInt(toParts[2]));
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Normalize dates to compare only date part (ignore time)
    const fromDateOnly = new Date(fromDate.getFullYear(), fromDate.getMonth(), fromDate.getDate());
    const toDateOnly = new Date(toDate.getFullYear(), toDate.getMonth(), toDate.getDate());
    const todayDateOnly = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    
    if (fromDateOnly > toDateOnly) {
        container.innerHTML = '<p class="text-sm text-red-500 text-center">Date From must be before Date To</p>';
        return;
    }
    
    // Check if any date is in the future (using date-only comparison)
    if (fromDateOnly > todayDateOnly || toDateOnly > todayDateOnly) {
        container.innerHTML = '<p class="text-sm text-red-500 text-center">Cannot select future dates. Only past and today\'s dates are allowed.</p>';
        return;
    }
    
    // Get filter range
    const filterFrom = '{{ request("date_from") }}';
    const filterTo = '{{ request("date_to") }}';
    
    if (filterFrom && filterTo) {
        const filterFromParts = filterFrom.split('-');
        const filterToParts = filterTo.split('-');
        const filterFromDate = new Date(parseInt(filterFromParts[0]), parseInt(filterFromParts[1]) - 1, parseInt(filterFromParts[2]));
        const filterToDate = new Date(parseInt(filterToParts[0]), parseInt(filterToParts[1]) - 1, parseInt(filterToParts[2]));
        
        if (fromDateOnly < filterFromDate || toDateOnly > filterToDate) {
            container.innerHTML = '<p class="text-sm text-red-500 text-center">Date range must be within the selected filter range</p>';
            return;
        }
    }
    
    // Generate days list
    let html = '';
    const currentDate = new Date(fromDate.getTime());
    const endDate = new Date(toDate.getTime());
    let dayIndex = 0;
    
    while (currentDate <= endDate) {
        // Format date as YYYY-MM-DD (timezone-safe, using local date components)
        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0');
        const day = String(currentDate.getDate()).padStart(2, '0');
        const dateStr = `${year}-${month}-${day}`;
        
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
                <div class="flex-1 relative">
                    <input type="text" 
                           name="days[${dayIndex}][time]" 
                           id="time-input-${dayIndex}"
                           value=""
                           placeholder="00:00"
                           class="time-input w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center"
                           style="text-align: center;"
                           oninput="formatTimeInput(this)"
                           onblur="formatTimeOnBlur(this)">
                    <button type="button" 
                            onclick="clearTimeField('time-input-${dayIndex}')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                            title="Clear time">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        // Move to next day (add 1 day)
        currentDate.setDate(currentDate.getDate() + 1);
        dayIndex++;
    }
    
    if (html === '') {
        container.innerHTML = '<p class="text-sm text-gray-500 text-center">No days in range</p>';
    } else {
        container.innerHTML = html;
        
        // Update date_from and date_to to match the actual generated days (fix timezone issues)
        setTimeout(() => {
            const dayDateInputs = document.querySelectorAll('input[name^="days"][name$="[date]"]');
            if (dayDateInputs.length > 0) {
                const firstDate = dayDateInputs[0].value;
                const lastDate = dayDateInputs[dayDateInputs.length - 1].value;
                
                // Update the form date fields to match the actual generated days
                const dateFromInput = document.getElementById('attendance_date_from');
                const dateToInput = document.getElementById('attendance_date_to');
                
                if (dateFromInput && dateToInput) {
                    dateFromInput.value = firstDate;
                    dateToInput.value = lastDate;
                }
            }
        }, 100);
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
    const today = new Date().toISOString().split('T')[0];
    
    if (filterFrom && filterTo && dateFromInput && dateToInput) {
        // Use today if within filter range, otherwise use filterFrom (but not future)
        const defaultDate = (today >= filterFrom && today <= filterTo) ? today : filterFrom;
        dateFromInput.value = defaultDate;
        dateToInput.value = defaultDate;
    } else if (dateFromInput && dateToInput) {
        // If no filter, default to today
        dateFromInput.value = today;
        dateToInput.value = today;
    }
});

// Format time input (same as admin DTR time input)
function formatTimeInput(input) {
    const cursorPos = input.selectionStart;
    const before = input.value;
    
    // Format the value using the same logic as admin
    function formatTime(value) {
        // Keep only digits, max 4
        let digits = value.replace(/\D/g, '').slice(0, 4);
        if (digits.length <= 2) {
            return digits;
        }
        const h = digits.slice(0, 2);
        const m = digits.slice(2);
        return m ? h + ':' + m : h;
    }
    
    input.value = formatTime(input.value);
    
    // Best-effort keep cursor near end
    if (document.activeElement === input) {
        input.selectionStart = input.selectionEnd = input.value.length;
    }
}

// Format time to HH:MM format on blur (when user finishes input)
function formatTimeOnBlur(input) {
    let value = input.value.trim();
    
    if (value === '') {
        input.value = '';
        return;
    }
    
    // Remove all non-digit characters
    let digits = value.replace(/\D/g, '');
    
    if (digits.length === 0) {
        input.value = '';
        return;
    }
    
    // Format to HH:MM (always 2 digits for hours and minutes)
    let hours = '';
    let minutes = '';
    
    if (value.includes(':')) {
        // Already has colon, split it
        const parts = value.split(':');
        hours = parts[0].replace(/\D/g, '').slice(-2); // Take last 2 digits for hours
        minutes = parts[1].replace(/\D/g, '').slice(0, 2); // Take first 2 digits for minutes
    } else {
        // No colon, split digits (last 2 are minutes)
        if (digits.length >= 2) {
            hours = digits.slice(0, digits.length - 2).slice(-2); // Take last 2 digits before minutes
            minutes = digits.slice(-2); // Last 2 digits are minutes
        } else {
            // Less than 2 digits, treat as hours only
            hours = digits.padStart(2, '0');
            minutes = '00';
        }
    }
    
    // Pad hours to 2 digits (always HH format)
    hours = hours.padStart(2, '0').slice(0, 2);
    
    // Pad minutes to 2 digits (always MM format)
    minutes = minutes.padStart(2, '0').slice(0, 2);
    
    // Format final value as HH:MM
    input.value = hours + ':' + minutes;
}


// Clear time field
function clearTimeField(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.value = '';
        input.focus();
    }
}

// Validate attendance form before submission
function validateAttendanceForm(event) {
    const form = document.getElementById('record-attendance-form');
    if (!form) return true;
    
    const timeInputs = form.querySelectorAll('input[name^="days"][name$="[time]"]');
    let hasValidTime = false;
    const emptyFields = [];
    const invalidFields = [];
    
    timeInputs.forEach((input, index) => {
        const timeValue = input.value.trim();
        // Check if time is in HH:MM format
        const timePattern = /^[0-9]{2}:[0-9]{2}$/;
        
        if (timeValue && timePattern.test(timeValue)) {
            hasValidTime = true;
        } else if (timeValue) {
            invalidFields.push(`Day ${index + 1} has invalid time format "${timeValue}". Please use HH:MM format (e.g., 00:00, 08:30).`);
        }
    });
    
    if (!hasValidTime) {
        event.preventDefault();
        alert('Please enter at least one valid time in HH:MM format (e.g., 08:00).\n\nEmpty or invalid time fields will be ignored.');
        return false;
    }
    
    if (invalidFields.length > 0) {
        const proceed = confirm('Some days have invalid time format. Only days with valid time (HH:MM) will be submitted.\n\n' + invalidFields.join('\n') + '\n\nDo you want to continue?');
        if (!proceed) {
            event.preventDefault();
            return false;
        }
    }
    
    // Remove empty time fields before submission
    timeInputs.forEach((input) => {
        const timeValue = input.value.trim();
        const timePattern = /^[0-9]{2}:[0-9]{2}$/;
        
        if (!timeValue || !timePattern.test(timeValue)) {
            // Remove the parent day container if time is invalid
            const dayContainer = input.closest('.flex.items-center.gap-3');
            if (dayContainer) {
                const dateInput = dayContainer.querySelector('input[type="date"]');
                if (dateInput) {
                    dateInput.remove();
                }
                input.remove();
            }
        }
    });
    
    return true;
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


