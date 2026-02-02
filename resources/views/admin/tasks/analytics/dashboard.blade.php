@extends('layouts.admin')

@section('title', 'Task Analytics Dashboard')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 space-y-6">
    <!-- Page Header -->
    <div class="px-4 py-6 sm:px-6 sm:py-8 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 shadow-lg rounded-2xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-medium text-indigo-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span>
                    Task Analytics
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Task Analytics Dashboard
                </h1>
                <p class="mt-1 text-sm sm:text-base text-indigo-100/90 max-w-2xl">
                    Comprehensive view of all tasks across all users with performance metrics and analytics.
                </p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ route('admin.tasks.analytics') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}"
                           class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}"
                           class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Task Type Filter -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Task Type</label>
                    <select name="type" id="type" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ $taskType == 'all' ? 'selected' : '' }}>All Tasks</option>
                        <option value="personal" {{ $taskType == 'personal' ? 'selected' : '' }}>Personal Tasks</option>
                        <option value="group" {{ $taskType == 'group' ? 'selected' : '' }}>Group Tasks</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="todo" {{ $statusFilter == 'todo' ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ $statusFilter == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ $statusFilter == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Task title, user..."
                           class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center px-4 sm:px-6 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
                <a href="{{ route('admin.tasks.analytics') }}" class="inline-flex items-center px-4 sm:px-6 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_tasks'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_tasks'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['in_progress_tasks'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['todo_tasks'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- All Tasks Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">All Tasks Dashboard</h2>
                    <p class="text-sm text-gray-600 mt-1">View all tasks created by all users</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creator</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($allTasks as $task)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                                @if($task->description)
                                    <div class="text-sm text-gray-500 mt-1">{{ Str::limit($task->description, 50) }}</div>
                                @endif
                                @if($task->parent)
                                    <div class="text-xs text-indigo-600 mt-1">Subtask of: {{ $task->parent->title }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div>{{ $task->creator->name ?? 'N/A' }}</div>
                                <div class="text-gray-500 text-xs">{{ $task->creator->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $task->type == 'personal' ? 'bg-purple-100 text-purple-800' : 'bg-pink-100 text-pink-800' }}">
                                    {{ ucfirst($task->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'todo' => 'bg-yellow-100 text-yellow-800',
                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                        'done' => 'bg-emerald-100 text-emerald-800',
                                    ];
                                    $statusColor = $statusColors[$task->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $task->priority ? ucfirst($task->priority) : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $task->created_at->format('M d, Y') }}<br>
                                <span class="text-xs">{{ $task->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($task->due_date)
                                    {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}<br>
                                    <span class="text-xs">{{ \Carbon\Carbon::parse($task->due_date)->format('h:i A') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                No tasks found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($allTasks->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $allTasks->links() }}
            </div>
        @endif
    </div>

    <!-- Fastest Task Completers Section -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Fastest Task Completers</h2>
                    <p class="text-sm text-gray-600 mt-1">Users who complete tasks the fastest (based on average completion time)</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($fastestCompleters->count() > 0)
                <!-- Chart -->
                <div class="mb-6">
                    <div class="h-80">
                        <canvas id="fastestCompletersChart"></canvas>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tasks Completed</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Completion Time</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($fastestCompleters as $index => $completer)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-indigo-100 text-xs font-medium text-indigo-700">
                                            #{{ $index + 1 }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $completer['user']->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $completer['user']->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        <span class="font-semibold">{{ $completer['total_completed'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        <div class="font-semibold text-emerald-600">
                                            @if($completer['average_hours'] < 24)
                                                {{ number_format($completer['average_hours'], 1) }} hours
                                            @else
                                                {{ number_format($completer['average_days'], 1) }} days
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            ({{ number_format($completer['average_hours'], 1) }}h avg)
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        @if($completer['total_hours'] < 24)
                                            {{ number_format($completer['total_hours'], 1) }} hours
                                        @else
                                            {{ number_format($completer['total_hours'] / 24, 1) }} days
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-sm text-gray-500">No completed tasks found for the selected period.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Task Status Distribution -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Task Status Distribution</h3>
                <p class="text-sm text-gray-600 mt-1">Breakdown of tasks by status</p>
            </div>
            <div class="h-80">
                <canvas id="statusDistributionChart"></canvas>
            </div>
        </div>

        <!-- Task Type Distribution -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Task Type Distribution</h3>
                <p class="text-sm text-gray-600 mt-1">Personal vs Group tasks</p>
            </div>
            <div class="h-80">
                <canvas id="typeDistributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Task Creation Trends -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Task Creation Trends</h3>
                <p class="text-sm text-gray-600 mt-1">Daily task creation over time</p>
            </div>
            <div class="h-80">
                <canvas id="creationTrendsChart"></canvas>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Task Completion Rate</h3>
                <p class="text-sm text-gray-600 mt-1">Completion percentage over time</p>
            </div>
            <div class="h-80">
                <canvas id="completionRateChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tasks by User -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Tasks by User</h3>
            <p class="text-sm text-gray-600 mt-1">Top users by task creation</p>
        </div>
        <div class="h-80">
            <canvas id="tasksByUserChart"></canvas>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusDistributionChart');
    if (statusCtx) {
        const statusData = @json($statusDistribution);
        new Chart(statusCtx, {
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
                animation: { duration: 2000 },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // Type Distribution Chart
    const typeCtx = document.getElementById('typeDistributionChart');
    if (typeCtx) {
        const typeData = @json($typeDistribution);
        new Chart(typeCtx, {
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
                animation: { duration: 2000 },
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // Creation Trends Chart
    const creationCtx = document.getElementById('creationTrendsChart');
    if (creationCtx) {
        const trends = @json($creationTrends);
        const trendKeys = Object.keys(trends);
        
        if (trendKeys.length > 0) {
            const dates = trendKeys.map(k => trends[k].date);
            new Chart(creationCtx, {
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
                            fill: true
                        },
                        {
                            label: 'Completed',
                            data: trendKeys.map(k => trends[k].completed || 0),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'In Progress',
                            data: trendKeys.map(k => trends[k].in_progress || 0),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'To Do',
                            data: trendKeys.map(k => trends[k].todo || 0),
                            borderColor: 'rgb(234, 179, 8)',
                            backgroundColor: 'rgba(234, 179, 8, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 2000 },
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

    // Completion Rate Chart
    const completionCtx = document.getElementById('completionRateChart');
    if (completionCtx) {
        const completion = @json($completionRate);
        const completionKeys = Object.keys(completion);
        
        if (completionKeys.length > 0) {
            const dates = completionKeys.map(k => completion[k].date);
            new Chart(completionCtx, {
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
                        yAxisID: 'y'
                    }, {
                        label: 'Total Tasks',
                        data: completionKeys.map(k => completion[k].total || 0),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 2000 },
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

    // Fastest Completers Chart
    const fastestCtx = document.getElementById('fastestCompletersChart');
    if (fastestCtx) {
        const completers = @json($fastestCompleters);
        if (completers && completers.length > 0) {
            new Chart(fastestCtx, {
                type: 'bar',
                data: {
                    labels: completers.map(c => c.user.name),
                    datasets: [{
                        label: 'Average Completion Time (hours)',
                        data: completers.map(c => c.average_hours),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 2,
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 2000 },
                    scales: {
                        x: { 
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Average Hours'
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const hours = context.parsed.x;
                                    const days = (hours / 24).toFixed(2);
                                    return `Avg: ${hours.toFixed(1)} hours (${days} days)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Tasks by User Chart
    const tasksByUserCtx = document.getElementById('tasksByUserChart');
    if (tasksByUserCtx) {
        const tasksByUser = @json($tasksByUser);
        if (tasksByUser && tasksByUser.length > 0) {
            new Chart(tasksByUserCtx, {
                type: 'bar',
                data: {
                    labels: tasksByUser.map(u => u.user ? u.user.name : 'Unknown'),
                    datasets: [
                        {
                            label: 'Total',
                            data: tasksByUser.map(u => u.total || 0),
                            backgroundColor: 'rgba(99, 102, 241, 0.8)',
                            borderColor: 'rgb(99, 102, 241)',
                            borderWidth: 2
                        },
                        {
                            label: 'Completed',
                            data: tasksByUser.map(u => u.completed || 0),
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 2
                        },
                        {
                            label: 'In Progress',
                            data: tasksByUser.map(u => u.in_progress || 0),
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 2
                        },
                        {
                            label: 'To Do',
                            data: tasksByUser.map(u => u.todo || 0),
                            backgroundColor: 'rgba(234, 179, 8, 0.8)',
                            borderColor: 'rgb(234, 179, 8)',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 2000 },
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
});
</script>
@endsection
