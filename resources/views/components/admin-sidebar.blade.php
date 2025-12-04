<!-- Admin Sidebar -->
<div class="fixed inset-y-0 left-0 z-50 bg-gray-900 transform transition-all duration-300 ease-in-out flex flex-col"
     :class="[
         { '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen },
         sidebarCollapsed ? 'w-16' : 'w-64'
     ]"
     x-data="{ sidebarOpen: false }"
     x-init="sidebarOpen = true"
     @sidebar-toggle.window="sidebarOpen = !sidebarOpen"
     x-effect="$store.sidebar = { collapsed: sidebarCollapsed }">

    <!-- Sidebar Header -->
    <div class="flex items-center justify-between h-16 px-6 bg-gray-800 flex-shrink-0">
        <div class="flex items-center space-x-2" :class="sidebarCollapsed ? 'justify-center' : ''">
            @if($settings['system_logo'])
                <img src="{{ Storage::url($settings['system_logo']) }}"
                     alt="{{ $settings['system_name'] }}"
                     class="h-8 w-auto object-contain">
            @endif
            <span class="text-white font-bold text-lg transition-opacity duration-300"
                  :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                {{ $settings['system_name'] }}
            </span>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="mt-6 px-3 flex-1 overflow-y-auto sidebar-scroll">
        <!-- Dashboard -->
        <div class="mb-6">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
               :class="sidebarCollapsed ? 'justify-center' : ''"
               :title="sidebarCollapsed ? 'Dashboard' : ''">
                <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                </svg>
                <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                    Dashboard
                </span>
            </a>
        </div>

        <!-- User Management -->
        <div class="mb-6">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100'">User Management</h3>
            <div class="space-y-1">
                <a href="{{ route('users.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('users.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Users' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Users
                    </span>
                </a>
                <a href="{{ route('universities.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('universities.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Universities' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Universities
                    </span>
                </a>
            </div>
        </div>

        <!-- Student Management -->
        <div class="mb-6">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100'">Student Management</h3>
            <div class="space-y-1">
                <a href="{{ route('admin.student-dtr.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-dtr.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Student DTR' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        DTR (Time Records)
                    </span>
                </a>
                <a href="{{ route('admin.student-leave-requests.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-leave-requests.index') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Student Leave Requests' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Student Leave Requests
                    </span>
                </a>
                <a href="{{ route('admin.student-leave-requests.calendar') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-leave-requests.calendar') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Student Leave Calendar' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Student Leave Calendar
                    </span>
                </a>
            </div>
        </div>

        <!-- Content Management -->
        <div class="mb-6">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100'">Content Management</h3>
            <div class="space-y-1">
                <a href="{{ route('quizzes.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('quizzes.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Quizzes' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Quizzes
                    </span>
                </a>
                <a href="{{ route('admin.manual-grading') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.manual-grading') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Manual Grading' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Manual Grading
                    </span>
                </a>
                <a href="{{ route('admin.forum.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.forum.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Forum' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Forum
                    </span>
                </a>
            </div>
        </div>

        <!-- Hiring Process -->
        <div class="mb-6">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100'">Hiring Process</h3>
            <div class="space-y-1">
                <a href="{{ route('admin.hiring-process.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hiring-process.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Hiring Process' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Hiring Process
                    </span>
                </a>
                <a href="{{ route('admin.hiring-positions.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hiring-positions.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Positions' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Positions
                    </span>
                </a>
                <a href="{{ route('admin.hiring-applications.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hiring-applications.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Applications' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Applications
                    </span>
                </a>
            </div>
        </div>

        <!-- Communication -->
        <div class="mb-6">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100'">Communication</h3>
            <div class="space-y-1">
                <a href="{{ route('contact-messages.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('contact-messages.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Messages' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Messages
                    </span>
                </a>
                <a href="{{ route('live-chat.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('live-chat.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Live Chat' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Live Chat
                    </span>
                </a>
                <a href="{{ route('admin.feedback.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.feedback.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Feedback' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2h-5m-1-13l-2 2m0 0l-2-2m2 2v4"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Feedback
                    </span>
                </a>
            </div>
        </div>

        <!-- Analytics & Reports -->
        <div class="mb-6">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100'">Analytics & Reports</h3>
            <div class="space-y-1">
                <a href="{{ route('admin.analytics.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Analytics' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Analytics
                    </span>
                </a>
                <a href="{{ route('admin.user-activity.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.user-activity.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'User Activity' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        User Activity
                    </span>
                </a>
            </div>
        </div>

        <!-- Employee Management -->
        <div class="mb-6">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100'">Employee Management</h3>
            <div class="space-y-1">
                <a href="{{ route('admin.dtr.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.dtr.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'DTR' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        DTR (Time Records)
                    </span>
                </a>
                <a href="{{ route('admin.leave-requests.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.leave-requests.index') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Leave Requests' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Leave Requests
                    </span>
                </a>
                <a href="{{ route('admin.leave-requests.calendar') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.leave-requests.calendar') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Leave Calendar' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Leave Calendar
                    </span>
                </a>
            </div>
        </div>

        <!-- System -->
        <div class="mb-6">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden' : 'opacity-100'">System</h3>
            <div class="space-y-1">
                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Settings' : ''">
                    <svg class="h-5 w-5" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Settings
                    </span>
                </a>
            </div>
        </div>
    </nav>

    <!-- User Info at Bottom -->
    <div class="p-4 bg-gray-800 border-t border-gray-700 flex-shrink-0">
        <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                    {{ auth()->user()->name[0] }}
                </span>
            </div>
            <div class="flex-1 min-w-0 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">Administrator</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" :class="sidebarCollapsed ? 'ml-0' : ''">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-white" :title="sidebarCollapsed ? 'Logout' : ''">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Mobile Sidebar Overlay -->
<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
     @click="sidebarOpen = false">
</div>
