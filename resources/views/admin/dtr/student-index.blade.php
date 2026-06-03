@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3 sm:p-4">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Student Daily Time Record (DTR)</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">View and manage all student time records</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ url('/admin/student-dtr/create') }}"
                   class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200 text-xs sm:text-sm">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add DTR Record
                </a>
                <a href="{{ url('/admin/dtr/template') }}"
                   class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200 text-xs sm:text-sm">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Template
                </a>
                <a href="{{ url('/admin/student-dtr/export/pdf?' . http_build_query(request()->query())) }}"
                   class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200 text-xs sm:text-sm">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Export PDF
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
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
        <!-- Collapsible Header -->
        <button type="button"
                class="w-full flex items-center justify-between px-4 sm:px-6 py-4 bg-gray-50 hover:bg-gray-100 transition text-left"
                onclick="toggleImportSection()"
                aria-expanded="false"
                aria-controls="import-section">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Import DTR Records</h2>
                <p class="text-sm text-gray-600 mt-1">Upload a CSV file to import multiple student time records at once</p>
            </div>
            <svg id="import-chevron" class="h-5 w-5 text-gray-500 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Collapsible Content -->
        <div id="import-section" class="hidden border-t border-gray-200">
            <div class="p-4 sm:p-6">
                <form action="{{ url('/admin/dtr/import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="type" value="student">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <div class="sm:flex-1">
                    <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">Accepted formats: CSV, TXT (Max: 5MB)</p>
                </div>
                <div class="flex sm:items-end">
                    <button type="submit"
                            class="inline-flex items-center px-4 sm:px-6 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-200 w-full sm:w-auto justify-center">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <li><strong>Student Email</strong> - Must match an existing student email</li>
                                <li><strong>Date</strong> - Format: YYYY-MM-DD (e.g., 2024-12-01)</li>
                                <li><strong>Worked Hours</strong> - Base hours worked for that day in <strong>HH:MM</strong> format (e.g., 08:00, 07:30)</li>
                                <li><strong>Added Time From Note</strong> - Extra hours to add in <strong>HH:MM</strong> format (e.g., 01:15, 00:00)</li>
                                <li><strong>Activity Percentage</strong> - Performance activity value from <strong>0 to 100</strong> (e.g., 92, 87.5, 100)</li>
                                <li><strong>Remarks</strong> - Any additional notes (optional)</li>
                            </ul>
                            <div class="mt-3 p-2 bg-blue-100 rounded border border-blue-200">
                                <p class="font-semibold text-blue-900 mb-1">Important Notes:</p>
                                <ul class="list-disc list-inside space-y-1 text-blue-800">
                                    <li>Import uses the <strong>same calculation logic as manual entry</strong></li>
                                    <li>If a DTR record already exists for a student on a given date, it will be <strong>skipped</strong> (not updated)</li>
                                    <li><strong>Total Hours</strong> and <strong>Overtime Hours</strong> are automatically calculated by the system</li>
                                    <li><strong>Status</strong> is automatically determined by the system based on the hours worked</li>
                                    <li>Weekly deficit is automatically calculated for each imported record</li>
                                </ul>
                            </div>
                            <p class="mt-2">Download the template CSV file for reference.</p>
                        </div>
                    </div>
                </div>
            </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ url('/admin/student-dtr') }}" class="space-y-4">
            <!-- Search Bar -->
            <div class="mb-4">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Student</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search by student name, email, or school..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                <p class="mt-1 text-xs text-gray-500">Search allows you to find any student by name, email, or school/university name, even if they don't have remaining time needed.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- University/School Filter -->
                <div>
                    <label for="university_id" class="block text-sm font-medium text-gray-700 mb-2">School/University</label>
                    <select name="university_id" id="university_id" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Schools</option>
                        @foreach($universities as $university)
                            <option value="{{ $university->id }}" {{ request('university_id') == $university->id ? 'selected' : '' }}>
                                {{ $university->name }}@if($university->location) ({{ $university->location }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Student Filter -->
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Student</label>
                    <select name="student_id" id="student_id" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All students with DTR</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
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
                <a href="{{ url('/admin/student-dtr') }}" class="inline-flex items-center px-4 sm:px-6 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- DTR Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Time Records</h2>
                    <p class="text-sm text-gray-600 mt-1">Total records: {{ $totalRecords }}</p>
                </div>
                <div class="flex items-center gap-3" id="bulk-actions" style="display: none;">
                    <span class="text-sm text-gray-700" id="selected-count">0 selected</span>
                    <button type="button" onclick="openBulkEditModal()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Selected
                    </button>
                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                    <button type="button" onclick="bulkDelete()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-red-600 hover:bg-red-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Selected
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="overflow-x-auto" id="dtr-groups-root">
            @forelse($groupedDtrs as $monthKey => $month)
                <div class="border-b border-gray-200" data-month-group>
                    <button type="button"
                            class="w-full flex items-center justify-between px-6 py-3 bg-gray-100 hover:bg-gray-200 transition text-left"
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
                                        class="w-full flex items-center justify-between px-6 py-2 bg-gray-50 hover:bg-gray-100 transition text-left"
                                        data-toggle="week">
                                    <h4 class="text-sm font-semibold text-gray-800">{{ $week['label'] }}</h4>
                                    <span class="ml-3 inline-flex items-center justify-center rounded-full bg-white/70 text-gray-700 text-xs px-2 py-0.5">
                                        <span class="mr-1" data-week-chevron>+</span>
                                        Toggle
                                    </span>
                                </button>

                                <div class="hidden" data-week-content style="display: none;">
                            @foreach($week['students'] as $studentGroup)
                                <div class="px-6 py-2 bg-white">
                                    <div class="flex items-center mb-2">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-indigo-600 font-medium text-xs">
                                                    {{ substr($studentGroup['student']->name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ $studentGroup['student']->name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $studentGroup['student']->email }}
                                            </div>
                                        </div>
                                    </div>

                                    <table class="min-w-full divide-y divide-gray-200 mb-4">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
                                                           onchange="toggleAllRows(this, '{{ $weekKey }}-{{ $studentGroup['student']->id }}')">
                                                </th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Worked Hours</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added Time From Note</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overtime</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity %</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($studentGroup['records'] as $dtr)
                                                <tr class="hover:bg-gray-50" data-dtr-id="{{ $dtr->id }}">
                                                    <td class="px-3 py-2 whitespace-nowrap">
                                                        <input type="checkbox" name="dtr_ids[]" value="{{ $dtr->id }}" 
                                                               class="dtr-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                               data-week-key="{{ $weekKey }}-{{ $studentGroup['student']->id }}"
                                                               onchange="updateBulkActions()">
                                                    </td>
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
                                                        <div class="text-sm font-medium text-indigo-700">
                                                            {{ $dtr->activity_percentage !== null ? number_format((float) $dtr->activity_percentage, 2) . '%' : '-' }}
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
                                                            <a href="{{ url('/admin/student-dtr/' . $dtr->id . '/edit') }}"
                                                               class="inline-flex items-center px-2.5 py-1.5 border border-indigo-200 text-xs font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                                                                Edit
                                                            </a>
                                                            @if(auth()->check() && auth()->user()->isSuperAdmin())
                                                                <form action="{{ url('/admin/student-dtr/' . $dtr->id) }}"
                                                                      method="POST"
                                                                      onsubmit="return confirm('Are you sure you want to delete this student DTR record? This action cannot be undone.');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                            class="inline-flex items-center px-2.5 py-1.5 border border-red-200 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gradient-to-r from-indigo-50 to-purple-50 border-t-2 border-indigo-300">
                                            @php
                                                // Calculate weekly total for this student
                                                $weeklyTotalHours = 0;
                                                $weeklyOvertimeHours = 0;
                                                foreach ($studentGroup['records'] as $dtr) {
                                                    $weeklyTotalHours += ($dtr->total_hours ?? 0);
                                                    $weeklyOvertimeHours += ($dtr->overtime_hours ?? 0);
                                                }
                                                $weeklyTotalMinutes = (int) round($weeklyTotalHours * 60);
                                                $weeklyTotalH = intdiv($weeklyTotalMinutes, 60);
                                                $weeklyTotalM = $weeklyTotalMinutes % 60;
                                                $weeklyTotalFormatted = sprintf('%02d:%02d', $weeklyTotalH, $weeklyTotalM);

                                                // Calculate total weekly overtime
                                                $weeklyOvertimeMinutes = (int) round($weeklyOvertimeHours * 60);
                                                $weeklyOvertimeH = intdiv($weeklyOvertimeMinutes, 60);
                                                $weeklyOvertimeM = $weeklyOvertimeMinutes % 60;
                                                $weeklyOvertimeFormatted = sprintf('%02d:%02d', $weeklyOvertimeH, $weeklyOvertimeM);

                                                // Calculate deficit: Weekly Total Base (40:00) - Weekly Total
                                                $weeklyBaseMinutes = 40 * 60; // 40:00 = 2400 minutes
                                                $deficitMinutes = max(0, $weeklyBaseMinutes - $weeklyTotalMinutes);
                                                $deficitH = intdiv($deficitMinutes, 60);
                                                $deficitM = $deficitMinutes % 60;
                                                $deficitFormatted = sprintf('%02d:%02d', $deficitH, $deficitM);
                                            @endphp
                                            <tr>
                                                <td class="px-3 py-3 whitespace-nowrap">
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                        </svg>
                                                        <span class="text-sm font-bold text-indigo-900">
                                                            Weekly Summary
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap" colspan="2">
                                                    <div class="flex items-center space-x-3 flex-wrap gap-y-2">
                                                        <div>
                                                            <div class="text-xs text-gray-600 font-medium mb-1">Weekly Total</div>
                                                            <div class="text-base font-bold text-indigo-900 bg-white px-3 py-1 rounded-lg border border-indigo-200 inline-block">
                                                                {{ $weeklyTotalMinutes > 0 ? $weeklyTotalFormatted : '00:00' }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 font-medium mb-1">Base</div>
                                                            <div class="text-sm font-semibold text-gray-700 bg-white px-2 py-1 rounded border border-gray-200 inline-block">
                                                                40:00
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 font-medium mb-1">Overtime</div>
                                                            <div class="text-sm font-bold {{ $weeklyOvertimeMinutes > 0 ? 'text-orange-600' : 'text-gray-600' }} bg-white px-2 py-1 rounded border {{ $weeklyOvertimeMinutes > 0 ? 'border-orange-200' : 'border-gray-200' }} inline-block">
                                                                {{ $weeklyOvertimeMinutes > 0 ? $weeklyOvertimeFormatted : '00:00' }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-600 font-medium mb-1">Deficit</div>
                                                            <div class="text-sm font-bold {{ $deficitMinutes > 0 ? 'text-red-600' : 'text-green-600' }} bg-white px-2 py-1 rounded border {{ $deficitMinutes > 0 ? 'border-red-200' : 'border-green-200' }} inline-block">
                                                                {{ $deficitFormatted }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3 whitespace-nowrap" colspan="4">
                                                    <div class="text-xs text-indigo-600 font-medium">
                                                        {{ $week['label'] }} • {{ $deficitMinutes > 0 ? 'Deficit: ' . $deficitFormatted . ' hours' : 'No deficit' }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </tfoot>
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
                    <p class="mt-1 text-sm text-gray-500">No student time records match your filters.</p>
                </div>
            @endforelse
        </div>
</div>

<script>
    // Toggle Import Section
    function toggleImportSection() {
        const section = document.getElementById('import-section');
        const chevron = document.getElementById('import-chevron');
        const button = event.currentTarget;

        if (section && chevron) {
            const isHidden = section.classList.contains('hidden');

            if (isHidden) {
                section.classList.remove('hidden');
                chevron.classList.add('rotate-180');
                button.setAttribute('aria-expanded', 'true');
            } else {
                section.classList.add('hidden');
                chevron.classList.remove('rotate-180');
                button.setAttribute('aria-expanded', 'false');
            }
        }
    }

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
        root.querySelectorAll('[data-month-group]').forEach(function (monthGroup) {
            const button = monthGroup.querySelector('[data-toggle="month"]');
            const content = monthGroup.querySelector('[data-month-content]');
            const chevron = monthGroup.querySelector('[data-month-chevron]');

            if (!button || !content) {
                console.warn('Month toggle elements not found', { button: !!button, content: !!content });
                return;
            }

            // Collapse all months by default
            content.classList.add('hidden');
            content.style.display = 'none';
            if (chevron) chevron.textContent = '+';

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

            // Collapse all weeks by default
            content.classList.add('hidden');
            content.style.display = 'none';
            if (chevron) chevron.textContent = '+';

            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleVisibility(content, chevron);
            });
        });

        // Auto-submit form when university changes to filter students
        const universitySelect = document.getElementById('university_id');
        if (universitySelect) {
            universitySelect.addEventListener('change', function() {
                // Reset student selection when university changes
                const studentSelect = document.getElementById('student_id');
                if (studentSelect) {
                    studentSelect.value = '';
                }
                // Optionally auto-submit the form
                // this.form.submit();
            });
        }
    });

    // Bulk operations
    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.dtr-checkbox:checked');
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');
        
        if (checkboxes.length > 0) {
            bulkActions.style.display = 'flex';
            selectedCount.textContent = checkboxes.length + ' selected';
        } else {
            bulkActions.style.display = 'none';
        }
    }

    function toggleAllRows(checkbox, weekKey) {
        const checkboxes = document.querySelectorAll(`.dtr-checkbox[data-week-key="${weekKey}"]`);
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateBulkActions();
    }

    function openBulkEditModal() {
        const checkboxes = document.querySelectorAll('.dtr-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one record to edit.');
            return;
        }
        
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        const selectedRows = Array.from(checkboxes).map(cb => {
            const row = cb.closest('tr');
            const dateCell = row.querySelector('td:nth-child(2)');
            const workedCell = row.querySelector('td:nth-child(3)');
            const addedCell = row.querySelector('td:nth-child(4)');
            const totalCell = row.querySelector('td:nth-child(5)');
            const statusCell = row.querySelector('td:nth-child(7)');
            const remarksCell = row.querySelector('td:nth-child(8)');
            
            // Extract current values
            const workedText = workedCell ? workedCell.textContent.trim() : '00:00';
            const addedText = addedCell ? addedCell.textContent.trim() : '00:00';
            const totalText = totalCell ? totalCell.textContent.trim() : '00:00';
            const statusBadge = statusCell ? statusCell.querySelector('span') : null;
            const statusText = statusBadge ? statusBadge.textContent.trim() : '';
            const remarksText = remarksCell ? remarksCell.querySelector('.text-sm')?.textContent.trim() || '-' : '-';
            
            // Get the actual status from the row's data attribute or parse from badge
            // The status is stored in the database, but we need to extract it from the display
            // For now, we'll default to 'present' and let user change it
            let statusValue = 'present';
            if (statusText.includes('Travel')) statusValue = 'travel';
            else if (statusText.includes('Under Time')) statusValue = 'present';
            else if (statusText.includes('Completed')) statusValue = 'present';
            
            // Use total hours as the default for total_hours field
            return {
                id: cb.value,
                date: dateCell ? dateCell.textContent.trim() : '',
                worked: workedText,
                added: addedText,
                total: totalText,
                status: statusValue,
                remarks: remarksText !== '-' ? remarksText : ''
            };
        });
        
        // Populate the form with individual records
        const recordsContainer = document.getElementById('bulk-edit-records');
        recordsContainer.innerHTML = '';
        
        selectedRows.forEach((row, index) => {
            const recordDiv = document.createElement('div');
            recordDiv.className = 'border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50';
            recordDiv.innerHTML = `
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-900">${row.date}</h4>
                    <span class="text-xs text-gray-500">Record #${index + 1}</span>
                </div>
                <input type="hidden" name="dtr_ids[]" value="${row.id}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Worked Hours (HH:MM)</label>
                        <input type="text" name="worked_hours[${row.id}]" value="${row.worked}" 
                               pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$"
                               placeholder="08:00" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                               onchange="calculateTotalHours(this, '${row.id}')">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Added Time (HH:MM)</label>
                        <input type="text" name="added_time_from_note[${row.id}]" value="${row.added}" 
                               pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$"
                               placeholder="00:00" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                               onchange="calculateTotalHours(this, '${row.id}')">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Total Hours (HH:MM) <span class="text-gray-500">(Auto-calculated)</span></label>
                        <input type="text" name="total_hours[${row.id}]" value="${row.total}" 
                               pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$"
                               placeholder="08:00" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm bg-gray-50"
                               readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select name="status[${row.id}]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option value="">Keep current</option>
                            <option value="present" ${row.status === 'present' ? 'selected' : ''}>Present</option>
                            <option value="absent" ${row.status === 'absent' ? 'selected' : ''}>Absent</option>
                            <option value="late" ${row.status === 'late' ? 'selected' : ''}>Late</option>
                            <option value="half_day" ${row.status === 'half_day' ? 'selected' : ''}>Half Day</option>
                            <option value="on_leave" ${row.status === 'on_leave' ? 'selected' : ''}>On Leave</option>
                            <option value="travel" ${row.status === 'travel' ? 'selected' : ''}>Travel</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Remarks</label>
                        <input type="text" name="remarks[${row.id}]" value="${row.remarks}" 
                               placeholder="Leave empty to keep current" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    </div>
                </div>
            `;
            recordsContainer.appendChild(recordDiv);
        });
        
        document.getElementById('bulk-edit-modal').classList.remove('hidden');
    }

    function closeBulkEditModal() {
        document.getElementById('bulk-edit-modal').classList.add('hidden');
    }

    function calculateTotalHours(input, recordId) {
        const workedInput = document.querySelector(`input[name="worked_hours[${recordId}]"]`);
        const addedInput = document.querySelector(`input[name="added_time_from_note[${recordId}]"]`);
        const totalInput = document.querySelector(`input[name="total_hours[${recordId}]"]`);
        
        if (!workedInput || !addedInput || !totalInput) return;
        
        const workedValue = workedInput.value.trim();
        const addedValue = addedInput.value.trim();
        
        if (!workedValue || !addedValue) {
            totalInput.value = '';
            return;
        }
        
        // Parse HH:MM format
        const parseTime = (timeStr) => {
            const parts = timeStr.split(':');
            if (parts.length !== 2) return 0;
            const hours = parseInt(parts[0]) || 0;
            const minutes = parseInt(parts[1]) || 0;
            return hours + (minutes / 60);
        };
        
        const workedDecimal = parseTime(workedValue);
        const addedDecimal = parseTime(addedValue);
        const totalDecimal = workedDecimal + addedDecimal;
        
        // Convert back to HH:MM
        const totalHours = Math.floor(totalDecimal);
        const totalMinutes = Math.round((totalDecimal - totalHours) * 60);
        const formattedTotal = `${String(totalHours).padStart(2, '0')}:${String(totalMinutes).padStart(2, '0')}`;
        
        totalInput.value = formattedTotal;
    }

    function bulkDelete() {
        const checkboxes = document.querySelectorAll('.dtr-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one record to delete.');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete ${checkboxes.length} selected record(s)? This action cannot be undone.`)) {
            return;
        }
        
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url("/admin/student-dtr/bulk-delete") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'dtr_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
</script>

<!-- Bulk Edit Modal -->
<div id="bulk-edit-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white my-10 max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Bulk Edit Time Records</h3>
                <button type="button" onclick="closeBulkEditModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ url('/admin/student-dtr/bulk-update') }}" method="POST">
                @csrf
                <div id="bulk-edit-records" class="space-y-4">
                    <!-- Records will be dynamically inserted here -->
                </div>
                
                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeBulkEditModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Update Selected Records
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

