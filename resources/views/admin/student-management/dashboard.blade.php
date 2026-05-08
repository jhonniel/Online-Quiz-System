@extends('layouts.admin')

@section('title', 'Student Time Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header & Summary -->
    <div class="px-4 py-6 sm:px-6 sm:py-8 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 shadow-lg rounded-2xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-medium text-indigo-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span>
                    Student Training Overview
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Student Time Dashboard
                </h1>
                <p class="mt-1 text-sm sm:text-base text-indigo-100/90 max-w-2xl">
                    Monitor student training progress with a ranked view of their
                    <span class="font-semibold">Remaining Time Needed</span>
                    based on required hours versus total time recorded in DTR.
                </p>
            </div>

        </div>
    </div>

    <!-- Ranking Table -->
    <div class="px-4 sm:px-6">
        <div class="bg-white shadow-md rounded-2xl overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Student Time Ranking</h2>
                    <p class="text-xs sm:text-sm text-gray-500">
                        Default sort is <span class="font-medium">Remaining Time Needed (High to Low)</span>.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <form method="GET" action="{{ url('/admin/student-management/dashboard') }}" class="w-full sm:w-auto flex flex-wrap items-end gap-2 sm:gap-2.5">
                        <div class="flex flex-col gap-1 min-w-[210px]">
                            <label for="sort_by" class="text-[11px] sm:text-xs text-gray-600 font-medium">Sort by</label>
                            <select id="sort_by" name="sort_by" class="h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-xs sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="school" {{ ($sortBy ?? 'remaining_hours') === 'school' ? 'selected' : '' }}>School</option>
                            <option value="required_hours" {{ ($sortBy ?? 'remaining_hours') === 'required_hours' ? 'selected' : '' }}>Time Needed</option>
                            <option value="total_hours" {{ ($sortBy ?? 'remaining_hours') === 'total_hours' ? 'selected' : '' }}>Total Time from DTR</option>
                            <option value="remaining_hours" {{ ($sortBy ?? 'remaining_hours') === 'remaining_hours' ? 'selected' : '' }}>Remaining Time Needed</option>
                            <option value="approved_leave_requests" {{ ($sortBy ?? 'remaining_hours') === 'approved_leave_requests' ? 'selected' : '' }}>Approved Absent Days</option>
                            <option value="estimated_end_date" {{ ($sortBy ?? 'remaining_hours') === 'estimated_end_date' ? 'selected' : '' }}>Estimated End Date</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1 min-w-[170px]">
                            <label for="sort_dir" class="text-[11px] sm:text-xs text-gray-600 font-medium">Order</label>
                            <select id="sort_dir" name="sort_dir" class="h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-xs sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="desc" {{ ($sortDir ?? 'desc') === 'desc' ? 'selected' : '' }}>High to Lowest</option>
                            <option value="asc" {{ ($sortDir ?? 'desc') === 'asc' ? 'selected' : '' }}>Lowest to High</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1 min-w-[210px]">
                            <label for="ranking_school" class="text-[11px] sm:text-xs text-gray-600 font-medium">School</label>
                            <select id="ranking_school" name="ranking_school" class="h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-xs sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All schools in ranking</option>
                                @foreach(($rankingSchoolOptions ?? collect()) as $schoolOption)
                                    <option value="{{ $schoolOption['id'] }}" {{ ($rankingSchool ?? '') === $schoolOption['id'] ? 'selected' : '' }}>
                                        {{ $schoolOption['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="h-10 inline-flex items-center justify-center px-4 rounded-md bg-indigo-600 text-white text-xs sm:text-sm font-medium hover:bg-indigo-700">
                            Apply
                        </button>
                    </form>

                    <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-gray-50 border border-gray-200 text-[11px] sm:text-xs text-gray-600">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                        Showing students who still need remaining time.
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Rank</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">School / University</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Time Needed (Required)</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Time from DTR</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Remaining Time Needed</th>
                            @if($showApprovedLeaveRequests ?? false)
                                <th class="px-3 sm:px-6 py-3 text-center text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved Absent Days</th>
                            @endif
                            <th class="px-3 sm:px-6 py-3 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Estimated End Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($students as $index => $row)
                            @php
                                $remaining = $row['remaining_hours'] ?? 0;
                                $remainingClass = $remaining <= 0 ? 'text-emerald-600' : 'text-rose-600';
                                $badgeClass = $remaining <= 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                    <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-[11px] font-medium text-gray-700">
                                        #{{ $index + 1 }}
                                        @if($remaining > 0 && isset($row['arrow_direction']))
                                            {{-- Only show arrow if student has remaining time needed --}}
                                            @if($row['arrow_direction'] === 'down')
                                                {{-- Arrow down (green) - rank declined --}}
                                                <svg class="w-3 h-3 ml-1 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 20l8-8h-5V4h-6v8H4l8 8z"/>
                                                </svg>
                                            @else
                                                {{-- Arrow up (red) - rank improved, same, or no history --}}
                                                <svg class="w-3 h-3 ml-1 text-rose-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 4l-8 8h5v8h6v-8h5l-8-8z"/>
                                                </svg>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                    <div class="font-semibold truncate max-w-[160px] sm:max-w-xs">
                                        {{ $row['student']->name }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 truncate max-w-[160px] sm:max-w-xs">
                                        {{ $row['student']->email }}
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-700">
                                    {{ optional($row['student']->university)->name ?? '—' }}
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-right text-gray-700">
                                    {{ $row['required_hours_formatted'] }} hrs
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-right text-gray-700">
                                    {{ $row['total_hours_formatted'] }} hrs
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $badgeClass }}">
                                        <span class="{{ $remainingClass }}">
                                            {{ $row['remaining_hours_formatted'] }} hrs
                                        </span>
                                    </span>
                                </td>
                                @if($showApprovedLeaveRequests ?? false)
                                    <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-center text-gray-700">
                                        @php
                                            $approvedAbsentDaysExceeded = (bool) ($row['approved_absent_days_exceeded'] ?? false);
                                            $approvedAbsentDaysClass = $approvedAbsentDaysExceeded
                                                ? 'border-red-200 bg-red-50 text-red-700'
                                                : 'border-indigo-200 bg-indigo-50 text-indigo-700';
                                        @endphp
                                        <span class="inline-flex items-center justify-center min-w-[2.25rem] px-2.5 py-1 rounded-full border text-[11px] font-semibold {{ $approvedAbsentDaysClass }}">
                                            {{ (int) ($row['approved_leave_requests'] ?? 0) }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-700">
                                    @if(($row['remaining_hours'] ?? 0) > 0 && !empty($row['estimated_end_date_formatted']))
                                        {{ $row['estimated_end_date_formatted'] }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($showApprovedLeaveRequests ?? false) ? 8 : 7 }}" class="px-3 sm:px-6 py-8 text-center text-sm text-gray-500">
                                    No student records found. Once students have required hours and DTR entries, they will appear here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


