@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-4">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Daily Time Record (DTR)</h1>
                    <p class="text-indigo-100 mt-1">View and manage all employee time records</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.dtr.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add DTR Record
                </a>
                <a href="{{ route('admin.dtr.template') }}"
                   class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Template
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
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
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
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

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Import Warnings</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Section -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Import DTR Records</h2>
                <p class="text-sm text-gray-600 mt-1">Upload a CSV file to import multiple employee time records at once</p>
            </div>
        </div>

        <form action="{{ route('admin.dtr.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">Accepted formats: CSV, TXT (Max: 5MB)</p>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Import CSV
                    </button>
                </div>
            </div>
            <div class="bg-blue-50 border-l-4 border-blue-400 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">CSV Format Requirements</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p class="mb-2">CSV columns (in order):</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Employee Email</strong> - Must match an existing employee email</li>
                                <li><strong>Date</strong> - Format: YYYY-MM-DD (e.g., 2024-12-01)</li>
                                <li><strong>Worked Hours</strong> - Base hours worked for that day in <strong>HH:MM</strong> (e.g., 08:00, 07:30)</li>
                                <li><strong>Added Time From Note</strong> - Extra hours to add in <strong>HH:MM</strong> (e.g., 01:15)</li>
                                <li><strong>Total Hours</strong> - Optional, will be recalculated as Worked Hours + Added Time From Note</li>
                                <li><strong>Overtime Hours</strong> - Optional, system will recalculate as (Total Hours − 08:00) when Total Hours &gt; 08:00</li>
                                <li><strong>Status</strong> - present, absent, late, half_day, on_leave (optional, defaults to present)</li>
                                <li><strong>Remarks</strong> - Any additional notes (optional)</li>
                            </ul>
                            <p class="mt-2">Download the template CSV file for reference.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6">
        <form method="GET" action="{{ route('admin.dtr.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Employee Filter -->
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                    <select name="employee_id" id="employee_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

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
                    </select>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('admin.dtr.index') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DTR Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Time Records</h2>
            <p class="text-sm text-gray-600 mt-1">Total records: {{ $totalRecords }}</p>
        </div>

        <div class="overflow-x-auto" id="dtr-groups-root">
            @forelse($groupedDtrs as $monthKey => $month)
                <div class="border-b border-gray-200" data-month-group>
                    <button type="button"
                            class="w-full flex items-center justify-between px-6 py-3 bg-gray-100 hover:bg-gray-200 transition text-left"
                            data-toggle="month">
                        <h3 class="text-md font-bold text-gray-900">{{ $month['label'] }}</h3>
                        <span class="ml-3 inline-flex items-center justify-center rounded-full bg-white/70 text-gray-700 text-xs px-2 py-0.5">
                            <span class="mr-1" data-month-chevron>−</span>
                            Toggle
                        </span>
                    </button>

                    <div class="border-t border-gray-200" data-month-content>
                        @foreach($month['weeks'] as $weekKey => $week)
                            <div class="border-b border-gray-200" data-week-group>
                                <button type="button"
                                        class="w-full flex items-center justify-between px-6 py-2 bg-gray-50 hover:bg-gray-100 transition text-left"
                                        data-toggle="week">
                                    <h4 class="text-sm font-semibold text-gray-800">{{ $week['label'] }}</h4>
                                    <span class="ml-3 inline-flex items-center justify-center rounded-full bg-white/70 text-gray-700 text-xs px-2 py-0.5">
                                        <span class="mr-1" data-week-chevron>−</span>
                                        Toggle
                                    </span>
                                </button>

                                <div data-week-content>
                            @foreach($week['employees'] as $employeeGroup)
                                <div class="px-6 py-2 bg-white">
                                    <div class="flex items-center mb-2">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-indigo-600 font-medium text-xs">
                                                    {{ substr($employeeGroup['employee']->name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $employeeGroup['employee']->name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $employeeGroup['employee']->email }}
                                            </div>
                                        </div>
                                    </div>

                                    <table class="min-w-full divide-y divide-gray-200 mb-4">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worked Hours</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added Time From Note</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($employeeGroup['records'] as $dtr)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 py-2 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">{{ $dtr->date->format('M d, Y') }}</div>
                                                        <div class="text-xs text-gray-500">{{ $dtr->date->format('l') }}</div>
                                                    </td>
                                                    <td class="px-3 py-2 whitespace-nowrap">
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
                                                    <td class="px-3 py-2 whitespace-nowrap">
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
                                                    <td class="px-3 py-2 whitespace-nowrap">
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
                                                    <td class="px-3 py-2 whitespace-nowrap">
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
                                                    <td class="px-3 py-2 whitespace-nowrap">
                                                        @php
                                                            // If status is travel, show Travel
                                                            if ($dtr->status === 'travel') {
                                                                $statusLabel = 'Travel';
                                                                $statusClass = 'bg-blue-100 text-blue-800';
                                                            } else {
                                                                // Otherwise, show Under Time or Completed based on total hours
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
                                                    <td class="px-3 py-2">
                                                        <div class="flex items-center space-x-3">
                                                            <div class="text-sm text-gray-500 max-w-xs truncate" title="{{ $dtr->remarks }}">
                                                                {{ $dtr->remarks ?: '-' }}
                                                            </div>
                                                            <a href="{{ route('admin.dtr.edit', $dtr) }}"
                                                               class="inline-flex items-center px-2.5 py-1.5 border border-indigo-200 text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                                                Edit
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No time records found</h3>
                    <p class="mt-1 text-sm text-gray-500">No employee time records match your filters.</p>
                </div>
            @endforelse
        </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('dtr-groups-root');
        if (!root) {
            console.error('DTR groups root not found');
            return;
        }

        function toggleVisibility(element, chevron) {
            if (!element) return;
            
            const isHidden = element.classList.contains('hidden');
            if (isHidden) {
                element.classList.remove('hidden');
                element.style.display = '';
                if (chevron) chevron.textContent = '−';
            } else {
                element.classList.add('hidden');
                element.style.display = 'none';
                if (chevron) chevron.textContent = '+';
            }
        }

        // Month toggles
        root.querySelectorAll('[data-month-group]').forEach(function (monthGroup, monthIndex) {
            const button = monthGroup.querySelector('[data-toggle="month"]');
            const content = monthGroup.querySelector('[data-month-content]');
            const chevron = monthGroup.querySelector('[data-month-chevron]');
            
            if (!button || !content) {
                console.warn('Month toggle elements not found', { button: !!button, content: !!content });
                return;
            }

            // Collapse all months except the first by default
            if (monthIndex > 0) {
                content.classList.add('hidden');
                content.style.display = 'none';
                if (chevron) chevron.textContent = '+';
            }

            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleVisibility(content, chevron);
            });
        });

        // Week toggles
        root.querySelectorAll('[data-week-group]').forEach(function (weekGroup) {
            const button = weekGroup.querySelector('[data-toggle="week"]');
            const content = weekGroup.querySelector('[data-week-content]');
            const chevron = weekGroup.querySelector('[data-week-chevron]');
            
            if (!button || !content) {
                console.warn('Week toggle elements not found', { button: !!button, content: !!content });
                return;
            }

            // Weeks expanded by default
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleVisibility(content, chevron);
            });
        });
    });
</script>

@endsection

