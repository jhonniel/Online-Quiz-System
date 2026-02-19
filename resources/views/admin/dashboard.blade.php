@extends('layouts.admin')

@push('styles')
<style>
.scrollbar-thin {
    scrollbar-width: thin;
}

.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.scrollbar-thin:hover::-webkit-scrollbar-thumb {
    background: #94a3b8;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
</style>
@endpush

@section('page-title', 'Admin Dashboard')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Dashboard</span>
        </div>
    </li>
@endsection

@section('content')
<div class="mx-2 sm:mx-3 lg:mx-4 xl:mx-6 space-y-6 sm:space-y-8">
    @if(session('error'))
        <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Page header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Admin Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">System overview, analytics, and quick actions. Full access only.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 sm:gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Users -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Users</dt>
                            <dd class="text-base sm:text-lg font-medium text-gray-900">{{ $totalUsers }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Quizzes -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Quizzes</dt>
                            <dd class="text-base sm:text-lg font-medium text-gray-900">{{ $totalQuizzes }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Active Users</dt>
                            <dd class="text-base sm:text-lg font-medium text-gray-900">{{ $activeUsers }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disabled Users -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Disabled Users</dt>
                            <dd class="text-base sm:text-lg font-medium text-gray-900">{{ $disabledUsers }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee & Student Statistics -->
    <div class="grid grid-cols-1 gap-3 sm:gap-4 md:gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Employees -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 overflow-hidden shadow rounded-lg border border-blue-200">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-blue-700 truncate">Total Employees</dt>
                            <dd class="text-base sm:text-lg font-medium text-blue-900">{{ $totalEmployees }}</dd>
                            <dd class="text-xs text-blue-600 mt-1">{{ $activeEmployees }} active</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Students -->
        <div class="bg-gradient-to-r from-purple-50 to-purple-100 overflow-hidden shadow rounded-lg border border-purple-200">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7m0-7l-6.16-3.422a12.083 12.083 0 00-.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 006.824-2.998 12.078 12.078 0 00-.665-6.479L12 14z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-purple-700 truncate">Total Students</dt>
                            <dd class="text-base sm:text-lg font-medium text-purple-900">{{ $totalStudents }}</dd>
                            <dd class="text-xs text-purple-600 mt-1">{{ $activeStudents }} active</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Leave Requests -->
        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 overflow-hidden shadow rounded-lg border border-yellow-200">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-yellow-700 truncate">Pending Leave Requests</dt>
                            <dd class="text-base sm:text-lg font-medium text-yellow-900">{{ $pendingLeaveRequests }}</dd>
                            <dd class="text-xs text-yellow-600 mt-1 hidden sm:block">{{ $employeeLeaveRequests }} employee, {{ $studentLeaveRequests }} student</dd>
                            <dd class="text-xs text-yellow-600 mt-1 sm:hidden">{{ $employeeLeaveRequests }} emp, {{ $studentLeaveRequests }} stu</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total DTR Records -->
        <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 overflow-hidden shadow rounded-lg border border-indigo-200">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-indigo-700 truncate">Total DTR Records</dt>
                            <dd class="text-base sm:text-lg font-medium text-indigo-900">{{ $totalDtrRecords }}</dd>
                            <dd class="text-xs text-indigo-600 mt-1">{{ $todayDtrRecords }} today</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time User Activity -->
    <div class="grid grid-cols-1 gap-3 sm:gap-4 md:gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Online Users Counter -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Online Users</dt>
                            <dd class="text-base sm:text-lg font-medium text-gray-900" id="online-count">{{ $activityStats['online'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Logins -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Today's Logins</dt>
                            <dd class="text-base sm:text-lg font-medium text-gray-900" id="today-logins">{{ $todayLogins }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Logouts -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4 sm:p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <div class="ml-3 sm:ml-5 w-0 flex-1 min-w-0">
                        <dl>
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Today's Logouts</dt>
                            <dd class="text-base sm:text-lg font-medium text-gray-900" id="today-logouts">{{ $todayLogouts }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Overview (Say-it, Contact, Notifications, Tickets, Hiring) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
            <h2 class="text-base font-semibold text-gray-900">System Overview</h2>
            <p class="text-sm text-gray-500 mt-0.5">Key metrics across the platform</p>
        </div>
        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="{{ url('/admin/confession/dashboard') }}" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-indigo-200 transition-colors">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                </div>
                <div class="ml-3 min-w-0">
                    <p class="text-xs font-medium text-gray-500">Say-it Posts</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($confessionPostsCount ?? 0) }}</p>
                </div>
            </a>
            <a href="{{ url('/admin/contact-messages') }}" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-indigo-200 transition-colors">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <div class="ml-3 min-w-0">
                    <p class="text-xs font-medium text-gray-500">Contact Messages</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($contactMessagesCount ?? 0) }}</p>
                </div>
            </a>
            <a href="{{ url('/admin/notifications') }}" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-indigo-200 transition-colors">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <div class="ml-3 min-w-0">
                    <p class="text-xs font-medium text-gray-500">Notifications</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($notificationsCount ?? 0) }}</p>
                </div>
            </a>
            <a href="{{ url('/admin/tickets') }}" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-indigo-200 transition-colors">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="ml-3 min-w-0">
                    <p class="text-xs font-medium text-gray-500">Open Tickets</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($openTicketsCount ?? 0) }}</p>
                </div>
            </a>
            <a href="{{ url('/admin/hiring-applications') }}" class="flex items-center p-4 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-indigo-200 transition-colors">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <div class="ml-3 min-w-0">
                    <p class="text-xs font-medium text-gray-500">Pending Hiring</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($pendingHiringCount ?? 0) }}</p>
                </div>
            </a>
            <div class="flex items-center p-4 rounded-lg border border-gray-200 bg-gray-50/50">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <div class="ml-3 min-w-0">
                    <p class="text-xs font-medium text-gray-500">Say-it Comments</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($confessionCommentsCount ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="mb-4 p-4 bg-white rounded-xl border border-gray-200">
        <div class="flex flex-wrap items-end gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Chart period:</span>
                <div class="flex flex-wrap gap-1">
                    <a href="{{ url()->current() }}?chart_period=day" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ ($chartPeriod ?? 'week') === 'day' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Day</a>
                    <a href="{{ url()->current() }}?chart_period=week" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ ($chartPeriod ?? 'week') === 'week' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Week</a>
                    <a href="{{ url()->current() }}?chart_period=month" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ ($chartPeriod ?? 'week') === 'month' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Month</a>
                    <a href="{{ url()->current() }}?chart_period=year" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ ($chartPeriod ?? 'week') === 'year' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Year</a>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 border-l border-gray-200 pl-4">
                <span class="text-sm font-medium text-gray-700">Custom range:</span>
                <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="chart_period" value="custom">
                    <label class="flex items-center gap-1.5">
                        <span class="text-xs text-gray-500">From</span>
                        <input type="date" name="chart_from" value="{{ $chartFrom ?? now()->subDays(6)->format('Y-m-d') }}" class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </label>
                    <label class="flex items-center gap-1.5">
                        <span class="text-xs text-gray-500">To</span>
                        <input type="date" name="chart_to" value="{{ $chartTo ?? now()->format('Y-m-d') }}" class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </label>
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">
                        Apply
                    </button>
                </form>
            </div>
            <span class="text-xs text-gray-500 ml-1">
                @if(($chartPeriod ?? 'week') === 'day')
                    Last 24 hours (hourly)
                @elseif(($chartPeriod ?? 'week') === 'week')
                    Last 7 days
                @elseif(($chartPeriod ?? 'week') === 'month')
                    Last 30 days
                @elseif(($chartPeriod ?? 'week') === 'year')
                    Last 12 months
                @else
                    {{ $chartFrom ?? '' }} to {{ $chartTo ?? '' }}
                @endif
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden lg:col-span-2">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Activity Trends</h2>
                <p class="text-sm text-gray-500 mt-0.5">Logins and quiz attempts</p>
            </div>
            <div class="p-6">
                <canvas id="activityChart" height="220"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">User Registrations</h2>
                <p class="text-sm text-gray-500 mt-0.5">New users</p>
            </div>
            <div class="p-6">
                <canvas id="userRegChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Login Time Trend -->
    <div class="mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Login Time Trend</h2>
                <p class="text-sm text-gray-500 mt-0.5">Login count per period</p>
            </div>
            <div class="p-6">
                <canvas id="loginTimeChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- User Activity Logs Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">User Activity Logs Trend</h2>
                <p class="text-sm text-gray-500 mt-0.5">Total activities per period</p>
            </div>
            <div class="p-6">
                <canvas id="activityLogTrendChart" height="220"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Activity Logs by Type</h2>
                <p class="text-sm text-gray-500 mt-0.5">Login, logout, page views per period</p>
            </div>
            <div class="p-6">
                <canvas id="activityLogByTypeChart" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Additional Charts: Logs, Activity, Students vs Employees, DTR, Leave -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Error Logs</h2>
                <p class="text-sm text-gray-500 mt-0.5">Errors per period</p>
            </div>
            <div class="p-6">
                <canvas id="errorLogChart" height="200"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Students vs Employees</h2>
                <p class="text-sm text-gray-500 mt-0.5">Registrations per period</p>
            </div>
            <div class="p-6">
                <canvas id="studentEmployeeChart" height="200"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">DTR Records</h2>
                <p class="text-sm text-gray-500 mt-0.5">Time records per period</p>
            </div>
            <div class="p-6">
                <canvas id="dtrChart" height="200"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Employee Leave Requests</h2>
                <p class="text-sm text-gray-500 mt-0.5">By status</p>
            </div>
            <div class="p-6">
                <canvas id="leaveRequestEmployeeChart" height="200"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Student Leave Requests</h2>
                <p class="text-sm text-gray-500 mt-0.5">By status</p>
            </div>
            <div class="p-6">
                <canvas id="leaveRequestStudentChart" height="200"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Activity Types</h2>
                <p class="text-sm text-gray-500 mt-0.5">Breakdown by selected period</p>
            </div>
            <div class="p-6">
                <canvas id="activityTypeChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Ongoing Leave & Time Remaining -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6 mt-6">
        <!-- Ongoing Leave - Employees -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Ongoing Leave</h2>
                <p class="text-sm text-gray-500 mt-0.5">Employees currently on leave</p>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto">
                @forelse($ongoingLeaveEmployees as $leave)
                    <a href="{{ url('/admin/leave-requests/' . $leave->id) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-transparent hover:border-amber-200 transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $leave->user->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500">{{ $leave->start_date->format('M j') }} – {{ $leave->end_date->format('M j, Y') }}</p>
                        </div>
                        <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded">{{ $leave->type_label }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No employees on leave today</p>
                @endforelse
            </div>
            @if($ongoingLeaveEmployees->isNotEmpty())
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
                    <a href="{{ url('/admin/leave-requests') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View all leave requests →</a>
                </div>
            @endif
        </div>

        <!-- Ongoing Leave - Students -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Ongoing Leave</h2>
                <p class="text-sm text-gray-500 mt-0.5">Students currently on leave</p>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto">
                @forelse($ongoingLeaveStudents as $leave)
                    <a href="{{ url('/admin/student-leave-requests') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-transparent hover:border-amber-200 transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $leave->user->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500">{{ $leave->start_date->format('M j') }} – {{ $leave->end_date->format('M j, Y') }}</p>
                        </div>
                        <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded">{{ $leave->type_label }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No students on leave today</p>
                @endforelse
            </div>
            @if($ongoingLeaveStudents->isNotEmpty())
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
                    <a href="{{ url('/admin/student-leave-requests') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View all student leave →</a>
                </div>
            @endif
        </div>

        <!-- Students with Time Remaining (DTR Deficit) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Time Remaining</h2>
                <p class="text-sm text-gray-500 mt-0.5">Students with DTR deficit (hours to render)</p>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto">
                @forelse($studentsWithDeficit as $user)
                    <a href="{{ url('/admin/student-dtr') }}?user={{ $user->id }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-transparent hover:border-red-200 transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                        </div>
                        @php
                            $defH = (int) floor($user->total_deficit_hours ?? 0);
                            $defM = (int) round((($user->total_deficit_hours ?? 0) - $defH) * 60);
                        @endphp
                        <span class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-1 rounded whitespace-nowrap">{{ sprintf('%02d:%02d', $defH, $defM) }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No students with deficit</p>
                @endforelse
            </div>
            @if($studentsWithDeficit->isNotEmpty())
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
                    <a href="{{ url('/admin/student-dtr') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View student DTR →</a>
                </div>
            @endif
        </div>

        <!-- Employees with Time Remaining (DTR Deficit) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">Time Remaining</h2>
                <p class="text-sm text-gray-500 mt-0.5">Employees with DTR deficit (hours to render)</p>
            </div>
            <div class="p-4 max-h-64 overflow-y-auto">
                @forelse($employeesWithDeficit as $user)
                    <a href="{{ url('/admin/dtr') }}?user={{ $user->id }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-transparent hover:border-red-200 transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                        </div>
                        @php
                            $defH = (int) floor($user->total_deficit_hours ?? 0);
                            $defM = (int) round((($user->total_deficit_hours ?? 0) - $defH) * 60);
                        @endphp
                        <span class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-1 rounded whitespace-nowrap">{{ sprintf('%02d:%02d', $defH, $defM) }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No employees with deficit</p>
                @endforelse
            </div>
            @if($employeesWithDeficit->isNotEmpty())
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">
                    <a href="{{ url('/admin/dtr') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">View employee DTR →</a>
                </div>
            @endif
        </div>
    </div>

    <!-- User Activity Monitoring -->
    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-2">
        <!-- Currently Online Users -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                    <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900">Currently Online Users</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 self-start sm:self-auto">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                        Live
                    </span>
                </div>
                <div class="space-y-2 sm:space-y-3 max-h-64 overflow-y-auto scrollbar-thin" id="online-users-list">
                    @forelse($onlineUsers as $session)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-2 sm:p-3 bg-gray-50 rounded-lg gap-2">
                            <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-indigo-600 font-semibold text-xs sm:text-sm">
                                        {{ substr($session->user->name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">{{ $session->user->name }}</p>
                                    <p class="text-xs text-gray-500 truncate hidden sm:block">{{ $session->user->email }}</p>
                                </div>
                            </div>
                            <div class="text-left sm:text-right flex-shrink-0">
                                <p class="text-xs text-gray-500">{{ $session->last_activity_at->diffForHumans() }}</p>
                                <p class="text-xs text-gray-400 hidden sm:block">{{ $session->ip_address }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs sm:text-sm text-gray-500 text-center py-4">No users currently online</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent User Activities -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                    <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900">Recent User Activities</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 self-start sm:self-auto">
                        <div class="w-2 h-2 bg-blue-400 rounded-full mr-1 animate-pulse"></div>
                        Live
                    </span>
                </div>
                <div class="space-y-2 sm:space-y-3 max-h-64 overflow-y-auto scrollbar-thin" id="recent-activities-list">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                @if($activity->activity_type === 'login')
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                @elseif($activity->activity_type === 'logout')
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $activity->user->name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                                    @if($activity->action)
                                        - {{ $activity->action }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No recent activities</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-2">
        <!-- Recent Quizzes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
                <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900 mb-3 sm:mb-4">Recent Quizzes</h3>
                <div class="space-y-3">
                    @forelse($recentQuizzes as $quiz)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $quiz->title }}</p>
                                <p class="text-sm text-gray-500">Created by {{ $quiz->creator->name }}</p>
                            </div>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $quiz->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No quizzes created yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
                <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900 mb-3 sm:mb-4">Recent Users</h3>
                <div class="space-y-3">
                    @forelse($recentUsers as $user)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No users registered yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Leave Requests -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
                <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900">Recent Leave Requests</h3>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-2">
                    <a href="{{ url('/admin/leave-requests') }}" class="text-xs sm:text-sm text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap">
                        Employee Requests →
                    </a>
                    <a href="{{ url('/admin/student-leave-requests') }}" class="text-xs sm:text-sm text-purple-600 hover:text-purple-800 font-medium whitespace-nowrap">
                        Student Requests →
                    </a>
                </div>
            </div>
            <div class="space-y-2 sm:space-y-3">
                @forelse($recentLeaveRequests as $leaveRequest)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-2 sm:p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors gap-2">
                        <div class="flex items-start sm:items-center space-x-2 sm:space-x-3 min-w-0 flex-1">
                            <div class="flex-shrink-0">
                                @if($leaveRequest->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @elseif($leaveRequest->status === 'approved')
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-medium text-gray-900 truncate">{{ $leaveRequest->user->name }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 break-words">
                                    <span class="hidden sm:inline">{{ $leaveRequest->typeLabel }} • </span>
                                    <span class="sm:hidden">{{ \Illuminate\Support\Str::limit($leaveRequest->typeLabel, 15) }} • </span>
                                    {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('M d') }} -
                                    {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('M d, Y') }}
                                    @if($leaveRequest->user->role === 'employee')
                                        <span class="text-blue-600">(Emp)</span>
                                    @else
                                        <span class="text-purple-600">(Stu)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-left sm:text-right flex-shrink-0">
                            <p class="text-xs text-gray-500">{{ $leaveRequest->created_at->diffForHumans() }}</p>
                            @if($leaveRequest->user->role === 'employee')
                                <a href="{{ url('/admin/leave-requests/' . $leaveRequest->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800 inline-block mt-1">View →</a>
                            @else
                                <a href="{{ url('/admin/leave-requests/' . $leaveRequest->id) }}" class="text-xs text-purple-600 hover:text-purple-800 inline-block mt-1">View →</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs sm:text-sm text-gray-500 text-center py-4">No leave requests yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
            <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900 mb-3 sm:mb-4">Quick Actions</h3>
            <div class="space-y-3 sm:space-y-4">
                <!-- Content Management -->
                <div>
                    <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2">Content Management</h4>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ url('/admin/quizzes/create') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                    Create Quiz
                </a>
                        <a href="{{ url('/admin/quizzes') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Manage Quizzes
                        </a>
                        <a href="{{ url('/admin/import') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Import Questions
                        </a>
                    </div>
                </div>

                <!-- Employee Management -->
                <div>
                    <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2">Employee Management</h4>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ url('/admin/dtr/create') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Create DTR
                        </a>
                        <a href="{{ url('/admin/dtr') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            View DTR
                        </a>
                        <a href="{{ url('/admin/leave-requests') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Leave Requests
                        </a>
                        <a href="{{ url('/admin/leave-calendar') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Leave Calendar
                        </a>
                    </div>
                </div>

                <!-- Student Management -->
                <div>
                    <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2">Student Management</h4>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ url('/admin/student-dtr/create') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700">
                            Create Student DTR
                        </a>
                        <a href="{{ url('/admin/student-dtr') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            View Student DTR
                        </a>
                        <a href="{{ url('/admin/student-leave-requests') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Student Leave Requests
                        </a>
                        <a href="{{ url('/admin/student-leave-calendar') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Student Leave Calendar
                        </a>
                    </div>
                </div>

                <!-- User & System Management -->
                <div>
                    <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2">User & System Management</h4>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ url('/admin/users/create') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Create User
                        </a>
                        <a href="{{ url('/admin/users') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Manage Users
                </a>
                        <a href="{{ url('/admin/universities') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Manage Universities
                        </a>
                        <a href="{{ url('/admin/settings') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            System Settings
                        </a>
                        <a href="{{ url('/admin/forum') }}" class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                            <span class="hidden sm:inline">Forum Management</span>
                            <span class="sm:hidden">Forum</span>
                </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rankings Section -->
    <div class="mt-6 sm:mt-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 gap-2">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Rankings & Analytics</h2>
            <div class="text-xs sm:text-sm text-gray-500">
                <span class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    📊 Live Data
                </span>
            </div>
        </div>

        <!-- Ranking Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4 mb-4 sm:mb-6">
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-lg p-3 sm:p-4 text-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-xl sm:text-2xl">🏆</span>
                    </div>
                    <div class="ml-2 sm:ml-3 min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium truncate">Top Student</p>
                        <p class="text-base sm:text-lg font-bold truncate">
                            @if($topStudents->count() > 0)
                                {{ $topStudents->first()->name }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-lg p-3 sm:p-4 text-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-xl sm:text-2xl">🏫</span>
                    </div>
                    <div class="ml-2 sm:ml-3 min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium truncate">Top University</p>
                        <p class="text-base sm:text-lg font-bold truncate">
                            @if($universityRanking->count() > 0)
                                {{ $universityRanking->first()->name }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-400 to-blue-500 rounded-lg p-3 sm:p-4 text-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-xl sm:text-2xl">🔥</span>
                    </div>
                    <div class="ml-2 sm:ml-3 min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium truncate">Popular Quiz</p>
                        <p class="text-base sm:text-lg font-bold truncate">
                            @if($quizPopularity->count() > 0)
                                {{ \Illuminate\Support\Str::limit($quizPopularity->first()->title, 15) }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-400 to-purple-500 rounded-lg p-3 sm:p-4 text-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-xl sm:text-2xl">🎯</span>
                    </div>
                    <div class="ml-2 sm:ml-3 min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium truncate">Best Performance</p>
                        <p class="text-base sm:text-lg font-bold truncate">
                            @if($quizPerformance->count() > 0)
                                {{ number_format($quizPerformance->first()->average_score, 1) }} avg
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-indigo-400 to-indigo-500 rounded-lg p-3 sm:p-4 text-white">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-xl sm:text-2xl">⚡</span>
                    </div>
                    <div class="ml-2 sm:ml-3 min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium truncate">Most Active User</p>
                        <p class="text-base sm:text-lg font-bold truncate">
                            @if($mostActiveUsers->count() > 0)
                                {{ optional($mostActiveUsers->first()->user)->name ?? 'N/A' }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Top Students by Score -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                        <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900">🏆 All-Time Top Students by Total Score</h3>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 self-start sm:self-auto">
                            {{ $topStudents->count() }} students
                        </span>
                    </div>

                    @if($topStudents->count() > 0)
                        <div class="relative">
                            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400">
                                <div class="space-y-3 p-3">
                                    @foreach($topStudents as $index => $student)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    @if($index === 0)
                                                        <span class="text-2xl">🥇</span>
                                                    @elseif($index === 1)
                                                        <span class="text-2xl">🥈</span>
                                                    @elseif($index === 2)
                                                        <span class="text-2xl">🥉</span>
                                                    @else
                                                        <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="ml-3">
                                                    <div class="flex items-center space-x-2">
                                                        <p class="text-sm font-medium text-gray-900">{{ $student->name }}</p>
                                                        @if($student->is_active)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Active
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                Inactive
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm text-gray-500">
                                                        @if($student->university)
                                                            {{ $student->university->name }}
                                                        @else
                                                            No university
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-indigo-600">{{ $student->quiz_attempt_history_sum_score ?? 0 }} pts</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Scroll indicator -->
                            @if($topStudents->count() > 8)
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-white to-transparent h-8 pointer-events-none"></div>
                                <div class="text-center mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                        </svg>
                                        Scroll down to see more students ({{ $topStudents->count() }} total)
                                    </span>
                                </div>
                            @else
                                <div class="text-center mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Showing all {{ $topStudents->count() }} students
                                    </span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No quiz attempts yet.</p>
                    @endif
                </div>
            </div>

            <!-- University Student Count -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                        <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900">🏫 Universities by Student Count</h3>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 self-start sm:self-auto">
                            {{ $universityRanking->count() }} universities
                        </span>
                    </div>

                    @if($universityRanking->count() > 0)
                        <div class="relative">
                            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 hover:scrollbar-thumb-gray-400">
                                <div class="space-y-3 p-3">
                                    @foreach($universityRanking as $index => $university)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    @if($index === 0)
                                                        <span class="text-2xl">🏆</span>
                                                    @elseif($index === 1)
                                                        <span class="text-2xl">🥈</span>
                                                    @elseif($index === 2)
                                                        <span class="text-2xl">🥉</span>
                                                    @else
                                                        <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">{{ $university->name }}</p>
                                                    @if($university->location)
                                                        <p class="text-sm text-gray-500">{{ $university->location }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-green-600">{{ $university->users_count }} students</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Scroll indicator -->
                            @if($universityRanking->count() > 8)
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-white to-transparent h-8 pointer-events-none"></div>
                                <div class="text-center mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                        </svg>
                                        Scroll down to see more universities ({{ $universityRanking->count() }} total)
                                    </span>
                                </div>
                            @else
                                <div class="text-center mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Showing all {{ $universityRanking->count() }} universities
                                    </span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No universities with students yet.</p>
                    @endif
                </div>
            </div>

            <!-- Most Active Users -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                        <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900">⚡ Most Active Users</h3>
                        <a href="{{ url('/admin/user-activity') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 self-start sm:self-auto">View all →</a>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Ranked by total actions from Activity Logs (all time)</p>
                    @if($mostActiveUsers->count() > 0)
                        <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                            <div class="space-y-2 p-3">
                                @foreach($mostActiveUsers as $index => $item)
                                    <a href="{{ url('/admin/user-activity') }}?user_id={{ $item->user_id }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-transparent hover:border-indigo-200 transition-colors">
                                        <div class="flex items-center space-x-3 min-w-0 flex-1">
                                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                                {{ $index === 0 ? 'bg-amber-100 text-amber-700' : ($index === 1 ? 'bg-gray-200 text-gray-700' : ($index === 2 ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-600')) }}">
                                                {{ $index + 1 }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ optional($item->user)->name ?? 'Unknown' }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ optional($item->user)->email ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ml-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                {{ number_format($item->action_count ?? $item->activity_count ?? 0) }} actions
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-8">No activity data yet</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mt-4 sm:mt-6">
            <!-- Quiz Popularity -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
                    <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900 mb-3 sm:mb-4">📊 Most Popular Quizzes</h3>
                    <div class="space-y-3">
                        @forelse($quizPopularity as $index => $quiz)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($index === 0)
                                            <span class="text-2xl">🔥</span>
                                        @elseif($index === 1)
                                            <span class="text-2xl">⭐</span>
                                        @elseif($index === 2)
                                            <span class="text-2xl">💫</span>
                                        @else
                                            <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                                {{ $index + 1 }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $quiz->title }}</p>
                                        <p class="text-sm text-gray-500">{{ $quiz->total_questions }} questions</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-blue-600">{{ $quiz->student_count }} takers</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No quiz attempts yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quiz Performance -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-3 py-4 sm:px-4 sm:py-5 lg:p-6">
                    <h3 class="text-base sm:text-lg leading-6 font-medium text-gray-900 mb-3 sm:mb-4">🎯 Best Performing Quizzes</h3>
                    <div class="space-y-3">
                        @forelse($quizPerformance as $index => $quiz)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($index === 0)
                                            <span class="text-2xl">🎯</span>
                                        @elseif($index === 1)
                                            <span class="text-2xl">💯</span>
                                        @elseif($index === 2)
                                            <span class="text-2xl">✨</span>
                                        @else
                                            <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium text-gray-600">
                                                {{ $index + 1 }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $quiz->title }}</p>
                                        <p class="text-sm text-gray-500">{{ $quiz->student_count }} students</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-purple-600">{{ number_format($quiz->average_score, 1) }} avg</p>
                                    <p class="text-xs text-gray-500">{{ $quiz->highest_score }} max</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No quiz performance data yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart data from server
    const loginLabels = @json($loginChartLabels ?? []);
    const loginData = @json($loginChartData ?? []);
    const quizAttemptLabels = @json($quizAttemptChartLabels ?? []);
    const quizAttemptData = @json($quizAttemptChartData ?? []);
    const userRegLabels = @json($userRegChartLabels ?? []);
    const userRegData = @json($userRegChartData ?? []);
    const errorLogLabels = @json($errorLogChartLabels ?? []);
    const errorLogData = @json($errorLogChartData ?? []);
    const studentRegLabels = @json($studentRegChartLabels ?? []);
    const studentRegData = @json($studentRegChartData ?? []);
    const employeeRegData = @json($employeeRegChartData ?? []);
    const dtrLabels = @json($dtrChartLabels ?? []);
    const dtrEmployeeData = @json($dtrEmployeeChartData ?? []);
    const dtrStudentData = @json($dtrStudentChartData ?? []);
    const leaveEmployeeLabels = @json($leaveRequestEmployeeLabels ?? []);
    const leaveEmployeeData = @json($leaveRequestEmployeeData ?? []);
    const leaveStudentLabels = @json($leaveRequestStudentLabels ?? []);
    const leaveStudentData = @json($leaveRequestStudentData ?? []);
    const activityTypeLabels = @json($activityTypeLabels ?? []);
    const activityTypeData = @json($activityTypeData ?? []);
    const loginTimeLabels = @json($loginTimeLabels ?? []);
    const loginTimeData = @json($loginTimeData ?? []);
    const activityLogLabels = @json($activityLogLabels ?? []);
    const activityLogTotalData = @json($activityLogTotalData ?? []);
    const activityLogByTypeLabels = @json($activityLogByTypeLabels ?? []);
    const activityLogLoginData = @json($activityLogLoginData ?? []);
    const activityLogLogoutData = @json($activityLogLogoutData ?? []);
    const activityLogPageViewData = @json($activityLogPageViewData ?? []);

    // Activity Trends Chart (Logins + Quiz Attempts)
    if (document.getElementById('activityChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('activityChart'), {
            type: 'line',
            data: {
                labels: loginLabels,
                datasets: [
                    {
                        label: 'Logins',
                        data: loginData,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Quiz Attempts',
                        data: quizAttemptData,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // User Activity Logs Trend Chart (line graph)
    if (document.getElementById('activityLogTrendChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('activityLogTrendChart'), {
            type: 'line',
            data: {
                labels: activityLogLabels,
                datasets: [{
                    label: 'Total Activities',
                    data: activityLogTotalData,
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // User Activity Logs by Type Chart (multi-line graph)
    if (document.getElementById('activityLogByTypeChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('activityLogByTypeChart'), {
            type: 'line',
            data: {
                labels: activityLogByTypeLabels,
                datasets: [
                    {
                        label: 'Login',
                        data: activityLogLoginData,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Logout',
                        data: activityLogLogoutData,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Page View',
                        data: activityLogPageViewData,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Login Time Trend Chart (line graph - when users usually login)
    if (document.getElementById('loginTimeChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('loginTimeChart'), {
            type: 'line',
            data: {
                labels: loginTimeLabels,
                datasets: [{
                    label: 'Logins',
                    data: loginTimeData,
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Hour of day' }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Login count' }
                    }
                }
            }
        });
    }

    // User Registrations Chart
    if (document.getElementById('userRegChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('userRegChart'), {
            type: 'bar',
            data: {
                labels: userRegLabels,
                datasets: [{
                    label: 'New Users',
                    data: userRegData,
                    backgroundColor: 'rgba(139, 92, 246, 0.7)',
                    borderColor: 'rgb(139, 92, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Error Logs Chart
    if (document.getElementById('errorLogChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('errorLogChart'), {
            type: 'bar',
            data: {
                labels: errorLogLabels,
                datasets: [{
                    label: 'Errors',
                    data: errorLogData,
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Students vs Employees Chart
    if (document.getElementById('studentEmployeeChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('studentEmployeeChart'), {
            type: 'bar',
            data: {
                labels: studentRegLabels,
                datasets: [
                    {
                        label: 'Students',
                        data: studentRegData,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    },
                    {
                        label: 'Employees',
                        data: employeeRegData,
                        backgroundColor: 'rgba(245, 158, 11, 0.7)',
                        borderColor: 'rgb(245, 158, 11)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // DTR Records Chart
    if (document.getElementById('dtrChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('dtrChart'), {
            type: 'line',
            data: {
                labels: dtrLabels,
                datasets: [
                    {
                        label: 'Employees',
                        data: dtrEmployeeData,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Students',
                        data: dtrStudentData,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Employee Leave Requests Doughnut Chart
    if (document.getElementById('leaveRequestEmployeeChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('leaveRequestEmployeeChart'), {
            type: 'doughnut',
            data: {
                labels: leaveEmployeeLabels,
                datasets: [{
                    data: leaveEmployeeData,
                    backgroundColor: ['rgba(245, 158, 11, 0.8)', 'rgba(34, 197, 94, 0.8)', 'rgba(239, 68, 68, 0.8)'],
                    borderColor: ['rgb(245, 158, 11)', 'rgb(34, 197, 94)', 'rgb(239, 68, 68)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    // Student Leave Requests Doughnut Chart
    if (document.getElementById('leaveRequestStudentChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('leaveRequestStudentChart'), {
            type: 'doughnut',
            data: {
                labels: leaveStudentLabels,
                datasets: [{
                    data: leaveStudentData,
                    backgroundColor: ['rgba(245, 158, 11, 0.8)', 'rgba(34, 197, 94, 0.8)', 'rgba(239, 68, 68, 0.8)'],
                    borderColor: ['rgb(245, 158, 11)', 'rgb(34, 197, 94)', 'rgb(239, 68, 68)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    // Activity Types Pie Chart
    if (document.getElementById('activityTypeChart') && typeof Chart !== 'undefined') {
        new Chart(document.getElementById('activityTypeChart'), {
            type: 'doughnut',
            data: {
                labels: activityTypeLabels,
                datasets: [{
                    data: activityTypeData,
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(6, 182, 212, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    // Real-time user activity updates
    function updateActivityData() {
        fetch('{{ url('/admin/activity-data') }}')
            .then(response => response.json())
            .then(data => {
                // Update online count
                const onlineCountElement = document.getElementById('online-count');
                if (onlineCountElement) {
                    onlineCountElement.textContent = data.activityStats.online;
                }

                // Update online users list
                const onlineUsersList = document.getElementById('online-users-list');
                if (onlineUsersList) {
                    if (data.onlineUsers.length === 0) {
                        onlineUsersList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No users currently online</p>';
                    } else {
                        onlineUsersList.innerHTML = data.onlineUsers.map(user => `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold text-sm">
                                            ${user.name.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">${user.name}</p>
                                        <p class="text-xs text-gray-500">${user.email}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">${user.last_activity}</p>
                                    <p class="text-xs text-gray-400">${user.ip_address}</p>
                                </div>
                            </div>
                        `).join('');
                    }
                }

                // Update recent activities list
                const recentActivitiesList = document.getElementById('recent-activities-list');
                if (recentActivitiesList) {
                    if (data.recentActivities.length === 0) {
                        recentActivitiesList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No recent activities</p>';
                    } else {
                        recentActivitiesList.innerHTML = data.recentActivities.map(activity => {
                            let iconClass = 'bg-blue-100';
                            let iconSvg = `
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            `;

                            if (activity.activity_type === 'login') {
                                iconClass = 'bg-green-100';
                                iconSvg = `
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                `;
                            } else if (activity.activity_type === 'logout') {
                                iconClass = 'bg-red-100';
                                iconSvg = `
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                `;
                            }

                            return `
                                <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 ${iconClass} rounded-full flex items-center justify-center">
                                            ${iconSvg}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">${activity.user_name}</p>
                                        <p class="text-sm text-gray-500">
                                            ${activity.activity_type.charAt(0).toUpperCase() + activity.activity_type.slice(1).replace('_', ' ')}
                                            ${activity.action ? '- ' + activity.action : ''}
                                        </p>
                                        <p class="text-xs text-gray-400">${activity.created_at}</p>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching activity data:', error);
            });
    }

    // Polling disabled to reduce server load
    // Update activity data every 10 seconds
    // setInterval(updateActivityData, 10000);

    // Initial load
    updateActivityData();
});
</script>
@endsection
