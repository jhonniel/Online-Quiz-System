@extends('layouts.admin')

@section('page-title', 'Admin Permissions')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Admin Permissions</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white">Admin Permissions Management</h2>
                <p class="mt-1 text-sm text-indigo-100">Manage feature access permissions for users who have been granted access</p>
            </div>
            @php
                $isFullAccessAdmin = auth()->user()->isSuperAdmin();
            @endphp
            <a href="{{ route('admin.admin-permissions.create') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-700 hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ $isFullAccessAdmin ? 'Add User' : 'Add Employee' }}
            </a>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <form id="admin-permissions-search-form" method="GET" action="{{ route('admin.admin-permissions.index') }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           id="admin-permissions-search-input"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by name or email..."
                           autocomplete="off"
                           class="block w-full pl-9 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    @if(request('search'))
                        <button type="button"
                                id="admin-permissions-clear-search-btn"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                title="Clear search">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Role Filter -->
            <div class="flex items-center space-x-2">
                <select name="role"
                        class="block w-full sm:w-auto pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                    @if($isFullAccessAdmin)
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="applicant" {{ request('role') == 'applicant' ? 'selected' : '' }}>Applicant</option>
                    @endif
                </select>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Filter
                </button>
                @if(request('search') || request('role'))
                    <a href="{{ route('admin.admin-permissions.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="flex-1 bg-white rounded-lg shadow-sm border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6 overflow-hidden flex flex-col">
        <div class="overflow-x-auto flex-1">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Content Management
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Analytics & Reports
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee Management
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Student Management
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Hiring Process
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Communication
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User Management
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            System
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        @php
                            $permission = $user->adminPermission;
                            $isSuperAdmin = !$permission;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-medium text-sm">{{ $user->getInitials() }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $user->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $user->getRoleLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $permission->content_management ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $permission->content_management ? '✓' : '✗' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $permission->analytics_reports ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $permission->analytics_reports ? '✓' : '✗' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $permission->employee_management ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $permission->employee_management ? '✓' : '✗' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $permission->student_management ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $permission->student_management ? '✓' : '✗' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $permission->hiring_process ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $permission->hiring_process ? '✓' : '✗' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $permission->communication ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $permission->communication ? '✓' : '✗' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $permission->user_management ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $permission->user_management ? '✓' : '✗' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $permission->system ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $permission->system ? '✓' : '✗' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($isSuperAdmin)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Full Access
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Limited Access
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.admin-permissions.edit', $user) }}"
                                   class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-8 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <p class="text-gray-900 font-medium mb-1">No users with permissions found</p>
                                    <p class="text-gray-500 mb-4">Add a {{ $isFullAccessAdmin ? 'user' : 'employee' }} to start managing permissions</p>
                                    <a href="{{ route('admin.admin-permissions.create') }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        {{ $isFullAccessAdmin ? 'Add User' : 'Add Employee' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages() || $users->total() > 0)
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $users->appends(request()->query())->links() }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div class="flex items-center">
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $users->firstItem() ?? 0 }}</span>
                            to
                            <span class="font-medium">{{ $users->lastItem() ?? 0 }}</span>
                            of
                            <span class="font-medium">{{ $users->total() }}</span>
                            results
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-700">Rows per page:</span>
                        <select id="admin-permissions-per-page-select"
                                class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div>
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('admin-permissions-search-form');
        const searchInput = document.getElementById('admin-permissions-search-input');
        const clearBtn = document.getElementById('admin-permissions-clear-search-btn');
        const perPageSelect = document.getElementById('admin-permissions-per-page-select');

        // Debounced search
        let t = null;
        if (form && searchInput) {
            searchInput.addEventListener('input', function () {
                if (t) clearTimeout(t);
                t = setTimeout(() => form.submit(), 350);
            });
        }

        // Clear search
        if (clearBtn && searchInput && form) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                form.submit();
            });
        }

        // Per page (preserve filters)
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function () {
                const url = new URL(window.location.href);
                const params = new URLSearchParams(url.search);
                params.set('per_page', this.value);
                params.delete('page');
                url.search = params.toString();
                window.location.href = url.toString();
            });
        }
    });
</script>
@endsection




