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
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">View employee leave requests on a monthly calendar.</p>
                </div>
            </div>
            <a href="{{ url('/admin/leave-requests') }}"
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
        <!-- Employee Filter Sidebar -->
        <div class="lg:col-span-1 space-y-2.5 sm:space-y-3">
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-2 sm:p-2.5">
                <h2 class="text-xs sm:text-sm font-bold text-gray-900 mb-1.5">Employees</h2>

                <!-- Department Filter -->
                <div class="mb-2">
                    <label for="department_id" class="block text-xs font-medium text-gray-700 mb-1.5">Filter by Department</label>
                    <select name="department_id" id="department_id"
                            onchange="filterByDepartment(this.value)"
                            class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $department)
                            <option value="{{ $department->id }}" {{ $selectedDepartmentId == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <p class="text-[11px] text-gray-500 mb-1.5 sm:mb-2">
                    Tap a name to focus, or choose All Employees.
                </p>
                <div class="space-y-1 text-xs sm:text-sm -mx-1 max-h-52 sm:max-h-60 overflow-y-auto">
                    @php
                        $allEmployeesParams = ['month' => $currentMonth->format('Y-m')];
                        if ($selectedDepartmentId) {
                            $allEmployeesParams['department_id'] = $selectedDepartmentId;
                        }
                    @endphp
                    <a href="{{ url('/admin/leave-calendar?' . http_build_query($allEmployeesParams)) }}"
                       class="flex items-center justify-between px-3 py-1.5 rounded-md mx-1 transition-colors duration-150 {{ !$selectedEmployeeId ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span>All Employees</span>
                    </a>
                    @foreach($employees as $employee)
                        @php
                            $employeeParams = ['month' => $currentMonth->format('Y-m'), 'employee' => $employee->id];
                            if ($selectedDepartmentId) {
                                $employeeParams['department_id'] = $selectedDepartmentId;
                            }
                        @endphp
                        <a href="{{ url('/admin/leave-calendar?' . http_build_query($employeeParams)) }}"
                           class="flex items-center justify-between px-3 py-1.5 rounded-md mx-1 transition-colors duration-150 {{ $selectedEmployeeId == $employee->id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="truncate">{{ $employee->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Quick Create Leave for Employee -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-2.5 sm:p-3">
                <h2 class="text-xs sm:text-sm font-bold text-gray-900 mb-1.5">File Leave for Employee(s)</h2>
                <form id="leave-calendar-file-leave-form" action="{{ url('/admin/leave-requests/create-for-employee') }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    @if($errors->any())
                        <div class="rounded-md bg-red-50 border border-red-200 p-2 text-[10px] text-red-800">
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="space-y-1">
                        <span id="create_user_ids_label" class="block text-xs font-medium text-gray-700">Select Employee(s)</span>
                        <div id="create_user_ids" class="border border-gray-300 rounded-md p-1.5 space-y-0.5 max-h-52 sm:max-h-60 overflow-y-auto" role="group" aria-labelledby="create_user_ids_label">
                            <div class="flex items-center mb-0.5">
                                <input type="checkbox" id="select-all-employees" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onchange="toggleAllEmployees(this)">
                                <label for="select-all-employees" class="ml-2 text-xs font-semibold text-gray-700 cursor-pointer">Select All</label>
                            </div>
                            @foreach($employees as $employee)
                                <div class="flex items-center">
                                    <input type="checkbox" name="user_ids[]" id="employee_{{ $employee->id }}" value="{{ $employee->id }}" class="employee-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="employee_{{ $employee->id }}" class="ml-2 text-xs text-gray-700 cursor-pointer">{{ $employee->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-gray-500 mt-0.5">Select one or more employees to file leave for</p>
                    </div>
                    <div class="space-y-1">
                        <label for="create_type" class="block text-xs font-medium text-gray-700">Type</label>
                        <select name="type" id="create_type" required class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" onchange="handleLeaveTypeChange(this)">
                            <option value="">Select type</option>
                            <option value="vacation_leave" {{ old('type') == 'vacation_leave' ? 'selected' : '' }}>Vacation Leave</option>
                            <option value="sick_leave" {{ old('type') == 'sick_leave' ? 'selected' : '' }}>Sick Leave</option>
                            @unless($hrLeaveTypesOnly ?? false)
                            <option value="work_from_home" {{ old('type') == 'work_from_home' ? 'selected' : '' }}>Work From Home</option>
                            <option value="absent" {{ old('type') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="overtime" {{ old('type') == 'overtime' ? 'selected' : '' }}>Overtime</option>
                            <option value="offset" {{ old('type') == 'offset' ? 'selected' : '' }}>Offset</option>
                            <option value="additional_time" {{ old('type') == 'additional_time' ? 'selected' : '' }}>Additional Time</option>
                            <option value="travel" {{ old('type') == 'travel' ? 'selected' : '' }}>Travel</option>
                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                            @endunless
                        </select>
                    </div>
                    <!-- Travel Hours Field (only shown for travel type) -->
                    <div id="travel_hours_container" class="space-y-1 hidden">
                        <label for="travel_hours" class="block text-xs font-medium text-gray-700">Hours per Day</label>
                        <input type="number" name="travel_hours" id="travel_hours" min="0" max="24" step="0.5" value="{{ old('travel_hours', '8.0') }}" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="8.0">
                        <p class="text-[10px] text-gray-500">Default: 8.0 hours per day. Can be customized.</p>
                    </div>

                    <!-- Overtime (admin filing — details optional; can complete on the request later) -->
                    <div id="admin_overtime_section" class="space-y-1.5 hidden border-t border-gray-100 pt-2 mt-1">
                        <p class="text-[10px] font-semibold text-gray-800">Overtime Details</p>
                        <p class="text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-100 rounded px-2 py-1">
                            Only employee, type, and start date are required. Add overtime hours now or update them later on the leave request.
                        </p>
                        <div>
                            <label for="admin_overtime_work_type" class="block text-xs font-medium text-gray-700">Overtime Type <span class="text-gray-400">(Optional)</span></label>
                            <select name="overtime_work_type" id="admin_overtime_work_type" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select type</option>
                                @foreach(\App\Models\LeaveRequest::overtimeWorkTypes() as $value => $label)
                                    <option value="{{ $value }}" {{ old('overtime_work_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('overtime_work_type')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="admin_overtime_hours" class="block text-xs font-medium text-gray-700">Total Overtime (HH:MM) <span class="text-gray-400">(Optional)</span></label>
                            <input type="text" name="overtime_hours" id="admin_overtime_hours" value="{{ old('overtime_hours') }}" placeholder="01:30" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="admin_overtime_dates" class="block text-xs font-medium text-gray-700">Overtime Dates <span class="text-gray-400">(Optional)</span></label>
                            <input type="text" name="overtime_dates" id="admin_overtime_dates" value="{{ old('overtime_dates') }}" placeholder="Uses start/end date if blank" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="admin_overtime_tasks" class="block text-xs font-medium text-gray-700">Tasks / ClickUp <span class="text-gray-400">(Optional)</span></label>
                            <textarea name="overtime_tasks" id="admin_overtime_tasks" rows="2" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500" placeholder="https://... URLs only">{{ old('overtime_tasks') }}</textarea>
                        </div>
                    </div>

                    <!-- Offset: optional Hours to Deduct (HH:MM), same as employee -->
                    <div id="admin_offset_section" class="space-y-1 hidden border-t border-gray-100 pt-2 mt-1">
                        <p class="text-[10px] font-semibold text-gray-800">Offset Details</p>
                        <p class="text-[10px] text-gray-500">Optional custom hours to deduct; if blank, duration × 8h (same as employee form).</p>
                        <div>
                            <label for="admin_offset_hours" class="block text-xs font-medium text-gray-700">Hours to Deduct (HH:MM) <span class="text-gray-400">(Optional)</span></label>
                            <input type="text" name="offset_hours" id="admin_offset_hours" value="{{ old('offset_hours') }}" placeholder="08:00" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Work From Home (same fields as employee) -->
                    <div id="admin_wfh_section" class="space-y-1.5 hidden border-t border-gray-100 pt-2 mt-1">
                        <p class="text-[10px] font-semibold text-gray-800">Work From Home Details</p>
                        <p class="text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-100 rounded px-2 py-1">
                            Admin filing bypasses the employee monthly WFH limit. Any days above the employee&rsquo;s remaining balance are deducted from their next month&rsquo;s allowance when approved.
                        </p>
                        <div>
                            <label for="admin_wfh_mode" class="block text-xs font-medium text-gray-700">Work Mode <span class="text-red-500">*</span></label>
                            <select name="wfh_mode" id="admin_wfh_mode" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select</option>
                                <option value="working_remotely" {{ old('wfh_mode') == 'working_remotely' ? 'selected' : '' }}>Working remotely</option>
                                <option value="request_to_be_excused" {{ old('wfh_mode') == 'request_to_be_excused' ? 'selected' : '' }}>Request to be excused</option>
                            </select>
                        </div>
                        <div>
                            <label for="admin_wfh_address" class="block text-xs font-medium text-gray-700">Remote Address <span class="text-red-500">*</span></label>
                            <input type="text" name="wfh_address" id="admin_wfh_address" value="{{ old('wfh_address') }}" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500" placeholder="Location">
                        </div>
                        <div>
                            <label for="admin_wfh_tasks" class="block text-xs font-medium text-gray-700">Tasks / ClickUp <span class="text-red-500">*</span></label>
                            <textarea name="wfh_tasks" id="admin_wfh_tasks" rows="2" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500" placeholder="https://... URLs only">{{ old('wfh_tasks') }}</textarea>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label for="create_start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="create_start_date" value="{{ old('start_date') }}" required class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="space-y-1">
                            <label for="create_end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="create_end_date" value="{{ old('end_date') }}" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="space-y-1" id="admin_reason_wrap">
                        <label for="create_reason" class="block text-xs font-medium text-gray-700"><span id="admin_reason_label">Reason</span> <span id="admin_reason_optional" class="text-gray-400">(optional)</span></label>
                        <textarea name="reason" id="create_reason" rows="2" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Add brief notes">{{ old('reason') }}</textarea>
                        <p class="text-[10px] text-gray-500 hidden" id="admin_travel_reason_help">For Travel, enter location / destination (required).</p>
                    </div>
                    <div class="space-y-1">
                        <label for="admin_supporting_documents" class="block text-xs font-medium text-gray-700">
                            Supporting Document(s)
                            <span id="admin_supporting_optional" class="text-gray-400">(optional)</span>
                            <span id="admin_supporting_required" class="text-red-500 hidden">*</span>
                        </label>
                        <input type="file" name="supporting_documents[]" id="admin_supporting_documents" multiple accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="text-[10px] text-gray-500">Upload up to 5 files (PDF/JPG/PNG), 5MB max per file.</p>
                    </div>
                    <button type="submit" id="file-leave-btn" class="w-full inline-flex items-center justify-center px-3 py-1 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        File Leave
                    </button>
                    <p class="text-[10px] text-gray-500" id="form-help-text">Creates a pending request that appears on the employee account and calendar. Admins can file any leave type for any date, including past dates.</p>
                    <p class="text-[10px] text-purple-600 font-medium hidden" id="travel-help-text">Travel requests are filed as pending and can use custom hours per day when approved.</p>
                </form>
            </div>
        </div>

        <!-- Month Navigation + Calendar -->
        <div class="lg:col-span-3 space-y-3 sm:space-y-4">
            <!-- Month Navigation -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                <div class="flex items-center justify-between sm:justify-start space-x-2 sm:space-x-3">
                    @php
                        $prevMonthParams = ['month' => $prevMonth];
                        if ($selectedEmployeeId) {
                            $prevMonthParams['employee'] = $selectedEmployeeId;
                        }
                        if ($selectedDepartmentId) {
                            $prevMonthParams['department_id'] = $selectedDepartmentId;
                        }
                    @endphp
                    <a href="{{ url('/admin/leave-calendar?' . http_build_query($prevMonthParams)) }}"
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
                    @php
                        $nextMonthParams = ['month' => $nextMonth];
                        if ($selectedEmployeeId) {
                            $nextMonthParams['employee'] = $selectedEmployeeId;
                        }
                        if ($selectedDepartmentId) {
                            $nextMonthParams['department_id'] = $selectedDepartmentId;
                        }
                    @endphp
                    <a href="{{ url('/admin/leave-calendar?' . http_build_query($nextMonthParams)) }}"
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
                    @if($selectedEmployeeId)
                        <span class="font-semibold">
                            {{ optional($employees->firstWhere('id', $selectedEmployeeId))->name ?? 'Selected Employee' }}
                        </span>
                    @else
                        <span class="font-semibold">all employees</span>
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
                                                    {{ $entry['employee']->name }}
                                                </div>
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="truncate text-[8px] sm:text-[9px] md:text-[10px]">{{ $entry['type_label'] }}</span>
                                                    @php
                                                        $statusLabel = match($entry['status']) {
                                                            'pending' => isset($entry['reviewed_at']) && $entry['reviewed_at'] ? 'Resubmission' : 'Pending',
                                                            'approved' => 'Approved',
                                                            'rejected' => 'Rejected',
                                                            default => ucfirst($entry['status']),
                                                        };
                                                    @endphp
                                                    <span class="ml-0.5 sm:ml-1 text-[8px] sm:text-[9px] font-semibold shrink-0">{{ $statusLabel }}</span>
                                                </div>
                                            </a>
                                        @endforeach

                                        @if($total > $displayed)
                                            @php
                                                $requestsData = array_map(function($req) {
                                                    return [
                                                        'id' => $req['id'],
                                                        'employee_name' => $req['employee']->name,
                                                        'type_label' => $req['type_label'],
                                                        'status' => $req['status'],
                                                    ];
                                                }, $day['requests']);
                                            @endphp
                                            <button type="button"
                                                    onclick="showAllLeaveRequests('{{ $day['date']->toDateString() }}', {{ json_encode($requestsData) }})"
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

<!-- Modal for showing all leave requests on a date -->
<div id="leave-requests-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 sm:w-3/4 md:w-1/2 lg:w-2/5 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-date-title">Leave Requests</h3>
                <button type="button" onclick="closeLeaveRequestsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="modal-requests-list" class="space-y-2 max-h-[60vh] overflow-y-auto">
                <!-- Leave requests will be inserted here -->
            </div>
        </div>
    </div>
</div>

<script>
function showAllLeaveRequests(dateString, requests) {
    const modal = document.getElementById('leave-requests-modal');
    const modalTitle = document.getElementById('modal-date-title');
    const modalList = document.getElementById('modal-requests-list');

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

    if (requests.length === 0) {
        modalList.innerHTML = '<p class="text-gray-500 text-sm">No leave requests for this date.</p>';
    } else {
        requests.forEach(function(entry) {
            const statusClass = getStatusClass(entry.status);
            const statusBadge = getStatusBadge(entry.status);

            const requestDiv = document.createElement('div');
            requestDiv.className = `border ${statusClass} rounded-lg p-3 hover:shadow-md transition-shadow`;
            const showUrl = '{{ url("/admin/leave-requests/") }}' + entry.id;
            requestDiv.innerHTML = `
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <a href="${showUrl}" class="block">
                            <div class="font-semibold text-gray-900 text-sm mb-1">${escapeHtml(entry.employee_name)}</div>
                            <div class="text-xs text-gray-600 mb-2">${escapeHtml(entry.type_label)}</div>
                            <div class="flex items-center gap-2">
                                ${statusBadge}
                            </div>
                        </a>
                    </div>
                </div>
            `;
            modalList.appendChild(requestDiv);
        });
    }

    modal.classList.remove('hidden');
}

function closeLeaveRequestsModal() {
    const modal = document.getElementById('leave-requests-modal');
    modal.classList.add('hidden');
}

function getStatusClass(status) {
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

function getStatusBadge(status) {
    const statusLabels = {
        'approved': { label: 'Approved', class: 'bg-emerald-100 text-emerald-800' },
        'pending': { label: 'Pending', class: 'bg-yellow-100 text-yellow-800' },
        'rejected': { label: 'Rejected', class: 'bg-red-100 text-red-800' }
    };

    const statusInfo = statusLabels[status] || { label: status, class: 'bg-gray-100 text-gray-800' };

    return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ${statusInfo.class}">
        ${statusInfo.label}
    </span>`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal when clicking outside
document.getElementById('leave-requests-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLeaveRequestsModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLeaveRequestsModal();
    }
});

// Toggle all employees checkbox
function toggleAllEmployees(selectAllCheckbox) {
    const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
    employeeCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateFileLeaveButton();
}

// Update file leave button state based on selected employees
function updateFileLeaveButton() {
    const selectedEmployees = document.querySelectorAll('.employee-checkbox:checked');
    const fileLeaveBtn = document.getElementById('file-leave-btn');
    if (selectedEmployees.length === 0) {
        fileLeaveBtn.disabled = true;
    } else {
        fileLeaveBtn.disabled = false;
    }
}

// Add event listeners to employee checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
    employeeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateFileLeaveButton);
    });

    // Check if any employees are selected on page load
    updateFileLeaveButton();

    const createTypeEl = document.getElementById('create_type');
    if (createTypeEl && createTypeEl.value) {
        handleLeaveTypeChange(createTypeEl);
    }

    // Form submission validation
    const form = document.querySelector("form[action='{{ url('/admin/leave-requests/create-for-employee') }}']");
    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedEmployees = document.querySelectorAll('.employee-checkbox:checked');
            if (selectedEmployees.length === 0) {
                e.preventDefault();
                alert('Please select at least one employee.');
                return false;
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin h-3 w-3 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Filing...';
        });
    }
});

function filterByDepartment(departmentId) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);

    // Update or remove department_id parameter
    if (departmentId) {
        params.set('department_id', departmentId);
    } else {
        params.delete('department_id');
    }

    // Preserve month and employee filters
    const month = params.get('month') || '{{ $currentMonth->format("Y-m") }}';
    const employee = params.get('employee');

    // Build new URL
    const newParams = new URLSearchParams();
    newParams.set('month', month);
    if (employee) {
        newParams.set('employee', employee);
    }
    if (departmentId) {
        newParams.set('department_id', departmentId);
    }

    window.location.href = '{{ url("/admin/leave-calendar") }}?' + newParams.toString();
}

// Handle leave type selection — match employee-side sections (travel, overtime, offset, WFH)
function handleLeaveTypeChange(selectElement) {
    const v = selectElement.value;
    const travelHoursContainer = document.getElementById('travel_hours_container');
    const travelHoursInput = document.getElementById('travel_hours');
    const formHelpText = document.getElementById('form-help-text');
    const travelHelpText = document.getElementById('travel-help-text');
    const overtimeSec = document.getElementById('admin_overtime_section');
    const offsetSec = document.getElementById('admin_offset_section');
    const wfhSec = document.getElementById('admin_wfh_section');
    const reasonLabel = document.getElementById('admin_reason_label');
    const reasonOptional = document.getElementById('admin_reason_optional');
    const travelReasonHelp = document.getElementById('admin_travel_reason_help');
    const createReason = document.getElementById('create_reason');

    const otH = document.getElementById('admin_overtime_hours');
    const otD = document.getElementById('admin_overtime_dates');
    const otT = document.getElementById('admin_overtime_tasks');
    const otWorkType = document.getElementById('admin_overtime_work_type');
    const wfhM = document.getElementById('admin_wfh_mode');
    const wfhA = document.getElementById('admin_wfh_address');
    const wfhTasks = document.getElementById('admin_wfh_tasks');
    const supportingInput = document.getElementById('admin_supporting_documents');
    const supportingOptional = document.getElementById('admin_supporting_optional');
    const supportingRequired = document.getElementById('admin_supporting_required');

    [travelHoursContainer, overtimeSec, offsetSec, wfhSec].forEach(el => el && el.classList.add('hidden'));
    [otH, otD, otT, otWorkType, wfhM, wfhA, wfhTasks].forEach(el => { if (el) el.required = false; });
    if (supportingInput) supportingInput.required = false;
    if (supportingOptional) supportingOptional.classList.remove('hidden');
    if (supportingRequired) supportingRequired.classList.add('hidden');
    if (createReason) createReason.required = false;
    if (reasonLabel) reasonLabel.textContent = 'Reason';
    if (reasonOptional) reasonOptional.classList.remove('hidden');
    if (travelReasonHelp) travelReasonHelp.classList.add('hidden');

    if (v === 'travel') {
        travelHoursContainer.classList.remove('hidden');
        travelHoursInput.required = false;
        formHelpText.classList.add('hidden');
        travelHelpText.classList.remove('hidden');
        if (reasonLabel) reasonLabel.textContent = 'Location of travel';
        if (reasonOptional) reasonOptional.classList.add('hidden');
        if (travelReasonHelp) travelReasonHelp.classList.remove('hidden');
        if (createReason) createReason.required = true;
    } else {
        travelHoursInput.required = false;
        formHelpText.classList.remove('hidden');
        travelHelpText.classList.add('hidden');
    }

    if (v === 'overtime') {
        overtimeSec.classList.remove('hidden');
    }

    if (v === 'offset') {
        offsetSec.classList.remove('hidden');
    }

    if (v === 'work_from_home') {
        wfhSec.classList.remove('hidden');
        wfhM.required = true;
        wfhA.required = true;
        wfhTasks.required = true;
    }
}
</script>
@endsection


