@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Dashboard</h1>
                <p class="text-indigo-100 text-sm">
                    @if($schoolName)
                        {{ $schoolName }} analytics overview
                    @else
                        Assign a school to this teacher account to view students
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 p-4 flex-shrink-0">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-sm text-gray-500">Total Students</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $totalStudents }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-sm text-gray-500">Active Students</p>
            <p class="text-2xl font-semibold text-green-700">{{ $activeStudents }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-sm text-gray-500">Ongoing Internships</p>
            <p class="text-2xl font-semibold text-indigo-700">{{ $ongoingInternships }}</p>
        </div>
    </div>

    <div class="bg-white border-t border-gray-200 flex-1 min-h-0 overflow-auto p-4">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Student Status</h3>
                <div class="h-64">
                    <canvas id="studentStatusChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Internship Progress</h3>
                <div class="h-64">
                    <canvas id="internshipStatusChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm xl:col-span-1">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Monthly Logged Hours</h3>
                <div class="h-64">
                    <canvas id="monthlyHoursChart"></canvas>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-900">
            Use <span class="font-semibold">My Students</span> in the sidebar to view the full student list from your school.
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="application/json" id="teacher-charts-data">{!! json_encode($teacherCharts ?? [
    'studentStatus' => ['labels' => [], 'values' => []],
    'internshipStatus' => ['labels' => [], 'values' => []],
    'monthlyHours' => ['labels' => [], 'values' => []],
]) !!}</script>
<script>
    (function () {
        const teacherChartsNode = document.getElementById('teacher-charts-data');
        const teacherCharts = teacherChartsNode ? JSON.parse(teacherChartsNode.textContent) : {
            studentStatus: { labels: [], values: [] },
            internshipStatus: { labels: [], values: [] },
            monthlyHours: { labels: [], values: [] },
        };

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
            },
        };

        const studentCtx = document.getElementById('studentStatusChart');
        if (studentCtx) {
            new Chart(studentCtx, {
                type: 'doughnut',
                data: {
                    labels: teacherCharts.studentStatus.labels,
                    datasets: [{
                        data: teacherCharts.studentStatus.values,
                        backgroundColor: ['#10B981', '#9CA3AF'],
                    }],
                },
                options: commonOptions,
            });
        }

        const internshipCtx = document.getElementById('internshipStatusChart');
        if (internshipCtx) {
            new Chart(internshipCtx, {
                type: 'pie',
                data: {
                    labels: teacherCharts.internshipStatus.labels,
                    datasets: [{
                        data: teacherCharts.internshipStatus.values,
                        backgroundColor: ['#4F46E5', '#A78BFA'],
                    }],
                },
                options: commonOptions,
            });
        }

        const hoursCtx = document.getElementById('monthlyHoursChart');
        if (hoursCtx) {
            new Chart(hoursCtx, {
                type: 'line',
                data: {
                    labels: teacherCharts.monthlyHours.labels,
                    datasets: [{
                        label: 'Total Hours',
                        data: teacherCharts.monthlyHours.values,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        fill: true,
                        tension: 0.3,
                    }],
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                        },
                    },
                },
            });
        }
    })();
</script>
@endsection
