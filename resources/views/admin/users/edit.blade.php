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
                            <label for="university_select" class="block text-sm font-semibold text-gray-700 mb-1.5">University/School</label>
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

                        <div id="department_wrapper" class="{{ old('role', $user->role) === 'employee' ? '' : 'hidden' }}">
                            <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-1.5">Department <span class="text-red-500">*</span></label>
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
                            <p class="mt-1 text-xs text-gray-500">For students: total hours needed via DTR.</p>
                            @error('required_training_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                @php
                    $currentYear = now()->year;
                    $leaveBalance = \App\Models\LeaveBalance::where('user_id', $user->id)->where('year', $currentYear)->first();
                    $vacationAllowance = old('vacation_allowance', $leaveBalance ? $leaveBalance->vacation_allowance : '');
                    $sickAllowance = old('sick_allowance', $leaveBalance ? $leaveBalance->sick_allowance : '');
                @endphp
                <div id="leave_balances_wrapper" class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 overflow-hidden {{ old('role', $user->role) === 'employee' ? '' : 'hidden' }}">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                        <h2 class="text-lg font-semibold text-gray-900">Leave Balances ({{ $currentYear }})</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Applicable for employee role</p>
                    </div>
                    <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="vacation_allowance" class="block text-sm font-semibold text-gray-700 mb-1.5">Vacation (days)</label>
                            <input type="number" name="vacation_allowance" id="vacation_allowance" step="0.01" min="0"
                                   value="{{ $vacationAllowance }}"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm"
                                   placeholder="e.g. 15">
                            @error('vacation_allowance') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="sick_allowance" class="block text-sm font-semibold text-gray-700 mb-1.5">Sick (days)</label>
                            <input type="number" name="sick_allowance" id="sick_allowance" step="0.01" min="0"
                                   value="{{ $sickAllowance }}"
                                   class="block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm"
                                   placeholder="e.g. 10">
                            @error('sick_allowance') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
    const leaveBalancesWrapper = document.getElementById('leave_balances_wrapper');
    const departmentWrapper = document.getElementById('department_wrapper');
    const departmentSelect = document.getElementById('department_id');

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
    function toggleLeaveBalances() {
        if (roleSelect && leaveBalancesWrapper) {
            leaveBalancesWrapper.classList.toggle('hidden', roleSelect.value !== 'employee');
        }
    }
    function toggleDepartment() {
        if (roleSelect && departmentWrapper && departmentSelect) {
            const show = roleSelect.value === 'employee';
            departmentWrapper.classList.toggle('hidden', !show);
            departmentSelect.required = show;
            if (!show) departmentSelect.value = '';
        }
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            toggleRequiredHours();
            toggleLeaveBalances();
            toggleDepartment();
        });
        toggleRequiredHours();
        toggleLeaveBalances();
        toggleDepartment();
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
        });
    }
});
</script>
@endsection
