<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $settings['system_name'])</title>

    <!-- Favicon -->
        @if(isset($settings['system_icon']) && $settings['system_icon'])
            <link rel="icon" type="image/x-icon" href="{{ $settings['system_icon_url'] ?? '' }}">
            <link rel="shortcut icon" type="image/x-icon" href="{{ $settings['system_icon_url'] ?? '' }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Quiz Animation Styles -->
    <style>
        .question-transition {
            transition: all 0.3s ease-in-out;
        }

        .question-enter {
            opacity: 0;
            transform: translateX(20px);
        }

        .question-enter-active {
            opacity: 1;
            transform: translateX(0);
        }

        .question-exit {
            opacity: 1;
            transform: translateX(0);
        }

        .question-exit-active {
            opacity: 0;
            transform: translateX(-20px);
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col transition-all duration-300" :class="sidebarCollapsed ? 'w-16' : 'w-64'">
                <!-- Sidebar Header -->
                <div class="flex items-center h-16 flex-shrink-0 px-4 bg-indigo-600">
                    <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                        @if($settings['system_logo'])
                            <img src="{{ $settings['system_logo_url'] ?? '' }}"
                                 alt="{{ $settings['system_name'] }}"
                                 class="h-8 w-auto object-contain" :class="sidebarCollapsed ? '' : 'mr-2'">
                        @endif
                        <span class="text-white font-semibold text-lg transition-opacity duration-300"
                              :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                            {{ $settings['system_name'] }}
                        </span>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex-1 flex flex-col overflow-y-auto bg-gray-800">
                    <nav class="flex-1 px-2 py-4 space-y-1">
                        <!-- Dashboard -->
                        <a href="{{ url('/dashboard') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.dashboard') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Dashboard' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Dashboard
                            </span>
                        </a>

                        @if(auth()->user()->role !== 'technician')
                            <!-- Quizzes -->
                            <a href="{{ url('/quizzes') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.quizzes.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Quizzes' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                    Quizzes
                                </span>
                            </a>
                        @endif

                        @if(auth()->user()->role === 'technician')
                            <a href="{{ url('/technician/tickets') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.technician-tickets.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Assigned Tickets' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L6 20.75M14.25 7l3.75-3.75M7 7h.01M17 17h.01M7 17h.01M17 7h.01M12 12l0 0"></path>
                                </svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                    Assigned Tickets
                                </span>
                            </a>
                        @endif

                        <!-- Application (Applicant) -->
                        @if(auth()->user()->role === 'applicant')
                        <a href="{{ url('/hiring-application') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.hiring-application.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Application' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Application
                            </span>
                        </a>
                        @endif

                        <!-- DTR (Employee Only) -->
                        @if(in_array(auth()->user()->role, ['employee', 'student']))
                        <a href="{{ url('/dtr') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.dtr.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'DTR' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                DTR
                            </span>
                        </a>
                        @endif

                        <!-- File Storage (Employee & Student) -->
                        @if(in_array(auth()->user()->role, ['employee', 'student']))
                        <a href="{{ url('/files') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.files.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'File Storage' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                File Storage
                            </span>
                        </a>
                        @endif

                        <!-- Leave Requests (Employee & Student) -->
                        @if(in_array(auth()->user()->role, ['employee', 'student']))
                        <a href="{{ url('/leave-requests') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.leave-requests.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Leave Requests' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Leave Requests
                            </span>
                        </a>
                        @endif

                        <!-- Chat -->
                        <a href="{{ url('/user-chat') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user-chat.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Chat' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Chat
                            </span>
                            <span id="unread-message-count" class="hidden ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">0</span>
                        </a>

                        <!-- Forum -->
                        <a href="{{ url('/forum') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('forum.index') || request()->routeIs('forum.show') || request()->routeIs('forum.saved') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Forum' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Forum
                            </span>
                        </a>

                        <!-- Feedback -->
                        <a href="{{ url('/feedback') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.feedback.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Feedback' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Feedback
                            </span>
                        </a>

                        <!-- Term of Reference (TOR) - Student Only -->
                        @if(auth()->user()->role === 'student')
                        <a href="{{ url('/tor') }}"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.tor') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                           :class="sidebarCollapsed ? 'justify-center' : ''"
                           :title="sidebarCollapsed ? 'Term of Reference (TOR)' : ''">
                            <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                                Term of Reference (TOR)
                            </span>
                        </a>
                        @endif

                        <!-- Admin permissions only (no admin dashboard link; show only assigned permission areas) -->
                        @if(auth()->user()->hasAnyAdminPermission() && !auth()->user()->isSuperAdmin())
                        <div class="pt-4 mt-4 border-t border-gray-700 space-y-1">
                            @if(auth()->user()->canAccessContentManagement())
                            <a href="{{ url('/admin/quizzes') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.quizzes.*') || request()->routeIs('quizzes.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Content' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Content</span>
                            </a>
                            @endif
                            @if(auth()->user()->canAccessAnalyticsReports())
                            <a href="{{ url('/admin/analytics') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.analytics.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Analytics' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Analytics</span>
                            </a>
                            @endif
                            @if(auth()->user()->canAccessEmployeeManagement())
                            <a href="{{ url('/admin/dtr') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.dtr.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Employees' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Employees</span>
                            </a>
                            @endif
                            @if(auth()->user()->canAccessStudentManagement())
                            <a href="{{ url('/admin/student-management/students') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-management.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Students' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Students</span>
                            </a>
                            @endif
                            @if(auth()->user()->canAccessHiringProcess())
                            <a href="{{ url('/admin/hiring-process') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hiring-process.*') || request()->routeIs('admin.hiring-*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Hiring' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Hiring</span>
                            </a>
                            @endif
                            @if(auth()->user()->canAccessCommunication())
                            <a href="{{ url('/admin/contact-messages') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('contact-messages.*') || request()->routeIs('live-chat.*') || request()->routeIs('admin.feedback.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Communication' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Communication</span>
                            </a>
                            @endif
                            @if(auth()->user()->canAccessLinkedAccounts())
                            <a href="{{ url('/admin/linked-accounts') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/linked-accounts*') || request()->is('admin/starlinks*') || request()->is('admin/omadas*') || request()->is('admin/subscription-plan-types*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Starlinks Accounts' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Starlinks Accounts</span>
                            </a>
                            @endif
                            @if(auth()->user()->canAccessUserManagement())
                            <a href="{{ url('/admin/users') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Users' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Users</span>
                            </a>
                            @endif
                            @if(auth()->user()->canAccessSystem())
                            <a href="{{ url('/admin/settings') }}"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}"
                               :class="sidebarCollapsed ? 'justify-center' : ''"
                               :title="sidebarCollapsed ? 'Settings' : ''">
                                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Settings</span>
                            </a>
                            @endif
                        </div>
                        @endif

                        <!-- Admin Permission section removed -->

                    </nav>

                    <!-- User Profile Section -->
                    <div class="flex-shrink-0 flex border-t border-gray-700 p-4">
                        <div class="flex items-center w-full" :class="sidebarCollapsed ? 'justify-center' : ''">
                            <a href="{{ url('/profile') }}" class="flex items-center flex-1 hover:bg-gray-700 rounded-md p-1 transition-colors duration-200" :class="sidebarCollapsed ? 'justify-center' : ''">
                                <div class="flex-shrink-0">
                                    @if(auth()->user()->profile_picture)
                                        <img src="{{ auth()->user()->getProfilePictureUrl() }}"
                                             alt="{{ auth()->user()->name }}"
                                             class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-indigo-600 font-semibold text-sm">
                                                {{ auth()->user()->getInitials() }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100 ml-3'">
                                    <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-300">{{ auth()->user()->getRankText() }}</p>
                                </div>
                            </a>
                            <div class="relative transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100 ml-3'" x-data="{ open: false }">
                                <button @click="open = !open" class="text-gray-400 hover:text-white focus:outline-none" :title="sidebarCollapsed ? 'Menu' : ''">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>
                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 bottom-0 mb-12 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                    <a href="{{ url('/profile/edit') }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Edit Profile
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <form method="POST" action="{{ url('/logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile sidebar -->
        <div class="lg:hidden">
            <!-- Mobile sidebar overlay -->
            <div x-show="sidebarOpen"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 z-40 lg:hidden">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
            </div>

            <!-- Mobile sidebar -->
            <div x-show="sidebarOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-800">
                <div class="flex flex-col h-full">
                    <!-- Mobile sidebar header -->
                    <div class="flex items-center justify-between h-16 px-4 bg-indigo-600">
                        <div class="flex items-center">
                            @if($settings['system_logo'])
                                <img src="{{ $settings['system_logo_url'] ?? '' }}"
                                     alt="{{ $settings['system_name'] }}"
                                     class="h-8 w-auto object-contain">
                            @endif
                            <span class="ml-2 text-white font-semibold text-lg">{{ $settings['system_name'] }}</span>
                        </div>
                        <button @click="sidebarOpen = false" class="text-white hover:text-gray-300 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile navigation -->
                    <nav class="flex-1 px-2 py-4 space-y-1">
                        <a href="{{ url('/dashboard') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.dashboard') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                            Dashboard
                        </a>

                        <a href="{{ url('/quizzes') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.quizzes.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Quizzes
                        </a>

                        @if(auth()->user()->role === 'applicant')
                        <a href="{{ url('/hiring-application') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.hiring-application.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Application
                        </a>
                        @endif

                        @if(in_array(auth()->user()->role, ['employee', 'student']))
                        <a href="{{ url('/files') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.files.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            File Storage
                        </a>
                        @endif

                        @if(in_array(auth()->user()->role, ['employee', 'student']))
                        <a href="{{ url('/dtr') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.dtr.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            DTR
                        </a>
                        @endif

                        @if(in_array(auth()->user()->role, ['employee', 'student']))
                        <a href="{{ url('/leave-requests') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.leave-requests.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Leave Requests
                        </a>
                        @endif

                        <a href="{{ url('/user-chat') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user-chat.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Chat
                            <span id="unread-message-count-mobile" class="hidden ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1 min-w-[20px] text-center">0</span>
                        </a>

                        <a href="{{ url('/forum') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('forum.index') || request()->routeIs('forum.show') || request()->routeIs('forum.saved') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                            </svg>
                            Forum
                        </a>

                        <a href="{{ url('/feedback') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.feedback.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            Feedback
                        </a>

                        @if(auth()->user()->role === 'student')
                        <a href="{{ url('/tor') }}"
                           @click="sidebarOpen = false"
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.tor') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Term of Reference (TOR)
                        </a>
                        @endif

                        <!-- Admin permissions only (no admin dashboard; only assigned permission areas) -->
                        @if(auth()->user()->hasAnyAdminPermission() && !auth()->user()->isSuperAdmin())
                        <div class="pt-4 mt-4 border-t border-gray-700 space-y-1">
                            @if(auth()->user()->canAccessContentManagement())
                            <a href="{{ url('/admin/quizzes') }}" @click="sidebarOpen = false"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.quizzes.*') || request()->routeIs('quizzes.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Content
                            </a>
                            @endif
                            @if(auth()->user()->canAccessAnalyticsReports())
                            <a href="{{ url('/admin/analytics') }}" @click="sidebarOpen = false"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.analytics.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                Analytics
                            </a>
                            @endif
                            @if(auth()->user()->canAccessEmployeeManagement())
                            <a href="{{ url('/admin/dtr') }}" @click="sidebarOpen = false"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.dtr.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Employees
                            </a>
                            @endif
                            @if(auth()->user()->canAccessStudentManagement())
                            <a href="{{ url('/admin/student-management/students') }}" @click="sidebarOpen = false"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-management.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>
                                Students
                            </a>
                            @endif
                            @if(auth()->user()->canAccessHiringProcess())
                            <a href="{{ url('/admin/hiring-process') }}" @click="sidebarOpen = false"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hiring-process.*') || request()->routeIs('admin.hiring-*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Hiring
                            </a>
                            @endif
                            @if(auth()->user()->canAccessCommunication())
                            <a href="{{ url('/admin/contact-messages') }}" @click="sidebarOpen = false"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('contact-messages.*') || request()->routeIs('live-chat.*') || request()->routeIs('admin.feedback.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Communication
                            </a>
                            @endif
                            @if(auth()->user()->canAccessUserManagement())
                            <a href="{{ url('/admin/users') }}" @click="sidebarOpen = false"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                Users
                            </a>
                            @endif
                            @if(auth()->user()->canAccessSystem())
                            <a href="{{ url('/admin/settings') }}" @click="sidebarOpen = false"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-purple-700 text-white' : 'text-purple-300 hover:bg-purple-700 hover:text-white' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Settings
                            </a>
                            @endif
                        </div>
                        @endif

                        <!-- Admin Permission: show assigned features (users with restricted admin access only) -->
                        @if(auth()->user()->adminPermission)
                        <div class="pt-4 mt-4 border-t border-gray-700 space-y-1">
                            <a href="{{ url('/admin/my-permissions') }}" @click="sidebarOpen = false"
                               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.my-permissions') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Admin Permission
                            </a>
                        </div>
                        @endif

                    </nav>

                    <!-- Mobile user profile section -->
                    <div class="flex-shrink-0 flex border-t border-gray-700 p-4">
                        <div class="flex items-center w-full">
                            <div class="flex-shrink-0">
                                @if(auth()->user()->profile_picture)
                                    <img src="{{ auth()->user()->getProfilePictureUrl() }}"
                                         alt="{{ auth()->user()->name }}"
                                         class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-600 font-semibold text-sm">
                                            {{ auth()->user()->getInitials() }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-300">{{ auth()->user()->getRankText() }}</p>
                            </div>
                            <div class="ml-3 relative" x-data="{ open: false }">
                                <button @click="open = !open" class="text-gray-400 hover:text-white focus:outline-none">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>
                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 bottom-0 mb-12 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                    <a href="{{ url('/profile/edit') }}"
                                       @click="sidebarOpen = false"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Edit Profile
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <form method="POST" action="{{ url('/logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300">
            <!-- Top header bar -->
            <div class="flex items-center justify-between h-16 px-4 bg-white border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Desktop sidebar toggle button -->
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors duration-200">
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <h1 class="text-lg font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                </div>

                <!-- Top right section -->
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell -->
                    <div class="relative" x-data="notificationBell()">
                        <button @click="toggleNotifications()" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span x-show="unreadCount > 0" x-text="unreadCount"
                                  class="notification-bell-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
                        </button>

                        <!-- Notification Dropdown -->
                        <div x-show="showNotifications" @click.away="showNotifications = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                                    <button @click="markAllAsRead()" class="text-sm text-indigo-600 hover:text-indigo-800">Mark all read</button>
                                </div>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <div x-show="notifications.length === 0" class="p-4 text-center text-gray-500">
                                    No notifications
                                </div>
                                <template x-for="notification in notifications" :key="notification.id">
                                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                                         :class="{ 'bg-blue-50': !notification.is_read }"
                                         @click="markAsRead(notification.id)">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-2 h-2 bg-indigo-500 rounded-full" x-show="!notification.is_read"></div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                                <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-400 mt-1" x-text="notification.created_at"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="p-4 border-t border-gray-200">
                                <a href="{{ url('/notifications') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 p-1">
                            @if(auth()->user()->profile_picture)
                                <img src="{{ auth()->user()->getProfilePictureUrl() }}"
                                     alt="{{ auth()->user()->name }}"
                                     class="h-8 w-8 rounded-full object-cover">
                            @else
                                <div class="h-8 w-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">{{ auth()->user()->getInitials() }}</span>
                                </div>
                            @endif
                            <span class="hidden sm:block text-gray-700 font-medium text-sm">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <a href="{{ url('/profile/edit') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Edit Profile
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <form method="POST" action="{{ url('/logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto bg-gray-50">
                <div class="h-full">
                    <div class="h-full">
                        @if(session('success'))
                            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3">
                                {{ session('error') }}
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    @yield('scripts')

    <!-- Live Chat for Logged-in Users -->
    @auth
        @if(!auth()->user()->isAdmin())
            <div id="live-chat-widget" class="fixed bottom-4 right-4 z-50">
                <!-- Chat Toggle Button -->
                <button id="chat-toggle" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 hover:scale-110">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </button>

                <!-- Chat Window -->
                <div id="chat-window" class="hidden absolute bottom-16 right-0 w-80 bg-white rounded-lg shadow-xl border border-gray-200">
                    <!-- Chat Header -->
                    <div class="bg-indigo-600 text-white p-4 rounded-t-lg flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div>
                                <h3 class="font-semibold">Live Support</h3>
                                <p class="text-sm text-indigo-100">We're here to help!</p>
                            </div>
                            <button id="inbox-btn" class="text-indigo-100 hover:text-white p-1" title="Inbox">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </button>
                        </div>
                        <button id="chat-close" class="text-indigo-100 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Inbox Panel -->
                    <div id="inbox-panel" class="hidden h-64 overflow-y-auto p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-semibold text-gray-900">Your Tickets</h4>
                            <button id="new-ticket-btn" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                New Ticket
                            </button>
                        </div>
                        <div id="tickets-list" class="space-y-2">
                            <!-- Tickets will be loaded here -->
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div id="chat-messages" class="h-64 overflow-y-auto p-4 space-y-3">
                        <div class="flex items-start space-x-2">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="bg-gray-100 rounded-lg p-3 max-w-xs">
                                <p class="text-sm text-gray-800">Hello! How can I help you today?</p>
                                <p class="text-xs text-gray-500 mt-1">Support Team</p>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="p-4 border-t border-gray-200">
                        <div id="chat-input-section">
                            <div class="flex space-x-2">
                                <input type="text" id="chat-input" placeholder="Type your message..."
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <button id="chat-send" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    Send
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Live chat is available during business hours (9 AM - 5 PM)</p>
                        </div>

                        <!-- Closed Chat Message -->
                        <div id="chat-closed-section" class="hidden">
                            <div class="bg-gray-100 border border-gray-200 rounded-lg p-3 text-center">
                                <p class="text-sm text-gray-600 mb-2">This chat has been closed by support.</p>
                                <p class="text-xs text-gray-500 mb-3">If you need further assistance, you can request to reopen it.</p>
                                <button id="reopen-request-btn" class="bg-yellow-600 text-white px-3 py-1 rounded text-xs hover:bg-yellow-700">
                                    Request to Reopen
                                </button>
                            </div>
                        </div>

                        <!-- Reopen Request Pending -->
                        <div id="chat-reopen-requested-section" class="hidden">
                            <div class="bg-orange-100 border border-orange-200 rounded-lg p-3 text-center">
                                <p class="text-sm text-orange-800 mb-2">Your reopen request has been submitted.</p>
                                <p class="text-xs text-orange-700 mb-3">Please wait for admin approval.</p>
                                <div class="flex items-center justify-center">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-orange-600"></div>
                                    <span class="ml-2 text-xs text-orange-600">Waiting for response...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Info -->
                        <div id="ticket-info" class="mt-2 text-xs text-gray-500 hidden">
                            <p><strong>Ticket:</strong> <span id="current-ticket-number"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const chatToggle = document.getElementById('chat-toggle');
                    const chatWindow = document.getElementById('chat-window');
                    const chatClose = document.getElementById('chat-close');
                    const chatInput = document.getElementById('chat-input');
                    const chatSend = document.getElementById('chat-send');
                    const chatMessages = document.getElementById('chat-messages');
                    const inboxBtn = document.getElementById('inbox-btn');
                    const inboxPanel = document.getElementById('inbox-panel');
                    const newTicketBtn = document.getElementById('new-ticket-btn');
                    const ticketsList = document.getElementById('tickets-list');

                    let messages = [];
                    let unreadCount = 0;
                    let currentTicketNumber = null;
                    let isChatClosed = false;
                    let tickets = [];
                    let isInboxOpen = false;

                    // Load messages on chat open
                    function loadMessages() {
                        if (!currentTicketNumber) {
                            return;
                        }

                        fetch(`{{ url("/chat/tickets/") . ":ticketNumber" }}`.replace(':ticketNumber', currentTicketNumber))
                            .then(response => response.json())
                            .then(data => {
                                if (data.messages) {
                                    messages = data.messages;
                                    displayMessages();
                                    markMessagesAsRead();
                                    updateChatStatus(data);
                                }
                            })
                            .catch(error => {
                                console.error('Error loading messages:', error);
                            });
                    }

                    // Update chat status based on ticket status
                    function updateChatStatus(ticketData) {
                        if (!ticketData) {
                            return;
                        }

                        const ticketStatus = ticketData.status;
                        const inputSection = document.getElementById('chat-input-section');
                        const closedSection = document.getElementById('chat-closed-section');
                        const reopenRequestedSection = document.getElementById('chat-reopen-requested-section');
                        const ticketInfo = document.getElementById('ticket-info');
                        const ticketNumberSpan = document.getElementById('current-ticket-number');

                        // Update UI based on ticket status
                        if (ticketStatus === 'reopen_requested') {
                            inputSection.classList.add('hidden');
                            closedSection.classList.add('hidden');
                            reopenRequestedSection.classList.remove('hidden');
                        } else if (ticketStatus === 'closed') {
                            inputSection.classList.add('hidden');
                            closedSection.classList.remove('hidden');
                            reopenRequestedSection.classList.add('hidden');
                        } else {
                            // Open, reopened, or other active statuses
                            inputSection.classList.remove('hidden');
                            closedSection.classList.add('hidden');
                            reopenRequestedSection.classList.add('hidden');
                        }

                        if (currentTicketNumber) {
                            ticketInfo.classList.remove('hidden');
                            ticketNumberSpan.textContent = currentTicketNumber;
                        }
                    }

                    // Display messages
                    function displayMessages() {
                        chatMessages.innerHTML = '';
                        messages.forEach(message => {
                            addMessageToUI(message.message, message.sender_type, message.created_at);
                        });
                        scrollToBottom();
                    }

                    // Add message to UI
                    function addMessageToUI(text, senderType, timestamp) {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'flex items-start space-x-2';

                        const isUser = senderType === 'user';
                        const time = new Date(timestamp).toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        if (isUser) {
                            messageDiv.innerHTML = `
                                <div class="flex-1"></div>
                                <div class="bg-indigo-600 text-white rounded-lg p-3 max-w-xs">
                                    <p class="text-sm">${text}</p>
                                    <p class="text-xs text-indigo-100 mt-1">You - ${time}</p>
                                </div>
                            `;
                        } else {
                            messageDiv.innerHTML = `
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="bg-gray-100 rounded-lg p-3 max-w-xs">
                                    <p class="text-sm text-gray-800">${text}</p>
                                    <p class="text-xs text-gray-500 mt-1">Support Team - ${time}</p>
                                </div>
                            `;
                        }

                        chatMessages.appendChild(messageDiv);
                    }

                    // Scroll to bottom
                    function scrollToBottom() {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }

                    // Mark messages as read
                    function markMessagesAsRead() {
                        const unreadMessageIds = messages
                            .filter(msg => msg.sender_type === 'admin' && !msg.is_read)
                            .map(msg => msg.id);

                        if (unreadMessageIds.length > 0) {
                            fetch('{{ url("/chat/mark-read") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ message_ids: unreadMessageIds })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    updateUnreadCount();
                                }
                            })
                            .catch(error => {
                                console.error('Error marking messages as read:', error);
                            });
                        }
                    }

                    // Update unread count
                    function updateUnreadCount() {
                        fetch('{{ url("/chat/unread-count") }}')
                            .then(response => response.json())
                            .then(data => {
                                unreadCount = data.count;
                                updateChatButton();
                            })
                            .catch(error => {
                                console.error('Error fetching unread count:', error);
                            });
                    }

                    // Update chat button with unread count
                    function updateChatButton() {
                        const chatToggle = document.getElementById('chat-toggle');
                        if (unreadCount > 0) {
                            chatToggle.innerHTML = `
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">${unreadCount}</span>
                            `;
                            chatToggle.classList.add('relative');
                        } else {
                            chatToggle.innerHTML = `
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            `;
                        }
                    }

                    // Toggle chat window
                    chatToggle.addEventListener('click', function() {
                        chatWindow.classList.toggle('hidden');
                        if (!chatWindow.classList.contains('hidden')) {
                            loadMessages();
                            chatInput.focus();
                        }
                    });

                    // Close chat window
                    chatClose.addEventListener('click', function() {
                        chatWindow.classList.add('hidden');
                    });

                    // Send message
                    function sendMessage() {
                        const message = chatInput.value.trim();
                        if (!message || isChatClosed) return;

                        // Add user message to UI immediately
                        addMessageToUI(message, 'user', new Date().toISOString());
                        chatInput.value = '';
                        scrollToBottom();

                        // Send to server
                        const requestData = { message: message };
                        if (currentTicketNumber) {
                            requestData.ticket_number = currentTicketNumber;
                        }

                        fetch('{{ url("/chat/messages") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(requestData)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                messages.push(data.message);
                                if (data.ticket_number) {
                                    currentTicketNumber = data.ticket_number;
                                    updateChatStatus();
                                }
                            } else {
                                console.error('Failed to send message:', data.message);
                                ToastNotification.error('Failed to send message: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error sending message:', error);
                            ToastNotification.error('Error sending message');
                        });
                    }

                    // Request to reopen chat
                    function requestReopen() {
                        if (!currentTicketNumber) return;

                        const reason = prompt('Please provide a reason for reopening this chat:');
                        if (reason && reason.trim()) {
                            fetch(`{{ url("/chat/tickets/") . ":ticketNumber" . "/reopen" }}`.replace(':ticketNumber', currentTicketNumber), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ reason: reason.trim() })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    ToastNotification.success('Reopen request submitted successfully. Admin will be notified.');
                                    loadMessages(); // Reload to get updated status
                                } else {
                                    ToastNotification.error('Failed to submit reopen request: ' + (data.message || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                console.error('Error submitting reopen request:', error);
                                ToastNotification.error('Error submitting reopen request');
                            });
                        }
                    }

                    // Inbox functionality
                    function loadTickets() {
                        fetch('{{ url("/chat/tickets") }}')
                            .then(response => response.json())
                            .then(data => {
                                tickets = data;
                                displayTickets();
                            })
                            .catch(error => {
                                console.error('Error loading tickets:', error);
                            });
                    }

                    function displayTickets() {
                        ticketsList.innerHTML = '';

                        if (tickets.length === 0) {
                            ticketsList.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No tickets yet. Create your first ticket!</p>';
                            return;
                        }

                        tickets.forEach(ticket => {
                            const ticketElement = document.createElement('div');
                            ticketElement.className = `p-3 border rounded-lg cursor-pointer hover:bg-gray-50 ${ticket.ticket_number === currentTicketNumber ? 'bg-indigo-50 border-indigo-200' : 'border-gray-200'}`;
                            ticketElement.innerHTML = `
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-gray-900">${ticket.subject || 'Support Request'}</span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeClass(ticket.status)}">
                                                ${ticket.status.replace('_', ' ')}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">${ticket.ticket_number}</p>
                                        <p class="text-xs text-gray-400">${formatDate(ticket.updated_at)}</p>
                                    </div>
                                    ${ticket.unread_count > 0 ? `<span class="bg-red-500 text-white text-xs rounded-full px-2 py-1">${ticket.unread_count}</span>` : ''}
                                </div>
                            `;

                            ticketElement.addEventListener('click', () => {
                                switchToTicket(ticket.ticket_number);
                            });

                            ticketsList.appendChild(ticketElement);
                        });
                    }

                    function getStatusBadgeClass(status) {
                        switch(status) {
                            case 'open': return 'bg-green-100 text-green-800';
                            case 'closed': return 'bg-gray-100 text-gray-800';
                            case 'reopened': return 'bg-yellow-100 text-yellow-800';
                            case 'reopen_requested': return 'bg-orange-100 text-orange-800';
                            default: return 'bg-gray-100 text-gray-800';
                        }
                    }

                    function formatDate(dateString) {
                        const date = new Date(dateString);
                        const now = new Date();
                        const diffInHours = (now - date) / (1000 * 60 * 60);

                        if (diffInHours < 1) {
                            return 'Just now';
                        } else if (diffInHours < 24) {
                            return `${Math.floor(diffInHours)}h ago`;
                        } else {
                            return `${Math.floor(diffInHours / 24)}d ago`;
                        }
                    }

                    function switchToTicket(ticketNumber) {
                        currentTicketNumber = ticketNumber;
                        isInboxOpen = false;
                        inboxPanel.classList.add('hidden');
                        chatMessages.classList.remove('hidden');
                        loadMessages();
                        displayTickets(); // Refresh to show current ticket as selected
                    }

                    function createNewTicket() {
                        const subject = prompt('What is this ticket about? (optional)');
                        if (subject !== null) {
                            fetch('{{ url("/chat/create") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ subject: subject })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    currentTicketNumber = data.ticket_number;
                                    isInboxOpen = false;
                                    inboxPanel.classList.add('hidden');
                                    chatMessages.classList.remove('hidden');
                                    loadTickets();
                                    loadMessages();
                                } else {
                                    ToastNotification.error('Error creating ticket: ' + (data.message || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                console.error('Error creating ticket:', error);
                                ToastNotification.error('Error creating ticket');
                            });
                        }
                    }

                    // Toggle inbox
                    inboxBtn.addEventListener('click', function() {
                        isInboxOpen = !isInboxOpen;
                        if (isInboxOpen) {
                            inboxPanel.classList.remove('hidden');
                            chatMessages.classList.add('hidden');
                            loadTickets();
                        } else {
                            inboxPanel.classList.add('hidden');
                            chatMessages.classList.remove('hidden');
                        }
                    });

                    // New ticket button
                    newTicketBtn.addEventListener('click', createNewTicket);

                    // Load first available ticket
                    function loadFirstTicket() {
                        fetch('{{ url("/chat/tickets") }}')
                            .then(response => response.json())
                            .then(data => {
                                if (data.length > 0) {
                                    currentTicketNumber = data[0].ticket_number;
                                    loadMessages();
                                }
                            })
                            .catch(error => {
                                console.error('Error loading first ticket:', error);
                            });
                    }

                    // Load notification counts
                    function loadNotificationCounts() {
                        // Load friend request count
                        fetch('{{ url("/friends") }}')
                            .then(response => response.text())
                            .then(html => {
                                // Parse the HTML to extract pending requests count
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const pendingRequests = doc.querySelectorAll('[data-friend-request]');
                                const count = pendingRequests.length;

                                // Friend request count removed - friends feature moved to profile page
                            })
                            .catch(error => {
                                console.error('Error loading friend request count:', error);
                            });

                        // Load unread message count
                        fetch('{{ url("/user-chat/unread-count") }}')
                            .then(response => response.json())
                            .then(data => {
                                const unreadMessageCount = document.getElementById('unread-message-count');
                                const unreadMessageCountMobile = document.getElementById('unread-message-count-mobile');
                                if (data.count > 0) {
                                    if (unreadMessageCount) unreadMessageCount.textContent = data.count;
                                    if (unreadMessageCountMobile) unreadMessageCountMobile.textContent = data.count;
                                    if (unreadMessageCount) unreadMessageCount.classList.remove('hidden');
                                    if (unreadMessageCountMobile) unreadMessageCountMobile.classList.remove('hidden');
                                } else {
                                    if (unreadMessageCount) unreadMessageCount.classList.add('hidden');
                                    if (unreadMessageCountMobile) unreadMessageCountMobile.classList.add('hidden');
                                }
                            })
                            .catch(error => {
                                console.error('Error loading unread message count:', error);
                            });
                    }

                    // Event listeners
                    chatSend.addEventListener('click', sendMessage);
                    chatInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            sendMessage();
                        }
                    });

                    // Reopen request button
                    const reopenRequestBtn = document.getElementById('reopen-request-btn');
                    if (reopenRequestBtn) {
                        reopenRequestBtn.addEventListener('click', requestReopen);
                    }

                    // Initial load
                    updateUnreadCount();
                    loadFirstTicket();
                    loadNotificationCounts();

                    // Polling disabled to reduce server load
                    // Poll for new messages every 10 seconds
                    // setInterval(function() {
                    //     if (!chatWindow.classList.contains('hidden')) {
                    //         loadMessages();
                    //     } else {
                    //         updateUnreadCount();
                    //     }
                    // }, 10000);
                });

                // User Status Tracking
                let lastActivity = Date.now();
                let isIdle = false;
                let statusUpdateInterval;

                // Track user activity
                function trackActivity() {
                    lastActivity = Date.now();
                    if (isIdle) {
                        isIdle = false;
                        updateUserStatus('online');
                    }
                }

                // Check if user is idle (no activity for 5 minutes)
                function checkIdleStatus() {
                    const now = Date.now();
                    const timeSinceActivity = now - lastActivity;
                    const idleThreshold = 5 * 60 * 1000; // 5 minutes

                    if (timeSinceActivity > idleThreshold && !isIdle) {
                        isIdle = true;
                        updateUserStatus('idle');
                    }
                }

                // Update user status
                function updateUserStatus(status) {
                    fetch('{{ url("/status/update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: status })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Status updated to:', data.status);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating status:', error);
                    });
                }

                // Set up activity tracking
                ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
                    document.addEventListener(event, trackActivity, true);
                });

                // Polling disabled to reduce server load
                // Check idle status every minute
                // statusUpdateInterval = setInterval(checkIdleStatus, 60000);

                // Set user as online when page loads
                updateUserStatus('online');

                // Set user as away when page is hidden
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        updateUserStatus('away');
                    } else {
                        updateUserStatus('online');
                        lastActivity = Date.now();
                    }
                });

                // Set user as offline when page is about to unload
                window.addEventListener('beforeunload', function() {
                    updateUserStatus('offline');
                });

                // Notification Bell Component
                function notificationBell() {
                    return {
                        showNotifications: false,
                        notifications: [],
                        unreadCount: 0,
                        pollingInterval: null,

                        init() {
                            this.loadNotifications();
                            // Polling disabled to reduce server load
                            // this.startPolling();
                        },

                        toggleNotifications() {
                            this.showNotifications = !this.showNotifications;
                            if (this.showNotifications) {
                                this.loadNotifications();
                            }
                        },

                        async loadNotifications() {
                            try {
                                const response = await fetch('{{ url("/notifications/recent") }}');
                                const data = await response.json();
                                this.notifications = data.notifications;
                                this.updateUnreadCount();
                            } catch (error) {
                                console.error('Error loading notifications:', error);
                            }
                        },

                        async updateUnreadCount() {
                            try {
                                const response = await fetch('{{ url("/notifications/unread-count") }}');
                                const data = await response.json();
                                this.unreadCount = data.unread_count;
                            } catch (error) {
                                console.error('Error updating unread count:', error);
                            }
                        },

                        async markAsRead(notificationId) {
                            try {
                                const response = await fetch('{{ url("/notifications/mark-read") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({ notification_id: notificationId })
                                });

                                if (response.ok) {
                                    // Remove the notification from the dropdown since it's now read
                                    this.notifications = this.notifications.filter(notification => notification.id !== notificationId);
                                    this.updateUnreadCount();
                                }
                            } catch (error) {
                                console.error('Error marking notification as read:', error);
                            }
                        },

                        async markAllAsRead() {
                            try {
                                const response = await fetch('{{ url("/notifications/mark-read") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({ mark_all: true })
                                });

                                if (response.ok) {
                                    // Clear all notifications from the dropdown since they're all read
                                    this.notifications = [];
                                    this.unreadCount = 0;
                                }
                            } catch (error) {
                                console.error('Error marking all notifications as read:', error);
                            }
                        },

                        startPolling() {
                            // Poll for new notifications every 30 seconds
                            this.pollingInterval = setInterval(() => {
                                this.updateUnreadCount();
                                if (this.showNotifications) {
                                    this.loadNotifications();
                                }
                            }, 30000);
                        },

                        stopPolling() {
                            if (this.pollingInterval) {
                                clearInterval(this.pollingInterval);
                            }
                        }
                    }
                }
            </script>
        @endif
    @endauth

    <!-- Toast Notifications -->
    @include('components.toast')

    <!-- Seasonal Effects -->
    @include('components.seasonal-effects')
</body>
</html>
