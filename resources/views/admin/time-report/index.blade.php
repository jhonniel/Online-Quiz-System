@extends('layouts.admin')

@section('page-title', 'Time Report')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Employee Management</span>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Time Report</span>
        </div>
    </li>
@endsection

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Time Report</h1>
                <p class="mt-2 text-sm text-gray-600">Weekly time tracking and attendance reports for employees</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ url('/admin/time-report') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="flex flex-col">
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select name="department_id" id="department_id" class="block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $department)
                            <option value="{{ $department->id }}" {{ $selectedDepartmentId == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Filter by Employee</label>
                    <select name="employee_id" id="employee_id" class="block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ $selectedEmployeeId == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? $weekStartDate->format('Y-m-d') }}"
                           class="block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="flex flex-col">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? $weekEndDate->format('Y-m-d') }}"
                           class="block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="flex flex-col">
                    <label for="week_start" class="block text-sm font-medium text-gray-700 mb-2">Or Select Week</label>
                    <input type="date" name="week_start" id="week_start" value="{{ $weekStartDate->format('Y-m-d') }}"
                           class="block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Leave date range empty to use week</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-admin-filter-button>Apply Filters</x-admin-filter-button>
                <a href="{{ url('/admin/time-report') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Reset
                </a>
            </div>
        </form>

        <!-- Week Navigation (only show if not using custom date range) -->
        @if(!$startDate || !$endDate)
        <div class="mt-4 flex items-center justify-between">
            @php
                $prevWeekParams = array_merge(request()->query(), ['week_start' => $previousWeek]);
                $nextWeekParams = array_merge(request()->query(), ['week_start' => $nextWeek]);
            @endphp
            <a href="{{ url('/admin/time-report?' . http_build_query($prevWeekParams)) }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Previous Week
            </a>
            <div class="text-sm font-medium text-gray-700">
                @if($startDate && $endDate)
                    Date Range: {{ $weekStartDate->format('M d, Y') }} - {{ $weekEndDate->format('M d, Y') }}
                @else
                    Week of {{ $weekStartDate->format('M d') }} - {{ $weekEndDate->format('M d, Y') }}
                @endif
            </div>
            <a href="{{ url('/admin/time-report?' . http_build_query($nextWeekParams)) }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Next Week
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        @else
        <div class="mt-4 text-center">
            <div class="text-sm font-medium text-gray-700">
                Date Range: {{ $weekStartDate->format('M d, Y') }} - {{ $weekEndDate->format('M d, Y') }}
            </div>
        </div>
        @endif
    </div>

    <!-- Overall Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Employees</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $overallStats['total_employees'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Hours</dt>
                            @php
                                $overallTotalMinutes = (int) round($overallStats['total_hours_all'] * 60);
                                $overallTotalH = intdiv($overallTotalMinutes, 60);
                                $overallTotalM = $overallTotalMinutes % 60;
                                $overallTotalFormatted = sprintf('%02d:%02d', $overallTotalH, $overallTotalM);
                            @endphp
                            <dd class="text-lg font-semibold text-gray-900">{{ $overallTotalFormatted }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Overtime</dt>
                            @php
                                $overallOvertimeMinutes = (int) round($overallStats['total_overtime_all'] * 60);
                                $overallOvertimeH = intdiv($overallOvertimeMinutes, 60);
                                $overallOvertimeM = $overallOvertimeMinutes % 60;
                                $overallOvertimeFormatted = sprintf('%02d:%02d', $overallOvertimeH, $overallOvertimeM);
                            @endphp
                            <dd class="text-lg font-semibold text-gray-900">{{ $overallOvertimeFormatted }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Days Present</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $overallStats['total_days_present'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Report Tiles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($weeklyReports as $report)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Employee Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-white truncate">{{ $report['employee']->name }}</h3>
                            <p class="text-xs text-indigo-100 truncate">{{ $report['employee']->email }}</p>
                        </div>
                        <div class="text-right ml-2 flex-shrink-0">
                            @php
                                $totalHoursMinutes = (int) round($report['total_hours'] * 60);
                                $totalHoursH = intdiv($totalHoursMinutes, 60);
                                $totalHoursM = $totalHoursMinutes % 60;
                                $totalHoursFormatted = sprintf('%02d:%02d', $totalHoursH, $totalHoursM);
                            @endphp
                            <div class="text-xl font-bold text-white">{{ $totalHoursFormatted }}</div>
                            <div class="text-xs text-indigo-100">Total Hours</div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-500">Overtime</div>
                            @php
                                $overtimeMinutes = (int) round($report['total_overtime'] * 60);
                                $overtimeH = intdiv($overtimeMinutes, 60);
                                $overtimeM = $overtimeMinutes % 60;
                                $overtimeFormatted = sprintf('%02d:%02d', $overtimeH, $overtimeM);
                            @endphp
                            <div class="text-lg font-bold text-yellow-600 mt-1">{{ $overtimeFormatted }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-500">Absent</div>
                            <div class="text-lg font-bold text-red-600 mt-1">{{ $report['days_absent'] }}</div>
                        </div>
                        @if(isset($report['days_on_leave']) && $report['days_on_leave'] > 0)
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-500">On Leave</div>
                            <div class="text-lg font-bold text-blue-600 mt-1">{{ $report['days_on_leave'] }}</div>
                        </div>
                        @endif
                        @if(isset($report['deficit_hours']))
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-500">Deficit</div>
                            @if($report['is_current_week'])
                                <div class="text-lg font-semibold text-gray-500 mt-1" title="Current week - deficit will be calculated after week ends">N/A</div>
                            @else
                                @php
                                    $deficitMinutes = (int) round($report['deficit_hours'] * 60);
                                    $deficitH = intdiv($deficitMinutes, 60);
                                    $deficitM = $deficitMinutes % 60;
                                    $deficitFormatted = sprintf('%02d:%02d', $deficitH, $deficitM);
                                @endphp
                                <div class="text-lg font-bold text-red-600 mt-1">
                                    {{ $deficitFormatted }}
                                </div>
                            @endif
                        </div>
                        @endif
                        @if(isset($report['balance_hours']))
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-500">Balance (Overtime - Deficit)</div>
                            @if($report['is_current_week'])
                                <div class="text-lg font-semibold text-gray-500 mt-1" title="Current week - balance will be calculated after week ends">N/A</div>
                            @else
                                @php
                                    $balanceMinutes = (int) round(abs($report['balance_hours']) * 60);
                                    $balanceH = intdiv($balanceMinutes, 60);
                                    $balanceM = $balanceMinutes % 60;
                                    $balanceFormatted = sprintf('%02d:%02d', $balanceH, $balanceM);
                                    $isBalanceNegative = $report['balance_hours'] < 0;
                                @endphp
                                <div class="text-lg font-bold {{ $isBalanceNegative ? 'text-red-600' : 'text-green-600' }} mt-1">
                                    {{ $isBalanceNegative ? '-' : '' }}{{ $balanceFormatted }}
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    <!-- Daily Breakdown -->
                    <div class="border-t pt-3">
                        <h4 class="text-xs font-semibold text-gray-700 mb-2">Daily Breakdown</h4>
                        <div class="space-y-1.5">
                            @foreach($report['daily_breakdown'] as $day)
                                <div class="flex items-center justify-between p-1.5 rounded-md {{ ($day['is_future'] ?? false) ? 'bg-gray-100' : ($day['total_hours'] > 0 ? 'bg-green-50' : 'bg-gray-50') }}">
                                    <div class="flex items-center space-x-2 min-w-0 flex-1">
                                        <div class="text-xs font-medium text-gray-700 w-20 flex-shrink-0">
                                            {{ $day['date']->format('D, M d') }}
                                        </div>
                                        <div class="flex items-center space-x-1 min-w-0">
                                            @if(isset($day['status_label']))
                                                <span class="px-1.5 py-0.5 text-xs font-medium rounded-full {{ $day['status_badge_class'] }}">
                                                    @if($day['status_label'] === 'completed')
                                                        @if($day['has_leave_request'] ?? false)
                                                            Completed ({{ $day['leave_type_label'] ?? 'Leave' }})
                                                        @else
                                                            Completed
                                                        @endif
                                                    @elseif($day['status_label'] === 'under_time')
                                                        @if($day['has_leave_request'] ?? false)
                                                            Under Time ({{ $day['leave_type_label'] ?? 'Leave' }})
                                                        @else
                                                            Under Time
                                                        @endif
                                                    @elseif($day['status_label'] === 'not_recorded')
                                                        Not Recorded
                                                    @elseif($day['status_label'] === 'leave')
                                                        {{ $day['leave_type_label'] ?? 'Leave' }}
                                                    @elseif($day['status_label'] === 'travel')
                                                        @if($day['has_leave_request'] ?? false)
                                                            TRAVEL ({{ $day['leave_type_label'] ?? 'Leave' }})
                                                        @else
                                                            TRAVEL
                                                        @endif
                                                    @elseif($day['status_label'] === 'no_records')
                                                        No Records
                                                    @else
                                                        Absent
                                                    @endif
                                                </span>
                                            @else
                                                <span class="px-1.5 py-0.5 text-xs font-medium rounded-full {{ $day['total_hours'] > 0 ? 'bg-green-100 text-green-800' : ($day['is_future'] ?? false ? 'bg-gray-100 text-gray-600' : 'bg-gray-100 text-gray-500') }}">
                                                    @if($day['is_future'] ?? false)
                                                        Not Recorded
                                                    @elseif(isset($day['dtr']) && $day['dtr']->status === 'travel')
                                                        @if($day['has_leave_request'] ?? false)
                                                            TRAVEL ({{ $day['leave_type_label'] ?? 'Leave' }})
                                                        @else
                                                            TRAVEL
                                                        @endif
                                                    @elseif($day['total_hours'] >= 8.0)
                                                        @if($day['has_leave_request'] ?? false)
                                                            Completed ({{ $day['leave_type_label'] ?? 'Leave' }})
                                                        @else
                                                            Completed
                                                        @endif
                                                    @elseif($day['total_hours'] > 0)
                                                        @if($day['has_leave_request'] ?? false)
                                                            Under Time ({{ $day['leave_type_label'] ?? 'Leave' }})
                                                        @else
                                                            Under Time
                                                        @endif
                                                    @else
                                                        No Records
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-xs font-medium text-gray-900 ml-2 flex-shrink-0">
                                        @if($day['is_future'] ?? false)
                                            <span class="text-gray-400 italic">Not yet</span>
                                        @elseif($day['total_hours'] > 0)
                                            @php
                                                $dayTotalMinutes = (int) round($day['total_hours'] * 60);
                                                $dayTotalH = intdiv($dayTotalMinutes, 60);
                                                $dayTotalM = $dayTotalMinutes % 60;
                                                $dayTotalFormatted = sprintf('%02d:%02d', $dayTotalH, $dayTotalM);

                                                $dayOvertimeMinutes = (int) round($day['overtime_hours'] * 60);
                                                $dayOvertimeH = intdiv($dayOvertimeMinutes, 60);
                                                $dayOvertimeM = $dayOvertimeMinutes % 60;
                                                $dayOvertimeFormatted = sprintf('%02d:%02d', $dayOvertimeH, $dayOvertimeM);
                                            @endphp
                                            {{ $dayTotalFormatted }}
                                            @if($day['overtime_hours'] > 0)
                                                <span class="text-yellow-600">(+{{ $dayOvertimeFormatted }})</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">0h</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white shadow rounded-lg p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No data found</h3>
                <p class="mt-1 text-sm text-gray-500">No time records found for the selected week.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const weekStartInput = document.getElementById('week_start');

    // When date range is changed, clear week filter
    function handleDateRangeChange() {
        if (startDateInput.value && endDateInput.value) {
            weekStartInput.value = '';
        }
    }

    // When week is changed, clear date range
    function handleWeekChange() {
        if (weekStartInput.value) {
            startDateInput.value = '';
            endDateInput.value = '';
        }
    }

    startDateInput.addEventListener('change', handleDateRangeChange);
    endDateInput.addEventListener('change', handleDateRangeChange);
    weekStartInput.addEventListener('change', handleWeekChange);
});
</script>
@endsection

