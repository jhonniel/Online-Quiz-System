@extends('layouts.admin')

@php
    $isTeacherView = (bool) ($isTeachersManagement ?? false) || (($roleFilter ?? request('role')) === 'teacher');
    $managementTitle = $isTeacherView ? 'Teachers Management' : 'User Management';
    $managementDescription = $isTeacherView
        ? 'Manage teacher accounts and invite links'
        : 'Manage system users, roles, and permissions';
    $usersIndexUrl = $isTeacherView ? url('/admin/teachers-management/teachers') : url('/admin/users');
    $usersExportQuery = array_filter([
        'search' => request('search', $search ?? ''),
        'school' => $schoolId ?? '',
        'role' => $isTeacherView ? '' : ($roleFilter ?? ''),
        'department' => (!$isTeacherView && ($roleFilter ?? '') === 'employee') ? ($departmentFilter ?? '') : '',
    ], fn ($value) => $value !== null && $value !== '');
    $usersExportPdfUrl = $isTeacherView
        ? route('admin.teachers-management.export-pdf', $usersExportQuery)
        : route('admin.users.export-pdf', $usersExportQuery);
@endphp

@section('page-title', $managementTitle)

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">{{ $isTeacherView ? 'Teachers Management' : 'Users' }}</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-4 min-w-0 px-3 sm:px-4 lg:px-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-xl shadow-sm p-4 sm:p-5 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight truncate">{{ $managementTitle }}</h1>
                    <p class="text-indigo-100 text-xs sm:text-sm mt-0.5">{{ $managementDescription }}</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-xs text-indigo-100">
                <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1">Total: {{ $users->total() }}</span>
                <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1">Page: {{ $users->count() }}</span>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm sm:text-base" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm sm:text-base" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm sm:text-base" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="min-w-0">
                    <strong class="font-medium">Validation errors:</strong>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded-lg text-sm sm:text-base" role="alert">
            <p class="font-medium mb-2">Import warnings</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Quick Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-shrink-0">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Users</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($users->total()) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Active (This Page)</p>
            <p class="mt-1 text-2xl font-semibold text-green-700">{{ $users->getCollection()->where('is_active', true)->count() }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Pending Approval (This Page)</p>
            <p class="mt-1 text-2xl font-semibold text-amber-700">{{ $users->getCollection()->where('is_approved', false)->count() }}</p>
        </div>
    </div>

    @if(!$isTeacherView)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5 flex-shrink-0">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-900">Import Employees</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Upload a CSV to create new employee accounts or update existing employee profiles.
                    New accounts use the default password <span class="font-medium text-gray-800">password</span>.
                    Existing employees are matched by email or name.
                </p>
                <p class="mt-2 text-xs text-gray-500">
                    Columns: email, employee_name, date_hired, tin, sss, hdmf, phic
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 shrink-0">
                <a href="{{ route('admin.users.employee-profile-template') }}"
                   class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Download Template
                </a>
            </div>
        </div>
        <form action="{{ route('admin.users.import-employee-profile') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col sm:flex-row sm:items-end gap-3">
            @csrf
            <div class="flex-1">
                <label for="employee_profile_csv" class="block text-sm font-medium text-gray-700 mb-1">CSV File</label>
                <input type="file" name="csv_file" id="employee_profile_csv" accept=".csv,text/csv" required
                       class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('csv_file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                Import Employees
            </button>
        </form>
    </div>
    @endif

    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex-shrink-0">
        <div class="flex flex-col lg:flex-row lg:flex-wrap lg:items-center gap-3 sm:gap-4">
            <form id="users-search-form" method="GET" action="{{ $usersIndexUrl }}" class="flex-1 min-w-0">
                @if(request()->has('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                @if(isset($schoolId) && $schoolId !== '' && $schoolId !== null)
                    <input type="hidden" name="school" value="{{ $schoolId }}">
                @endif
                @if(isset($roleFilter) && $roleFilter !== '')
                    <input type="hidden" name="role" value="{{ $roleFilter }}">
                @endif
                @if(($roleFilter ?? '') === 'employee' && !empty($departmentFilter))
                    <input type="hidden" name="department" value="{{ $departmentFilter }}">
                @endif
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           id="search-input"
                           name="search"
                           value="{{ request('search', $search ?? '') }}"
                           placeholder="Search name, email, role, dept, position, university, or ID..."
                           autocomplete="off"
                           class="block w-full pl-9 pr-10 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @if(request('search'))
                        <button type="button"
                                id="clear-search-btn"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 touch-manipulation"
                                title="Clear search">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            </form>
            <div class="flex-shrink-0 self-start lg:self-auto">
                <button type="submit" form="users-search-form"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Search
                </button>
            </div>
            @if(isset($schools) && $schools->isNotEmpty())
            <form method="GET" action="{{ $usersIndexUrl }}" class="flex-shrink-0" id="school-filter-form">
                @if(request()->has('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(isset($roleFilter) && $roleFilter !== '')
                    <input type="hidden" name="role" value="{{ $roleFilter }}">
                @endif
                @if(($roleFilter ?? '') === 'employee' && !empty($departmentFilter))
                    <input type="hidden" name="department" value="{{ $departmentFilter }}">
                @endif
                <div class="flex items-center gap-2">
                    <label for="school-filter" class="text-sm font-medium text-gray-700 whitespace-nowrap">School</label>
                    <select name="school" id="school-filter" class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 min-w-[140px]">
                        <option value="">All schools</option>
                        @foreach($schools as $s)
                            <option value="{{ $s->id }}" {{ (isset($schoolId) && (string)$schoolId === (string)$s->id) ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            @endif
            @if(!$isTeacherView)
                <form method="GET" action="{{ $usersIndexUrl }}" class="flex-shrink-0" id="role-filter-form">
                    @if(request()->has('per_page'))
                        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(isset($schoolId) && $schoolId !== '' && $schoolId !== null)
                        <input type="hidden" name="school" value="{{ $schoolId }}">
                    @endif
                    <div class="flex items-center gap-2">
                        <label for="role-filter" class="text-sm font-medium text-gray-700 whitespace-nowrap">Role</label>
                        <select name="role" id="role-filter" class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 min-w-[140px]">
                            <option value="">All roles</option>
                            <option value="admin" {{ (isset($roleFilter) && $roleFilter === 'admin') ? 'selected' : '' }}>Administrator</option>
                            <option value="student" {{ (isset($roleFilter) && $roleFilter === 'student') ? 'selected' : '' }}>Student</option>
                            <option value="employee" {{ (isset($roleFilter) && $roleFilter === 'employee') ? 'selected' : '' }}>Employee</option>
                            <option value="hr" {{ (isset($roleFilter) && $roleFilter === 'hr') ? 'selected' : '' }}>HR</option>
                            <option value="teacher" {{ (isset($roleFilter) && $roleFilter === 'teacher') ? 'selected' : '' }}>Teacher</option>
                            <option value="technician" {{ (isset($roleFilter) && $roleFilter === 'technician') ? 'selected' : '' }}>Technician</option>
                            <option value="applicant" {{ (isset($roleFilter) && $roleFilter === 'applicant') ? 'selected' : '' }}>Applicant</option>
                            <option value="user" {{ (isset($roleFilter) && $roleFilter === 'user') ? 'selected' : '' }}>User (Legacy)</option>
                        </select>
                    </div>
                </form>
                @if(($roleFilter ?? '') === 'employee' && ($departments ?? collect())->isNotEmpty())
                <form method="GET" action="{{ $usersIndexUrl }}" class="flex-shrink-0" id="department-filter-form">
                    @if(request()->has('per_page'))
                        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(isset($schoolId) && $schoolId !== '' && $schoolId !== null)
                        <input type="hidden" name="school" value="{{ $schoolId }}">
                    @endif
                    <input type="hidden" name="role" value="employee">
                    <div class="flex items-center gap-2">
                        <label for="department-filter" class="text-sm font-medium text-gray-700 whitespace-nowrap">Department</label>
                        <select name="department" id="department-filter" class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 min-w-[160px] max-w-[220px]">
                            <option value="">All departments</option>
                            <option value="unassigned" {{ ($departmentFilter ?? '') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ (isset($departmentFilter) && (string) $departmentFilter === (string) $department->id) ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                @endif
            @endif
            <div class="flex items-center gap-2 flex-shrink-0 lg:ml-auto">
                <button type="button" id="export-pdf-btn"
                        title="Export all users matching current filters, or only selected users when checkboxes are checked"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span id="export-pdf-btn-label">Export PDF</span>
                </button>
                <button id="send-credentials-btn" type="button" disabled
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Send Credentials</span>
                </button>
                <a href="{{ url('/admin/users/create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>{{ $isTeacherView ? 'Add Teacher' : 'Add User' }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Bulk Action Bar (Hidden by default) -->
    <div id="bulk-action-bar" class="hidden bg-indigo-50 border border-indigo-200 rounded-xl shadow-sm p-4 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-col xs:flex-row xs:flex-wrap xs:items-center gap-2 sm:gap-4">
                <span id="selected-count" class="text-sm font-medium text-indigo-900">0 users selected</span>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <label for="bulk-role-select" class="text-sm font-medium text-indigo-900">Assign Role</label>
                    <div class="flex flex-wrap items-center gap-2">
                        <select id="bulk-role-select" class="block flex-1 min-w-[140px] px-3 py-2.5 sm:py-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                            <option value="">Select a role</option>
                            <option value="admin">Administrator</option>
                            <option value="student">Student</option>
                            <option value="employee">Employee</option>
                            <option value="hr">HR</option>
                            <option value="teacher">Teacher</option>
                            <option value="technician">Technician</option>
                            <option value="applicant">Applicant</option>
                            <option value="user">User (Legacy)</option>
                        </select>
                        <button id="bulk-assign-btn" type="button" disabled
                                class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation min-h-[44px] sm:min-h-0">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Apply
                        </button>
                    </div>
                </div>
                @if(!$isTeacherView)
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <label for="bulk-department-select" class="text-sm font-medium text-indigo-900">Assign Department</label>
                    <div class="flex flex-wrap items-center gap-2">
                        <select id="bulk-department-select" class="block flex-1 min-w-[180px] px-3 py-2.5 sm:py-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                            <option value="">Select a department</option>
                            @foreach(($departments ?? collect()) as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <select id="bulk-department-position-select" class="block flex-1 min-w-[180px] px-3 py-2.5 sm:py-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                            <option value="">Select a position</option>
                        </select>
                        <button id="bulk-department-assign-btn" type="button" disabled
                                class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation min-h-[44px] sm:min-h-0">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Apply
                        </button>
                    </div>
                </div>
                @endif
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <label for="bulk-ojt-date" class="text-sm font-medium text-indigo-900">Set OJT Exit Date</label>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="date" id="bulk-ojt-date"
                               class="block flex-1 min-w-[180px] px-3 py-2.5 sm:py-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-white">
                        <button id="bulk-ojt-assign-btn" type="button" disabled
                                class="inline-flex items-center justify-center px-4 py-2.5 sm:py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation min-h-[44px] sm:min-h-0">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Apply
                        </button>
                    </div>
                </div>
            </div>
            <button id="clear-selection-btn" type="button"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium touch-manipulation py-1 self-start sm:self-center">
                Clear Selection
            </button>
        </div>
    </div>

    <!-- Users List: Cards on mobile, Table on desktop -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-visible flex-1 flex flex-col min-h-0">
        @if($users->count() > 0)
            <form id="bulk-role-form" method="POST" action="{{ url('/admin/users/bulk-assign-role') }}">
                @csrf
                <input type="hidden" name="role" id="role-input" value="">
            </form>
            @if(!$isTeacherView)
            <form id="bulk-department-form" method="POST" action="{{ url('/admin/users/bulk-assign-department') }}">
                @csrf
                <input type="hidden" name="department_id" id="department-input" value="">
                <input type="hidden" name="department_position_id" id="department-position-input" value="">
            </form>
            @endif
            <form id="bulk-ojt-form" method="POST" action="{{ url('/admin/users/bulk-assign-ojt-target-end-date') }}">
                @csrf
                <input type="hidden" name="ojt_target_end_date" id="ojt-date-input" value="">
            </form>

            <!-- Mobile: Card list (visible below md) -->
            <div class="md:hidden flex-1 overflow-visible pb-32">
                <ul class="divide-y divide-gray-200 p-3 sm:p-4">
                    @foreach($users as $user)
                        <li class="py-4 first:pt-2">
                            <div class="flex gap-3 items-start">
                                <label class="flex-shrink-0 mt-1 cursor-pointer">
                                    <input type="checkbox" name="selected_users[]" value="{{ $user->id }}"
                                           class="user-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </label>
                                <a href="{{ url('/admin/users/' . $user->id) }}" class="flex-1 min-w-0 flex gap-3">
                                    @if($user->profile_picture)
                                        <img class="h-12 w-12 rounded-full object-cover flex-shrink-0" src="{{ $user->getProfilePictureUrl() }}" alt="">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-sm font-medium text-indigo-600">{{ $user->getInitials() }}</span>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 truncate"><x-user-name :user="$user" :size="16" /></p>
                                        <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $user->getRoleBadgeClass() }}">
                                                {{ $user->getRoleLabel() }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $user->is_active ? 'Active' : 'Disabled' }}
                                            </span>
                                        </div>
                                        @if(!$isTeacherView && in_array($user->role, ['employee', 'hr', 'student'], true))
                                            <p class="mt-1.5 text-xs text-gray-500 truncate">
                                                @if($user->department)
                                                    <span class="font-medium text-gray-700">{{ $user->department->name }}</span>
                                                    @if($user->isStaffMember() && $user->payslipPositionLabel())
                                                        <span class="text-gray-400"> · </span>{{ $user->payslipPositionLabel() }}
                                                    @endif
                                                @else
                                                    <span class="text-amber-600">No department assigned</span>
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </a>
                                <div class="relative flex-shrink-0 z-[95]" x-data="{ open: false, menuStyle: '' }">
                                    <button type="button"
                                            x-ref="trigger"
                                            @click="
                                                open = !open;
                                                if (open) {
                                                    const rect = $refs.trigger.getBoundingClientRect();
                                                    const menuWidth = 208;
                                                    const left = Math.max(8, Math.min(window.innerWidth - menuWidth - 8, rect.right - menuWidth));
                                                    menuStyle = `top: ${rect.bottom + 4}px; left: ${left}px; width: ${menuWidth}px;`;
                                                }
                                            "
                                            class="p-2 -m-2 text-gray-400 hover:text-gray-600 rounded-lg touch-manipulation"
                                            aria-haspopup="true" :aria-expanded="open">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>
                                    <template x-teleport="body">
                                        <div x-show="open" @click.away="open = false" x-cloak
                                             x-transition
                                             class="fixed bg-white rounded-lg shadow-lg py-1 z-[99999] border border-gray-200"
                                             :style="menuStyle">
                                            <a href="{{ url('/admin/users/' . $user->id) }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">View</a>
                                            <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Edit</a>
                                            @if(!$user->is_approved)
                                                <form method="POST" action="{{ url('/admin/users/' . $user->id . '/approve') }}" class="block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-green-700 hover:bg-green-50" onclick="return confirmUserAction('approve', this)">Approve</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ url('/admin/users/' . $user->id . '/disapprove') }}" class="block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-amber-700 hover:bg-amber-50" onclick="return confirmUserAction('disapprove', this)">Disapprove</button>
                                                </form>
                                            @endif
                                            @if(!$user->profile_verified)
                                                <form method="POST" action="{{ url('/admin/users/' . $user->id . '/grant-verified-badge') }}" class="block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-emerald-700 hover:bg-emerald-50">Grant verified badge</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ url('/admin/users/' . $user->id . '/revoke-verified-badge') }}" class="block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">Remove verified badge</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ url('/admin/users/' . $user->id . '/toggle-status') }}" class="block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm {{ $user->is_active ? 'text-red-700 hover:bg-red-50' : 'text-green-700 hover:bg-green-50' }}">{{ $user->is_active ? 'Disable' : 'Enable' }}</button>
                                            </form>
                                            <div class="border-t border-gray-100"></div>
                                            <button type="button" data-send-credentials data-user-id="{{ $user->id }}" data-user-name="{{ e($user->name) }}" data-user-email="{{ e($user->email) }}" class="w-full text-left px-4 py-2.5 text-sm text-blue-700 hover:bg-blue-50">Send Credentials</button>
                                            <div class="border-t border-gray-100"></div>
                                            <form method="POST" action="{{ url('/admin/users/' . $user->id) }}" class="block" onsubmit="return confirmUserAction('delete', this)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-red-700 hover:bg-red-50">Delete</button>
                                            </form>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Desktop: Table (visible from md up) -->
            <div class="hidden md:block overflow-x-auto overflow-y-visible flex-1 min-h-0 pb-24">
                <table class="w-full min-w-[960px] border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-3 py-3.5 text-left w-10">
                                <input type="checkbox" id="select-all"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">University</th>
                            @if(!$isTeacherView)
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Department</th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Position</th>
                            @endif
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Approval</th>
                            <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden xl:table-cell">Created</th>
                            <th scope="col" class="relative px-4 py-3.5 w-14"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-3 py-3 sm:px-4">
                                    <input type="checkbox" name="selected_users[]" value="{{ $user->id }}"
                                           class="user-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ url('/admin/users/' . $user->id) }}" class="flex-shrink-0 h-9 w-9 hover:opacity-80">
                                            @if($user->profile_picture)
                                                <img class="h-9 w-9 rounded-full object-cover" src="{{ $user->getProfilePictureUrl() }}" alt="">
                                            @else
                                                <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-indigo-600">{{ $user->getInitials() }}</span>
                                                </div>
                                            @endif
                                        </a>
                                        <div class="min-w-0">
                                            <a href="{{ url('/admin/users/' . $user->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline truncate block"><x-user-name :user="$user" :size="16" /></a>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $user->getRoleBadgeClass() }}">{{ $user->getRoleLabel() }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 truncate max-w-[180px]">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 hidden lg:table-cell truncate max-w-[140px]">
                                    {{ $user->university ? $user->university->name : '—' }}
                                </td>
                                @if(!$isTeacherView)
                                <td class="px-4 py-3 text-sm text-gray-600 hidden lg:table-cell truncate max-w-[120px]">
                                    @if(in_array($user->role, ['employee', 'hr', 'student'], true) && $user->department)
                                        {{ $user->department->name }}
                                    @elseif(in_array($user->role, ['employee', 'hr', 'student'], true))
                                        <span class="text-amber-600">—</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 hidden lg:table-cell truncate max-w-[140px]">
                                    @if($user->isStaffMember() && $user->payslipPositionLabel())
                                        {{ $user->payslipPositionLabel() }}
                                    @elseif($user->isStaffMember())
                                        <span class="text-amber-600">—</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                @endif
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 hidden lg:table-cell">
                                    @if($user->is_approved)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 hidden xl:table-cell">{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="relative inline-block z-[95]" x-data="{ open: false, menuStyle: '' }">
                                        <button type="button"
                                                x-ref="trigger"
                                                @click="
                                                    open = !open;
                                                    if (open) {
                                                        const rect = $refs.trigger.getBoundingClientRect();
                                                        const menuWidth = 192;
                                                        const left = Math.max(8, Math.min(window.innerWidth - menuWidth - 8, rect.right - menuWidth));
                                                        menuStyle = `top: ${rect.bottom + 4}px; left: ${left}px; width: ${menuWidth}px;`;
                                                    }
                                                "
                                                class="p-1 text-gray-400 hover:text-gray-600 rounded"
                                                aria-haspopup="true" :aria-expanded="open">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" @click.away="open = false" x-cloak
                                                 x-transition
                                                 class="fixed bg-white rounded-lg shadow-lg py-1 z-[99999] border border-gray-200"
                                                 :style="menuStyle">
                                                <a href="{{ url('/admin/users/' . $user->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    View
                                                </a>
                                                <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Edit
                                                </a>
                                                @if(!$user->is_approved)
                                                    <form method="POST" action="{{ url('/admin/users/' . $user->id . '/approve') }}" class="block">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50 flex items-center" onclick="return confirmUserAction('approve', this)">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            Approve
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ url('/admin/users/' . $user->id . '/disapprove') }}" class="block">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-amber-700 hover:bg-amber-50 flex items-center" onclick="return confirmUserAction('disapprove', this)">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            Disapprove
                                                        </button>
                                                    </form>
                                                @endif
                                                @if(!$user->profile_verified)
                                                    <form method="POST" action="{{ url('/admin/users/' . $user->id . '/grant-verified-badge') }}" class="block">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50 flex items-center">
                                                            <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M9 12l2 2 4-4"/></svg>
                                                            Grant verified badge
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ url('/admin/users/' . $user->id . '/revoke-verified-badge') }}" class="block">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                                            Remove verified badge
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ url('/admin/users/' . $user->id . '/toggle-status') }}" class="block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm {{ $user->is_active ? 'text-red-700 hover:bg-red-50' : 'text-green-700 hover:bg-green-50' }} flex items-center">
                                                        @if($user->is_active)
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/></svg>
                                                            Disable
                                                        @else
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            Enable
                                                        @endif
                                                    </button>
                                                </form>
                                                <div class="border-t border-gray-100"></div>
                                                <button type="button" data-send-credentials data-user-id="{{ $user->id }}" data-user-name="{{ e($user->name) }}" data-user-email="{{ e($user->email) }}" class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    Send Credentials
                                                </button>
                                                <div class="border-t border-gray-100"></div>
                                                <form method="POST" action="{{ url('/admin/users/' . $user->id) }}" class="block" onsubmit="return confirmUserAction('delete', this)">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 flex items-center">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50/80 px-3 py-3 sm:px-6 border-t border-gray-200 flex-shrink-0">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs sm:text-sm text-gray-600 order-2 sm:order-1">
                        Showing <span class="font-medium text-gray-900">{{ $users->firstItem() }}</span>–<span class="font-medium text-gray-900">{{ $users->lastItem() }}</span>
                        of <span class="font-medium text-gray-900">{{ $users->total() }}</span>
                    </p>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 order-1 sm:order-2">
                        <div class="flex items-center gap-2">
                            <label for="per-page-select" class="text-xs sm:text-sm text-gray-600 whitespace-nowrap">Per page</label>
                            <select id="per-page-select" class="text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 py-2 pl-2 pr-8 min-h-[40px] sm:min-h-0">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <div class="flex-1 sm:flex-initial overflow-x-auto">
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12 px-4 sm:px-6">
                <svg class="mx-auto h-12 w-12 sm:h-14 sm:w-14 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
                <h3 class="mt-3 text-base sm:text-lg font-semibold text-gray-900">{{ $isTeacherView ? 'No teachers found' : 'No users found' }}</h3>
                <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto">{{ $isTeacherView ? 'Get started by creating a teacher account or adjust your search filters.' : 'Get started by creating a new user or adjust your search filters.' }}</p>
                <div class="mt-6">
                    <a href="{{ url('/admin/users/create') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 touch-manipulation min-h-[44px]">
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        {{ $isTeacherView ? 'Add Teacher' : 'Add User' }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function confirmUserAction(action, button) {
        if (typeof event !== 'undefined' && event) {
            event.preventDefault();
        }

        let message = '';
        switch(action) {
            case 'approve':
                message = 'Are you sure you want to approve this user?';
                break;
            case 'disapprove':
                message = 'Are you sure you want to disapprove this user?';
                break;
            case 'delete':
                message = 'Are you sure you want to delete this user? This action cannot be undone.';
                break;
            default:
                return false;
        }

        if (confirm(message)) {
            button.closest('form').submit();
        }

        return false;
    }

    // Bulk Role Assignment
    document.addEventListener('DOMContentLoaded', function() {
        // Search (submit on Enter key or Search button only)
        const searchForm = document.getElementById('users-search-form');
        const searchInput = document.getElementById('search-input');
        const clearSearchBtn = document.getElementById('clear-search-btn');

        if (clearSearchBtn && searchInput && searchForm) {
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                searchForm.submit();
            });
        }

        // School filter: auto-submit on change
        const schoolFilter = document.getElementById('school-filter');
        const schoolFilterForm = document.getElementById('school-filter-form');
        if (schoolFilter && schoolFilterForm) {
            schoolFilter.addEventListener('change', function() {
                schoolFilterForm.submit();
            });
        }

        // Role filter: auto-submit on change
        const roleFilter = document.getElementById('role-filter');
        const roleFilterForm = document.getElementById('role-filter-form');
        if (roleFilter && roleFilterForm) {
            roleFilter.addEventListener('change', function() {
                roleFilterForm.submit();
            });
        }

        // Department filter (employees only): auto-submit on change
        const departmentFilter = document.getElementById('department-filter');
        const departmentFilterForm = document.getElementById('department-filter-form');
        if (departmentFilter && departmentFilterForm) {
            departmentFilter.addEventListener('change', function() {
                departmentFilterForm.submit();
            });
        }

        // Per-page selector (preserve search + other params)
        const perPageSelect = document.getElementById('per-page-select');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                const url = new URL(window.location.href);
                const params = new URLSearchParams(url.search);
                params.set('per_page', this.value);
                // reset to page 1 when changing page size
                params.delete('page');
                url.search = params.toString();
                window.location.href = url.toString();
            });
        }

        const selectAllCheckbox = document.getElementById('select-all');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkActionBar = document.getElementById('bulk-action-bar');
        const selectedCountSpan = document.getElementById('selected-count');
        const bulkRoleSelect = document.getElementById('bulk-role-select');
        const bulkAssignBtn = document.getElementById('bulk-assign-btn');
        const clearSelectionBtn = document.getElementById('clear-selection-btn');
        const bulkRoleForm = document.getElementById('bulk-role-form');
        const roleInput = document.getElementById('role-input');
        const bulkDepartmentSelect = document.getElementById('bulk-department-select');
        const bulkDepartmentAssignBtn = document.getElementById('bulk-department-assign-btn');
        const bulkDepartmentForm = document.getElementById('bulk-department-form');
        const departmentInput = document.getElementById('department-input');
        const bulkDepartmentPositionSelect = document.getElementById('bulk-department-position-select');
        const departmentPositionInput = document.getElementById('department-position-input');
        const bulkDepartmentPositions = @json($departmentPositionMap ?? []);
        const bulkOjtDate = document.getElementById('bulk-ojt-date');
        const bulkOjtAssignBtn = document.getElementById('bulk-ojt-assign-btn');
        const bulkOjtForm = document.getElementById('bulk-ojt-form');
        const ojtDateInput = document.getElementById('ojt-date-input');
        const exportPdfBtn = document.getElementById('export-pdf-btn');
        const exportPdfBtnLabel = document.getElementById('export-pdf-btn-label');
        const usersExportPdfBaseUrl = @json($usersExportPdfUrl);

        function getPageUserCount() {
            return new Set(Array.from(userCheckboxes).map(cb => cb.value)).size;
        }

        function getSelectedUserIds() {
            return [...new Set(
                Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value)
            )];
        }

        function syncUserCheckboxGroup(source) {
            document.querySelectorAll('.user-checkbox[value="' + source.value + '"]').forEach(cb => {
                cb.checked = source.checked;
            });
        }

        function updateExportPdfButton() {
            if (!exportPdfBtnLabel) {
                return;
            }

            const count = getSelectedUserIds().length;
            exportPdfBtnLabel.textContent = count > 0
                ? 'Export PDF (' + count + ' selected)'
                : 'Export PDF';
        }

        exportPdfBtn?.addEventListener('click', function() {
            const selectedUserIds = getSelectedUserIds();
            const exportUrl = new URL(usersExportPdfBaseUrl, window.location.origin);

            if (selectedUserIds.length > 0) {
                selectedUserIds.forEach(function(userId) {
                    exportUrl.searchParams.append('user_ids[]', userId);
                });
            }

            window.open(exportUrl.toString(), '_blank', 'noopener,noreferrer');
        });

        updateExportPdfButton();

        // Update selected count and show/hide bulk action bar
        function updateSelection() {
            const count = getSelectedUserIds().length;
            const pageUserCount = getPageUserCount();

            selectedCountSpan.textContent = count + ' user' + (count !== 1 ? 's' : '') + ' selected';

            if (count > 0) {
                bulkActionBar.classList.remove('hidden');
            } else {
                bulkActionBar.classList.add('hidden');
            }

            // Update select all checkbox state (one row per user; mobile + desktop share the same id)
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = count === pageUserCount && count > 0;
                selectAllCheckbox.indeterminate = count > 0 && count < pageUserCount;
            }

            // Enable/disable assign button based on role selection
            bulkAssignBtn.disabled = !bulkRoleSelect.value || count === 0;
            if (bulkDepartmentAssignBtn && bulkDepartmentSelect) {
                const positions = bulkDepartmentPositions[bulkDepartmentSelect.value] || bulkDepartmentPositions[String(bulkDepartmentSelect.value)] || [];
                bulkDepartmentAssignBtn.disabled = !bulkDepartmentSelect.value || count === 0 || positions.length === 0 || !(bulkDepartmentPositionSelect && bulkDepartmentPositionSelect.value);
            }
            if (bulkOjtAssignBtn && bulkOjtDate) {
                bulkOjtAssignBtn.disabled = !bulkOjtDate.value || count === 0;
            }

            updateExportPdfButton();
        }

        // Select all checkbox
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelection();
                updateSendCredentialsButton();
            });
        }

        // Individual checkboxes (sync mobile + desktop pairs for the same user)
        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                syncUserCheckboxGroup(this);
                updateSelection();
                updateSendCredentialsButton();
            });
        });

        // Role select change
        bulkRoleSelect.addEventListener('change', function() {
            bulkAssignBtn.disabled = !this.value || getSelectedUserIds().length === 0;
        });
        function renderBulkDepartmentPositions() {
            if (!bulkDepartmentPositionSelect || !bulkDepartmentSelect) {
                return;
            }

            const departmentId = bulkDepartmentSelect.value;
            const positions = bulkDepartmentPositions[departmentId] || bulkDepartmentPositions[String(departmentId)] || [];

            bulkDepartmentPositionSelect.innerHTML = '<option value="">Select a position</option>';
            positions.forEach((position) => {
                const option = document.createElement('option');
                option.value = String(position.id);
                option.textContent = position.name;
                bulkDepartmentPositionSelect.appendChild(option);
            });

            updateSelection();
        }

        if (bulkDepartmentSelect && bulkDepartmentAssignBtn) {
            bulkDepartmentSelect.addEventListener('change', renderBulkDepartmentPositions);
            bulkDepartmentPositionSelect?.addEventListener('change', updateSelection);
        }
        if (bulkOjtDate && bulkOjtAssignBtn) {
            bulkOjtDate.addEventListener('change', function() {
                bulkOjtAssignBtn.disabled = !this.value || getSelectedUserIds().length === 0;
            });
        }

        // Clear selection
        clearSelectionBtn.addEventListener('click', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
            bulkRoleSelect.value = '';
            if (bulkDepartmentSelect) {
                bulkDepartmentSelect.value = '';
            }
            if (bulkDepartmentPositionSelect) {
                bulkDepartmentPositionSelect.innerHTML = '<option value="">Select a position</option>';
            }
            if (bulkOjtDate) {
                bulkOjtDate.value = '';
            }
            updateSelection();
            updateSendCredentialsButton();
        });

        // Bulk assign role
        bulkAssignBtn.addEventListener('click', function() {
            const selectedUserIds = getSelectedUserIds();
            const selectedRole = bulkRoleSelect.value;

            if (selectedUserIds.length === 0 || !selectedRole) {
                alert('Please select at least one user and a role.');
                return;
            }

            const roleLabel = bulkRoleSelect.options[bulkRoleSelect.selectedIndex].text;
            const confirmMessage = `Are you sure you want to assign the role "${roleLabel}" to ${selectedUserIds.length} selected user(s)?`;

            if (confirm(confirmMessage)) {
                // Remove existing user_ids inputs
                bulkRoleForm.querySelectorAll('input[name="user_ids[]"]').forEach(input => input.remove());

                // Add each user ID as a separate input field
                selectedUserIds.forEach(userId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = userId;
                    bulkRoleForm.appendChild(input);
                });

                roleInput.value = selectedRole;
                bulkRoleForm.submit();
            }
        });

        if (bulkDepartmentAssignBtn && bulkDepartmentForm && departmentInput && bulkDepartmentSelect) {
            bulkDepartmentAssignBtn.addEventListener('click', function() {
                const selectedUserIds = getSelectedUserIds();
                const selectedDepartmentId = bulkDepartmentSelect.value;
                const selectedPositionId = bulkDepartmentPositionSelect ? bulkDepartmentPositionSelect.value : '';

                if (selectedUserIds.length === 0 || !selectedDepartmentId) {
                    alert('Please select at least one user and a department.');
                    return;
                }

                if (!selectedPositionId) {
                    alert('Please select a position for employee assignments.');
                    return;
                }

                const departmentLabel = bulkDepartmentSelect.options[bulkDepartmentSelect.selectedIndex]?.text || 'department';
                const positionLabel = bulkDepartmentPositionSelect?.options[bulkDepartmentPositionSelect.selectedIndex]?.text || 'position';
                const confirmMessage = `Assign "${departmentLabel}" / "${positionLabel}" to ${selectedUserIds.length} selected user(s)?\n\nEmployees receive the position. Students receive the department only.`;

                if (!confirm(confirmMessage)) {
                    return;
                }

                bulkDepartmentForm.querySelectorAll('input[name="user_ids[]"]').forEach(input => input.remove());
                selectedUserIds.forEach(userId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = userId;
                    bulkDepartmentForm.appendChild(input);
                });

                departmentInput.value = selectedDepartmentId;
                if (departmentPositionInput) {
                    departmentPositionInput.value = selectedPositionId;
                }
                bulkDepartmentForm.submit();
            });
        }

        if (bulkOjtAssignBtn && bulkOjtForm && ojtDateInput && bulkOjtDate) {
            bulkOjtAssignBtn.addEventListener('click', function() {
                const selectedUserIds = getSelectedUserIds();
                const selectedDate = bulkOjtDate.value;

                if (selectedUserIds.length === 0 || !selectedDate) {
                    alert('Please select at least one user and an OJT target date.');
                    return;
                }

                const confirmMessage = `Set OJT target end date to ${selectedDate} for ${selectedUserIds.length} selected user(s)?\n\nOnly student roles will be updated.`;
                if (!confirm(confirmMessage)) {
                    return;
                }

                bulkOjtForm.querySelectorAll('input[name="user_ids[]"]').forEach(input => input.remove());
                selectedUserIds.forEach(userId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = userId;
                    bulkOjtForm.appendChild(input);
                });

                ojtDateInput.value = selectedDate;
                bulkOjtForm.submit();
            });
        }

        // Send Credentials Button
        const sendCredentialsBtn = document.getElementById('send-credentials-btn');
        sendCredentialsBtn.addEventListener('click', function() {
            const selectedUserIds = getSelectedUserIds();

            if (selectedUserIds.length === 0) {
                alert('Please select at least one user to send credentials.');
                return;
            }

            openBulkSendCredentialsModal(selectedUserIds);
        });

        // Update send credentials button state
        function updateSendCredentialsButton() {
            sendCredentialsBtn.disabled = getSelectedUserIds().length === 0;
        }

        // Send credentials buttons (data attributes avoid inline Blade in onclick)
        document.querySelectorAll('[data-send-credentials]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = parseInt(this.getAttribute('data-user-id'), 10);
                var name = this.getAttribute('data-user-name') || '';
                var email = this.getAttribute('data-user-email') || '';
                openSendCredentialsModal(id, name, email);
            });
        });
    });

    // Single User Send Credentials Modal
    function openSendCredentialsModal(userId, userName, userEmail) {
        const password = prompt(`Enter password for ${userName} (${userEmail}):\n\nPassword must be at least 8 characters.`, '');

        if (password === null) {
            return; // User cancelled
        }

        if (password.length < 8) {
            alert('Password must be at least 8 characters long.');
            return;
        }

        if (confirm(`Send credentials email to ${userName} (${userEmail})?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('admin/users') }}/${userId}/send-credentials`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = password;

            form.appendChild(csrfToken);
            form.appendChild(passwordInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Bulk Send Credentials Modal
    function openBulkSendCredentialsModal(userIds) {
        const password = prompt(`Enter password for ${userIds.length} selected user(s):\n\nThis password will be sent to all selected users.\nPassword must be at least 8 characters.`, '');

        if (password === null) {
            return; // User cancelled
        }

        if (password.length < 8) {
            alert('Password must be at least 8 characters long.');
            return;
        }

        if (confirm(`Send credentials email to ${userIds.length} selected user(s)?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url("/admin/users/send-bulk-credentials") }}';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'password';
            passwordInput.value = password;

            form.appendChild(csrfToken);
            form.appendChild(passwordInput);

            userIds.forEach(userId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = userId;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection
