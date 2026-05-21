@extends('layouts.admin')

@push('styles')
<style>
.scrollbar-thin {
    scrollbar-width: thin;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.scrollbar-thin:hover::-webkit-scrollbar-thumb {
    background: #94a3b8;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Student Performance Analytics
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Track overall student performance across all quizzes. Scores use attempt history and include manual grading.
            </p>
        </div>
    </div>

    <!-- Student Performance Statistics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Students</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $studentStats['total_students'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Students</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $studentStats['active_students'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Avg Score per Attempt</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $studentStats['average_per_attempt'] ?? 0 }} <span class="text-sm font-normal text-gray-500">pts</span></dd>
                            @if(($studentStats['total_quiz_attempts'] ?? 0) > 0 && ($studentStats['average_per_attempt'] ?? 0) == 0)
                                <dd class="text-xs text-amber-600 mt-0.5">{{ $studentStats['total_quiz_attempts'] }} attempt(s); grading may be pending</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Best Single Attempt</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $studentStats['highest_total_score'] }} <span class="text-sm font-normal text-gray-500">pts</span></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Topic-Based Performance Section -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                📚 Performance by Topic
            </h3>
            <p class="text-sm text-gray-500 mb-6">
                Student performance grouped by quiz topics/subjects
            </p>

            @if($topicPerformance->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($topicPerformance as $topicData)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                             onclick="openTopicDetailsModal(@js($topicData['topic']))">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900">{{ $topicData['topic'] }}</h4>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $topicData['total_quizzes'] }} quiz{{ $topicData['total_quizzes'] > 1 ? 'es' : '' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $topicData['unique_students'] }}</div>
                                    <div class="text-sm text-gray-500">Students</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $topicData['average_score'] }}</div>
                                    <div class="text-sm text-gray-500">Avg Score</div>
                                </div>
                            </div>

                            @if($topicData['top_performers']->count() > 0)
                                <div class="space-y-2">
                                    <h5 class="text-sm font-medium text-gray-700">Top Performers:</h5>
                                    @foreach($topicData['top_performers']->take(3) as $index => $performer)
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-medium {{ $index === 0 ? 'bg-yellow-100 text-yellow-800' : ($index === 1 ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800') }}">
                                                    {{ $index + 1 }}
                                                </span>
                                                <span class="ml-2 font-medium">{{ $performer['user']->name }}</span>
                                            </div>
                                            <span class="text-gray-600">{{ $performer['total_score'] }} pts</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No topic data available</h3>
                    <p class="mt-1 text-sm text-gray-500">Topic stats appear after students complete at least one active quiz (topics are optional; attempts without a topic appear as Uncategorized).</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Student Topic Strengths Section -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                🎯 Student Strengths by Topic
            </h3>
            <p class="text-sm text-gray-500 mb-6">
                Students ranked by their strongest topic performance
                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ $studentTopicStrengths->count() }} students
                </span>
            </p>

            @if($studentTopicStrengths->count() > 0)
                <div class="relative">
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strongest Topic</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topics Covered</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">University</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($studentTopicStrengths as $student)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($student['user']->profile_picture)
                                                    <img class="h-10 w-10 rounded-full" src="{{ Storage::url($student['user']->profile_picture) }}" alt="{{ $student['user']->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ substr($student['user']->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $student['user']->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $student['user']->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $student['strongest_topic'] ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $student['strongest_topic_score'] }} avg
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $student['total_topics'] }} topics
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $student['university']->name ?? 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>

                    <!-- Scroll indicator -->
                    @if($studentTopicStrengths->count() > 3)
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-white to-transparent h-8 pointer-events-none"></div>
                        <div class="text-center mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                </svg>
                                Scroll down to see more students ({{ $studentTopicStrengths->count() }} total)
                            </span>
                        </div>
                    @else
                        <div class="text-center mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Showing all {{ $studentTopicStrengths->count() }} students
                            </span>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No topic strengths data</h3>
                    <p class="mt-1 text-sm text-gray-500">Learners need scorable quiz attempts. Assign a topic on each quiz, or they appear under Uncategorized.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Top Performers Section -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                🏆 Top Performers (Overall Score)
            </h3>
            <p class="text-sm text-gray-500 mb-6">
                Students ranked by total points across all quiz attempts (includes manually graded questions)
                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ $topPerformers->count() }} users
                </span>
            </p>

            @if($topPerformers->count() > 0)
                <div class="relative">
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">University</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quizzes Taken</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Score</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topPerformers as $performer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $performer['rank_badge_class'] }}">
                                            {{ $performer['rank_icon'] }} #{{ $performer['rank'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($performer['user']->profile_picture)
                                                    <img class="h-10 w-10 rounded-full" src="{{ Storage::url($performer['user']->profile_picture) }}" alt="{{ $performer['user']->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ substr($performer['user']->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $performer['user']->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $performer['user']->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $performer['university']->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $performer['total_score'] }} points
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $performer['total_attempts'] }} quizzes
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $performer['average_score'] }} avg
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>

                    <!-- Scroll indicator -->
                    @if($topPerformers->count() > 3)
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-white to-transparent h-8 pointer-events-none"></div>
                        <div class="text-center mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                </svg>
                                Scroll down to see more users ({{ $topPerformers->count() }} total)
                            </span>
                        </div>
                    @else
                        <div class="text-center mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Showing all {{ $topPerformers->count() }} users
                            </span>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No quiz attempts yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Students need to complete quizzes to appear in the rankings.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Participation Rate</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $studentStats['participation_rate'] }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Students</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $overallStats['active_users'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Quizzes</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $overallStats['total_quizzes'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Average Score</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $overallStats['average_score'] }} pts</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz Performance Ranking Section -->
    @if(isset($quizRankings) && $quizRankings->isNotEmpty())
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Quiz Performance Ranking</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Students ranked by total quiz scores from all completed attempts.
                    </p>
                </div>
                <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-200 text-xs text-indigo-600">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                    Based on quiz attempt history scores
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rank</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">School / University</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Score</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Attempts</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Average Score</th>
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
                                    {{ number_format($ranking['average_score'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Performers by Quiz -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Top Performers by Quiz</h3>
            <p class="text-sm text-gray-500 mb-6">One row per student per quiz (their best score). Retakes are not listed separately.</p>

            @if($topPerformersByQuiz->count() > 0)
                <div class="space-y-6">
                    @foreach($topPerformersByQuiz as $quizData)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">{{ $quizData['quiz']->title }}</h4>
                                    <p class="text-sm text-gray-500">{{ $quizData['quiz']->description }}</p>
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600">
                                        <span>{{ $quizData['student_count'] }} students</span>
                                        <span>{{ $quizData['total_attempts'] }} attempts</span>
                                        <span>Avg best: {{ number_format($quizData['average_score'], 1) }} / {{ $quizData['max_points'] ?? 0 }} pts</span>
                                        <span>Top: {{ $quizData['highest_score'] }} pts</span>
                                    </div>
                                </div>
                                <button onclick="showQuizDetails({{ $quizData['quiz']->id }})"
                                        class="bg-indigo-600 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-700 transition-colors">
                                    View Details
                                </button>
                            </div>

                            @if($quizData['top_attempts']->count() > 0)
                                <div class="overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">University</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Best Score</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Attempt</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($quizData['top_attempts'] as $index => $attempt)
                                                <tr class="{{ $index < 3 ? 'bg-yellow-50' : '' }}">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        @if($index === 0)
                                                            <span class="text-yellow-600">🥇 1st</span>
                                                        @elseif($index === 1)
                                                            <span class="text-gray-600">🥈 2nd</span>
                                                        @elseif($index === 2)
                                                            <span class="text-orange-600">🥉 3rd</span>
                                                        @else
                                                            #{{ $index + 1 }}
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-8 w-8">
                                                                @if($attempt->user->profile_picture)
                                                                    <img class="h-8 w-8 rounded-full" src="{{ Storage::url($attempt->user->profile_picture) }}" alt="{{ $attempt->user->name }}">
                                                                @else
                                                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                                        <span class="text-sm font-medium text-gray-700">{{ substr($attempt->user->name, 0, 1) }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="ml-3">
                                                                <div class="text-sm font-medium text-gray-900">{{ $attempt->user->name }}</div>
                                                                <div class="text-sm text-gray-500">{{ $attempt->user->email }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $attempt->user->university->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $attempt->score >= $quizData['highest_score'] * 0.9 ? 'bg-green-100 text-green-800' : ($attempt->score >= $quizData['highest_score'] * 0.7 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                            {{ $attempt->score }} pts
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $attempt->completed_at ? \Carbon\Carbon::parse($attempt->completed_at)->format('M j, Y g:i A') : 'N/A' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No attempts yet</h3>
                                    <p class="mt-1 text-sm text-gray-500">This quiz hasn't been taken by any students yet.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No quiz data available</h3>
                    <p class="mt-1 text-sm text-gray-500">Create some quizzes and have students take them to see analytics.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent High Scores -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent High Scores</h3>
            <p class="text-sm text-gray-500 mb-4">Latest activity by quiz — each student appears once per quiz with their best score.</p>

            @if($recentHighScores->count() > 0)
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">University</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentHighScores as $score)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                @if($score['user']->profile_picture)
                                                    <img class="h-8 w-8 rounded-full" src="{{ Storage::url($score['user']->profile_picture) }}" alt="{{ $score['user']->name }}">
                                                @else
                                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ substr($score['user']->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $score['user']->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $score['user']->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $score['quiz']->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $score['percentage'] >= 90 ? 'bg-green-100 text-green-800' : ($score['percentage'] >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $score['attempt']->score }} pts
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $score['percentage'] }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $score['university']->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $score['attempt']->completed_at ? \Carbon\Carbon::parse($score['attempt']->completed_at)->format('M j, Y g:i A') : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No high scores yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Students need to complete quizzes to see high scores here.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Quiz Details Modal -->
<div id="quizDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Quiz Performance Details</h3>
                <button onclick="closeQuizDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="quizDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
    </div>

    <!-- Topic Details Modal -->
    <div id="topicDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTopicName"></h3>
                    <button onclick="closeTopicDetailsModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div id="topicDetailsContent" class="space-y-4">
                    <!-- Content will be loaded here via AJAX -->
                    <p>Loading topic details...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
function analyticsModalSkeletonHtml() {
    return window.AppSkeleton ? AppSkeleton.html('modal') : '<p class="text-sm text-gray-500 py-8 text-center">Loading…</p>';
}

function showQuizDetails(quizId) {
    const modal = document.getElementById('quizDetailsModal');
    const content = document.getElementById('quizDetailsContent');

    content.innerHTML = analyticsModalSkeletonHtml();

    modal.classList.remove('hidden');

    // Fetch quiz details
    fetch(`/admin/analytics/quiz/${quizId}`)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = `
                <div class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-900">${data.quiz.title}</h4>
                        <p class="text-sm text-gray-600 mt-1">${data.quiz.description || 'No description'}</p>
                        <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Students:</span>
                                <span class="ml-1 text-gray-900">${data.student_count ?? 0}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Attempts:</span>
                                <span class="ml-1 text-gray-900">${data.total_attempts}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Avg Best Score:</span>
                                <span class="ml-1 text-gray-900">${data.average_score} pts</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Highest Score:</span>
                                <span class="ml-1 text-gray-900">${data.highest_score} pts</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Completion Rate:</span>
                                <span class="ml-1 text-gray-900">${data.completion_rate}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h5 class="text-md font-medium text-gray-900 mb-3">Score Distribution</h5>
                            <div class="space-y-2">
                                ${Object.entries(data.score_distribution).map(([range, count]) => `
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">${range}</span>
                                        <span class="text-sm font-medium text-gray-900">${count} students</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        <div>
                            <h5 class="text-md font-medium text-gray-900 mb-3">University Breakdown</h5>
                            <div class="space-y-2">
                                ${data.university_breakdown.map(uni => `
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">${uni.university}</span>
                                        <span class="text-sm font-medium text-gray-900">${uni.average_score} pts avg</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>

                    <div>
                        <h5 class="text-md font-medium text-gray-900 mb-3">Top Performers</h5>
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">University</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    ${data.top_performers.map(attempt => `
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                ${attempt.user.name}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${attempt.user.university ? attempt.user.university.name : 'N/A'}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ${attempt.score} pts
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                ${new Date(attempt.completed_at).toLocaleDateString()}
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Error loading quiz details</h3>
                    <p class="mt-1 text-sm text-gray-500">Please try again later.</p>
                </div>
            `;
        });
}

function closeQuizDetailsModal() {
    document.getElementById('quizDetailsModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('quizDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQuizDetailsModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQuizDetailsModal();
        closeTopicDetailsModal();
    }
});

// Topic Details Modal Functions
function openTopicDetailsModal(topic) {
    document.getElementById('topicDetailsModal').classList.remove('hidden');
    document.getElementById('modalTopicName').textContent = topic + ' Performance Details';
    document.getElementById('topicDetailsContent').innerHTML = analyticsModalSkeletonHtml();

    fetch(`/admin/analytics/topic/${encodeURIComponent(topic)}`)
        .then(response => response.json())
        .then(data => {
            let content = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-500">Total Quizzes: <span class="font-medium text-gray-900">${data.total_quizzes}</span></p>
                        <p class="text-sm text-gray-500">Total Attempts: <span class="font-medium text-gray-900">${data.total_attempts}</span></p>
                        <p class="text-sm text-gray-500">Unique Students: <span class="font-medium text-gray-900">${data.unique_students}</span></p>
                        <p class="text-sm text-gray-500">Average Score: <span class="font-medium text-gray-900">${data.average_score.toFixed(2)} pts</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Highest Score: <span class="font-medium text-gray-900">${data.highest_score} pts</span></p>
                        <p class="text-sm text-gray-500">Lowest Score: <span class="font-medium text-gray-900">${data.lowest_score} pts</span></p>
                    </div>
                </div>

                <h4 class="font-medium text-gray-700 mb-2">Quizzes in this Topic:</h4>
                ${data.quizzes.length > 0 ? `
                    <ul class="divide-y divide-gray-200">
                        ${data.quizzes.map(quiz => `
                            <li class="py-2 flex items-center justify-between text-sm text-gray-700">
                                <span>${quiz.title} (${quiz.total_questions} questions, ${quiz.total_points} pts)</span>
                                <span>Avg: ${quiz.average_score.toFixed(2)} pts (${quiz.attempts} attempts)</span>
                            </li>
                        `).join('')}
                    </ul>
                ` : '<p class="text-sm text-gray-500">No quizzes found for this topic.</p>'}

                <h4 class="font-medium text-gray-700 mb-2 mt-4">Top Performers in ${topic}:</h4>
                ${data.top_performers.length > 0 ? `
                    <ul class="divide-y divide-gray-200">
                        ${data.top_performers.map((performer, index) => `
                            <li class="py-2 flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-medium ${index === 0 ? 'bg-yellow-100 text-yellow-800' : (index === 1 ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800')}">
                                        ${index + 1}
                                    </span>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">${performer.user.name}</p>
                                        <p class="text-xs text-gray-500">${performer.university ? performer.university.name : 'N/A'}</p>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-900">
                                    ${performer.total_score} pts (${performer.average_score.toFixed(2)} avg)
                                </div>
                            </li>
                        `).join('')}
                    </ul>
                ` : '<p class="text-sm text-gray-500">No top performers yet for this topic.</p>'}

                <h4 class="font-medium text-gray-700 mb-2 mt-4">University Breakdown:</h4>
                ${data.university_breakdown.length > 0 ? `
                    <ul class="divide-y divide-gray-200">
                        ${data.university_breakdown.map(uni => `
                            <li class="py-2 flex items-center justify-between text-sm text-gray-700">
                                <span>${uni.university_name} (${uni.total_students} students)</span>
                                <span>Avg Score: ${uni.average_score.toFixed(2)} pts</span>
                            </li>
                        `).join('')}
                    </ul>
                ` : '<p class="text-sm text-gray-500">No university data for this topic yet.</p>'}
            `;
            document.getElementById('topicDetailsContent').innerHTML = content;
        })
        .catch(error => {
            console.error('Error fetching topic details:', error);
            document.getElementById('topicDetailsContent').innerHTML = '<p class="text-red-600">Error loading topic details.</p>';
        });
}

function closeTopicDetailsModal() {
    document.getElementById('topicDetailsModal').classList.add('hidden');
}

// Close topic modal when clicking outside
document.getElementById('topicDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTopicDetailsModal();
    }
});
</script>
@endsection
