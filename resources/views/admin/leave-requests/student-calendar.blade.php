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
                    <h1 class="text-2xl sm:text-3xl font-bold">Leave Calendar</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">View student leave requests on a monthly calendar.</p>
                </div>
            </div>
            <a href="{{ url('/admin/student-leave-requests') }}"
               class="inline-flex items-center justify-center px-3 py-2 sm:px-4 sm:py-2 text-xs sm:text-sm bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200">
                <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="hidden sm:inline">Back to Leave Requests</span>
                <span class="sm:hidden">Back</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 sm:gap-4 items-start">
        <!-- Student Filter Sidebar -->
        <div class="lg:col-span-1 space-y-3 sm:space-y-4">
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-3 sm:p-4">
                <h2 class="text-xs sm:text-sm font-bold text-gray-900 mb-2">Students</h2>
                <p class="text-xs text-gray-500 mb-2 sm:mb-3">
                    Tap a name to focus on that student's leave, or choose "All Students" to view everyone.
                </p>
                <div class="space-y-1 max-h-[calc(100vh-20rem)] sm:max-h-[calc(100vh-24rem)] overflow-y-auto text-xs sm:text-sm -mx-1">
                    <a href="{{ url('/admin/student-leave-calendar?month=' . $currentMonth->format('Y-m')) }}"
                       class="flex items-center justify-between px-3 py-1.5 rounded-md mx-1 transition-colors duration-150 {{ !$selectedStudent ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span>All Students</span>
                    </a>
                    @foreach($students as $student)
                        <a href="{{ url('/admin/student-leave-calendar?month=' . $currentMonth->format('Y-m') . '&student=' . $student->id) }}"
                           class="flex items-center justify-between px-3 py-1.5 rounded-md mx-1 transition-colors duration-150 {{ $selectedStudent == $student->id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="truncate">{{ $student->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Quick Create Leave for Student(s) -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-3 sm:p-4">
                <h2 class="text-xs sm:text-sm font-bold text-gray-900 mb-2">File Leave for Student(s)</h2>
                <form action="{{ url('/admin/student-leave-requests/create-for-student') }}" method="POST" class="space-y-3">
                    @csrf

                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-gray-700">Select Student(s)</label>
                        <div class="max-h-32 overflow-y-auto border border-gray-300 rounded-md p-2 space-y-1">
                            <div class="flex items-center mb-1">
                                <input type="checkbox" id="select-all-students" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="toggleAllStudents(this)">
                                <label for="select-all-students" class="ml-2 text-xs font-semibold text-gray-700 cursor-pointer">Select All</label>
                            </div>
                            @foreach(($studentsForFiling ?? $students) as $student)
                                <div class="flex items-center">
                                    <input type="checkbox" name="student_ids[]" id="student_{{ $student->id }}" value="{{ $student->id }}" class="student-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="student_{{ $student->id }}" class="ml-2 text-xs text-gray-700 cursor-pointer">{{ $student->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Select one or more students to file leave for</p>
                    </div>

                    <div class="space-y-1">
                        <label for="student_create_type" class="block text-xs font-medium text-gray-700">Type</label>
                        <select name="type" id="student_create_type" required class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select type</option>
                            <option value="additional_time">Additional Time</option>
                            <option value="absent">Absent</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label for="student_create_start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="student_create_start_date" required class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="space-y-1">
                            <label for="student_create_end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="student_create_end_date" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="student_create_reason" class="block text-xs font-medium text-gray-700">Reason (optional)</label>
                        <textarea name="reason" id="student_create_reason" rows="3" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Add brief notes"></textarea>
                    </div>

                    <button type="submit" id="file-student-leave-btn" class="w-full inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        File Leave
                    </button>
                    <p class="text-[11px] text-gray-500">Creates a pending request that appears on the student calendar.</p>
                </form>
            </div>
        </div>

        <!-- Month Navigation + Calendar -->
        <div class="lg:col-span-3 space-y-3 sm:space-y-4">
            <!-- Month Navigation -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                <div class="flex items-center justify-between sm:justify-start space-x-2 sm:space-x-3">
                    <a href="{{ url('/admin/student-leave-calendar?month=' . $prevMonth . '&student=' . $selectedStudent) }}"
                       class="inline-flex items-center px-2.5 sm:px-3 py-1.5 border border-gray-300 rounded-md text-xs sm:text-sm text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        <span class="hidden sm:inline">Previous</span>
                        <span class="sm:hidden">Prev</span>
                    </a>
                    <span class="text-base sm:text-lg font-semibold text-gray-900 px-2 sm:px-0">
                        {{ $currentMonth->format('F Y') }}
                    </span>
                    <a href="{{ url('/admin/student-leave-calendar?month=' . $nextMonth . '&student=' . $selectedStudent) }}"
                       class="inline-flex items-center px-2.5 sm:px-3 py-1.5 border border-gray-300 rounded-md text-xs sm:text-sm text-gray-700 bg-white hover:bg-gray-50">
                        <span class="hidden sm:inline">Next</span>
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 sm:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="text-xs text-gray-500 sm:text-right">
                    <span class="hidden sm:inline">Showing leave requests that overlap this month</span>
                    <span class="sm:hidden">Showing</span>
                    @if($selectedStudent)
                        <span class="font-semibold">
                            {{ optional($students->firstWhere('id', $selectedStudent))->name ?? 'Selected Student' }}
                        </span>
                    @else
                        <span class="font-semibold">all students</span>
                    @endif
                    <span class="hidden sm:inline">.</span>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden min-h-[calc(100vh-20rem)] sm:min-h-[calc(100vh-16rem)] flex flex-col">
                <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
                    @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $weekday)
                        <div class="px-1 sm:px-2 md:px-3 py-1.5 sm:py-2 text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">
                            {{ $weekday }}
                        </div>
                    @endforeach
                </div>
                <div class="divide-y divide-gray-200 flex-1 grid grid-rows-6 overflow-x-auto">
                    @foreach($weeks as $week)
                        <div class="grid grid-cols-7 flex-1 min-w-[700px]">
                            @foreach($week as $day)
                                @php
                                    $isCurrentMonth = $day['date']->format('Y-m') === $currentMonth->format('Y-m');
                                    // Compare using Manila timezone
                                    $isToday = $day['date']->isSameDay(\Carbon\Carbon::now('Asia/Manila'));
                                @endphp
                                <div class="border-r border-b last:border-b-0 border-gray-200 px-1 sm:px-1.5 md:px-2 py-1 sm:py-1.5 md:py-2 text-[10px] sm:text-xs flex flex-col h-full
                                            {{ $isCurrentMonth ? ($isToday ? 'bg-emerald-50' : 'bg-white') : 'bg-gray-50' }}">
                                    <div class="flex items-center justify-between mb-0.5 sm:mb-1 flex-shrink-0">
                                        <span class="font-semibold text-xs sm:text-sm {{ $isCurrentMonth ? 'text-gray-900' : 'text-gray-400' }}">
                                            {{ $day['date']->format('j') }}
                                        </span>
                                        @if($isToday)
                                            <span class="inline-flex items-center px-1 sm:px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[9px] sm:text-[10px] font-semibold">
                                                <span class="hidden sm:inline">Today</span>
                                                <span class="sm:hidden">T</span>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-0.5 sm:space-y-1 flex-1 overflow-y-auto min-h-0">
                                        @php
                                            $displayed = 0;
                                            $total = count($day['requests']);
                                        @endphp
                                        @foreach($day['requests'] as $entry)
                                            @if($displayed >= 2)
                                                @break
                                            @endif
                                            @php
                                                $displayed++;
                                                $statusClass = match($entry['status']) {
                                                    'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                    'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                                    'rejected' => 'bg-red-50 text-red-700 border-red-100',
                                                    default => 'bg-gray-50 text-gray-700 border-gray-100',
                                                };
                                            @endphp
                                            <a href="{{ url('/admin/leave-requests/' . $entry['id']) }}"
                                               class="block border {{ $statusClass }} rounded px-1 sm:px-1.5 py-0.5 text-[9px] sm:text-[10px] md:text-[11px] hover:border-indigo-300 hover:bg-indigo-50/70">
                                                <div class="font-semibold truncate">
                                                    {{ $entry['student']->name }}
                                                </div>
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="truncate text-[8px] sm:text-[9px] md:text-[10px]">{{ $entry['type_label'] }}</span>
                                                    <span class="ml-0.5 sm:ml-1 text-[8px] sm:text-[9px] capitalize shrink-0">{{ substr($entry['status'], 0, 1) }}</span>
                                                </div>
                                            </a>
                                        @endforeach

                                        @if($total > $displayed)
                                            @php
                                                $requestsData = array_map(function($req) {
                                                    return [
                                                        'id' => $req['id'],
                                                        'student_name' => $req['student']->name,
                                                        'type_label' => $req['type_label'],
                                                        'status' => $req['status'],
                                                    ];
                                                }, $day['requests']);
                                            @endphp
                                            <button type="button"
                                                    onclick="showAllStudentLeaveRequests('{{ $day['date']->toDateString() }}', {{ json_encode($requestsData) }})"
                                                    class="w-full text-[9px] sm:text-[10px] md:text-[11px] text-indigo-600 hover:text-indigo-700 font-semibold hover:underline text-left">
                                                +{{ $total - $displayed }} more…
                                            </button>
                                        @endif

                                        @if($total === 0)
                                            <div class="text-[9px] sm:text-[10px] md:text-[11px] text-gray-300 italic">
                                                <span class="hidden sm:inline">No leave</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for showing all student leave requests on a date -->
