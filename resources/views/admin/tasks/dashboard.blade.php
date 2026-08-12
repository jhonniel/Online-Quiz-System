@extends('layouts.admin')

@section('title', 'Task Dashboard')
@section('page-title', 'Task Dashboard')

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

/* Chart Container Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chart-container {
    opacity: 0;
    animation: fadeInUp 0.8s ease-out forwards;
    transition: all 0.3s ease;
}

.chart-container:hover {
    transform: translateY(-2px);
}

/* Staggered animation delays for professional appearance */
.chart-container {
    animation-delay: calc(var(--chart-index, 0) * 0.1s);
}

/* Section-based animation delays */
.chart-container:nth-of-type(1) { --chart-index: 1; }
.chart-container:nth-of-type(2) { --chart-index: 2; }
.chart-container:nth-of-type(3) { --chart-index: 3; }
.chart-container:nth-of-type(4) { --chart-index: 4; }
.chart-container:nth-of-type(5) { --chart-index: 5; }
.chart-container:nth-of-type(6) { --chart-index: 6; }
.chart-container:nth-of-type(7) { --chart-index: 7; }
.chart-container:nth-of-type(8) { --chart-index: 8; }
.chart-container:nth-of-type(9) { --chart-index: 9; }
.chart-container:nth-of-type(10) { --chart-index: 10; }
.chart-container:nth-of-type(11) { --chart-index: 11; }
.chart-container:nth-of-type(12) { --chart-index: 12; }
.chart-container:nth-of-type(13) { --chart-index: 13; }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6 px-2 sm:px-0">
    <!-- Page Header -->
    <div class="px-4 py-6 sm:px-6 sm:py-8 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 shadow-lg rounded-2xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-medium text-indigo-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span>
                    All Tasks
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Task Dashboard
                </h1>
                <p class="mt-1 text-sm sm:text-base text-indigo-100/90 max-w-2xl">
                    View and manage all tasks across the system. Personal and group tasks are displayed here.
                </p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ url('/admin/tasks/dashboard') }}" id="filterForm" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="chart-filter w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="todo" {{ $statusFilter == 'todo' ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ $statusFilter == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ $statusFilter == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Task Type</label>
                    <select name="type" id="type" class="chart-filter w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ $typeFilter == 'all' || !$typeFilter ? 'selected' : '' }}>All Types</option>
                        <option value="personal" {{ $typeFilter == 'personal' ? 'selected' : '' }}>Personal Tasks</option>
                        <option value="group" {{ $typeFilter == 'group' ? 'selected' : '' }}>Group Tasks</option>
                    </select>
                </div>

                <!-- View Toggle -->
                <div>
                    <label for="view" class="block text-sm font-medium text-gray-700 mb-2">View</label>
                    <select name="view" id="view" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="board" {{ $view == 'board' ? 'selected' : '' }}>Board View</option>
                        <option value="list" {{ $view == 'list' ? 'selected' : '' }}>List View</option>
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
                <a href="{{ url('/admin/tasks/dashboard') }}" class="inline-flex items-center px-4 sm:px-6 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow border border-gray-200 p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Tasks</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tasks->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-emerald-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tasks->where('status', 'done')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">In Progress</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tasks->where('status', 'in_progress')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">To Do</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tasks->where('status', 'todo')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Section Header -->
    <div class="mt-8 mb-6">
        <div class="flex items-center space-x-3">
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
            <h2 class="text-xl font-bold text-gray-800 px-4">Task Analytics</h2>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
        </div>
    </div>

    <!-- Overview Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Task Status Distribution -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Status Distribution</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Tasks by status</p>
                    </div>
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="statusDistributionChart"></canvas>
            </div>
        </div>

        <!-- Task Type Distribution -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Type Distribution</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Personal vs Group</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="typeDistributionChart"></canvas>
            </div>
        </div>

        <!-- Priority Distribution -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Priority Distribution</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Tasks by priority</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="priorityDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Trends Section Header -->
    <div class="mt-8 mb-6">
        <div class="flex items-center space-x-3">
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
            <h2 class="text-xl font-bold text-gray-800 px-4">Trends & Performance</h2>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
        </div>
    </div>

    <!-- Trends Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Task Creation Trends -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Creation Trends</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daily task creation (30 days)</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-80">
                <canvas id="creationTrendsChart"></canvas>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Completion Rate</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Completion over time</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-80">
                <canvas id="completionRateChart"></canvas>
            </div>
        </div>
    </div>

    <!-- User Performance Section Header -->
    <div class="mt-8 mb-6">
        <div class="flex items-center space-x-3">
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
            <h2 class="text-xl font-bold text-gray-800 px-4">User Performance</h2>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
        </div>
    </div>

    <!-- User Performance Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Tasks by User -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Tasks by User</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Top creators</p>
                    </div>
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="tasksByUserChart"></canvas>
            </div>
        </div>

        <!-- Top Task Completers -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Top Completers</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Most completed tasks</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="usersWithMostCompletedChart"></canvas>
            </div>
        </div>

        <!-- Most Active Users -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Most Active</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Task changes made</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="taskChangesByUserChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Activity Tracking Section Header -->
    <div class="mt-8 mb-6">
        <div class="flex items-center space-x-3">
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
            <h2 class="text-xl font-bold text-gray-800 px-4">Activity Tracking</h2>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
        </div>
    </div>

    <!-- Activity Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Task Changes Over Time -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Task Changes</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daily updates & changes</p>
                    </div>
                    <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-80">
                <canvas id="taskChangesOverTimeChart"></canvas>
            </div>
        </div>

        <!-- Task Lists Creation Trends -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">List Creation Trends</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daily task list creation</p>
                    </div>
                    <div class="w-10 h-10 bg-violet-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-80">
                <canvas id="taskListCreationTrendsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Task Lists Analytics Section Header -->
    <div class="mt-8 mb-6">
        <div class="flex items-center space-x-3">
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
            <h2 class="text-xl font-bold text-gray-800 px-4">Task Lists Analytics</h2>
            <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
        </div>
    </div>

    <!-- Task Lists Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Task Lists by User -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Lists by User</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Most task lists created</p>
                    </div>
                    <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="taskListsByUserChart"></canvas>
            </div>
        </div>

        <!-- Tasks per Task List -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Tasks per List</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Top lists by task count</p>
                    </div>
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="tasksPerTaskListChart"></canvas>
            </div>
        </div>

        <!-- Task List Growth -->
        <div class="chart-container bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">List Growth</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Lists & tasks over time</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <canvas id="taskListGrowthOverTimeChart"></canvas>
            </div>
        </div>
    </div>

    @if($view === 'board')
        <!-- Board View -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach(['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'] as $status => $label)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $label }}</h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                        {{ $tasksByStatus[$status]->count() }}
                    </span>
                </div>
                <div class="space-y-3 max-h-[600px] overflow-y-auto scrollbar-thin">
                    @forelse($tasksByStatus[$status] as $task)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-medium text-gray-900">{{ $task->title }}</h4>
                            <span class="px-2 py-1 text-xs font-medium rounded {{ $task->type === 'personal' ? 'bg-purple-100 text-purple-800' : 'bg-pink-100 text-pink-800' }}">
                                {{ ucfirst($task->type) }}
                            </span>
                        </div>
                        @if($task->description)
                        <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ \Illuminate\Support\Str::limit($task->description, 100) }}</p>
                        @endif
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>By: <x-user-name :user="$task->creator" class="inline" /></span>
                            @if($task->due_date)
                            <span class="text-red-600">{{ $task->due_date->format('M d, Y') }}</span>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            @if($task->assignments->count() > 0)
                            <div class="flex -space-x-2">
                                @foreach($task->assignments->take(3) as $assignment)
                                <div class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-medium" title="{{ $assignment->user->name }}">
                                    {{ substr($assignment->user->name, 0, 1) }}
                                </div>
                                @endforeach
                                @if($task->assignments->count() > 3)
                                <div class="w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs font-medium">
                                    +{{ $task->assignments->count() - 3 }}
                                </div>
                                @endif
                            </div>
                            @endif
                            <a href="{{ url('/admin/tasks?type=' . $task->type) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                View Details →
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400">
                        <p>No tasks in this status</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    @else
        <!-- List View -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignees</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tasks as $task)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                                @if($task->description)
                                <div class="text-sm text-gray-500">{{ \Illuminate\Support\Str::limit($task->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded {{ $task->type === 'personal' ? 'bg-purple-100 text-purple-800' : 'bg-pink-100 text-pink-800' }}">
                                    {{ ucfirst($task->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded
                                    {{ $task->status === 'done' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $task->status === 'todo' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <x-user-name :user="$task->creator" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($task->assignments->count() > 0)
                                <div class="flex -space-x-2">
                                    @foreach($task->assignments->take(3) as $assignment)
                                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-medium border-2 border-white" title="{{ $assignment->user->name }}">
                                        {{ substr($assignment->user->name, 0, 1) }}
                                    </div>
                                    @endforeach
                                    @if($task->assignments->count() > 3)
                                    <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs font-medium border-2 border-white">
                                        +{{ $task->assignments->count() - 3 }}
                                    </div>
                                    @endif
                                </div>
                                @else
                                <span class="text-sm text-gray-400">No assignees</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($task->due_date)
                                <span class="{{ $task->due_date->isPast() && $task->status !== 'done' ? 'text-red-600 font-medium' : '' }}">
                                    {{ $task->due_date->format('M d, Y') }}
                                </span>
                                @else
                                <span class="text-gray-400">No due date</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ url('/admin/tasks?type=' . $task->type) }}" class="text-indigo-600 hover:text-indigo-900">
                                    View →
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No tasks found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store chart instances
    let chartInstances = {};
    
    // Function to update all charts with new data
    function updateAllCharts(statusFilter, typeFilter) {
        const url = new URL('{{ url("/admin/tasks/dashboard/chart-data") }}', window.location.origin);
        if (statusFilter) url.searchParams.append('status', statusFilter);
        if (typeFilter && typeFilter !== 'all') url.searchParams.append('type', typeFilter);
        
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update Status Distribution Chart
            if (chartInstances.statusChart && data.statusDistribution && Object.keys(data.statusDistribution).length > 0) {
                chartInstances.statusChart.data.labels = Object.keys(data.statusDistribution).map(k => k.charAt(0).toUpperCase() + k.slice(1).replace('_', ' '));
                chartInstances.statusChart.data.datasets[0].data = Object.values(data.statusDistribution);
                chartInstances.statusChart.update('active');
            }
            
            // Update Type Distribution Chart
            if (chartInstances.typeChart && data.typeDistribution && Object.keys(data.typeDistribution).length > 0) {
                chartInstances.typeChart.data.labels = Object.keys(data.typeDistribution).map(k => k.charAt(0).toUpperCase() + k.slice(1));
                chartInstances.typeChart.data.datasets[0].data = Object.values(data.typeDistribution);
                chartInstances.typeChart.update('active');
            }
            
            // Update Creation Trends Chart
            if (chartInstances.creationChart) {
                const trendKeys = Object.keys(data.creationTrends);
                if (trendKeys.length > 0) {
                    const dates = trendKeys.map(k => data.creationTrends[k].date);
                    chartInstances.creationChart.data.labels = dates;
                    chartInstances.creationChart.data.datasets[0].data = trendKeys.map(k => data.creationTrends[k].total || 0);
                    chartInstances.creationChart.data.datasets[1].data = trendKeys.map(k => data.creationTrends[k].completed || 0);
                    chartInstances.creationChart.data.datasets[2].data = trendKeys.map(k => data.creationTrends[k].in_progress || 0);
                    chartInstances.creationChart.data.datasets[3].data = trendKeys.map(k => data.creationTrends[k].todo || 0);
                    chartInstances.creationChart.update('active');
                }
            }
            
            // Update Completion Rate Chart
            if (chartInstances.completionChart) {
                const completionKeys = Object.keys(data.completionRate);
                if (completionKeys.length > 0) {
                    const dates = completionKeys.map(k => data.completionRate[k].date);
                    chartInstances.completionChart.data.labels = dates;
                    chartInstances.completionChart.data.datasets[0].data = completionKeys.map(k => data.completionRate[k].rate || 0);
                    chartInstances.completionChart.data.datasets[1].data = completionKeys.map(k => data.completionRate[k].total || 0);
                    chartInstances.completionChart.update('active');
                }
            }
            
            // Update Priority Distribution Chart
            if (chartInstances.priorityChart && data.priorityDistribution && Object.keys(data.priorityDistribution).length > 0) {
                chartInstances.priorityChart.data.labels = Object.keys(data.priorityDistribution).map(k => k.charAt(0).toUpperCase() + k.slice(1));
                chartInstances.priorityChart.data.datasets[0].data = Object.values(data.priorityDistribution);
                chartInstances.priorityChart.update('active');
            }
            
            // Update Tasks by User Chart
            if (chartInstances.tasksByUserChart && data.tasksByUser && data.tasksByUser.length > 0) {
                chartInstances.tasksByUserChart.data.labels = data.tasksByUser.map(u => u.user ? u.user.name : 'Unknown');
                chartInstances.tasksByUserChart.data.datasets[0].data = data.tasksByUser.map(u => u.total);
                chartInstances.tasksByUserChart.data.datasets[1].data = data.tasksByUser.map(u => u.completed);
                chartInstances.tasksByUserChart.update('active');
            }
            
            // Update Users with Most Completed Chart
            if (chartInstances.mostCompletedChart && data.usersWithMostCompleted && data.usersWithMostCompleted.length > 0) {
                chartInstances.mostCompletedChart.data.labels = data.usersWithMostCompleted.map(u => u.user ? u.user.name : 'Unknown');
                chartInstances.mostCompletedChart.data.datasets[0].data = data.usersWithMostCompleted.map(u => u.completed_count);
                chartInstances.mostCompletedChart.update('active');
            }
            
            // Update Task Changes Over Time Chart
            if (chartInstances.changesOverTimeChart) {
                const changeKeys = Object.keys(data.taskChangesOverTime);
                if (changeKeys.length > 0) {
                    const dates = changeKeys.map(k => data.taskChangesOverTime[k].date);
                    chartInstances.changesOverTimeChart.data.labels = dates;
                    chartInstances.changesOverTimeChart.data.datasets[0].data = changeKeys.map(k => data.taskChangesOverTime[k].changes || 0);
                    chartInstances.changesOverTimeChart.update('active');
                }
            }
            
            // Update Task Changes by User Chart
            if (chartInstances.changesByUserChart && data.taskChangesByUser && data.taskChangesByUser.length > 0) {
                chartInstances.changesByUserChart.data.labels = data.taskChangesByUser.map(u => u.user ? u.user.name : 'Unknown');
                chartInstances.changesByUserChart.data.datasets[0].data = data.taskChangesByUser.map(u => u.changes_count);
                chartInstances.changesByUserChart.update('active');
            }
            
            // Update Task Lists by User Chart
            if (chartInstances.taskListsByUserChart && data.taskListsByUser && data.taskListsByUser.length > 0) {
                chartInstances.taskListsByUserChart.data.labels = data.taskListsByUser.map(u => u.user ? u.user.name : 'Unknown');
                chartInstances.taskListsByUserChart.data.datasets[0].data = data.taskListsByUser.map(u => u.task_lists_count);
                chartInstances.taskListsByUserChart.data.datasets[1].data = data.taskListsByUser.map(u => u.total_tasks);
                chartInstances.taskListsByUserChart.update('active');
            }
            
            // Update Task List Creation Trends Chart
            if (chartInstances.taskListCreationTrendsChart) {
                const trendKeys = Object.keys(data.taskListCreationTrends);
                if (trendKeys.length > 0) {
                    const dates = trendKeys.map(k => data.taskListCreationTrends[k].date);
                    chartInstances.taskListCreationTrendsChart.data.labels = dates;
                    chartInstances.taskListCreationTrendsChart.data.datasets[0].data = trendKeys.map(k => data.taskListCreationTrends[k].created || 0);
                    chartInstances.taskListCreationTrendsChart.update('active');
                }
            }
            
            // Update Tasks per Task List Chart
            if (chartInstances.tasksPerTaskListChart && data.tasksPerTaskList && data.tasksPerTaskList.length > 0) {
                chartInstances.tasksPerTaskListChart.data.labels = data.tasksPerTaskList.map(tl => tl.task_list ? (tl.task_list.name + ' (' + tl.task_list.user_name + ')') : 'Unknown');
                chartInstances.tasksPerTaskListChart.data.datasets[0].data = data.tasksPerTaskList.map(tl => tl.total_tasks);
                chartInstances.tasksPerTaskListChart.data.datasets[1].data = data.tasksPerTaskList.map(tl => tl.completed_tasks);
                chartInstances.tasksPerTaskListChart.update('active');
            }
            
            // Update Task List Growth Over Time Chart
            if (chartInstances.taskListGrowthOverTimeChart) {
                const growthKeys = Object.keys(data.taskListGrowthOverTime);
                if (growthKeys.length > 0) {
                    const dates = growthKeys.map(k => data.taskListGrowthOverTime[k].date);
                    chartInstances.taskListGrowthOverTimeChart.data.labels = dates;
                    chartInstances.taskListGrowthOverTimeChart.data.datasets[0].data = growthKeys.map(k => data.taskListGrowthOverTime[k].total_lists || 0);
                    chartInstances.taskListGrowthOverTimeChart.data.datasets[1].data = growthKeys.map(k => data.taskListGrowthOverTime[k].total_tasks || 0);
                    chartInstances.taskListGrowthOverTimeChart.update('active');
                }
            }
        })
        .catch(error => {
            console.error('Error updating charts:', error);
        });
    }
    
    // Add event listeners to filter inputs
    document.querySelectorAll('.chart-filter').forEach(filter => {
        filter.addEventListener('change', function() {
            const statusFilter = document.getElementById('status').value;
            const typeFilter = document.getElementById('type').value;
            updateAllCharts(statusFilter, typeFilter);
        });
    });
    
    // Wait for CSS animations to start before initializing charts
    setTimeout(function() {
    // Status Distribution Chart (Doughnut)
    const statusCtx = document.getElementById('statusDistributionChart');
    if (statusCtx) {
        const statusData = @json($statusDistribution);
        if (statusData && Object.keys(statusData).length > 0) {
            chartInstances.statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusData).map(k => k.charAt(0).toUpperCase() + k.slice(1).replace('_', ' ')),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: [
                        'rgba(234, 179, 8, 0.8)',   // todo - yellow
                        'rgba(59, 130, 246, 0.8)',  // in_progress - blue
                        'rgba(16, 185, 129, 0.8)'   // done - green
                    ],
                    borderColor: [
                        'rgb(234, 179, 8)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2500,
                    easing: 'easeOutQuart',
                    animateRotate: true,
                    animateScale: true,
                    delay: function(context) {
                        return context.dataIndex * 200;
                    }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        } else {
            console.warn('Status distribution data is empty');
        }
    }

    // Type Distribution Chart (Pie)
    const typeCtx = document.getElementById('typeDistributionChart');
    if (typeCtx) {
        const typeData = @json($typeDistribution);
        if (typeData && Object.keys(typeData).length > 0) {
            chartInstances.typeChart = new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(typeData).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
                datasets: [{
                    data: Object.values(typeData),
                    backgroundColor: [
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ],
                    borderColor: [
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2500,
                    easing: 'easeOutQuart',
                    animateRotate: true,
                    animateScale: true,
                    delay: function(context) {
                        return context.dataIndex * 300;
                    }
                },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        } else {
            console.warn('Type distribution data is empty');
        }
    }

    // Creation Trends Chart (Line Graph)
    const creationCtx = document.getElementById('creationTrendsChart');
    if (creationCtx) {
        const trends = @json($creationTrends);
        const trendKeys = Object.keys(trends);
        
        if (trendKeys.length > 0) {
            const dates = trendKeys.map(k => trends[k].date);
            chartInstances.creationChart = new Chart(creationCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [
                        {
                            label: 'Total Created',
                            data: trendKeys.map(k => trends[k].total || 0),
                            borderColor: 'rgb(99, 102, 241)',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 8,
                            pointHoverBorderWidth: 3,
                            pointBackgroundColor: 'rgb(99, 102, 241)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Completed',
                            data: trendKeys.map(k => trends[k].completed || 0),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 8,
                            pointHoverBorderWidth: 3,
                            pointBackgroundColor: 'rgb(16, 185, 129)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'In Progress',
                            data: trendKeys.map(k => trends[k].in_progress || 0),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 8,
                            pointHoverBorderWidth: 3,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'To Do',
                            data: trendKeys.map(k => trends[k].todo || 0),
                            borderColor: 'rgb(234, 179, 8)',
                            backgroundColor: 'rgba(234, 179, 8, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 8,
                            pointHoverBorderWidth: 3,
                            pointBackgroundColor: 'rgb(234, 179, 8)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 20 + context.datasetIndex * 100;
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    }

    // Completion Rate Chart (Line Graph)
    const completionCtx = document.getElementById('completionRateChart');
    if (completionCtx) {
        const completion = @json($completionRate);
        const completionKeys = Object.keys(completion);
        
        if (completionKeys.length > 0) {
            const dates = completionKeys.map(k => completion[k].date);
            chartInstances.completionChart = new Chart(completionCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Completion Rate (%)',
                        data: completionKeys.map(k => completion[k].rate || 0),
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 8,
                        pointHoverBorderWidth: 3,
                        pointBackgroundColor: 'rgb(99, 102, 241)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y'
                    }, {
                        label: 'Total Tasks',
                        data: completionKeys.map(k => completion[k].total || 0),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: false,
                        pointRadius: 4,
                        pointHoverRadius: 8,
                        pointHoverBorderWidth: 3,
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 25 + context.datasetIndex * 150;
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            max: 100,
                            ticks: { callback: v => v + '%' }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    }

    // Priority Distribution Chart (Bar)
    const priorityCtx = document.getElementById('priorityDistributionChart');
    if (priorityCtx) {
        const priorityData = @json($priorityDistribution);
        if (priorityData && Object.keys(priorityData).length > 0) {
            chartInstances.priorityChart = new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(priorityData).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
                datasets: [{
                    label: 'Tasks',
                    data: Object.values(priorityData),
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',   // high - red
                        'rgba(234, 179, 8, 0.8)',   // medium - yellow
                        'rgba(16, 185, 129, 0.8)'    // low - green
                    ],
                    borderColor: [
                        'rgb(239, 68, 68)',
                        'rgb(234, 179, 8)',
                        'rgb(16, 185, 129)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart',
                    delay: function(context) {
                        return context.dataIndex * 100;
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
        } else {
            console.warn('Priority distribution data is empty');
        }
    }

    // Tasks by User Chart (Bar)
    const userCtx = document.getElementById('tasksByUserChart');
    if (userCtx) {
        const userData = @json($tasksByUser);
        if (userData && userData.length > 0) {
            chartInstances.tasksByUserChart = new Chart(userCtx, {
                type: 'bar',
                data: {
                    labels: userData.map(u => u.user ? u.user.name : 'Unknown'),
                    datasets: [{
                        label: 'Total Tasks',
                        data: userData.map(u => u.total),
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }, {
                        label: 'Completed',
                        data: userData.map(u => u.completed),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 80 + context.datasetIndex * 50;
                        }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    }

    // Users with Most Completed Tasks Chart (Bar)
    const mostCompletedCtx = document.getElementById('usersWithMostCompletedChart');
    if (mostCompletedCtx) {
        const mostCompletedData = @json($usersWithMostCompleted);
        if (mostCompletedData && mostCompletedData.length > 0) {
            chartInstances.mostCompletedChart = new Chart(mostCompletedCtx, {
                type: 'bar',
                data: {
                    labels: mostCompletedData.map(u => u.user ? u.user.name : 'Unknown'),
                    datasets: [{
                        label: 'Completed Tasks',
                        data: mostCompletedData.map(u => u.completed_count),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 100;
                        }
                    },
                    scales: {
                        x: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    }

    // Task Changes Over Time Chart (Line)
    const changesOverTimeCtx = document.getElementById('taskChangesOverTimeChart');
    if (changesOverTimeCtx) {
        const changesData = @json($taskChangesOverTime);
        const changeKeys = Object.keys(changesData);
        
        if (changeKeys.length > 0) {
            const dates = changeKeys.map(k => changesData[k].date);
            chartInstances.changesOverTimeChart = new Chart(changesOverTimeCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Task Changes',
                        data: changeKeys.map(k => changesData[k].changes || 0),
                        borderColor: 'rgb(236, 72, 153)',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 8,
                        pointHoverBorderWidth: 3,
                        pointBackgroundColor: 'rgb(236, 72, 153)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 20;
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    }

    // Task Changes by User Chart (Bar)
    const changesByUserCtx = document.getElementById('taskChangesByUserChart');
    if (changesByUserCtx) {
        const changesByUserData = @json($taskChangesByUser);
        if (changesByUserData && changesByUserData.length > 0) {
            chartInstances.changesByUserChart = new Chart(changesByUserCtx, {
                type: 'bar',
                data: {
                    labels: changesByUserData.map(u => u.user ? u.user.name : 'Unknown'),
                    datasets: [{
                        label: 'Task Changes',
                        data: changesByUserData.map(u => u.changes_count),
                        backgroundColor: 'rgba(139, 92, 246, 0.8)',
                        borderColor: 'rgb(139, 92, 246)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 80;
                        }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    }

    // Task Lists by User Chart (Bar)
    const taskListsByUserCtx = document.getElementById('taskListsByUserChart');
    if (taskListsByUserCtx) {
        const taskListsByUserData = @json($taskListsByUser);
        if (taskListsByUserData && taskListsByUserData.length > 0) {
            chartInstances.taskListsByUserChart = new Chart(taskListsByUserCtx, {
                type: 'bar',
                data: {
                    labels: taskListsByUserData.map(u => u.user ? u.user.name : 'Unknown'),
                    datasets: [{
                        label: 'Task Lists',
                        data: taskListsByUserData.map(u => u.task_lists_count),
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }, {
                        label: 'Total Tasks',
                        data: taskListsByUserData.map(u => u.total_tasks),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 80 + context.datasetIndex * 50;
                        }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    }

    // Task List Creation Trends Chart (Line)
    const taskListCreationTrendsCtx = document.getElementById('taskListCreationTrendsChart');
    if (taskListCreationTrendsCtx) {
        const taskListTrendsData = @json($taskListCreationTrends);
        const trendKeys = Object.keys(taskListTrendsData);
        
        if (trendKeys.length > 0) {
            const dates = trendKeys.map(k => taskListTrendsData[k].date);
            chartInstances.taskListCreationTrendsChart = new Chart(taskListCreationTrendsCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Task Lists Created',
                        data: trendKeys.map(k => taskListTrendsData[k].created || 0),
                        borderColor: 'rgb(139, 92, 246)',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 8,
                        pointHoverBorderWidth: 3,
                        pointBackgroundColor: 'rgb(139, 92, 246)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 20;
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    }

    // Tasks per Task List Chart (Bar)
    const tasksPerTaskListCtx = document.getElementById('tasksPerTaskListChart');
    if (tasksPerTaskListCtx) {
        const tasksPerTaskListData = @json($tasksPerTaskList);
        if (tasksPerTaskListData && tasksPerTaskListData.length > 0) {
            chartInstances.tasksPerTaskListChart = new Chart(tasksPerTaskListCtx, {
                type: 'bar',
                data: {
                    labels: tasksPerTaskListData.map(tl => tl.task_list ? (tl.task_list.name + ' (' + tl.task_list.user_name + ')') : 'Unknown'),
                    datasets: [{
                        label: 'Total Tasks',
                        data: tasksPerTaskListData.map(tl => tl.total_tasks),
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }, {
                        label: 'Completed',
                        data: tasksPerTaskListData.map(tl => tl.completed_tasks),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 100;
                        }
                    },
                    scales: {
                        x: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    }

    // Task List Growth Over Time Chart (Line)
    const taskListGrowthOverTimeCtx = document.getElementById('taskListGrowthOverTimeChart');
    if (taskListGrowthOverTimeCtx) {
        const growthData = @json($taskListGrowthOverTime);
        const growthKeys = Object.keys(growthData);
        
        if (growthKeys.length > 0) {
            const dates = growthKeys.map(k => growthData[k].date);
            chartInstances.taskListGrowthOverTimeChart = new Chart(taskListGrowthOverTimeCtx, {
                type: 'line',
                data: {
                    labels: dates,
                    datasets: [{
                        label: 'Total Task Lists',
                        data: growthKeys.map(k => growthData[k].total_lists || 0),
                        borderColor: 'rgb(139, 92, 246)',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 8,
                        pointHoverBorderWidth: 3,
                        pointBackgroundColor: 'rgb(139, 92, 246)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y'
                    }, {
                        label: 'Total Tasks in Lists',
                        data: growthKeys.map(k => growthData[k].total_tasks || 0),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 8,
                        pointHoverBorderWidth: 3,
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart',
                        delay: function(context) {
                            return context.dataIndex * 20 + context.datasetIndex * 150;
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            grid: { drawOnChartArea: true }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    }
    }, 100); // Small delay to ensure CSS animations start first
});
</script>
@endsection
