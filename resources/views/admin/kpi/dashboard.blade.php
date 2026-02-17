@extends('layouts.admin')

@section('title', 'Key Performance Indicator Dashboard')
@section('page-title', 'KPI Dashboard')

@section('content')
<div class="space-y-4 sm:space-y-6 px-2 sm:px-0">
    <!-- Page Header -->
    <div class="px-4 py-6 sm:px-6 sm:py-8 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 shadow-lg rounded-2xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-medium text-indigo-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span>
                    Performance Analytics
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                    Key Performance Indicator Dashboard
                </h1>
                <p class="mt-1 text-sm sm:text-base text-indigo-100/90 max-w-2xl">
                    Track employee and student performance metrics including attendance, work hours, and best performers.
                </p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ url('/admin/kpi/dashboard') }}" class="space-y-4">
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


                <!-- Department Filter -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select name="department_id" id="department_id" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
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
                <a href="{{ url('/admin/kpi/dashboard') }}" class="inline-flex items-center px-4 sm:px-6 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalUsers }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Perfect Attendance</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $perfectAttendanceCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Avg Performance</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($avgPerformanceScore, 1) }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-200 p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Hours</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ sprintf('%02d:%02d', intdiv((int) round($totalHoursAll * 60), 60), (int) round($totalHoursAll * 60) % 60) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 10 Performers Chart -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Top 10 Performers</h3>
                <p class="text-sm text-gray-600 mt-1">Highest performance scores</p>
            </div>
            <div class="h-80">
                <canvas id="topPerformersChart"></canvas>
            </div>
        </div>

        <!-- Performance Score Distribution -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Performance Score Distribution</h3>
                <p class="text-sm text-gray-600 mt-1">Distribution across score ranges</p>
            </div>
            <div class="h-80">
                <canvas id="scoreDistributionChart"></canvas>
            </div>
        </div>

        <!-- Department Performance Comparison -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Department Performance</h3>
                <p class="text-sm text-gray-600 mt-1">Average performance by department</p>
            </div>
            <div class="h-80">
                <canvas id="departmentPerformanceChart"></canvas>
            </div>
        </div>

        <!-- Attendance Breakdown -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Attendance Breakdown</h3>
                <p class="text-sm text-gray-600 mt-1">Overall attendance statistics</p>
            </div>
            <div class="h-80">
                <canvas id="attendanceBreakdownChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Additional Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Line Graph - Performance Trends Over Time -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Performance Trends</h3>
                <p class="text-sm text-gray-600 mt-1">Daily performance score over time</p>
            </div>
            <div class="h-80">
                <canvas id="performanceTrendsChart"></canvas>
            </div>
        </div>

        <!-- Stacked Area Chart - Attendance Breakdown Over Time -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Attendance Trends</h3>
                <p class="text-sm text-gray-600 mt-1">Daily attendance breakdown over time</p>
            </div>
            <div class="h-80">
                <canvas id="stackedAreaChart"></canvas>
            </div>
        </div>

        <!-- Radar Chart - Performance Comparison -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Performance Comparison</h3>
                <p class="text-sm text-gray-600 mt-1">Top performer vs average across metrics</p>
            </div>
            <div class="h-80">
                <canvas id="radarChart"></canvas>
            </div>
        </div>

        <!-- Gauge Chart - Overall Performance Score -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-4 sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Overall Performance</h3>
                <p class="text-sm text-gray-600 mt-1">Average performance score gauge</p>
            </div>
            <div class="h-80 flex items-center justify-center">
                <canvas id="gaugeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Performance Ranking</h2>
                    <p class="text-sm text-gray-600 mt-1">Sorted by performance score (highest first)</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Performance Score</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($performanceData as $index => $data)
                        @php
                            $score = $data['performance_score'];
                            $scoreColor = $score >= 90 ? 'text-emerald-600' : ($score >= 70 ? 'text-yellow-600' : 'text-red-600');
                            $scoreBg = $score >= 90 ? 'bg-emerald-50 border-emerald-200' : ($score >= 70 ? 'bg-yellow-50 border-yellow-200' : 'bg-red-50 border-red-200');
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-700">
                                    #{{ $index + 1 }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $data['user']->name }}</div>
                                <div class="text-sm text-gray-500">{{ $data['user']->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $data['user']->department->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-sm text-gray-900">
                                    <div>Present: <span class="font-semibold text-emerald-600">{{ $data['present_count'] }}</span></div>
                                    <div>Late: <span class="font-semibold text-yellow-600">{{ $data['late_count'] }}</span></div>
                                    <div>Absent: <span class="font-semibold text-red-600">{{ $data['absent_count'] }}</span></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                {{ sprintf('%02d:%02d', intdiv((int) round($data['total_hours'] * 60), 60), (int) round($data['total_hours'] * 60) % 60) }}
                                <div class="text-xs text-gray-500 mt-1">{{ $data['working_days'] }} days</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-semibold {{ $scoreBg }} {{ $scoreColor }}">
                                    {{ number_format($data['performance_score'], 1) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($data['has_perfect_attendance'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        Perfect
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Good
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                No performance data found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Top 10 Performers Chart
    const topPerformersCtx = document.getElementById('topPerformersChart');
    if (topPerformersCtx) {
        const topPerformersData = @json($topPerformers);
        new Chart(topPerformersCtx, {
            type: 'bar',
            data: {
                labels: topPerformersData.map(p => p.name.length > 15 ? p.name.substring(0, 15) + '...' : p.name),
                datasets: [{
                    label: 'Performance Score (%)',
                    data: topPerformersData.map(p => p.score),
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart',
                    delay: function(context) {
                        return context.dataIndex * 50;
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Score: ' + context.parsed.x + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Performance Score Distribution Chart
    const scoreDistributionCtx = document.getElementById('scoreDistributionChart');
    if (scoreDistributionCtx) {
        const scoreRanges = @json($scoreRanges);
        new Chart(scoreDistributionCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(scoreRanges),
                datasets: [{
                    label: 'Number of Employees',
                    data: Object.values(scoreRanges),
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',  // emerald for 90-100
                        'rgba(34, 197, 94, 0.8)',   // green for 80-89
                        'rgba(234, 179, 8, 0.8)',   // yellow for 70-79
                        'rgba(251, 146, 60, 0.8)',  // orange for 60-69
                        'rgba(239, 68, 68, 0.8)'    // red for 0-59
                    ],
                    borderColor: [
                        'rgb(16, 185, 129)',
                        'rgb(34, 197, 94)',
                        'rgb(234, 179, 8)',
                        'rgb(251, 146, 60)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart',
                    delay: function(context) {
                        return context.dataIndex * 80;
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
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' employee(s)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Department Performance Chart
    const departmentPerformanceCtx = document.getElementById('departmentPerformanceChart');
    if (departmentPerformanceCtx) {
        const departmentAvg = @json($departmentAvg);
        const deptNames = Object.keys(departmentAvg);
        const deptScores = Object.values(departmentAvg);
        
        new Chart(departmentPerformanceCtx, {
            type: 'bar',
            data: {
                labels: deptNames.map(name => name.length > 20 ? name.substring(0, 20) + '...' : name),
                datasets: [{
                    label: 'Average Performance Score (%)',
                    data: deptScores,
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                    borderColor: 'rgb(139, 92, 246)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart',
                    delay: function(context) {
                        return context.dataIndex * 100;
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Avg Score: ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Attendance Breakdown Chart
    const attendanceBreakdownCtx = document.getElementById('attendanceBreakdownChart');
    if (attendanceBreakdownCtx) {
        const attendanceBreakdown = @json($attendanceBreakdown);
        new Chart(attendanceBreakdownCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Completed', 'Late', 'Under Time', 'Absent'],
                datasets: [{
                    data: [
                        attendanceBreakdown.present,
                        attendanceBreakdown.completed,
                        attendanceBreakdown.late,
                        attendanceBreakdown.under_time,
                        attendanceBreakdown.absent
                    ],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',  // emerald for present
                        'rgba(34, 197, 94, 0.8)',   // green for completed
                        'rgba(234, 179, 8, 0.8)',   // yellow for late
                        'rgba(251, 146, 60, 0.8)',  // orange for under time
                        'rgba(239, 68, 68, 0.8)'    // red for absent
                    ],
                    borderColor: [
                        'rgb(16, 185, 129)',
                        'rgb(34, 197, 94)',
                        'rgb(234, 179, 8)',
                        'rgb(251, 146, 60)',
                        'rgb(239, 68, 68)'
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
                    animateRotate: true,
                    animateScale: true
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Line Graph - Performance Trends Over Time
    const performanceTrendsCtx = document.getElementById('performanceTrendsChart');
    if (performanceTrendsCtx) {
        const dailyPerformance = @json($dailyPerformance);
        const dates = Object.keys(dailyPerformance).map(key => dailyPerformance[key].date);
        const scores = Object.keys(dailyPerformance).map(key => dailyPerformance[key].performance_score);
        const avgHours = Object.keys(dailyPerformance).map(key => dailyPerformance[key].avg_hours);

        new Chart(performanceTrendsCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Performance Score (%)',
                        data: scores,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Avg Hours',
                        data: avgHours,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
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
                        return context.dataIndex * 30;
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Performance Score (%)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Average Hours'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }

    // Stacked Area Chart - Attendance Breakdown Over Time
    const stackedAreaCtx = document.getElementById('stackedAreaChart');
    if (stackedAreaCtx) {
        const stackedAreaData = @json($stackedAreaData);
        const dates = stackedAreaData.map(d => d.date);
        
        new Chart(stackedAreaCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: 'Present',
                        data: stackedAreaData.map(d => d.present),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.6)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Completed',
                        data: stackedAreaData.map(d => d.completed),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.6)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Late',
                        data: stackedAreaData.map(d => d.late),
                        borderColor: 'rgb(234, 179, 8)',
                        backgroundColor: 'rgba(234, 179, 8, 0.6)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Under Time',
                        data: stackedAreaData.map(d => d.under_time),
                        borderColor: 'rgb(251, 146, 60)',
                        backgroundColor: 'rgba(251, 146, 60, 0.6)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Absent',
                        data: stackedAreaData.map(d => d.absent),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.6)',
                        tension: 0.4,
                        fill: true
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
                        return context.dataIndex * 20;
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    x: {
                        stacked: false
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }

    // Radar Chart - Performance Comparison
    const radarCtx = document.getElementById('radarChart');
    if (radarCtx) {
        const normalizedAvgMetrics = @json($normalizedAvgMetrics);
        const normalizedTopMetrics = @json($normalizedTopMetrics);
        
        new Chart(radarCtx, {
            type: 'radar',
            data: {
                labels: Object.keys(normalizedAvgMetrics),
                datasets: [
                    {
                        label: 'Average Performance',
                        data: Object.values(normalizedAvgMetrics),
                        borderColor: 'rgb(139, 92, 246)',
                        backgroundColor: 'rgba(139, 92, 246, 0.2)',
                        pointBackgroundColor: 'rgb(139, 92, 246)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(139, 92, 246)'
                    },
                    {
                        label: 'Top Performer',
                        data: Object.values(normalizedTopMetrics),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        pointBackgroundColor: 'rgb(239, 68, 68)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(239, 68, 68)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart',
                    animateRotate: true,
                    animateScale: true
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    }

    // Gauge Chart - Overall Performance Score
    const gaugeCtx = document.getElementById('gaugeChart');
    if (gaugeCtx) {
        const avgScore = {{ $avgPerformanceScore }};
        
        // Create color-coded segments for the gauge
        const segmentData = [];
        const segmentColors = [];
        
        // Define segments: 0-30 (red), 30-50 (orange), 50-70 (yellow), 70-90 (light green), 90-100 (green)
        const segments = [
            { start: 0, end: 30, color: 'rgb(239, 68, 68)' },
            { start: 30, end: 50, color: 'rgb(251, 146, 60)' },
            { start: 50, end: 70, color: 'rgb(234, 179, 8)' },
            { start: 70, end: 90, color: 'rgb(34, 197, 94)' },
            { start: 90, end: 100, color: 'rgb(16, 185, 129)' }
        ];
        
        // Build segments up to the current score
        segments.forEach(segment => {
            if (avgScore > segment.start) {
                const segmentValue = Math.min(segment.end - segment.start, avgScore - segment.start);
                segmentData.push(segmentValue);
                segmentColors.push(segment.color);
            } else {
                segmentData.push(0);
                segmentColors.push('rgb(229, 231, 235)');
            }
        });
        
        // Add remaining as gray
        const filled = segmentData.reduce((a, b) => a + b, 0);
        const remaining = 100 - filled;
        if (remaining > 0) {
            segmentData.push(remaining);
            segmentColors.push('rgb(229, 231, 235)');
        }
        
        // Create gauge chart using Chart.js with custom plugin
        const gaugeChart = new Chart(gaugeCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: segmentData,
                    backgroundColor: segmentColors,
                    borderWidth: 0,
                    circumference: 180,
                    rotation: 270
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart',
                    animateRotate: true,
                    animateScale: true
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            },
            plugins: [{
                id: 'gaugeCenter',
                afterDraw: function(chart) {
                    const ctx = chart.ctx;
                    const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
                    const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2 + 50;
                    
                    // Determine text color based on score
                    let textColor = 'rgb(239, 68, 68)'; // red
                    if (avgScore >= 90) {
                        textColor = 'rgb(16, 185, 129)'; // green
                    } else if (avgScore >= 70) {
                        textColor = 'rgb(34, 197, 94)'; // light green
                    } else if (avgScore >= 50) {
                        textColor = 'rgb(234, 179, 8)'; // yellow
                    } else if (avgScore >= 30) {
                        textColor = 'rgb(251, 146, 60)'; // orange
                    }
                    
                    ctx.save();
                    ctx.font = 'bold 32px Arial';
                    ctx.fillStyle = textColor;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(avgScore.toFixed(1) + '%', centerX, centerY);
                    
                    ctx.font = '14px Arial';
                    ctx.fillStyle = '#6B7280';
                    ctx.fillText('Performance Score', centerX, centerY + 30);
                    ctx.restore();
                }
            }]
        });
    }
});
</script>
@endsection
