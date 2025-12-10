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
            <a href="{{ route('admin.leave-requests.index') }}"
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
        <div class="lg:col-span-1 space-y-3 sm:space-y-4">
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-3 sm:p-4">
                <h2 class="text-xs sm:text-sm font-bold text-gray-900 mb-2">Employees</h2>

                <!-- Department Filter -->
                <div class="mb-3">
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

                <p class="text-xs text-gray-500 mb-2 sm:mb-3">
                    Tap a name to focus on that employee's leave, or choose "All Employees" to view everyone.
                </p>
                <div class="space-y-1 max-h-[calc(100vh-20rem)] sm:max-h-[calc(100vh-24rem)] overflow-y-auto text-xs sm:text-sm -mx-1">
                    @php
                        $allEmployeesParams = ['month' => $currentMonth->format('Y-m')];
                        if ($selectedDepartmentId) {
                            $allEmployeesParams['department_id'] = $selectedDepartmentId;
                        }
                    @endphp
                    <a href="{{ route('admin.leave-requests.calendar', $allEmployeesParams) }}"
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
                        <a href="{{ route('admin.leave-requests.calendar', $employeeParams) }}"
                           class="flex items-center justify-between px-3 py-1.5 rounded-md mx-1 transition-colors duration-150 {{ $selectedEmployeeId == $employee->id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                            <span class="truncate">{{ $employee->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Quick Create Leave for Employee -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-3 sm:p-4">
                <h2 class="text-xs sm:text-sm font-bold text-gray-900 mb-2">File Leave for Employee</h2>
                <form action="{{ route('admin.leave-requests.store-for-employee') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="space-y-1">
                        <label for="create_user_id" class="block text-xs font-medium text-gray-700">Employee</label>
                        <select name="user_id" id="create_user_id" required class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label for="create_type" class="block text-xs font-medium text-gray-700">Type</label>
                        <select name="type" id="create_type" required class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select type</option>
                            <option value="vacation_leave">Vacation Leave</option>
                            <option value="sick_leave">Sick Leave</option>
                            <option value="work_from_home">Work From Home</option>
                            <option value="absent">Absent</option>
                            <option value="overtime">Overtime</option>
                            <option value="offset">Offset</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label for="create_start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="create_start_date" required class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="space-y-1">
                            <label for="create_end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="create_end_date" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label for="create_reason" class="block text-xs font-medium text-gray-700">Reason (optional)</label>
                        <textarea name="reason" id="create_reason" rows="3" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Add brief notes"></textarea>
                    </div>
                    <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        File Leave
                    </button>
                    <p class="text-[11px] text-gray-500">Creates a pending request that appears on the employee account and calendar.</p>
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
                    <a href="{{ route('admin.leave-requests.calendar', $prevMonthParams) }}"
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
                    <a href="{{ route('admin.leave-requests.calendar', $nextMonthParams) }}"
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
                                            <a href="{{ route('admin.leave-requests.show', $entry['id']) }}"
                                               class="block border {{ $statusClass }} rounded px-1 sm:px-1.5 py-0.5 text-[9px] sm:text-[10px] md:text-[11px] hover:border-indigo-300 hover:bg-indigo-50/70">
                                                <div class="font-semibold truncate">
                                                    {{ $entry['employee']->name }}
                                                </div>
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="truncate text-[8px] sm:text-[9px] md:text-[10px]">{{ $entry['type_label'] }}</span>
                                                    <span class="ml-0.5 sm:ml-1 text-[8px] sm:text-[9px] capitalize shrink-0">{{ substr($entry['status'], 0, 1) }}</span>
                                                </div>
                                            </a>
                                        @endforeach

                                        @if($total > $displayed)
                                            <div class="text-[9px] sm:text-[10px] md:text-[11px] text-gray-500">
                                                +{{ $total - $displayed }} more…
                                            </div>
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

<script>
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

    window.location.href = '{{ route("admin.leave-requests.calendar") }}?' + newParams.toString();
}
</script>
@endsection


