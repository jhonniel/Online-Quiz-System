@extends('layouts.admin')

@section('page-title', 'Create New User')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Users</span>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Create</span>
        </div>
    </li>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">
    <!-- Header Section with Gradient -->
    <div class="mb-6 sm:mb-8">
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl sm:rounded-2xl shadow-xl p-5 sm:p-8 text-white relative overflow-hidden">
            <!-- Decorative background elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-10 rounded-full -ml-24 -mb-24"></div>

            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="bg-white/20 backdrop-blur-sm p-3 sm:p-4 rounded-xl flex-shrink-0">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-3xl font-bold mb-1">Create New User</h1>
                        <p class="text-indigo-100 text-sm sm:text-lg">Add a new user account with proper permissions and settings</p>
                    </div>
                </div>
                <a href="{{ url('/admin/users') }}" class="inline-flex items-center justify-center bg-white/20 hover:bg-white/30 backdrop-blur-sm px-5 py-2.5 sm:px-6 sm:py-3 rounded-xl font-semibold transition-all duration-200 w-full sm:w-auto touch-manipulation min-h-[44px] sm:min-h-0">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Back to Users</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <form action="{{ url('/admin/users') }}" method="POST" id="createUserForm" class="divide-y divide-gray-100">
            @csrf

            <!-- Basic Information Section -->
            <div class="p-4 sm:p-6 lg:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="bg-indigo-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Basic Information</h2>
                        <p class="text-sm text-gray-500">Essential details for the user account</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   id="name"
                        name="name"
                                   value="{{ old('name') }}"
                                   required
                                   placeholder="John Doe"
                                   class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white @error('name') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                        </div>
                        @error('name')
                            <p class="text-sm text-red-600 flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-gray-700">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <input type="email"
                                   id="email"
                        name="email"
                                   value="{{ old('email') }}"
                                   required
                                   placeholder="john.doe@example.com"
                                   class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white @error('email') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Used for login and notifications</p>
                        @error('email')
                            <p class="text-sm text-red-600 flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                </div>

                    <!-- Role -->
                    <div class="space-y-2">
                        <label for="role" class="block text-sm font-semibold text-gray-700">
                            User Role <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <select name="role"
                                    id="role"
                                    required
                                    class="block w-full pl-12 pr-10 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white appearance-none cursor-pointer @error('role') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                <option value="">Select a role</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                                <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="technician" {{ old('role') == 'technician' ? 'selected' : '' }}>Technician</option>
                                <option value="applicant" {{ old('role') == 'applicant' ? 'selected' : '' }}>Applicant</option>
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User (Legacy)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Define user permissions and access level</p>
                        @error('role')
                            <p class="text-sm text-red-600 flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
            </div>

                    <!-- University -->
                    <div class="space-y-2">
                        <label for="university_select" class="block text-sm font-semibold text-gray-700">
                            University/School
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <select name="university_id"
                                    id="university_select"
                                    class="block w-full pl-12 pr-10 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white appearance-none cursor-pointer">
                                <option value="">Select or add new (optional)</option>
                                @foreach($universities as $university)
                                    <option value="{{ $university->id }}" {{ old('university_id') == $university->id ? 'selected' : '' }}>
                                        {{ $university->name }}
                                    </option>
                                @endforeach
                                <option value="new" class="text-indigo-600 font-semibold">+ Add New University</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- New University Input -->
                        <div id="new-university-container" class="hidden mt-3 transition-all duration-300">
                            <label for="new_university_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                New University Name <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                       name="new_university_name"
                                       id="new_university_name"
                                       placeholder="Enter university name"
                                       class="block w-full px-4 py-3 border-2 border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-indigo-50 focus:bg-white @error('new_university_name') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                            </div>
                            @error('new_university_name')
                                <p class="text-sm text-red-600 flex items-center mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Department (Employees and Students) -->
                    <div class="space-y-2" id="department_wrapper"
                         @if(in_array(old('role'), ['employee', 'student'], true)) style="" @else style="display:none;" @endif>
                        <label for="department_id" class="block text-sm font-semibold text-gray-700">
                            Department <span class="text-red-500" id="department_required_indicator" @if(old('role') !== 'employee') style="display:none;" @endif>*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <select name="department_id"
                                    id="department_id"
                                    class="block w-full pl-12 pr-10 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white appearance-none cursor-pointer @error('department_id') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                <option value="">Select a department</option>
                                @foreach($departments ?? [] as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            <a href="{{ url('/admin/departments/create') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 underline">Create new department</a> if not in the list
                        </p>
                        @error('department_id')
                            <p class="text-sm text-red-600 flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Required Training Hours (Students Only) -->
                    <div class="space-y-2" id="required_training_hours_wrapper"
                         @if(old('role') === 'student') style="" @else style="display:none;" @endif>
                        <label for="required_training_hours" class="block text-sm font-semibold text-gray-700">
                            Required Training Hours (Time Needed to Acquire)
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="required_training_hours"
                                   id="required_training_hours"
                                   value="{{ old('required_training_hours') }}"
                                   placeholder="e.g. 160 (for 160 hours)"
                                   class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white @error('required_training_hours') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Optional. For <span class="font-semibold">students</span>, this is the total hours they need to acquire via DTR.
                        </p>
                        @error('required_training_hours')
                            <p class="text-sm text-red-600 flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Leave Balances (Employees Only) -->
                    @php
                        $currentYear = now()->year;
                    @endphp
                    <div class="space-y-2" id="leave_balances_wrapper"
                         @if(old('role') === 'employee') style="" @else style="display:none;" @endif>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="vacation_allowance" class="block text-sm font-semibold text-gray-700">
                                    Vacation Leave Balance (Days)
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="vacation_allowance"
                                           id="vacation_allowance"
                                           value="{{ old('vacation_allowance') }}"
                                           placeholder="e.g. 15"
                                           class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white @error('vacation_allowance') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Vacation leave balance for {{ $currentYear }}.
                                </p>
                                @error('vacation_allowance')
                                    <p class="text-sm text-red-600 flex items-center mt-1">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="sick_allowance" class="block text-sm font-semibold text-gray-700">
                                    Sick Leave Balance (Days)
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="sick_allowance"
                                           id="sick_allowance"
                                           value="{{ old('sick_allowance') }}"
                                           placeholder="e.g. 10"
                                           class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white @error('sick_allowance') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Sick leave balance for {{ $currentYear }}.
                                </p>
                                @error('sick_allowance')
                                    <p class="text-sm text-red-600 flex items-center mt-1">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Section -->
            <div class="p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-gray-50 to-white">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="bg-purple-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Security Settings</h2>
                        <p class="text-sm text-gray-500">Set up password and account access</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Password -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   required
                                   placeholder="Enter secure password"
                                   class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white @error('password') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                            <button type="button"
                                    onclick="togglePassword('password')"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                                <svg id="password-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="password-strength" class="hidden mt-2">
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div id="password-strength-bar" class="h-full transition-all duration-300 rounded-full"></div>
                                </div>
                                <span id="password-strength-text" class="text-xs font-medium"></span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimum 8 characters with letters and numbers</p>
                        @error('password')
                            <p class="text-sm text-red-600 flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   placeholder="Re-enter password"
                                   class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-gray-50 focus:bg-white @error('password_confirmation') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                            <button type="button"
                                    onclick="togglePassword('password_confirmation')"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                                <svg id="password_confirmation-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="password-match" class="hidden mt-2">
                            <p class="text-sm flex items-center">
                                <svg id="match-icon" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span id="match-text"></span>
                            </p>
                        </div>
                        @error('password_confirmation')
                            <p class="text-sm text-red-600 flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Account Status Section -->
            <div class="p-4 sm:p-6 lg:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="bg-green-100 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Account Status</h2>
                        <p class="text-sm text-gray-500">Control user access and permissions</p>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6">
                    <label class="flex items-start space-x-4 cursor-pointer group">
                        <div class="relative flex-shrink-0">
                            <input type="checkbox"
                                   name="is_active"
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600"></div>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 group-hover:text-green-700 transition-colors">
                                Active Account
                            </div>
                            <p class="text-sm text-gray-600 mt-1">
                                When enabled, the user can log in and access the system. Disable to temporarily restrict access.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="p-4 sm:p-6 lg:p-8 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4">
                    <a href="{{ url('/admin/users') }}"
                       class="inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 rounded-xl font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 w-full sm:w-auto min-h-[48px] sm:min-h-0 touch-manipulation">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-8 py-3 border border-transparent rounded-xl font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-lg hover:shadow-xl transition-all duration-200 w-full sm:w-auto min-h-[48px] sm:min-h-0 touch-manipulation">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create User
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const universitySelect = document.getElementById('university_select');
    const newUniversityContainer = document.getElementById('new-university-container');
    const newUniversityInput = document.getElementById('new_university_name');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');
    const passwordStrengthDiv = document.getElementById('password-strength');
    const passwordStrengthBar = document.getElementById('password-strength-bar');
    const passwordStrengthText = document.getElementById('password-strength-text');
    const passwordMatchDiv = document.getElementById('password-match');
    const matchIcon = document.getElementById('match-icon');
    const matchText = document.getElementById('match-text');
    const roleSelect = document.getElementById('role');
    const requiredHoursWrapper = document.getElementById('required_training_hours_wrapper');

    // University selection toggle
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

        // Initialize on page load
        if (universitySelect.value === 'new') {
            newUniversityContainer.classList.remove('hidden');
            newUniversityInput.required = true;
        }
    }

    // Show Required Training Hours only for students
    if (roleSelect && requiredHoursWrapper) {
        function toggleRequiredHours() {
            if (roleSelect.value === 'student') {
                requiredHoursWrapper.style.display = '';
            } else {
                requiredHoursWrapper.style.display = 'none';
            }
        }

        roleSelect.addEventListener('change', toggleRequiredHours);
        // Initialize on load
        toggleRequiredHours();
    }

    // Show Leave Balances only for employees
    const leaveBalancesWrapper = document.getElementById('leave_balances_wrapper');
    if (roleSelect && leaveBalancesWrapper) {
        function toggleLeaveBalances() {
            if (roleSelect.value === 'employee') {
                leaveBalancesWrapper.style.display = '';
            } else {
                leaveBalancesWrapper.style.display = 'none';
            }
        }

        roleSelect.addEventListener('change', toggleLeaveBalances);
        // Initialize on load
        toggleLeaveBalances();
    }

    // Show Department for employees and students
    const departmentWrapper = document.getElementById('department_wrapper');
    const departmentSelect = document.getElementById('department_id');
    const departmentRequiredIndicator = document.getElementById('department_required_indicator');
    if (roleSelect && departmentWrapper) {
        function toggleDepartment() {
            const show = roleSelect.value === 'employee' || roleSelect.value === 'student';
            if (show) {
                departmentWrapper.style.display = '';
                if (departmentSelect) {
                    departmentSelect.required = roleSelect.value === 'employee';
                }
                if (departmentRequiredIndicator) {
                    departmentRequiredIndicator.style.display = roleSelect.value === 'employee' ? '' : 'none';
                }
            } else {
                departmentWrapper.style.display = 'none';
                if (departmentSelect) {
                    departmentSelect.required = false;
                    departmentSelect.value = '';
                }
                if (departmentRequiredIndicator) {
                    departmentRequiredIndicator.style.display = 'none';
                }
            }
        }

        roleSelect.addEventListener('change', toggleDepartment);
        // Initialize on load
        toggleDepartment();
    }

    // Password strength checker
    if (passwordInput && passwordStrengthDiv) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            if (password.length > 0) {
                passwordStrengthDiv.classList.remove('hidden');
                const strength = calculatePasswordStrength(password);
                updatePasswordStrength(strength);
            } else {
                passwordStrengthDiv.classList.add('hidden');
            }
        });
    }

    // Password match checker
    if (passwordInput && passwordConfirmationInput && passwordMatchDiv) {
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmation = passwordConfirmationInput.value;

            if (confirmation.length > 0) {
                passwordMatchDiv.classList.remove('hidden');
                if (password === confirmation && password.length > 0) {
                    matchIcon.classList.remove('text-red-500');
                    matchIcon.classList.add('text-green-500');
                    matchText.textContent = 'Passwords match';
                    matchText.classList.remove('text-red-600');
                    matchText.classList.add('text-green-600');
                } else {
                    matchIcon.classList.remove('text-green-500');
                    matchIcon.classList.add('text-red-500');
                    matchText.textContent = 'Passwords do not match';
                    matchText.classList.remove('text-green-600');
                    matchText.classList.add('text-red-600');
                }
            } else {
                passwordMatchDiv.classList.add('hidden');
            }
        }

        passwordInput.addEventListener('input', checkPasswordMatch);
        passwordConfirmationInput.addEventListener('input', checkPasswordMatch);
    }

    // Form validation
    const form = document.getElementById('createUserForm');
        if (form) {
            form.addEventListener('submit', function(e) {
            if (universitySelect && universitySelect.value === 'new' && (!newUniversityInput || !newUniversityInput.value.trim())) {
                    e.preventDefault();
                if (typeof ToastNotification !== 'undefined') {
                    ToastNotification.warning('Please enter a university name when selecting "Add New University"');
                } else {
                    alert('Please enter a university name when selecting "Add New University"');
                }
                if (newUniversityInput) {
                    newUniversityInput.focus();
                }
                return false;
                }
            });
        }
});

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(fieldId + '-eye');

    if (field.type === 'password') {
        field.type = 'text';
        eye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0a9.953 9.953 0 015.07-1.458M6.29 6.29L12 12m6.71-5.71a9.953 9.953 0 011.458 5.07M18.71 18.71A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029"></path>';
    } else {
        field.type = 'password';
        eye.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
}

function calculatePasswordStrength(password) {
    let strength = 0;

    if (password.length >= 8) strength += 1;
    if (password.length >= 12) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/\d/.test(password)) strength += 1;
    if (/[^a-zA-Z\d]/.test(password)) strength += 1;

    return Math.min(strength, 4);
}

function updatePasswordStrength(strength) {
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
    const texts = ['Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
    const widths = ['25%', '50%', '75%', '100%'];

    passwordStrengthBar.className = `h-full transition-all duration-300 rounded-full ${colors[strength - 1] || colors[0]}`;
    passwordStrengthBar.style.width = widths[strength - 1] || widths[0];
    passwordStrengthText.textContent = texts[strength] || texts[0];
    passwordStrengthText.className = `text-xs font-medium ${strength >= 3 ? 'text-green-600' : strength >= 2 ? 'text-yellow-600' : 'text-red-600'}`;
}
</script>
@endsection