<div id="student-leave-requests-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 sm:w-3/4 md:w-1/2 lg:w-2/5 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="student-modal-date-title">Leave Requests</h3>
                <button type="button" onclick="closeStudentLeaveRequestsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="student-modal-requests-list" class="space-y-2 max-h-[60vh] overflow-y-auto">
                <!-- Leave requests will be inserted here -->
            </div>
        </div>
    </div>
</div>

<script>
// Toggle all students checkbox
function toggleAllStudents(selectAllCheckbox) {
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    studentCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateFileStudentLeaveButton();
}

function updateFileStudentLeaveButton() {
    const selectedStudents = document.querySelectorAll('.student-checkbox:checked');
    const btn = document.getElementById('file-student-leave-btn');
    if (!btn) return;
    btn.disabled = selectedStudents.length === 0;
}

document.addEventListener('DOMContentLoaded', function() {
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    studentCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateFileStudentLeaveButton));
    updateFileStudentLeaveButton();
});

function showAllStudentLeaveRequests(dateString, requests) {
    const modal = document.getElementById('student-leave-requests-modal');
    const modalTitle = document.getElementById('student-modal-date-title');
    const modalList = document.getElementById('student-modal-requests-list');

    // Format date for display
    const date = new Date(dateString);
    const formattedDate = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    modalTitle.textContent = `Leave Requests - ${formattedDate}`;

    // Clear previous content
    modalList.innerHTML = '';

    if (!requests || requests.length === 0) {
        modalList.innerHTML = '<p class="text-gray-500 text-sm">No leave requests for this date.</p>';
    } else {
        requests.forEach(function(entry) {
            const statusClass = studentGetStatusClass(entry.status);
            const statusBadge = studentGetStatusBadge(entry.status);
            const showUrl = '{{ url("/admin/leave-requests/") }}' + entry.id;

            const requestDiv = document.createElement('div');
            requestDiv.className = `border ${statusClass} rounded-lg p-3 hover:shadow-md transition-shadow`;
            requestDiv.innerHTML = `
                <a href="${showUrl}" class="block">
                    <div class="font-semibold text-gray-900 text-sm mb-1">${studentEscapeHtml(entry.student_name)}</div>
                    <div class="text-xs text-gray-600 mb-2">${studentEscapeHtml(entry.type_label)}</div>
                    <div class="flex items-center gap-2">
                        ${statusBadge}
                    </div>
                </a>
            `;
            modalList.appendChild(requestDiv);
        });
    }

    modal.classList.remove('hidden');
}

function closeStudentLeaveRequestsModal() {
    document.getElementById('student-leave-requests-modal').classList.add('hidden');
}

function studentGetStatusClass(status) {
    switch(status) {
        case 'approved':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'pending':
            return 'bg-yellow-50 text-yellow-700 border-yellow-200';
        case 'rejected':
            return 'bg-red-50 text-red-700 border-red-200';
        default:
            return 'bg-gray-50 text-gray-700 border-gray-200';
    }
}

function studentGetStatusBadge(status) {
    const statusLabels = {
        'approved': { label: 'Approved', class: 'bg-emerald-100 text-emerald-800' },
        'pending': { label: 'Pending', class: 'bg-yellow-100 text-yellow-800' },
        'rejected': { label: 'Rejected', class: 'bg-red-100 text-red-800' }
    };

    const statusInfo = statusLabels[status] || { label: status, class: 'bg-gray-100 text-gray-800' };

    return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ${statusInfo.class}">
        ${studentEscapeHtml(statusInfo.label)}
    </span>`;
}

function studentEscapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

// Close modal when clicking outside
document.getElementById('student-leave-requests-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStudentLeaveRequestsModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStudentLeaveRequestsModal();
    }
});
</script>
@endsection


