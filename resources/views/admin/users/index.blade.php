@extends('layouts.admin')

@section('page-title', 'User Management')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Users</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mx-2 sm:mx-3 lg:mx-4 xl:mx-6" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mx-2 sm:mx-3 lg:mx-4 xl:mx-6" role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mx-2 sm:mx-3 lg:mx-4 xl:mx-6" role="alert">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
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

    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           id="search-input"
                           placeholder="Search users..."
                           class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                <button class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span class="hidden sm:inline">Filter</span>
                </button>
                <button id="send-credentials-btn" type="button" disabled
                        class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="hidden sm:inline">Send Credentials</span>
                </button>
                <a href="{{ route('users.create') }}" class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="hidden sm:inline">Add User</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Users</h1>
                <p class="text-indigo-100 text-sm">Manage system users and their permissions</p>
            </div>
        </div>
    </div>

    <!-- Bulk Action Bar (Hidden by default) -->
    <div id="bulk-action-bar" class="hidden bg-indigo-50 border border-indigo-200 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4 flex-wrap gap-2">
                <span id="selected-count" class="text-sm font-medium text-indigo-900">0 users selected</span>
                <div class="flex items-center space-x-2">
                    <label for="bulk-role-select" class="text-sm font-medium text-indigo-900">Assign Role:</label>
                    <select id="bulk-role-select" class="block px-3 py-2 border border-indigo-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        <option value="">Select a role</option>
                        <option value="admin">Administrator</option>
                        <option value="student">Student</option>
                        <option value="employee">Employee</option>
                        <option value="applicant">Applicant</option>
                        <option value="user">User (Legacy)</option>
                    </select>
                    <button id="bulk-assign-btn" type="button" disabled
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Apply
                    </button>
                </div>
            </div>
            <button id="clear-selection-btn" type="button"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                Clear Selection
            </button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow-sm border-t border-b border-gray-200 overflow-hidden flex-1 flex flex-col">
        @if($users->count() > 0)
            <form id="bulk-role-form" method="POST" action="{{ route('admin.users.bulk-assign-role') }}">
                @csrf
                <input type="hidden" name="role" id="role-input" value="">
            </form>
            <div class="overflow-x-auto flex-1">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left">
                                <input type="checkbox" id="select-all"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden sm:inline">User ID</span>
                                <span class="sm:hidden">ID</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden md:inline">Email</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden lg:inline">University</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden md:inline">Status</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden lg:inline">Approval</span>
                            </th>
                            <th scope="col" class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden lg:inline">Created</span>
                            </th>
                            <th scope="col" class="relative px-3 sm:px-4 lg:px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $index => $user)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-3 sm:px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="selected_users[]" value="{{ $user->id }}"
                                           class="user-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($user->profile_picture)
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $user->getProfilePictureUrl() }}" alt="{{ $user->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-indigo-600">{{ $user->getInitials() }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $user->getRoleBadgeClass() }}">
                                                {{ $user->getRoleLabel() }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($user->university)
                                        {{ $user->university->name }}
                                    @else
                                        <span class="text-gray-400 italic">Not specified</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->is_approved)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open"
                                                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>

                                        <!-- Simple Dropdown -->
                                        <div x-show="open"
                                             @click.away="open = false"
                                             @keydown.escape.window="open = false"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">

                                            <a href="{{ route('users.show', $user) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View Profile
                                            </a>

                                            <a href="{{ route('users.edit', $user) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </a>

                                            @if(!$user->is_approved)
                                                <form method="POST" action="{{ route('admin.users.approve', $user) }}" class="block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50 flex items-center" onclick="return confirmUserAction('approve', this)">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Approve
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.users.disapprove', $user) }}" class="block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50 flex items-center" onclick="return confirmUserAction('disapprove', this)">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Disapprove
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="w-full text-left px-4 py-2 text-sm {{ $user->is_active ? 'text-red-700 hover:bg-red-50' : 'text-green-700 hover:bg-green-50' }} flex items-center">
                                                    @if($user->is_active)
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                                        </svg>
                                                        Disable
                                                    @else
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Enable
                                                    @endif
                                                </button>
                                            </form>

                                            <div class="border-t border-gray-100"></div>

                                            <button onclick="openSendCredentialsModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}')"
                                                    class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                                Send Credentials
                                            </button>

                                            <div class="border-t border-gray-100"></div>

                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="block" onsubmit="return confirmUserAction('delete', this)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Close dropdown portal -->
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $users->links() }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div class="flex items-center">
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $users->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $users->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $users->total() }}</span>
                            results
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-700">Rows per page:</span>
                        <select class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                    <div>
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new user.</p>
                <div class="mt-6">
                    <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add User
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function confirmUserAction(action, button) {
        event.preventDefault();

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
        const selectAllCheckbox = document.getElementById('select-all');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkActionBar = document.getElementById('bulk-action-bar');
        const selectedCountSpan = document.getElementById('selected-count');
        const bulkRoleSelect = document.getElementById('bulk-role-select');
        const bulkAssignBtn = document.getElementById('bulk-assign-btn');
        const clearSelectionBtn = document.getElementById('clear-selection-btn');
        const bulkRoleForm = document.getElementById('bulk-role-form');
        const roleInput = document.getElementById('role-input');

        // Update selected count and show/hide bulk action bar
        function updateSelection() {
            const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
            const count = selectedCheckboxes.length;

            selectedCountSpan.textContent = count + ' user' + (count !== 1 ? 's' : '') + ' selected';

            if (count > 0) {
                bulkActionBar.classList.remove('hidden');
            } else {
                bulkActionBar.classList.add('hidden');
            }

            // Update select all checkbox state
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = count === userCheckboxes.length && count > 0;
                selectAllCheckbox.indeterminate = count > 0 && count < userCheckboxes.length;
            }

            // Enable/disable assign button based on role selection
            bulkAssignBtn.disabled = !bulkRoleSelect.value || count === 0;
        }

        // Select all checkbox
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateSelection();
            });
        }

        // Individual checkboxes
        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelection);
        });

        // Role select change
        bulkRoleSelect.addEventListener('change', function() {
            bulkAssignBtn.disabled = !this.value || document.querySelectorAll('.user-checkbox:checked').length === 0;
        });

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
            updateSelection();
        });

        // Bulk assign role
        bulkAssignBtn.addEventListener('click', function() {
            const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
            const selectedUserIds = Array.from(selectedCheckboxes).map(cb => cb.value);
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

        // Send Credentials Button
        const sendCredentialsBtn = document.getElementById('send-credentials-btn');
        sendCredentialsBtn.addEventListener('click', function() {
            const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
            const selectedUserIds = Array.from(selectedCheckboxes).map(cb => cb.value);

            if (selectedUserIds.length === 0) {
                alert('Please select at least one user to send credentials.');
                return;
            }

            openBulkSendCredentialsModal(selectedUserIds);
        });

        // Update send credentials button state
        function updateSendCredentialsButton() {
            const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
            sendCredentialsBtn.disabled = selectedCheckboxes.length === 0;
        }

        // Update send credentials button when selection changes
        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelection();
                updateSendCredentialsButton();
            });
        });

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                updateSendCredentialsButton();
            });
        }

        clearSelectionBtn.addEventListener('click', function() {
            updateSendCredentialsButton();
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
            form.action = '{{ route("admin.users.send-bulk-credentials") }}';

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
