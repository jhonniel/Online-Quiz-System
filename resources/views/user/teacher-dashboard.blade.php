@extends('layouts.user')

@section('page-title', 'Dashboard')

@section('content')
<div class="h-full flex flex-col min-h-0 min-w-0">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-3 sm:p-4 flex-shrink-0">
        <div class="flex items-start sm:items-center gap-2 min-w-0">
            <div class="flex-shrink-0 mt-0.5 sm:mt-0">
                <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14z"></path>
                </svg>
            </div>
            <div class="ml-0 sm:ml-3 min-w-0 flex-1">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white break-words">Dashboard</h1>
                <p class="text-indigo-100 text-sm mt-0.5 break-words leading-snug">
                    @if($schoolName)
                        {{ $schoolName }} analytics overview
                    @else
                        Assign a school to this teacher account to view students
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-2 sm:gap-3 px-3 sm:px-4 py-3 sm:py-4 flex-shrink-0">
        <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm min-w-0">
            <p class="text-xs sm:text-sm text-gray-500 leading-tight">Total Students</p>
            <p class="text-xl sm:text-2xl font-semibold text-gray-900 tabular-nums mt-1">{{ $totalStudents }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm min-w-0">
            <p class="text-xs sm:text-sm text-gray-500 leading-tight">Ongoing Internships</p>
            <p class="text-xl sm:text-2xl font-semibold text-indigo-700 tabular-nums mt-1">{{ $ongoingInternships }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm min-w-0">
            <p class="text-xs sm:text-sm text-gray-500 leading-tight">Completed Internships</p>
            <p class="text-xl sm:text-2xl font-semibold text-emerald-700 tabular-nums mt-1">{{ $completedInternships ?? 0 }}</p>
        </div>
        <a
            href="{{ url('/teacher/pending-applications') }}"
            class="block bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm min-w-0 no-underline text-inherit transition-shadow hover:shadow-md hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <p class="text-xs sm:text-sm text-gray-500 leading-tight">Student Application</p>
            <p class="text-xl sm:text-2xl font-semibold text-amber-700 tabular-nums mt-1">{{ $pendingApplicationsCount ?? 0 }}</p>
        </a>
        <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm min-w-0">
            <p class="text-xs sm:text-sm text-gray-500 leading-tight">Exit conference day</p>
            <p class="text-lg sm:text-2xl font-semibold tabular-nums text-slate-800 mt-1 break-words leading-tight">
                @if(! empty($nextExitConferenceDate))
                    {{ $nextExitConferenceDate->format('M d, Y') }}
                @else
                    <span class="text-gray-400">—</span>
                @endif
            </p>
        </div>
    </div>

    <div class="bg-white border-t border-gray-200 flex-1 min-h-0 overflow-auto min-w-0 p-3 sm:p-4">
        <div class="grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-4 gap-3 sm:gap-4 mb-4">
            <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm min-w-0">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Student Status</h3>
                <div class="h-52 sm:h-64 min-h-[13rem]">
                    <canvas id="studentStatusChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm min-w-0">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Internship Progress</h3>
                <div class="h-52 sm:h-64 min-h-[13rem]">
                    <canvas id="internshipStatusChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm xl:col-span-1 min-w-0">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Monthly Logged Hours</h3>
                <div class="h-52 sm:h-64 min-h-[13rem]">
                    <canvas id="monthlyHoursChart"></canvas>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4 shadow-sm xl:col-span-2 2xl:col-span-1 min-w-0">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Internship Hours Summary</h3>
                <div class="h-52 sm:h-64 min-h-[13rem]">
                    <canvas id="internshipHoursSummaryChart"></canvas>
                </div>
            </div>
        </div>

        @php
            $absentRanking = $studentsApprovedAbsentRanking ?? collect();
        @endphp
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden mb-4 min-w-0 w-full max-w-full">
            <div class="px-3 sm:px-4 py-3 border-b border-gray-100 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 break-words">Student absent days</h3>
                </div>
                @if($schoolName)
                    <a href="{{ url('/teacher/students') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 shrink-0 inline-flex py-1">Open My Students →</a>
                @endif
            </div>
            @if($absentRanking->isEmpty())
                <div class="px-4 py-8 text-center text-sm text-gray-500">
                    @if($schoolName)
                        No students are linked to your school yet.
                    @else
                        Assign a school to this teacher account to see student absence rankings.
                    @endif
                </div>
            @else
                <div class="w-full max-w-full overflow-x-auto max-h-[26rem] overflow-y-auto" style="-webkit-overflow-scrolling: touch;">
                    <table class="w-full border-collapse divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-14">#</th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell whitespace-nowrap">Status</th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell whitespace-nowrap">Est. end date</th>
                                <th scope="col" class="px-4 py-2.5 text-right text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap w-32 lg:w-40">Absent days count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($absentRanking as $studentRow)
                                @php
                                    $count = (int) ($studentRow->approved_absent_days ?? 0);
                                    $requiredH = (float) ($studentRow->required_training_hours ?? 0);
                                    $remainingH = (float) ($studentRow->remaining_hours ?? 0);
                                @endphp
                                <tr class="hover:bg-gray-50/80">
                                    <td class="px-4 py-2.5 text-sm tabular-nums text-gray-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2.5 text-sm min-w-0">
                                        <span class="font-medium text-gray-900 break-words">{{ $studentRow->name }}</span>
                                        <span class="block text-xs text-gray-500 break-all">{{ $studentRow->email }}</span>
                                        <span class="lg:hidden block text-[11px] text-gray-600 mt-1">
                                            <span class="text-gray-500">Est. end:</span>
                                            @if($remainingH <= 0 && $requiredH > 0)
                                                Completed
                                            @elseif(! empty($studentRow->estimated_end_date))
                                                {{ \Carbon\Carbon::parse($studentRow->estimated_end_date)->format('M d, Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-sm hidden sm:table-cell whitespace-nowrap">
                                        @if($studentRow->is_active)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-sm text-gray-700 hidden lg:table-cell whitespace-nowrap">
                                        @if($remainingH <= 0 && $requiredH > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                        @elseif(! empty($studentRow->estimated_end_date))
                                            {{ \Carbon\Carbon::parse($studentRow->estimated_end_date)->format('M d, Y') }}
                                        @else
                                            <span class="text-gray-400" title="Needs remaining hours and recent DTR activity to project a date">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-sm text-right">
                                        @if($count > 0)
                                            <span class="inline-flex min-w-[2rem] justify-end tabular-nums font-semibold text-gray-900">{{ $count }}</span>
                                        @else
                                            <span class="tabular-nums text-gray-400">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-3 sm:p-4 text-sm text-indigo-900 break-words">
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
    'internshipHoursSummary' => ['labels' => [], 'values' => []],
]) !!}</script>
<script>
    (function () {
        const teacherChartsNode = document.getElementById('teacher-charts-data');
        const teacherCharts = teacherChartsNode ? JSON.parse(teacherChartsNode.textContent) : {
            studentStatus: { labels: [], values: [] },
            internshipStatus: { labels: [], values: [] },
            monthlyHours: { labels: [], values: [] },
            internshipHoursSummary: { labels: [], values: [] },
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

        const internshipHoursSummaryCtx = document.getElementById('internshipHoursSummaryChart');
        if (internshipHoursSummaryCtx) {
            new Chart(internshipHoursSummaryCtx, {
                type: 'bar',
                data: {
                    labels: teacherCharts.internshipHoursSummary.labels,
                    datasets: [{
                        label: 'Hours',
                        data: teacherCharts.internshipHoursSummary.values,
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b'],
                        borderRadius: 6,
                    }],
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        ...commonOptions.plugins,
                        legend: { display: false },
                    },
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
