@extends('layouts.admin')

@section('page-title', 'Edit Admin Permissions')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Admin Permissions</span>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Edit</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white">Edit Admin Permissions</h2>
                <p class="mt-1 text-sm text-indigo-100">Manage feature access for {{ $user->name }} ({{ $user->getRoleLabel() }})</p>
            </div>
            <a href="{{ url('/admin/admin-permissions') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-700 hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="flex-1 bg-white rounded-lg shadow-sm border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6 p-4 sm:p-6">
        <form action="{{ url('/admin/admin-permissions/' . $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- User Info -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-2">User Information</h3>
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0 h-12 w-12">
                        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-indigo-600 font-medium">{{ $user->getInitials() }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $user->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                {{ $user->getRoleLabel() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Feature Permissions</h3>
                <p class="text-sm text-gray-600 mb-6">Toggle the features this {{ strtolower($user->getRoleLabel()) }} can access. If no permissions are set, the user will have full access to all features.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Content Management -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="content_management"
                               id="content_management"
                               value="1"
                               {{ ($permission && $permission->content_management) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="content_management" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                Content Management
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to quizzes, forum, and content creation</p>
                        </div>
                    </div>

                    <!-- Analytics & Reports -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="analytics_reports"
                               id="analytics_reports"
                               value="1"
                               {{ ($permission && $permission->analytics_reports) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="analytics_reports" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                Analytics & Reports
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to analytics dashboard and reports</p>
                        </div>
                    </div>

                    <!-- Employee Management -->
                    <div class="md:col-span-2">
                        <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <input type="checkbox"
                                   name="employee_management"
                                   id="employee_management"
                                   value="1"
                                   {{ ($permission && $permission->employee_management) ? 'checked' : '' }}
                                   class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                   onchange="toggleDepartmentSelection()">
                            <div class="flex-1">
                                <label for="employee_management" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                    Employee Management
                                </label>
                                <p class="mt-1 text-sm text-gray-500">Access to employee DTR, leave requests, and time reports</p>

                                <!-- Employee Department Selection -->
                                <div id="employee-department-selection" class="mt-4 {{ ($permission && $permission->employee_management) ? '' : 'hidden' }}">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Allowed Employee Departments
                                        <span class="text-xs text-gray-500 font-normal">(Leave empty to allow all departments)</span>
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-48 overflow-y-auto p-3 bg-gray-50 rounded-md border border-gray-200">
                                        @foreach($departments as $department)
                                            <div class="flex items-center">
                                                <input type="checkbox"
                                                       name="allowed_employee_departments[]"
                                                       id="employee_dept_{{ $department->id }}"
                                                       value="{{ $department->id }}"
                                                       {{ ($permission && (($permission->allowed_employee_departments ?? $permission->allowed_departments) && in_array($department->id, ($permission->allowed_employee_departments ?? $permission->allowed_departments)))) ? 'checked' : '' }}
                                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="employee_dept_{{ $department->id }}" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                                    {{ $department->name }}
                                                    @if($department->code)
                                                        <span class="text-gray-500">({{ $department->code }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($departments->isEmpty())
                                        <p class="mt-2 text-sm text-gray-500">No departments available. <a href="{{ url('/admin/departments') }}" class="text-indigo-600 hover:text-indigo-800">Create departments</a> first.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Management -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="student_management"
                               id="student_management"
                               value="1"
                               {{ ($permission && $permission->student_management) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                               onchange="toggleDepartmentSelection()">
                        <div class="flex-1">
                            <label for="student_management" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                Student Management
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to student dashboard, DTR, and leave requests</p>

                            <!-- Student Department Selection -->
                            <div id="student-department-selection" class="mt-4 {{ ($permission && $permission->student_management) ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Allowed Student Departments
                                    <span class="text-xs text-gray-500 font-normal">(Leave empty to allow all departments)</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-48 overflow-y-auto p-3 bg-gray-50 rounded-md border border-gray-200">
                                    @foreach($departments as $department)
                                        <div class="flex items-center">
                                            <input type="checkbox"
                                                   name="allowed_student_departments[]"
                                                   id="student_dept_{{ $department->id }}"
                                                   value="{{ $department->id }}"
                                                   {{ ($permission && (($permission->allowed_student_departments ?? $permission->allowed_departments) && in_array($department->id, ($permission->allowed_student_departments ?? $permission->allowed_departments)))) ? 'checked' : '' }}
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                            <label for="student_dept_{{ $department->id }}" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                                {{ $department->name }}
                                                @if($department->code)
                                                    <span class="text-gray-500">({{ $department->code }})</span>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @if($departments->isEmpty())
                                    <p class="mt-2 text-sm text-gray-500">No departments available. <a href="{{ url('/admin/departments') }}" class="text-indigo-600 hover:text-indigo-800">Create departments</a> first.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Hiring Process -->
                    <div class="md:col-span-2">
                        <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <input type="checkbox"
                                   name="hiring_process"
                                   id="hiring_process"
                                   value="1"
                                   {{ ($permission && $permission->hiring_process) ? 'checked' : '' }}
                                   class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                   onchange="togglePositionSelection()">
                            <div class="flex-1">
                                <label for="hiring_process" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                    Hiring Process
                                </label>
                                <p class="mt-1 text-sm text-gray-500">Access to hiring positions and applications</p>

                                <!-- Position Selection (shown only when Hiring Process is checked) -->
                                <div id="position-selection" class="mt-4 {{ ($permission && $permission->hiring_process) ? '' : 'hidden' }}">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Allowed Positions
                                        <span class="text-xs text-gray-500 font-normal">(Leave empty to allow all positions)</span>
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-48 overflow-y-auto p-3 bg-gray-50 rounded-md border border-gray-200">
                                        @foreach($hiringPositions as $position)
                                            <div class="flex items-center">
                                                <input type="checkbox"
                                                       name="allowed_positions[]"
                                                       id="pos_{{ $position->id }}"
                                                       value="{{ $position->id }}"
                                                       {{ ($permission && $permission->allowed_positions && in_array($position->id, $permission->allowed_positions)) ? 'checked' : '' }}
                                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="pos_{{ $position->id }}" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                                    {{ $position->title }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($hiringPositions->isEmpty())
                                        <p class="mt-2 text-sm text-gray-500">No hiring positions available. <a href="{{ url('/admin/hiring-positions') }}" class="text-indigo-600 hover:text-indigo-800">Create positions</a> first.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Communication -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="communication"
                               id="communication"
                               value="1"
                               {{ ($permission && $permission->communication) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="communication" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                Communication
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to live chat, contact messages, and notifications</p>
                        </div>
                    </div>

                    <!-- Linked Accounts -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="linked_accounts"
                               id="linked_accounts"
                               value="1"
                               {{ ($permission && ($permission->linked_accounts ?? false)) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="linked_accounts" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                Linked Accounts
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to Linked Accounts dashboard, Starlinks, Omada, and Plan Types</p>
                        </div>
                    </div>

                    <!-- Billing -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="billing"
                               id="billing"
                               value="1"
                               {{ ($permission && $permission->billing) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="billing" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                Billing
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to Starlink/Omada billing, mark as paid, advance payment, and statements</p>
                        </div>
                    </div>

                    <!-- File Storage -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="files"
                               id="files"
                               value="1"
                               {{ ($permission && $permission->files) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="files" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                File Storage
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to admin file storage and shared files</p>
                        </div>
                    </div>

                    <!-- Confession (Say-it) -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="confession"
                               id="confession"
                               value="1"
                               {{ ($permission && $permission->confession) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="confession" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                Confession (Say-it)
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to confession board management, topics, and banned words</p>
                        </div>
                    </div>

                    <!-- Feedback -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="feedback"
                               id="feedback"
                               value="1"
                               {{ ($permission && $permission->feedback) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="feedback" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                Feedback
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to feedback management and assignment</p>
                        </div>
                    </div>

                    <!-- User Management -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="user_management"
                               id="user_management"
                               value="1"
                               {{ ($permission && $permission->user_management) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="user_management" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                User Management
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to user management and administration</p>
                        </div>
                    </div>

                    <!-- System -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <input type="checkbox"
                               name="system"
                               id="system"
                               value="1"
                               {{ ($permission && $permission->system) ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <div class="flex-1">
                            <label for="system" class="block text-sm font-medium text-gray-900 cursor-pointer">
                                System
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Access to settings, error logs, and user activity</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ url('/admin/admin-permissions') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Permissions
                </button>
            </div>
        </form>

        <!-- Remove Permissions (Make Super Admin/Employee) -->
        @if($permission)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <form action="{{ url('/admin/admin-permissions/' . $user->id) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to remove all permission restrictions for this {{ strtolower($user->getRoleLabel()) }}? They will have full access to all features.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Remove All Restrictions (Full Access)
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
function toggleDepartmentSelection() {
    const employeeManagementCheckbox = document.getElementById('employee_management');
    const studentManagementCheckbox = document.getElementById('student_management');
    const employeeDepartmentSelection = document.getElementById('employee-department-selection');
    const studentDepartmentSelection = document.getElementById('student-department-selection');

    if (employeeManagementCheckbox && employeeDepartmentSelection) {
        if (employeeManagementCheckbox.checked) {
            employeeDepartmentSelection.classList.remove('hidden');
        } else {
            employeeDepartmentSelection.classList.add('hidden');
            const departmentCheckboxes = employeeDepartmentSelection.querySelectorAll('input[type="checkbox"]');
            departmentCheckboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
        }
    }

    if (studentManagementCheckbox && studentDepartmentSelection) {
        if (studentManagementCheckbox.checked) {
            studentDepartmentSelection.classList.remove('hidden');
        } else {
            studentDepartmentSelection.classList.add('hidden');
            const departmentCheckboxes = studentDepartmentSelection.querySelectorAll('input[type="checkbox"]');
            departmentCheckboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
        }
    }
}

function togglePositionSelection() {
    const hiringProcessCheckbox = document.getElementById('hiring_process');
    const positionSelection = document.getElementById('position-selection');

    if (hiringProcessCheckbox && positionSelection) {
        if (hiringProcessCheckbox.checked) {
            positionSelection.classList.remove('hidden');
        } else {
            positionSelection.classList.add('hidden');
            // Uncheck all position checkboxes when Hiring Process is disabled
            const positionCheckboxes = positionSelection.querySelectorAll('input[type="checkbox"]');
            positionCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleDepartmentSelection();
    togglePositionSelection();
});
</script>
@endsection


