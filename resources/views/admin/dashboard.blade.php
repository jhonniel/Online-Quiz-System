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

@section('page-title', 'Admin Dashboard')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Dashboard</span>
        </div>
    </li>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Users -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalUsers }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Quizzes -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Quizzes</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalQuizzes }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Users -->
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $activeUsers }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disabled Users -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Disabled Users</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $disabledUsers }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time User Activity -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <!-- Online Users Counter -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Online Users</dt>
                            <dd class="text-lg font-medium text-gray-900" id="online-count">{{ $activityStats['online'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Logins -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Today's Logins</dt>
                            <dd class="text-lg font-medium text-gray-900" id="today-logins">{{ $todayLogins }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Logouts -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Today's Logouts</dt>
                            <dd class="text-lg font-medium text-gray-900" id="today-logouts">{{ $todayLogouts }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Activity Monitoring -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <!-- Currently Online Users -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Currently Online Users</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                        Live
                    </span>
                </div>
                <div class="space-y-3 max-h-64 overflow-y-auto" id="online-users-list">
                    @forelse($onlineUsers as $session)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-600 font-semibold text-sm">
                                        {{ substr($session->user->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $session->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $session->user->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $session->last_activity_at->diffForHumans() }}</p>
                                <p class="text-xs text-gray-400">{{ $session->ip_address }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No users currently online</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent User Activities -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Recent User Activities</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <div class="w-2 h-2 bg-blue-400 rounded-full mr-1 animate-pulse"></div>
                        Live
                    </span>
                </div>
                <div class="space-y-3 max-h-64 overflow-y-auto" id="recent-activities-list">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                @if($activity->activity_type === 'login')
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                @elseif($activity->activity_type === 'logout')
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $activity->user->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                                    @if($activity->action)
                                        - {{ $activity->action }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No recent activities</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <!-- Recent Quizzes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Quizzes</h3>
                <div class="space-y-3">
                    @forelse($recentQuizzes as $quiz)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $quiz->title }}</p>
                                <p class="text-sm text-gray-500">Created by {{ $quiz->creator->name }}</p>
                            </div>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $quiz->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No quizzes created yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Users</h3>
                <div class="space-y-3">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No users registered yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create User
                </a>
                <a href="{{ route('quizzes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Create Quiz
                </a>
                <a href="{{ route('universities.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    Manage Universities
                </a>
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Manage Users
                </a>
                <a href="{{ route('quizzes.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Manage Quizzes
                </a>
                <a href="{{ route('admin.forum.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Forum Management
                </a>
            </div>
        </div>
    </div>

    <!-- Rankings Section -->
    <div class="mt-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Rankings & Analytics</h2>
            <div class="text-sm text-gray-500">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    📊 Live Data
                </span>
            </div>
        </div>

        <!-- Ranking Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-lg p-4 text-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl">🏆</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">Top Student</p>
                        <p class="text-lg font-bold">
                            @if($topStudents->count() > 0)
                                {{ $topStudents->first()->name }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-lg p-4 text-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl">🏫</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">Top University</p>
                        <p class="text-lg font-bold">
                            @if($universityRanking->count() > 0)
                                {{ $universityRanking->first()->name }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-400 to-blue-500 rounded-lg p-4 text-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl">🔥</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">Popular Quiz</p>
                        <p class="text-lg font-bold">
                            @if($quizPopularity->count() > 0)
                                {{ Str::limit($quizPopularity->first()->title, 15) }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-400 to-purple-500 rounded-lg p-4 text-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl">🎯</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">Best Performance</p>
                        <p class="text-lg font-bold">
                            @if($quizPerformance->count() > 0)
                                {{ number_format($quizPerformance->first()->average_score, 1) }} avg
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Students by Score -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">🏆 All-Time Top Students by Total Score</h3>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $topStudents->count() }} students
                        </span>
                    </div>

                    @if($topStudents->count() > 0)
                        <div class="relative">
                            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400">
                                <div class="space-y-3 p-3">
                                    @foreach($topStudents as $index => $student)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    @if($index === 0)
                                                        <span class="text-2xl">🥇</span>
                                                    @elseif($index === 1)
                                                        <span class="text-2xl">🥈</span>
                                                    @elseif($index === 2)
                                                        <span class="text-2xl">🥉</span>
                                                    @else
                                                        <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="ml-3">
                                                    <div class="flex items-center space-x-2">
                                                        <p class="text-sm font-medium text-gray-900">{{ $student->name }}</p>
                                                        @if($student->is_active)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Active
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                Inactive
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm text-gray-500">
                                                        @if($student->university)
                                                            {{ $student->university->name }}
                                                        @else
                                                            No university
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-indigo-600">{{ $student->quiz_attempt_history_sum_score ?? 0 }} pts</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Scroll indicator -->
                            @if($topStudents->count() > 8)
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-white to-transparent h-8 pointer-events-none"></div>
                                <div class="text-center mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                        </svg>
                                        Scroll down to see more students ({{ $topStudents->count() }} total)
                                    </span>
                                </div>
                            @else
                                <div class="text-center mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Showing all {{ $topStudents->count() }} students
                                    </span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No quiz attempts yet.</p>
                    @endif
                </div>
            </div>

            <!-- University Student Count -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">🏫 Universities by Student Count</h3>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $universityRanking->count() }} universities
                        </span>
                    </div>

                    @if($universityRanking->count() > 0)
                        <div class="relative">
                            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400">
                                <div class="space-y-3 p-3">
                                    @foreach($universityRanking as $index => $university)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    @if($index === 0)
                                                        <span class="text-2xl">🏆</span>
                                                    @elseif($index === 1)
                                                        <span class="text-2xl">🥈</span>
                                                    @elseif($index === 2)
                                                        <span class="text-2xl">🥉</span>
                                                    @else
                                                        <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">{{ $university->name }}</p>
                                                    @if($university->location)
                                                        <p class="text-sm text-gray-500">{{ $university->location }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-green-600">{{ $university->users_count }} students</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Scroll indicator -->
                            @if($universityRanking->count() > 8)
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-white to-transparent h-8 pointer-events-none"></div>
                                <div class="text-center mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                        </svg>
                                        Scroll down to see more universities ({{ $universityRanking->count() }} total)
                                    </span>
                                </div>
                            @else
                                <div class="text-center mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Showing all {{ $universityRanking->count() }} universities
                                    </span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No universities with students yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <!-- Quiz Popularity -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">📊 Most Popular Quizzes</h3>
                    <div class="space-y-3">
                        @forelse($quizPopularity as $index => $quiz)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($index === 0)
                                            <span class="text-2xl">🔥</span>
                                        @elseif($index === 1)
                                            <span class="text-2xl">⭐</span>
                                        @elseif($index === 2)
                                            <span class="text-2xl">💫</span>
                                        @else
                                            <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                                {{ $index + 1 }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $quiz->title }}</p>
                                        <p class="text-sm text-gray-500">{{ $quiz->total_questions }} questions</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-blue-600">{{ $quiz->student_count }} takers</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No quiz attempts yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quiz Performance -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">🎯 Best Performing Quizzes</h3>
                    <div class="space-y-3">
                        @forelse($quizPerformance as $index => $quiz)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($index === 0)
                                            <span class="text-2xl">🎯</span>
                                        @elseif($index === 1)
                                            <span class="text-2xl">💯</span>
                                        @elseif($index === 2)
                                            <span class="text-2xl">✨</span>
                                        @else
                                            <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                                {{ $index + 1 }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $quiz->title }}</p>
                                        <p class="text-sm text-gray-500">{{ $quiz->student_count }} students</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-purple-600">{{ number_format($quiz->average_score, 1) }} avg</p>
                                    <p class="text-xs text-gray-500">{{ $quiz->highest_score }} max</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No quiz performance data yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time user activity updates
    function updateActivityData() {
        fetch('{{ route("admin.activity-data") }}')
            .then(response => response.json())
            .then(data => {
                // Update online count
                const onlineCountElement = document.getElementById('online-count');
                if (onlineCountElement) {
                    onlineCountElement.textContent = data.activityStats.online;
                }

                // Update online users list
                const onlineUsersList = document.getElementById('online-users-list');
                if (onlineUsersList) {
                    if (data.onlineUsers.length === 0) {
                        onlineUsersList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No users currently online</p>';
                    } else {
                        onlineUsersList.innerHTML = data.onlineUsers.map(user => `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold text-sm">
                                            ${user.name.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">${user.name}</p>
                                        <p class="text-xs text-gray-500">${user.email}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">${user.last_activity}</p>
                                    <p class="text-xs text-gray-400">${user.ip_address}</p>
                                </div>
                            </div>
                        `).join('');
                    }
                }

                // Update recent activities list
                const recentActivitiesList = document.getElementById('recent-activities-list');
                if (recentActivitiesList) {
                    if (data.recentActivities.length === 0) {
                        recentActivitiesList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No recent activities</p>';
                    } else {
                        recentActivitiesList.innerHTML = data.recentActivities.map(activity => {
                            let iconClass = 'bg-blue-100';
                            let iconSvg = `
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            `;

                            if (activity.activity_type === 'login') {
                                iconClass = 'bg-green-100';
                                iconSvg = `
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                `;
                            } else if (activity.activity_type === 'logout') {
                                iconClass = 'bg-red-100';
                                iconSvg = `
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                `;
                            }

                            return `
                                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 ${iconClass} rounded-full flex items-center justify-center">
                                            ${iconSvg}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">${activity.user_name}</p>
                                        <p class="text-sm text-gray-500">
                                            ${activity.activity_type.charAt(0).toUpperCase() + activity.activity_type.slice(1).replace('_', ' ')}
                                            ${activity.action ? '- ' + activity.action : ''}
                                        </p>
                                        <p class="text-xs text-gray-400">${activity.created_at}</p>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching activity data:', error);
            });
    }

    // Update activity data every 10 seconds
    setInterval(updateActivityData, 10000);

    // Initial load
    updateActivityData();
});
</script>
@endsection
