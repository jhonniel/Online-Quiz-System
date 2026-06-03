@extends('layouts.admin')

@section('page-title', 'Edit User')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ url('/admin/users') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Users</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500 truncate max-w-[100px] sm:max-w-none">{{ $user->name }}</span>
        </div>
    </li>
@endsection

@section('content')
<div class="w-full px-3 sm:px-4 lg:px-6 xl:px-8">
    <div class="mb-6 sm:mb-8">
        <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-xl sm:rounded-2xl shadow-xl p-5 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-24 -mt-24"></div>
            <div class="absolute bottom-0 left-0 w-56 h-56 bg-white/10 rounded-full -ml-20 -mb-20"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="bg-white/20 backdrop-blur-sm p-3 sm:p-4 rounded-xl flex-shrink-0">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-3xl font-bold truncate">Edit User Profile</h1>
                        <p class="text-indigo-100 text-sm sm:text-base mt-0.5">Update account details for {{ $user->name }}</p>
                    </div>
                </div>
                <a href="{{ url('/admin/users') }}" class="inline-flex items-center justify-center px-5 py-3 bg-white/20 hover:bg-white/30 rounded-xl font-medium transition w-full sm:w-auto min-h-[46px]">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <form action="{{ url('/admin/users/' . $user->id) }}" method="POST" id="editUserForm" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 2xl:grid-cols-4 gap-6">
            <div class="2xl:col-span-3 space-y-6">
                <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                        <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Personal info and role configuration</p>
                    </div>
                    <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('name') border-red-500 @enderror">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('email') border-red-500 @enderror">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                            <select name="role" id="role" required
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error('role') border-red-500 @enderror">
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                                <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="teacher" {{ old('role', $user->role) == 'teacher' ? 'selected' : '' }}>Teacher</option>
                                <option value="technician" {{ old('role', $user->role) == 'technician' ? 'selected' : '' }}>Technician</option>
                                <option value="applicant" {{ old('role', $user->role) == 'applicant' ? 'selected' : '' }}>Applicant</option>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User (Legacy)</option>
                            </select>
                            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                        <h2 class="text-lg font-semibold text-gray-900">University, Department, and Role-Based Details</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Academic and work-related fields</p>
                    </div>
                    <div class="p-5 sm:p-6 space-y-5">
                        <div>
                            <label for="university_select" class="block text-sm font-semibold text-gray-700 mb-1.5">University/School <span class="text-red-500 {{ old('role', $user->role) === 'teacher' ? '' : 'hidden' }}" id="university_required_indicator">*</span></label>
                            <select name="university_id" id="university_select"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">No university selected</option>
                                @foreach($universities as $university)
                                    <option value="{{ $university->id }}" {{ old('university_id', $user->university_id) == $university->id ? 'selected' : '' }}>
                                        {{ $university->name }}
                                    </option>
                                @endforeach
                                <option value="new" class="text-indigo-600 font-semibold">+ Add New University</option>
                            </select>
                            <div id="new-university-container" class="hidden mt-3">
                                <label for="new_university_name" class="block text-sm font-semibold text-gray-700 mb-1.5">New University Name <span class="text-red-500">*</span></label>
                                <input type="text" name="new_university_name" id="new_university_name"
                                       class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm"
                                       placeholder="Enter new university name"
                                       value="{{ old('new_university_name') }}">
                                @error('new_university_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Select existing or choose "Add New University"</p>
                            @error('university_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div id="department_wrapper" class="{{ in_array(old('role', $user->role), ['employee', 'student'], true) ? '' : 'hidden' }}">
                            <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-1.5">Department <span class="text-red-500 {{ old('role', $user->role) === 'employee' ? '' : 'hidden' }}" id="department_required_indicator">*</span></label>
                            <select name="department_id" id="department_id"
                                    class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm @error('department_id') border-red-500 @enderror">
                                <option value="">Select a department</option>
                                @foreach($departments ?? [] as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                <a href="{{ url('/admin/departments/create') }}" target="_blank" class="text-indigo-600 hover:underline">Create new department</a> if not in the list
                            </p>
                            @error('department_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div id="required_training_hours_wrapper" class="{{ old('role', $user->role) === 'student' ? '' : 'hidden' }}">
                            <label for="required_training_hours" class="block text-sm font-semibold text-gray-700 mb-1.5">Required Training Hours</label>
                            <input type="number" name="required_training_hours" id="required_training_hours" step="0.01" min="0"
                                   value="{{ old('required_training_hours', $user->required_training_hours) }}"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm"
                                   placeholder="e.g. 160">
                            <p class="mt-1 text-xs text-gray-500">For students: total hours needed via DTR. Estimated completion from pace appears on their dashboard unless you set the target date below.</p>
                            @error('required_training_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                            <div class="mt-4">
                                <label for="ojt_target_end_date" class="block text-sm font-semibold text-gray-700 mb-1.5">OJT target end date / exit conference</label>
                                @php
                                    $ojtTargetEndDateValue = $user->ojt_target_end_date;
                                    if (is_string($ojtTargetEndDateValue) && $ojtTargetEndDateValue !== '') {
                                        $ojtTargetEndDateValue = \Carbon\Carbon::parse($ojtTargetEndDateValue);
                                    }
                                @endphp
                                <input type="date" name="ojt_target_end_date" id="ojt_target_end_date"
                                       value="{{ old('ojt_target_end_date', $ojtTargetEndDateValue instanceof \Carbon\CarbonInterface ? $ojtTargetEndDateValue->format('Y-m-d') : '') }}"
                                       class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm">
                                <p class="mt-1 text-xs text-gray-500">Optional. Shown on the student dashboard as the official OJT deadline (clear the date to remove).</p>
                                @error('ojt_target_end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="mt-4">
                                <label for="student_absence_allowance" class="block text-sm font-semibold text-gray-700 mb-1.5">Allowable Absences Balance (Days)</label>
                                <input type="number" name="student_absence_allowance" id="student_absence_allowance" step="0.01" min="0" max="365"
                                       value="{{ old('student_absence_allowance', \App\Models\User::normalizedStudentAbsenceAllowance($user->student_absence_allowance)) }}"
                                       class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm"
                                       placeholder="e.g. 5">
                                <p class="mt-1 text-xs text-gray-500">Students cannot file <strong>Absent</strong> leave once this balance reaches 0.</p>
                                @error('student_absence_allowance') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50/60 p-4" id="student_merits_wrapper">
                                <h3 class="text-sm font-semibold text-gray-900">Merits</h3>
                                <p class="mt-1 text-xs text-gray-600">
                                    Automatic: 1 merit per <strong>{{ \App\Support\StudentViolationCounter::UNDERTIME_FILINGS_PER_MERIT }}</strong> filed time requests below <strong>08:00</strong>, plus approved absent days minus the allowable absence balance above (when over zero).
                                </p>
                                @if($studentMeritBreakdown)
                                    <dl class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                                        <div class="rounded-lg bg-white/80 border border-amber-100 px-3 py-2">
                                            <dt class="text-xs text-gray-500">Under-time filings</dt>
                                            <dd class="font-semibold text-gray-900">{{ number_format($studentMeritBreakdown['undertime']) }}</dd>
                                        </div>
                                        <div class="rounded-lg bg-white/80 border border-amber-100 px-3 py-2">
                                            <dt class="text-xs text-gray-500">Excess absences</dt>
                                            <dd class="font-semibold text-gray-900">{{ number_format($studentMeritBreakdown['excess_absence']) }}</dd>
                                        </div>
                                        <div class="rounded-lg bg-white/80 border border-amber-100 px-3 py-2">
                                            <dt class="text-xs text-gray-500">Manual (admin)</dt>
                                            <dd class="font-semibold text-gray-900">{{ number_format($studentMeritBreakdown['manual']) }}</dd>
                                        </div>
                                        <div class="rounded-lg bg-white border border-amber-300 px-3 py-2">
                                            <dt class="text-xs font-medium text-amber-800">Total merits</dt>
                                            <dd class="text-lg font-bold text-amber-900">{{ number_format($studentMeritBreakdown['total']) }}</dd>
                                        </div>
                                    </dl>
                                @endif
                                @if(auth()->user()->isAdmin())
                                    <div class="mt-4">
                                        <label for="student_manual_merits" class="block text-sm font-semibold text-gray-700 mb-1.5">Manual merit count</label>
                                        <input type="number" name="student_manual_merits" id="student_manual_merits" min="0" max="9999" step="1"
                                               value="{{ old('student_manual_merits', (int) ($user->student_manual_merits ?? 0)) }}"
                                               class="block w-full max-w-xs px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 text-sm">
                                        <p class="mt-1 text-xs text-gray-500">Added on top of automatic merits. Only administrators can edit this.</p>
                                        @error('student_manual_merits') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @elseif($user->role === 'student')
                                    <p class="mt-3 text-xs text-gray-500">Contact an administrator to adjust manual merits.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div id="student_rules_compliance_wrapper" class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 overflow-hidden {{ old('role', $user->role) === 'student' ? '' : 'hidden' }}">
                    <div class="px-6 py-4 border-b border-gray-100 bg-amber-50/80">
                        <h2 class="text-lg font-semibold text-gray-900">Student rules &amp; notices</h2>
                        <p class="text-sm text-gray-600 mt-0.5">
                            <strong class="text-gray-800">Rules violation warning</strong> (yellow): light-amber rules modal, “You have violated the rules”, and a <strong class="text-amber-800">yellow scrolling banner</strong> at the top.
                            <strong class="text-gray-800">Final notice</strong> (red): red rules modal, “This is your final warning…”, and a <strong class="text-red-700">red scrolling banner</strong>.
                            <span class="text-gray-800 font-medium">Only one of these can be enabled at a time.</span>
                            @if($studentMeritNoticeSettings)
                                When automatic notices are enabled (see <a href="{{ url('/admin/system/rules') }}" class="text-indigo-600 hover:underline font-medium">System → Rules</a>),
                                <strong>{{ $studentMeritNoticeSettings['warning'] }}+ merit(s)</strong> turns on the violation warning;
                                <strong>{{ $studentMeritNoticeSettings['final'] }}+ merit(s)</strong> turns on final notice instead.
                            @else
                                Merit thresholds are configured under System → Rules.
                            @endif
                            Notice text is filled from their merit breakdown. Uncheck both notices below to disable and block auto re-enable, or use the automation checkbox.
                        </p>
                    </div>
                    <div class="p-5 sm:p-6 space-y-5">
                        @error('student_rules_notices')
                            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</div>
                        @enderror
                        <input type="hidden" name="student_rules_warning" value="0">
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" name="student_rules_warning" value="1" id="student_rules_warning"
                                   class="mt-1 h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded"
                                   {{ (string) old('student_rules_warning', ($user->student_rules_warning ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">
                                <span class="font-semibold text-gray-900">Rules violation warning</span><br>
                                When enabled, the student gets a <strong class="text-amber-800">yellow top banner</strong> with a continuous marquee and the rules modal uses a light yellow style with “You have violated the rules” above the title when they log in.
                                <span class="block mt-1 text-xs text-gray-500">If you turn this on here, automatic enable/disable from time requests will no longer change it for this student.</span>
                            </span>
                        </label>
                        @error('student_rules_warning') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        <div class="border-t border-gray-100 pt-5">
                            <input type="hidden" name="student_rules_marquee_enabled" value="0">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox" name="student_rules_marquee_enabled" value="1" id="student_rules_marquee_enabled"
                                       class="mt-1 h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded"
                                       {{ (string) old('student_rules_marquee_enabled', ($user->student_rules_marquee_enabled ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">
                                    <span class="font-semibold text-gray-900">Enable final notice (scrolling banner)</span><br>
                                    When enabled, a <strong class="text-red-700">red</strong> “Final notice” banner with scrolling text appears at the top of the student portal on every page until you turn it off or change their role.
                                    <span class="block mt-1 text-xs text-gray-500">Automatically enabled when the student reaches 3 merits. If you turn this on here, merit-based automation will no longer change it.</span>
                                </span>
                            </label>
                            @error('student_rules_marquee_enabled') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="student_rules_notice_message" class="block text-sm font-semibold text-gray-700 mb-1.5">Notice message</label>
                            <textarea name="student_rules_notice_message" id="student_rules_notice_message" rows="3"
                                      class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm @error('student_rules_notice_message') border-red-500 @enderror"
                                      placeholder="This text is saved to the account and shown in the scrolling top banner (yellow or red) for the option you enable above.">{{ old('student_rules_notice_message', $user->student_rules_notice_message ?? '') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Required when either notice type is enabled. Plain text; it is stored in the database and drives the marquee for that student.</p>
                            @error('student_rules_notice_message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="border-t border-gray-100 pt-5">
                            <input type="hidden" name="student_rules_allow_merit_automation" value="0">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox" name="student_rules_allow_merit_automation" value="1" id="student_rules_allow_merit_automation"
                                       class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                       {{ (string) old('student_rules_allow_merit_automation', ($user->student_rules_merit_automation_disabled ?? false) ? '0' : '1') === '1' ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">
                                    <span class="font-semibold text-gray-900">Allow automatic merit-based notices</span><br>
                                    When checked, the system may enable or update violation / final notices from this student’s merit count (using thresholds in System → Rules).
                                    <span class="block mt-1 text-xs text-gray-500">Uncheck and save to keep notices off (or as you set them) without the system turning them back on. Turning off both notices above also blocks automation until you check this again.</span>
                                </span>
                            </label>
                        </div>
                        <div class="border-t border-amber-200 pt-5 space-y-2">
                            <input type="hidden" name="student_terminated" value="0">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox" name="student_terminated" value="1" id="student_terminated"
                                       class="mt-1 h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                                       {{ (string) old('student_terminated', ($user->student_terminated ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">
                                    <span class="font-semibold text-red-800">Student terminated</span><br>
                                    When checked, the student sees a full-screen <strong class="text-red-800">Account terminated</strong> warning (with a glitch-style effect) and cannot open any other page until you uncheck this and save. They may still sign out from that screen.
                                </span>
                            </label>
                            @error('student_terminated') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                @php
                    $currentYear = now()->year;
                    $leaveBalance = \App\Models\LeaveBalance::where('user_id', $user->id)->where('year', $currentYear)->first();
                    $combinedLeaveAllowance = old(
                        'leave_allowance',
                        $leaveBalance ? ((float) $leaveBalance->vacation_allowance + (float) $leaveBalance->sick_allowance) : ''
                    );
                @endphp
                <div id="leave_balances_wrapper" class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 overflow-hidden {{ old('role', $user->role) === 'employee' ? '' : 'hidden' }}">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                        <h2 class="text-lg font-semibold text-gray-900">Leave Credits ({{ $currentYear }})</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Applicable for employee role</p>
                    </div>
                    <div class="p-5 sm:p-6">
                        <div>
                            <label for="leave_allowance" class="block text-sm font-semibold text-gray-700 mb-1.5">Leave Credits (days)</label>
                            <input type="number" name="leave_allowance" id="leave_allowance" step="0.01" min="0"
                                   value="{{ $combinedLeaveAllowance }}"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm"
                                   placeholder="e.g. 25">
                            <p class="mt-1 text-xs text-gray-500">Combined leave credits for employee leave requests.</p>
                            @error('leave_allowance') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 2xl:col-span-1">
                <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                        <h3 class="text-base font-semibold text-gray-900">Account Status</h3>
                    </div>
                    <div class="p-5">
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                   class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">Active (user can log in and use the system)</span>
                        </label>
                    </div>
                </div>

                <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                        <h3 class="text-base font-semibold text-gray-900">Change Password</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <p class="text-sm text-gray-500">Leave blank to keep current password.</p>
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">New Password</label>
                            <input type="password" name="password" id="password"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm @error('password') border-red-500 @enderror"
                                   placeholder="Leave blank to keep current">
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm"
                                   placeholder="Re-enter new password">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 p-4 sm:p-6 flex flex-col-reverse sm:flex-row justify-end gap-3">
            <a href="{{ url('/admin/users') }}"
               class="inline-flex items-center justify-center px-5 py-3 border border-gray-300 rounded-xl font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full sm:w-auto min-h-[46px]">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center px-7 py-3 border border-transparent rounded-xl font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full sm:w-auto min-h-[46px]">
                Update User
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const universitySelect = document.getElementById('university_select');
    const newUniversityContainer = document.getElementById('new-university-container');
    const newUniversityInput = document.getElementById('new_university_name');
    const roleSelect = document.getElementById('role');
    const requiredHoursWrapper = document.getElementById('required_training_hours_wrapper');
    const studentRulesComplianceWrapper = document.getElementById('student_rules_compliance_wrapper');
    const leaveBalancesWrapper = document.getElementById('leave_balances_wrapper');
    const departmentWrapper = document.getElementById('department_wrapper');
    const departmentSelect = document.getElementById('department_id');
    const universityRequiredIndicator = document.getElementById('university_required_indicator');

    if (universitySelect && newUniversityContainer && newUniversityInput) {
        universitySelect.addEventListener('change', function() {
            if (this.value === 'new') {
                newUniversityContainer.classList.remove('hidden');
                newUniversityInput.focus();
                newUniversityInput.required = true;
            } else {
                newUniversityContainer.classList.add('hidden');
                newUniversityInput.value = '';
                newUniversityInput.required = false;
            }
        });
        if (universitySelect.value === 'new') {
            newUniversityContainer.classList.remove('hidden');
            newUniversityInput.required = true;
        }
    }

    function toggleRequiredHours() {
        if (roleSelect && requiredHoursWrapper) {
            requiredHoursWrapper.classList.toggle('hidden', roleSelect.value !== 'student');
        }
    }
    function toggleStudentRulesCompliance() {
        if (roleSelect && studentRulesComplianceWrapper) {
            studentRulesComplianceWrapper.classList.toggle('hidden', roleSelect.value !== 'student');
        }
    }
    function toggleLeaveBalances() {
        if (roleSelect && leaveBalancesWrapper) {
            leaveBalancesWrapper.classList.toggle('hidden', roleSelect.value !== 'employee');
        }
    }
    function toggleDepartment() {
        if (roleSelect && departmentWrapper && departmentSelect) {
            const show = roleSelect.value === 'employee' || roleSelect.value === 'student';
            departmentWrapper.classList.toggle('hidden', !show);
            departmentSelect.required = roleSelect.value === 'employee';
            const departmentRequiredIndicator = document.getElementById('department_required_indicator');
            if (departmentRequiredIndicator) {
                departmentRequiredIndicator.classList.toggle('hidden', roleSelect.value !== 'employee');
            }
            if (!show) departmentSelect.value = '';
        }
    }

    function toggleUniversityRequirement() {
        if (roleSelect && universitySelect) {
            const isTeacher = roleSelect.value === 'teacher';
            universitySelect.required = isTeacher;
            if (universityRequiredIndicator) {
                universityRequiredIndicator.classList.toggle('hidden', !isTeacher);
            }
        }
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            toggleRequiredHours();
            toggleStudentRulesCompliance();
            toggleLeaveBalances();
            toggleDepartment();
            toggleUniversityRequirement();
        });
        toggleRequiredHours();
        toggleStudentRulesCompliance();
        toggleLeaveBalances();
        toggleDepartment();
        toggleUniversityRequirement();
    }

    const studentRulesWarningCb = document.getElementById('student_rules_warning');
    const studentRulesMarqueeCb = document.getElementById('student_rules_marquee_enabled');
    if (studentRulesWarningCb && studentRulesMarqueeCb) {
        const meritAutomationCb = document.getElementById('student_rules_allow_merit_automation');
        function syncMeritAutomationCheckbox() {
            if (!meritAutomationCb) return;
            if (!studentRulesWarningCb.checked && !studentRulesMarqueeCb.checked) {
                meritAutomationCb.checked = false;
            }
        }
        studentRulesWarningCb.addEventListener('change', function() {
            if (this.checked) {
                studentRulesMarqueeCb.checked = false;
            }
            syncMeritAutomationCheckbox();
        });
        studentRulesMarqueeCb.addEventListener('change', function() {
            if (this.checked) {
                studentRulesWarningCb.checked = false;
            }
            syncMeritAutomationCheckbox();
        });

        syncMeritAutomationCheckbox();
    }

    const form = document.getElementById('editUserForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (universitySelect && universitySelect.value === 'new' && (!newUniversityInput || !newUniversityInput.value.trim())) {
                e.preventDefault();
                if (typeof ToastNotification !== 'undefined') {
                    ToastNotification.warning('Please enter a university name when selecting "Add New University"');
                } else {
                    alert('Please enter a university name when selecting "Add New University"');
                }
                if (newUniversityContainer) newUniversityContainer.classList.remove('hidden');
                if (newUniversityInput) {
                    newUniversityInput.focus();
                    newUniversityInput.required = true;
                }
                return false;
            }
            if (roleSelect && roleSelect.value === 'student'
                && studentRulesWarningCb && studentRulesMarqueeCb
                && studentRulesWarningCb.checked && studentRulesMarqueeCb.checked) {
                e.preventDefault();
                const msg = 'Rules violation warning and final notice cannot both be enabled. Uncheck one of them.';
                if (typeof ToastNotification !== 'undefined') {
                    ToastNotification.warning(msg);
                } else {
                    alert(msg);
                }
                return false;
            }
        });
    }
});
</script>
@endsection
