@extends('layouts.admin')

@section('page-title', 'My Permissions')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">My Permissions</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white">My Permissions</h2>
                <p class="mt-1 text-sm text-indigo-100">Your assigned admin access — what you can see and do in the admin area.</p>
            </div>
            <a href="{{ url('/admin/dashboard') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-700 hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>
        </div>
    </div>

    <div class="flex-1 bg-white rounded-lg shadow-sm border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6 p-4 sm:p-6">
        <!-- User info -->
        <div class="mb-6 pb-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Account</h3>
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

        @if($isSuperAdmin)
            <div class="rounded-lg bg-green-50 border border-green-200 p-6 text-center">
                <p class="text-base font-medium text-green-800">You have full access to all admin features.</p>
                <p class="mt-2 text-sm text-green-700">As a super admin, you can access every area in the admin panel.</p>
            </div>
        @else
            <h3 class="text-lg font-medium text-gray-900 mb-4">Feature access</h3>
            <p class="text-sm text-gray-600 mb-6">These are the areas you can access. Contact an administrator to change your permissions.</p>

            @php
                $items = [
                    ['key' => 'content_management', 'label' => 'Content Management', 'desc' => 'Quizzes, forum, and content creation'],
                    ['key' => 'analytics_reports', 'label' => 'Analytics & Reports', 'desc' => 'Analytics dashboard and reports'],
                    ['key' => 'employee_management', 'label' => 'Employee Management', 'desc' => 'Employee DTR, leave requests, and time reports'],
                    ['key' => 'student_management', 'label' => 'Student Management', 'desc' => 'Student dashboard, DTR, and leave requests'],
                    ['key' => 'hiring_process', 'label' => 'Hiring Process', 'desc' => 'Hiring positions and applications'],
                    ['key' => 'communication', 'label' => 'Communication', 'desc' => 'Live chat, contact messages, and notifications'],
                    ['key' => 'linked_accounts', 'label' => 'Linked Accounts', 'desc' => 'Linked Accounts dashboard, Starlinks, Omada, and Plan Types'],
                    ['key' => 'billing', 'label' => 'Billing', 'desc' => 'Starlink/Omada billing, mark as paid, and statements'],
                    ['key' => 'files', 'label' => 'File Storage', 'desc' => 'Admin file storage and shared files'],
                    ['key' => 'confession', 'label' => 'Confession (Say-it)', 'desc' => 'Confession board management, topics, and banned words'],
                    ['key' => 'feedback', 'label' => 'Feedback', 'desc' => 'Feedback management and assignment'],
                    ['key' => 'user_management', 'label' => 'User Management', 'desc' => 'User management and administration'],
                    ['key' => 'system', 'label' => 'System', 'desc' => 'Settings, error logs, and user activity'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($items as $item)
                    @php
                        $allowed = $permission && ($permission->{$item['key']} ?? false);
                    @endphp
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg {{ $allowed ? 'bg-green-50 border-green-200' : 'bg-gray-50' }}">
                        <div class="flex-shrink-0 mt-0.5">
                            @if($allowed)
                                <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-green-100 text-green-600" title="You have access">✓</span>
                            @else
                                <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-gray-200 text-gray-500" title="No access">—</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900">{{ $item['label'] }}</div>
                            <p class="mt-1 text-sm text-gray-500">{{ $item['desc'] }}</p>
                            @if($item['key'] === 'employee_management' && $permission && $permission->employee_management && !empty($permission->allowed_departments))
                                @php $deptNames = $departments->whereIn('id', $permission->allowed_departments)->pluck('name'); @endphp
                                <p class="mt-2 text-xs text-gray-600">Allowed departments: {{ $deptNames->join(', ') }}</p>
                            @endif
                            @if($item['key'] === 'hiring_process' && $permission && $permission->hiring_process && !empty($permission->allowed_positions))
                                @php $posNames = $hiringPositions->whereIn('id', $permission->allowed_positions)->pluck('title'); @endphp
                                <p class="mt-2 text-xs text-gray-600">Allowed positions: {{ $posNames->join(', ') }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
