@extends('layouts.admin')

@section('content')
@php
    $showDepartmentColumn = auth()->user()->isAdmin();
    $showMeritModalProfileLink = auth()->user()->isAdmin();
    $canManageMeritAutomation = auth()->user()->isAdmin();
@endphp
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-white">Students</h1>
                    <p class="text-indigo-100">List of all students and their internship duration</p>
                </div>
            </div>
            <a href="{{ url('/admin/student-management/dashboard') }}" class="inline-flex items-center px-4 py-2 border border-white border-opacity-20 rounded-md text-sm font-medium text-white hover:bg-white hover:bg-opacity-10">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Students Analytics -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-7 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Students</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($statsTotalStudents ?? 0) }}</p>
            <p class="mt-1 text-xs text-gray-500">Current filtered scope</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">With Logged Time</p>
            <p class="mt-2 text-2xl font-bold text-indigo-700">{{ number_format($statsWithLoggedTime ?? 0) }}</p>
            <p class="mt-1 text-xs text-gray-500">Students with DTR hours</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Completed</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($statsCompleted ?? 0) }}</p>
            <p class="mt-1 text-xs text-gray-500">Reached required hours</p>
        </div>
        @php
            $ojtTotalSlots = (int) ($statsOjtTotalSlots ?? 0);
            $ojtUsedSlots = (int) ($statsOjtUsedSlots ?? 0);
            $ojtAvailableSlots = (int) ($statsOjtAvailableSlots ?? 0);
            $ojtOverSlots = (int) ($statsOjtOverSlots ?? 0);
            $ojtOverCapacity = $ojtTotalSlots > 0 && $ojtUsedSlots > $ojtTotalSlots;
            $ojtCardClass = $ojtOverCapacity
                ? 'bg-rose-50 border-rose-200'
                : 'bg-white border-gray-200';
            $ojtTitleClass = $ojtOverCapacity ? 'text-rose-700' : 'text-gray-500';
            $ojtValueClass = $ojtOverCapacity ? 'text-rose-700' : 'text-gray-900';
            $ojtIconClass = $ojtOverCapacity ? 'text-rose-600' : 'text-indigo-600';
        @endphp
        <div class="border rounded-lg p-4 shadow-sm {{ $ojtCardClass }}">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 {{ $ojtIconClass }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    @if($ojtTotalSlots > 0)
                        <p class="text-2xl font-bold {{ $ojtValueClass }}">
                            {{ number_format($ojtUsedSlots) }} / {{ number_format($ojtTotalSlots) }}
                        </p>
                        <p class="text-xs font-semibold uppercase tracking-wide mt-1 {{ $ojtTitleClass }}">OJT Slots Used (Ongoing)</p>
                        @if($ojtOverCapacity)
                            <p class="mt-1 text-xs text-rose-700">Over capacity by {{ number_format($ojtOverSlots) }} slot{{ $ojtOverSlots === 1 ? '' : 's' }}</p>
                        @else
                            <p class="mt-1 text-xs font-medium text-emerald-700">{{ number_format($ojtAvailableSlots) }} slot{{ $ojtAvailableSlots === 1 ? '' : 's' }} available</p>
                        @endif
                    @else
                        <p class="text-2xl font-bold {{ $ojtValueClass }}">{{ number_format($ojtUsedSlots) }}</p>
                        <p class="text-xs font-semibold uppercase tracking-wide mt-1 {{ $ojtTitleClass }}">Ongoing Students</p>
                        <p class="mt-1 text-xs text-gray-500">
                            <a href="{{ url('/admin/settings') }}" class="text-indigo-600 hover:text-indigo-800 underline">Set total OJT slots</a> in Admin Settings
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Average Completion</p>
            <p class="mt-2 text-2xl font-bold text-purple-700">{{ number_format((float) ($statsAvgCompletion ?? 0), 1) }}%</p>
            <p class="mt-1 text-xs text-gray-500">Across visible students</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved Leave Requests</p>
            <p class="mt-2 text-2xl font-bold text-indigo-700">{{ number_format((int) ($statsTotalApprovedLeaveRequests ?? 0)) }}</p>
            <p class="mt-1 text-xs text-gray-500">Total approved in current scope</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Most Leave Requests</p>
            @if(!empty($statsTopLeaveRequester))
                <p class="mt-2 text-lg font-bold text-gray-900 truncate" title="{{ $statsTopLeaveRequester['name'] }}">{{ $statsTopLeaveRequester['name'] }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ number_format((int) $statsTopLeaveRequester['count']) }} approved requests</p>
            @else
                <p class="mt-2 text-base font-semibold text-gray-600">No approved requests yet</p>
                <p class="mt-1 text-xs text-gray-500">Will show top student here</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900">Top Schools (Student Count)</h3>
            <div class="mt-3 h-64">
                <canvas id="topSchoolsBarChart"></canvas>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900">Remaining Hours Distribution</h3>
            <div class="mt-3 h-64">
                <canvas id="remainingHoursBarChart"></canvas>
            </div>
            <p class="mt-3 text-xs text-gray-500">Includes students who are done and those still ongoing.</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">Exit conference — closest to today (by school)</h3>
            <p class="mt-1 text-xs text-gray-500">
                Only schools with at least one student still completing OJT (required hours not yet fully logged). For each school, the student shown is the ongoing one whose exit-conference date is nearest to today
                (admin-set OJT target if present; otherwise estimated from required hours and first DTR date, matching the student dashboard).
                Rows are sorted by exit date (earliest first). Uses <strong>all students in scope</strong>, not the search box below.
            </p>
        </div>
        @include('admin.student-management.partials.exit-conference-closest-by-school-table', ['exitConferenceClosestBySchool' => $exitConferenceClosestBySchool ?? []])
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4">
        <form method="GET" action="{{ url('/admin/student-management/students') }}" class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full sm:w-auto">
                <div class="flex-1 min-w-[240px] max-w-md">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search', $search ?? '') }}"
                               placeholder="{{ $showDepartmentColumn ? 'Search students (name, email, university, department, ID)...' : 'Search students (name, email, university, ID)...' }}"
                               autocomplete="off"
                               class="block w-full pl-9 pr-10 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @if(request('search'))
                            <a href="{{ url('/admin/student-management/students?' . http_build_query(array_filter(['per_page' => request('per_page')]))) }}"
                               class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                               title="Clear search">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <label for="per_page" class="text-sm font-medium text-gray-700">Show:</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-sm text-gray-500">per page</span>
                </div>
            </div>

            <div class="text-sm text-gray-500">
                Showing {{ $students->firstItem() ?? 0 }}-{{ $students->lastItem() ?? 0 }} of {{ $students->total() }} students
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School / University</th>
                        @if($showDepartmentColumn)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" title="Total merits. Click for breakdown. Sub-line shows under-time + excess absences + manual (e.g. 2+1+0).">Merits</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Internship Started</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Internship Ended</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($students as $student)
                        @php
                            $required = (float) ($student->required_training_hours ?? 0);
                            $total = (float) ($student->internship_total_hours ?? 0);
                            $started = $student->internship_start ? \Illuminate\Support\Carbon::parse($student->internship_start) : null;
                            $last = $student->internship_last ? \Illuminate\Support\Carbon::parse($student->internship_last) : null;
                            $ended = ($required > 0 && $total >= $required) ? $last : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-medium text-sm">{{ substr($student->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $student->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ optional($student->university)->name ?? '—' }}
                            </td>
                            @if($showDepartmentColumn)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ optional($student->department)->name ?? '—' }}
                                </td>
                            @endif
                            @php
                                $meritBreakdown = $violationBreakdowns[$student->id] ?? ['undertime' => 0, 'excess_absence' => 0, 'manual' => 0, 'total' => 0];
                                $meritsCount = (int) ($meritBreakdown['total'] ?? 0);
                                $meritTitle = $meritsCount > 0
                                    ? sprintf(
                                        'Total %d = under-time %d + excess absences %d + manual %d',
                                        $meritsCount,
                                        (int) ($meritBreakdown['undertime'] ?? 0),
                                        (int) ($meritBreakdown['excess_absence'] ?? 0),
                                        (int) ($meritBreakdown['manual'] ?? 0)
                                    )
                                    : 'No merits on record';
                            @endphp
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <button type="button"
                                        class="merit-details-trigger inline-flex flex-col items-center rounded-md px-2 py-1 -mx-2 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1 {{ $meritsCount > 0 ? 'font-semibold text-amber-700 hover:bg-amber-50 hover:text-amber-900' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700' }}"
                                        data-student-id="{{ $student->id }}"
                                        data-merits-url="{{ route('admin.student-management.students.merits', $student) }}"
                                        title="{{ $meritTitle }} — click for full details">
                                    <span class="tabular-nums">{{ number_format($meritsCount) }}</span>
                                    @if($meritsCount > 0)
                                        <span class="block text-[10px] font-normal leading-tight text-amber-800/90 mt-0.5 tabular-nums">
                                            {{ (int) $meritBreakdown['undertime'] }}+{{ (int) $meritBreakdown['excess_absence'] }}+{{ (int) $meritBreakdown['manual'] }}
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $started ? $started->format('M j, Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($ended)
                                    <span class="text-gray-700">{{ $ended->format('M j, Y') }}</span>
                                @elseif($started)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Ongoing</span>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-semibold">
                                {{ number_format($total, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700">
                                {{ number_format($required, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($showDepartmentColumn ? 7 : 6) + 1 }}" class="px-6 py-12 text-center text-sm text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Merit details modal --}}
<div id="meritDetailsModal" class="fixed inset-0 z-50 hidden" aria-hidden="true" role="dialog" aria-labelledby="meritDetailsModalTitle" data-show-profile-link="{{ $showMeritModalProfileLink ? '1' : '0' }}" data-can-edit-automation="{{ $canManageMeritAutomation ? '1' : '0' }}">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" data-merit-modal-dismiss></div>
    <div class="fixed inset-0 flex items-start justify-center p-4 sm:p-6 overflow-y-auto pointer-events-none">
        <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-xl border border-gray-200 pointer-events-auto my-8">
            <div class="flex items-start justify-between gap-4 px-5 py-4 border-b border-gray-100 bg-amber-50/80 rounded-t-xl">
                <div class="min-w-0">
                    <h2 id="meritDetailsModalTitle" class="text-lg font-semibold text-gray-900 truncate">Merit details</h2>
                    <p id="meritDetailsModalSubtitle" class="text-sm text-gray-600 mt-0.5 truncate"></p>
                </div>
                <button type="button" class="shrink-0 rounded-lg p-2 text-gray-500 hover:bg-white hover:text-gray-700" data-merit-modal-dismiss aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="meritDetailsModalBody" class="px-5 py-4 max-h-[min(70vh,640px)] overflow-y-auto text-sm text-gray-700">
                <div id="meritDetailsContent">
                    <p class="text-gray-500">Loading…</p>
                </div>
                <div id="meritDetailsManageSection" class="hidden mt-6 pt-5 border-t-2 border-amber-200">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">Manage merits &amp; notices</h3>
                    <p class="text-xs text-gray-500 mb-4">Update manual merits and student rules notices without leaving this view.</p>
                    <form id="meritDetailsForm" class="space-y-4 rounded-xl border border-amber-200 bg-amber-50/40 p-4">
                        <div id="meritModalManualWrap" class="hidden">
                            <label for="merit_modal_manual_merits" class="block text-sm font-semibold text-gray-700 mb-1">Manual merit count</label>
                            <input type="number" name="student_manual_merits" id="merit_modal_manual_merits" min="0" max="9999" step="1"
                                   class="block w-full max-w-xs rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 bg-white">
                            <p class="mt-1 text-xs text-gray-500">Added on top of automatic merits (admin only).</p>
                        </div>
                        <div class="space-y-3">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="student_rules_warning" id="merit_modal_rules_warning" value="1"
                                       class="mt-1 h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
                                <span class="text-sm text-gray-700">
                                    <span class="font-semibold text-gray-900">Rules violation warning</span><br>
                                    <span class="text-xs text-gray-500">Yellow scrolling banner and rules modal for the student.</span>
                                </span>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="student_rules_marquee_enabled" id="merit_modal_final_notice" value="1"
                                       class="mt-1 h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
                                <span class="text-sm text-gray-700">
                                    <span class="font-semibold text-gray-900">Enable final notice (scrolling banner)</span><br>
                                    <span class="text-xs text-gray-500">Red final notice banner. Only one notice type at a time.</span>
                                </span>
                            </label>
                        </div>
                        <div>
                            <label for="merit_modal_notice_message" class="block text-sm font-semibold text-gray-700 mb-1">Notice message</label>
                            <textarea name="student_rules_notice_message" id="merit_modal_notice_message" rows="3"
                                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white"
                                      placeholder="Required when a notice is enabled. Shown in the student’s scrolling banner."></textarea>
                        </div>
                        @if($canManageMeritAutomation)
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="student_rules_allow_merit_automation" id="merit_modal_allow_automation" value="1"
                                       class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <span class="text-sm text-gray-700">
                                    <span class="font-semibold text-gray-900">Allow automatic merit-based notices</span><br>
                                    <span class="text-xs text-gray-500">When unchecked, merit totals will not auto-enable or change notices for this student.</span>
                                </span>
                            </label>
                        @endif
                        <p id="meritDetailsFormError" class="hidden text-sm text-red-600 rounded-lg border border-red-200 bg-red-50 px-3 py-2"></p>
                    </form>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 flex flex-wrap items-center justify-end gap-3 rounded-b-xl bg-gray-50/80">
                @if($showMeritModalProfileLink)
                    <a id="meritDetailsEditLink" href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hidden">Open student profile</a>
                @endif
                <button type="button" id="meritDetailsSaveBtn" class="hidden inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                    Save changes
                </button>
                <button type="button" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50" data-merit-modal-dismiss>Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="application/json" id="student-list-analytics-data">
{!! json_encode([
    'topSchools' => [
        'labels' => array_values(array_map('strval', array_keys(($statsTopSchools ?? collect())->toArray()))),
        'values' => array_values(array_map('intval', array_values(($statsTopSchools ?? collect())->toArray()))),
    ],
    'remainingBuckets' => [
        'labels' => array_values(array_map('strval', array_keys($statsRemainingBuckets ?? []))),
        'values' => array_values(array_map('intval', array_values($statsRemainingBuckets ?? []))),
    ],
]) !!}
</script>
<script>
    (function () {
        const node = document.getElementById('student-list-analytics-data');
        const payload = node ? JSON.parse(node.textContent) : {
            topSchools: { labels: [], values: [] },
            remainingBuckets: { labels: [], values: [] },
        };

        const common = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#e5e7eb' },
                },
                x: {
                    grid: { display: false },
                },
            },
        };

        const schoolsCanvas = document.getElementById('topSchoolsBarChart');
        if (schoolsCanvas) {
            new Chart(schoolsCanvas, {
                type: 'bar',
                data: {
                    labels: payload.topSchools.labels,
                    datasets: [{
                        data: payload.topSchools.values,
                        backgroundColor: '#4f46e5',
                        borderRadius: 6,
                        maxBarThickness: 46,
                    }],
                },
                options: common,
            });
        }

        const remainingCanvas = document.getElementById('remainingHoursBarChart');
        if (remainingCanvas) {
            new Chart(remainingCanvas, {
                type: 'bar',
                data: {
                    labels: payload.remainingBuckets.labels,
                    datasets: [{
                        data: payload.remainingBuckets.values,
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        maxBarThickness: 46,
                    }],
                },
                options: common,
            });
        }
    })();

    (function () {
        const modal = document.getElementById('meritDetailsModal');
        const body = document.getElementById('meritDetailsModalBody');
        const content = document.getElementById('meritDetailsContent');
        const manageSection = document.getElementById('meritDetailsManageSection');
        const subtitle = document.getElementById('meritDetailsModalSubtitle');
        const editLink = document.getElementById('meritDetailsEditLink');
        const meritForm = document.getElementById('meritDetailsForm');
        const saveBtn = document.getElementById('meritDetailsSaveBtn');
        const formError = document.getElementById('meritDetailsFormError');
        const manualWrap = document.getElementById('meritModalManualWrap');
        const warningCb = document.getElementById('merit_modal_rules_warning');
        const finalCb = document.getElementById('merit_modal_final_notice');
        const automationCb = document.getElementById('merit_modal_allow_automation');
        const showProfileLink = modal && modal.getAttribute('data-show-profile-link') === '1';
        const canEditAutomation = modal && modal.getAttribute('data-can-edit-automation') === '1';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let currentUpdateUrl = '';
        let currentTriggerBtn = null;
        if (!modal || !body || !content) return;

        function closeModal() {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        }

        modal.querySelectorAll('[data-merit-modal-dismiss]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function statusBadge(status) {
            const s = String(status || '').toLowerCase();
            const colors = s === 'approved'
                ? 'bg-emerald-100 text-emerald-800'
                : (s === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700');
            return '<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium ' + colors + '">' + escapeHtml(status) + '</span>';
        }

        function renderDetails(data) {
            const b = data.breakdown || {};
            const absence = data.absence || {};
            const notices = data.notices || {};
            const thresholds = data.thresholds || {};
            const undertime = data.undertime_filings || [];
            const absentReqs = data.absent_requests || [];

            let noticeLines = [];
            if (notices.rules_warning) noticeLines.push('Rules violation warning (active)');
            if (notices.final_notice) noticeLines.push('Final notice (active)');
            if (notices.merit_automation_disabled) noticeLines.push('Automatic merit notices blocked');
            if (noticeLines.length === 0) noticeLines.push('No active rules notices from merits');

            let undertimeRows = undertime.length
                ? undertime.map(function (row) {
                    return '<tr class="border-t border-gray-100"><td class="py-2 pr-3">' + escapeHtml(row.date) + '</td>'
                        + '<td class="py-2 pr-3 font-mono text-xs">' + escapeHtml(row.hours_label) + '</td>'
                        + '<td class="py-2">' + statusBadge(row.status) + '</td></tr>';
                }).join('')
                : '<tr><td colspan="3" class="py-3 text-gray-500">No under-time filings below 08:00.</td></tr>';

            let absentRows = absentReqs.length
                ? absentReqs.map(function (row) {
                    return '<tr class="border-t border-gray-100"><td class="py-2 pr-3">' + escapeHtml(row.range) + '</td>'
                        + '<td class="py-2 pr-3 tabular-nums">' + escapeHtml(row.days) + ' day(s)</td>'
                        + '<td class="py-2">' + statusBadge(row.status) + '</td></tr>';
                }).join('')
                : '<tr><td colspan="3" class="py-3 text-gray-500">No approved absent leave requests.</td></tr>';

            content.innerHTML =
                '<div class="space-y-5">'
                + '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">'
                + '<div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2"><p class="text-xs text-gray-500">Under-time merits</p><p class="text-xl font-bold text-gray-900 tabular-nums">' + escapeHtml(b.undertime ?? 0) + '</p>'
                + '<p class="text-[10px] text-gray-500 mt-0.5">' + escapeHtml(data.undertime_filing_count ?? 0) + ' filing(s) ÷ ' + escapeHtml(data.undertime_filings_per_merit ?? 5) + '</p></div>'
                + '<div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2"><p class="text-xs text-gray-500">Excess absences</p><p class="text-xl font-bold text-gray-900 tabular-nums">' + escapeHtml(b.excess_absence ?? 0) + '</p></div>'
                + '<div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2"><p class="text-xs text-gray-500">Manual</p><p class="text-xl font-bold text-gray-900 tabular-nums">' + escapeHtml(b.manual ?? 0) + '</p></div>'
                + '<div class="rounded-lg border border-amber-300 bg-amber-100/50 px-3 py-2"><p class="text-xs font-medium text-amber-900">Total merits</p><p class="text-2xl font-bold text-amber-900 tabular-nums">' + escapeHtml(b.total ?? 0) + '</p></div>'
                + '</div>'
                + '<p class="text-xs text-gray-500">Auto notices (system): violation warning at <strong>' + escapeHtml(thresholds.warning ?? 1) + '+</strong> merits; final notice at <strong>' + escapeHtml(thresholds.final ?? 3) + '+</strong> merits.</p>'
                + '<p class="text-xs"><button type="button" id="meritScrollToManage" class="text-indigo-600 font-semibold hover:text-indigo-800 hover:underline">Edit manual merits &amp; notices below ↓</button></p>'
                + '<div class="rounded-lg border border-gray-200 p-3"><p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Rules notices</p><ul class="text-sm text-gray-700 list-disc list-inside">' + noticeLines.map(function (l) { return '<li>' + escapeHtml(l) + '</li>'; }).join('') + '</ul></div>'
                + '<div><h3 class="text-sm font-semibold text-gray-900 mb-1">Allowable absences</h3>'
                + '<dl class="grid grid-cols-2 gap-2 text-sm"><div><dt class="text-gray-500">Balance allowed</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.allowable) + ' days</dd></div>'
                + '<div><dt class="text-gray-500">Approved absent days</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.approved_days) + '</dd></div>'
                + '<div><dt class="text-gray-500">Remaining</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.remaining_balance) + ' days</dd></div>'
                + '<div><dt class="text-gray-500">Days over balance</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.days_over_balance ?? absence.excess_merits) + '</dd></div>'
                + '<div><dt class="text-gray-500">Excess absence merits</dt><dd class="font-semibold tabular-nums">' + escapeHtml(absence.excess_merits) + '</dd></div></dl>'
                + '<p class="mt-2 text-xs text-gray-500">' + escapeHtml(data.rules?.excess_absence || '') + '</p></div>'
                + '<div><h3 class="text-sm font-semibold text-gray-900 mb-2">Under-time time requests <span class="font-normal text-gray-500">(' + undertime.length + ')</span></h3>'
                + '<p class="text-xs text-gray-500 mb-2">' + escapeHtml(data.rules?.undertime || '') + '</p>'
                + '<div class="overflow-x-auto rounded-lg border border-gray-200"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase"><tr><th class="px-3 py-2">Date</th><th class="px-3 py-2">Filed</th><th class="px-3 py-2">Status</th></tr></thead><tbody class="px-3">' + undertimeRows + '</tbody></table></div></div>'
                + '<div><h3 class="text-sm font-semibold text-gray-900 mb-2">Approved absent leave <span class="font-normal text-gray-500">(' + absentReqs.length + ')</span></h3>'
                + '<div class="overflow-x-auto rounded-lg border border-gray-200"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase"><tr><th class="px-3 py-2">Period</th><th class="px-3 py-2">Days</th><th class="px-3 py-2">Status</th></tr></thead><tbody>' + absentRows + '</tbody></table></div></div>'
                + '<p class="text-xs text-gray-500">' + escapeHtml(data.rules?.manual || '') + '</p>'
                + '</div>';

            populateManageForm(data);
        }

        if (body && manageSection) {
            body.addEventListener('click', function (e) {
                if (e.target && e.target.id === 'meritScrollToManage') {
                    manageSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        }

        function populateManageForm(data) {
            if (!manageSection || !meritForm) return;
            const editable = data.editable || {};
            currentUpdateUrl = data.update_url || '';

            if (manualWrap) {
                manualWrap.classList.toggle('hidden', !editable.can_edit_manual);
            }
            const manualInput = document.getElementById('merit_modal_manual_merits');
            if (manualInput) {
                manualInput.value = editable.manual_merits ?? 0;
            }
            if (warningCb) warningCb.checked = !!editable.rules_warning;
            if (finalCb) finalCb.checked = !!editable.final_notice;
            if (canEditAutomation && automationCb) {
                automationCb.checked = editable.allow_merit_automation !== false;
            }
            const noticeTa = document.getElementById('merit_modal_notice_message');
            if (noticeTa) noticeTa.value = editable.notice_message || '';

            manageSection.classList.remove('hidden');
            if (saveBtn) saveBtn.classList.remove('hidden');
            if (formError) formError.classList.add('hidden');
        }

        function bindNoticeCheckboxes() {
            if (!warningCb || !finalCb) return;
            warningCb.addEventListener('change', function () {
                if (this.checked) finalCb.checked = false;
                syncAutomationFromNotices();
            });
            finalCb.addEventListener('change', function () {
                if (this.checked) warningCb.checked = false;
                syncAutomationFromNotices();
            });
        }
        function syncAutomationFromNotices() {
            if (!canEditAutomation || !automationCb) return;
            if (!warningCb.checked && !finalCb.checked) {
                automationCb.checked = false;
            }
        }
        bindNoticeCheckboxes();

        function updateTableMeritCell(btn, breakdown) {
            if (!btn || !breakdown) return;
            const total = parseInt(breakdown.total, 10) || 0;
            const undertime = parseInt(breakdown.undertime, 10) || 0;
            const excess = parseInt(breakdown.excess_absence, 10) || 0;
            const manual = parseInt(breakdown.manual, 10) || 0;
            btn.title = total > 0
                ? 'Total ' + total + ' = under-time ' + undertime + ' + excess absences ' + excess + ' + manual ' + manual + ' — click for full details'
                : 'No merits on record';
            btn.className = 'merit-details-trigger inline-flex flex-col items-center rounded-md px-2 py-1 -mx-2 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1 '
                + (total > 0 ? 'font-semibold text-amber-700 hover:bg-amber-50 hover:text-amber-900' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700');
            let html = '<span class="tabular-nums">' + total.toLocaleString() + '</span>';
            if (total > 0) {
                html += '<span class="block text-[10px] font-normal leading-tight text-amber-800/90 mt-0.5 tabular-nums">'
                    + undertime + '+' + excess + '+' + manual + '</span>';
            }
            btn.innerHTML = html;
        }

        if (saveBtn && meritForm) {
            saveBtn.addEventListener('click', function () {
                if (!currentUpdateUrl) return;
                if (warningCb && finalCb && warningCb.checked && finalCb.checked) {
                    if (formError) {
                        formError.textContent = 'Rules violation warning and final notice cannot both be enabled.';
                        formError.classList.remove('hidden');
                    }
                    return;
                }
                saveBtn.disabled = true;
                if (formError) formError.classList.add('hidden');

                const payload = {
                    student_rules_warning: warningCb && warningCb.checked ? 1 : 0,
                    student_rules_marquee_enabled: finalCb && finalCb.checked ? 1 : 0,
                    student_rules_notice_message: document.getElementById('merit_modal_notice_message')?.value || '',
                };
                if (canEditAutomation && automationCb) {
                    payload.student_rules_allow_merit_automation = automationCb.checked ? 1 : 0;
                }
                const manualInput = document.getElementById('merit_modal_manual_merits');
                if (manualInput && !manualWrap.classList.contains('hidden')) {
                    payload.student_manual_merits = parseInt(manualInput.value, 10) || 0;
                }

                fetch(currentUpdateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                })
                    .then(function (res) {
                        return res.json().then(function (json) {
                            if (!res.ok) throw json;
                            return json;
                        });
                    })
                    .then(function (json) {
                        const data = json.details || json;
                        const student = data.student || {};
                        subtitle.textContent = (student.name || '') + (student.email ? ' · ' + student.email : '');
                        renderDetails(data);
                        if (currentTriggerBtn && data.breakdown) {
                            updateTableMeritCell(currentTriggerBtn, data.breakdown);
                        }
                    })
                    .catch(function (err) {
                        let msg = 'Could not save. Please try again.';
                        if (err && err.message) {
                            msg = err.message;
                        } else if (err && err.errors) {
                            const first = Object.values(err.errors).flat()[0];
                            if (first) msg = first;
                        }
                        if (formError) {
                            formError.textContent = msg;
                            formError.classList.remove('hidden');
                        }
                    })
                    .finally(function () {
                        saveBtn.disabled = false;
                    });
            });
        }

        document.querySelectorAll('.merit-details-trigger').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const url = btn.getAttribute('data-merits-url');
                if (!url) return;

                currentTriggerBtn = btn;
                currentUpdateUrl = '';
                if (manageSection) manageSection.classList.add('hidden');
                if (saveBtn) saveBtn.classList.add('hidden');
                openModal();
                content.innerHTML = '<p class="text-gray-500 py-6 text-center">Loading merit details…</p>';
                subtitle.textContent = '';

                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                    .then(function (res) {
                        if (!res.ok) throw new Error('Failed to load');
                        return res.json();
                    })
                    .then(function (data) {
                        const student = data.student || {};
                        subtitle.textContent = (student.name || '') + (student.email ? ' · ' + student.email : '');
                        if (showProfileLink && editLink && student.edit_url) {
                            editLink.href = student.edit_url;
                            editLink.classList.remove('hidden');
                        } else if (editLink) {
                            editLink.classList.add('hidden');
                            editLink.href = '#';
                        }
                        currentUpdateUrl = data.update_url || '';
                        renderDetails(data);
                    })
                    .catch(function () {
                        content.innerHTML = '<p class="text-red-600 py-4">Could not load merit details. Please try again.</p>';
                        if (manageSection) manageSection.classList.add('hidden');
                        if (saveBtn) saveBtn.classList.add('hidden');
                    });
            });
        });
    })();
</script>
@endsection

