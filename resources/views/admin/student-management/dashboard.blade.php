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

            @php
                $totalStudents = $students->count();
                $totalRequired = $students->sum('required_hours');
                $totalDtr = $students->sum('total_hours');
                $avgCompletion = $totalRequired > 0 ? ($totalDtr / max($totalRequired, 0.01)) * 100 : 0;
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 w-full md:w-auto">
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl px-4 py-3 sm:px-5 sm:py-4 text-indigo-50">
                    <div class="text-[11px] sm:text-xs uppercase tracking-wide text-indigo-100/80">Total Students</div>
                    <div class="mt-1 text-xl sm:text-2xl font-bold">{{ $totalStudents }}</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl px-4 py-3 sm:px-5 sm:py-4 text-indigo-50">
                    <div class="text-[11px] sm:text-xs uppercase tracking-wide text-indigo-100/80">With Remaining Time</div>
                    <div class="mt-1 text-xl sm:text-2xl font-bold text-rose-200">{{ $studentsWithRemainingTime ?? 0 }}</div>
                    <div class="mt-1 text-[10px] text-indigo-100/70">Students still need to complete hours</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl px-4 py-3 sm:px-5 sm:py-4 text-indigo-50">
                    <div class="text-[11px] sm:text-xs uppercase tracking-wide text-indigo-100/80">Total Required Time</div>
                    <div class="mt-1 text-xl sm:text-2xl font-bold">
                        {{ sprintf('%02d:%02d', intdiv((int) round($totalRequired * 60), 60), (int) round($totalRequired * 60) % 60) }}
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl px-4 py-3 sm:px-5 sm:py-4 text-indigo-50">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] sm:text-xs uppercase tracking-wide text-indigo-100/80">Avg. Completion</span>
                    </div>
                    <div class="mt-1 flex items-end space-x-2">
                        <span class="text-xl sm:text-2xl font-bold">{{ number_format($avgCompletion, 1) }}%</span>
                    </div>
                    <div class="mt-2 w-full h-1.5 rounded-full bg-white/15 overflow-hidden">
                        <div class="h-full bg-emerald-400 rounded-full" style="width: {{ max(0, min(100, $avgCompletion)) }}%;"></div>
                    </div>
                </div>
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
                        Sorted by <span class="font-medium">Remaining Time Needed</span> in descending order.
                    </p>
                </div>
                <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-gray-50 border border-gray-200 text-[11px] sm:text-xs text-gray-600">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                    Negative remaining time means the student has already exceeded the required hours.
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 sm:px-6 py-8 text-center text-sm text-gray-500">
                                    No student records found. Once students have required hours and DTR entries, they will appear here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quiz Performance Ranking Section -->
    @if(isset($quizRankings) && $quizRankings->isNotEmpty())
    <div class="px-4 sm:px-6">
        <div class="bg-white shadow-md rounded-2xl overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900">Quiz Performance Ranking</h2>
                    <p class="text-xs sm:text-sm text-gray-500">
                        Students ranked by total quiz scores from all completed attempts.
                    </p>
                </div>
                <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-200 text-[11px] sm:text-xs text-indigo-600">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                    Based on quiz attempt history scores
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Rank</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">School / University</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Score</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Attempts</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Average Score</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($quizRankings as $ranking)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                                    <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-[11px] font-medium text-gray-700">
                                        #{{ $ranking['rank'] }}
                                        @if(isset($ranking['arrow_direction']))
                                            @if($ranking['arrow_direction'] === 'up')
                                                <span class="ml-1 text-emerald-600">⬆️</span>
                                            @elseif($ranking['arrow_direction'] === 'down')
                                                <span class="ml-1 text-rose-600">⬇️</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                    <div class="font-semibold truncate max-w-[160px] sm:max-w-xs">
                                        {{ $ranking['student']->name }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 truncate max-w-[160px] sm:max-w-xs">
                                        {{ $ranking['student']->email }}
                                    </div>
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-700">
                                    {{ optional($ranking['university'])->name ?? '—' }}
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-right text-gray-900 font-semibold">
                                    {{ number_format($ranking['total_score'], 0) }}
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-right text-gray-700">
                                    {{ $ranking['total_attempts'] }}
                                </td>
                                <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs sm:text-sm text-right text-gray-700">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-medium">
                                        {{ number_format($ranking['average_score'], 2) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection


