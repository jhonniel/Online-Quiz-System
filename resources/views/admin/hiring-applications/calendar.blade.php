@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-2 sm:p-3">
                    <svg class="h-7 w-7 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Calendar Interview</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">View scheduled interviews on a monthly calendar.</p>
                </div>
            </div>
            <a href="{{ route('admin.hiring-applications.index') }}"
               class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-xs sm:text-sm bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200">
                <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="hidden sm:inline">Back to Applications</span>
                <span class="sm:hidden">Back</span>
            </a>
        </div>
    </div>

    <!-- Calendar Navigation -->
    <div class="bg-white rounded-2xl shadow border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.hiring-applications.calendar') }}?month={{ $prevMonth->format('Y-m') }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Previous
            </a>
            <h2 class="text-lg font-bold text-gray-900">{{ $currentMonth->format('F Y') }}</h2>
            <a href="{{ route('admin.hiring-applications.calendar') }}?month={{ $nextMonth->format('Y-m') }}"
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Next
                <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mon</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tue</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wed</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thu</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fri</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sat</th>
                        <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sun</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($weeks as $week)
                        <tr>
                            @foreach($week as $day)
                                @php
                                    $isCurrentMonth = $day['date']->format('Y-m') === $currentMonth->format('Y-m');
                                    $isToday = $day['date']->isToday();
                                    $hasInterviews = count($day['interviews']) > 0;
                                @endphp
                                <td class="px-2 py-3 align-top border border-gray-200 {{ $isCurrentMonth ? '' : 'bg-gray-50' }} {{ $isToday ? 'bg-blue-50 border-blue-300' : '' }}"
                                    style="min-width: 120px; height: 120px; vertical-align: top;">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium {{ $isCurrentMonth ? 'text-gray-900' : 'text-gray-400' }} {{ $isToday ? 'text-blue-700 font-bold' : '' }}">
                                            {{ $day['date']->format('j') }}
                                        </span>
                                        @if($hasInterviews)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                {{ count($day['interviews']) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="space-y-1">
                                        @foreach($day['interviews'] as $interview)
                                            @php
                                                $bgColor = $interview['type'] === 'accepted' ? 'bg-green-100 hover:bg-green-200 text-green-800' : 'bg-indigo-100 hover:bg-indigo-200 text-indigo-800';
                                                $label = $interview['type'] === 'accepted' ? 'Accepted' : 'Interview';
                                            @endphp
                                            <a href="{{ route('admin.hiring-applications.show', $interview['id']) }}"
                                               class="block px-2 py-1 text-xs rounded {{ $bgColor }} transition-colors"
                                               title="{{ $interview['applicant_name'] }} - {{ $interview['position'] }} ({{ $interview['interview_time'] }})">
                                                <div class="font-semibold truncate">{{ $interview['interview_time'] }} - {{ $label }}</div>
                                                <div class="truncate">{{ $interview['applicant_name'] }}</div>
                                                <div class="truncate {{ $interview['type'] === 'accepted' ? 'text-green-700' : 'text-indigo-600' }}">{{ $interview['position'] }}</div>
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary -->
    @php
        $scheduledCount = $applications->where('status', 'interview_scheduled')->count();
        $acceptedCount = $applications->where('status', 'accepted')->count();
        $totalCount = $applications->count();
    @endphp
    @if($totalCount > 0)
        <div class="bg-white rounded-2xl shadow border border-gray-200 p-4">
            <h3 class="text-lg font-bold text-gray-900 mb-3">Summary for {{ $currentMonth->format('F Y') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-indigo-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-indigo-600">Scheduled Interviews</div>
                    <div class="text-2xl font-bold text-indigo-900">{{ $scheduledCount }}</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-green-600">Accepted Applicants</div>
                    <div class="text-2xl font-bold text-green-900">{{ $acceptedCount }}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-600">Total</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $totalCount }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow border border-gray-200 p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No activities scheduled</h3>
            <p class="mt-2 text-sm text-gray-500">There are no scheduled interviews or accepted applicants for {{ $currentMonth->format('F Y') }}.</p>
        </div>
    @endif
</div>
@endsection
