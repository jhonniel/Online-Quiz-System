@extends('layouts.admin')

@section('title', 'Task Analytics Dashboard')
@section('page-title', 'Task Analytics')

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
<div class="space-y-4 sm:space-y-6 px-2 sm:px-0">
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
                    Comprehensive analytics and insights into task performance, completion rates, and user productivity.
                </p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ route('admin.tasks.analytics') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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

    <!-- Charts Row 2 - Line Graphs -->
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

    <!-- Charts Row 3 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Priority Distribution -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Priority Distribution</h3>
                <p class="text-sm text-gray-600 mt-1">Tasks by priority level</p>
            </div>
            <div class="h-80">
                <canvas id="priorityDistributionChart"></canvas>
            </div>
        </div>

        <!-- Tasks by Day of Week -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Tasks by Day of Week</h3>
                <p class="text-sm text-gray-600 mt-1">Task creation patterns</p>
            </div>
            <div class="h-80">
                <canvas id="dayOfWeekChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 4 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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

        <!-- Fastest Task Completers -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Fastest Task Completers</h3>
                <p class="text-sm text-gray-600 mt-1">Users with fastest average completion time</p>
            </div>
            <div class="h-80">
                <canvas id="fastestCompletersChart"></canvas>
            </div>
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

    // Creation Trends Chart (Line Graph)
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

    // Completion Rate Chart (Line Graph)
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

    // Priority Distribution Chart
    const priorityCtx = document.getElementById('priorityDistributionChart');
    if (priorityCtx) {
        const priorityData = @json($priorityDistribution);
        new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(priorityData).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
                datasets: [{
                    label: 'Tasks',
                    data: Object.values(priorityData),
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',   // high - red
                        'rgba(234, 179, 8, 0.8)',   // medium - yellow
                        'rgba(16, 185, 129, 0.8)'   // low - green
                    ],
                    borderColor: [
                        'rgb(239, 68, 68)',
                        'rgb(234, 179, 8)',
                        'rgb(16, 185, 129)'
                    ],
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 2000 },
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // Tasks by Day of Week Chart
    const dayOfWeekCtx = document.getElementById('dayOfWeekChart');
    if (dayOfWeekCtx) {
        const dayData = @json($tasksByDayOfWeek);
        const dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        const orderedData = dayOrder.map(day => dayData[day] ?? 0);
        
        new Chart(dayOfWeekCtx, {
            type: 'bar',
            data: {
                labels: dayOrder,
                datasets: [{
                    label: 'Tasks Created',
                    data: orderedData,
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 2000 },
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
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
});
</script>
@endsection
