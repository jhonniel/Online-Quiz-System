@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Leave Calendar</h1>
                    <p class="text-indigo-100 mt-1">View employee leave requests on a monthly calendar.</p>
                </div>
            </div>
            <a href="{{ route('admin.leave-requests.index') }}"
               class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Leave Requests
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-4 items-start">
        <!-- Employee Filter Sidebar -->
        <div class="xl:col-span-1 space-y-4">
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-4">
                <h2 class="text-sm font-bold text-gray-900 mb-2">Employees</h2>
                <p class="text-xs text-gray-500 mb-3">
                    Tap a name to focus on that employee's leave, or choose "All Employees" to view everyone.
                </p>
                <div class="space-y-1 max-h-[calc(100vh-20rem)] overflow-y-auto text-sm -mx-1">
                    <a href="{{ route('admin.leave-requests.calendar', ['month' => $currentMonth->format('Y-m')]) }}"
                       class="flex items-center justify-between px-3 py-1.5 rounded-md mx-1 transition-colors duration-150 {{ !$selectedEmployeeId ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span>All Employees</span>
                    </a>
                    @foreach($employees as $employee)
                        <a href="{{ route('admin.leave-requests.calendar', ['month' => $currentMonth->format('Y-m'), 'employee' => $employee->id]) }}"
                           class="flex items-center justify-between px-3 py-1.5 rounded-md mx-1 transition-colors duration-150 {{ $selectedEmployeeId == $employee->id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="truncate">{{ $employee->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Month Navigation + Calendar -->
        <div class="xl:col-span-3 space-y-4">
            <!-- Month Navigation -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.leave-requests.calendar', ['month' => $prevMonth, 'employee' => $selectedEmployeeId]) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Previous
                    </a>
                    <span class="text-lg font-semibold text-gray-900">
                        {{ $currentMonth->format('F Y') }}
                    </span>
                    <a href="{{ route('admin.leave-requests.calendar', ['month' => $nextMonth, 'employee' => $selectedEmployeeId]) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
                        Next
                        <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="text-xs text-gray-500 md:text-right">
                    Showing leave requests that overlap this month
                    @if($selectedEmployeeId)
                        for <span class="font-semibold">
                            {{ optional($employees->firstWhere('id', $selectedEmployeeId))->name ?? 'Selected Employee' }}
                        </span>
                    @else
                        for <span class="font-semibold">all employees</span>
                    @endif
                    .
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden min-h-[calc(100vh-16rem)] flex flex-col">
        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $weekday)
                <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">
                    {{ $weekday }}
                </div>
            @endforeach
        </div>
        <div class="divide-y divide-gray-200 flex-1 grid grid-rows-6">
            @foreach($weeks as $week)
                <div class="grid grid-cols-7 flex-1">
                    @foreach($week as $day)
                        @php
                            $isCurrentMonth = $day['date']->format('Y-m') === $currentMonth->format('Y-m');
                            // Compare using Manila timezone
                            $isToday = $day['date']->isSameDay(\Carbon\Carbon::now('Asia/Manila'));
                        @endphp
                        <div class="border-r border-b last:border-b-0 border-gray-200 px-1.5 sm:px-2 py-2 text-xs flex flex-col h-full
                                    {{ $isCurrentMonth ? ($isToday ? 'bg-emerald-50' : 'bg-white') : 'bg-gray-50' }}">
                            <div class="flex items-center justify-between mb-1 flex-shrink-0">
                                <span class="font-semibold {{ $isCurrentMonth ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $day['date']->format('j') }}
                                </span>
                                @if($isToday)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-semibold">
                                        Today
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-1 flex-1 overflow-y-auto min-h-0">
                                @php
                                    $displayed = 0;
                                    $total = count($day['requests']);
                                @endphp
                                @foreach($day['requests'] as $entry)
                                    @if($displayed >= 3)
                                        @break
                                    @endif
                                    @php
                                        $displayed++;
                                        $statusClass = match($entry['status']) {
                                            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'rejected' => 'bg-red-50 text-red-700 border-red-100',
                                            default => 'bg-gray-50 text-gray-700 border-gray-100',
                                        };
                                    @endphp
                                    <a href="{{ route('admin.leave-requests.show', $entry['id']) }}"
                                       class="block border {{ $statusClass }} rounded-md px-1.5 py-0.5 text-[11px] hover:border-indigo-300 hover:bg-indigo-50/70">
                                        <div class="font-semibold truncate">
                                            {{ $entry['employee']->name }}
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="truncate">{{ $entry['type_label'] }}</span>
                                            <span class="ml-1 text-[10px] capitalize">{{ $entry['status'] }}</span>
                                        </div>
                                    </a>
                                @endforeach

                                @if($total > $displayed)
                                    <div class="text-[11px] text-gray-500">
                                        +{{ $total - $displayed }} more…
                                    </div>
                                @endif

                                @if($total === 0)
                                    <div class="text-[11px] text-gray-300 italic">
                                        No leave
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
            </div>
        </div>
    </div>
</div>
@endsection


