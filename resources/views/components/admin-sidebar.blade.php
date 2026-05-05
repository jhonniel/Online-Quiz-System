<!-- Sidebar Container with Shared State -->
<div x-data="{
         sidebarOpen: false,
         sidebarCollapsed: false,
         isDesktop: false,
         init() {
             // Initialize sidebar state based on screen size
             this.isDesktop = window.innerWidth >= 1024;
             this.sidebarOpen = this.isDesktop;

             // Ensure Alpine store is initialized
             if (typeof Alpine !== 'undefined') {
                 if (!Alpine.store('sidebar')) {
                     Alpine.store('sidebar', { collapsed: false });
                 }
                 // Sync local variable with store
                 this.sidebarCollapsed = Alpine.store('sidebar').collapsed || false;
             }

             // Debounce resize handler to avoid too many updates
             let resizeTimeout;
             const handleResize = () => {
                 clearTimeout(resizeTimeout);
                 resizeTimeout = setTimeout(() => {
                     const wasDesktop = this.isDesktop;
                     this.isDesktop = window.innerWidth >= 1024;

                     // Only auto-manage sidebar when transitioning between desktop/mobile
                     if (this.isDesktop && !wasDesktop) {
                         // Transitioning from mobile to desktop - open sidebar
                         this.sidebarOpen = true;
                     } else if (!this.isDesktop && wasDesktop) {
                         // Transitioning from desktop to mobile - close sidebar
                         this.sidebarOpen = false;
                     }
                     // If staying on same device type, respect current state
                 }, 150);
             };

             window.addEventListener('resize', handleResize);
         },
         closeSidebar() {
             // Explicit close function - force close
             this.sidebarOpen = false;
         },
         toggleSidebar() {
             // Toggle sidebar function
             this.sidebarOpen = !this.sidebarOpen;
         }
         }"
     @sidebar-toggle.window="toggleSidebar()"
     @close-sidebar.window="closeSidebar()">

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

    <!-- Admin Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 bg-gray-900 transition-all duration-300 ease-in-out flex flex-col"
         :class="[
             sidebarOpen || isDesktop ? 'translate-x-0' : '-translate-x-full',
             sidebarCollapsed ? 'w-16' : 'w-64'
         ]"
         style="transform: translateX(0);"
         x-bind:style="(sidebarOpen || isDesktop) ? 'transform: translateX(0) !important;' : 'transform: translateX(-100%);'"
         x-init="
             if (typeof Alpine !== 'undefined') {
                 if (!Alpine.store('sidebar')) {
                     Alpine.store('sidebar', { collapsed: false });
                 }
                 sidebarCollapsed = Alpine.store('sidebar').collapsed || false;
             }
         "
         @sidebar-collapse-changed.window="sidebarCollapsed = $event.detail">

    <!-- Sidebar Header -->
    <div class="flex items-center justify-between h-16 px-6 bg-gray-800 flex-shrink-0">
        <div class="flex items-center space-x-2" :class="sidebarCollapsed ? 'justify-center' : ''">
            @if($settings['system_logo'])
                <img src="{{ $settings['system_logo_url'] ?? '' }}"
                     alt="{{ $settings['system_name'] }}"
                     class="h-8 w-auto object-contain">
            @endif
            <span class="text-white font-bold text-lg transition-opacity duration-300"
                  :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                {{ $settings['system_name'] }}
            </span>
        </div>
        <button type="button"
                @click.stop="sidebarOpen = false"
                onclick="window.dispatchEvent(new CustomEvent('close-sidebar'))"
                class="lg:hidden text-gray-400 hover:text-white focus:outline-none z-50 relative cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="mt-6 px-3 flex-1 overflow-y-auto sidebar-scroll">
        <!-- Dashboard (full access only) -->
        @if(auth()->user()->isSuperAdmin())
        <div class="mb-6">
            <a href="{{ url('/admin/dashboard') }}"
               class="flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'"
               :title="sidebarCollapsed ? 'Dashboard' : ''">
                <svg class="h-6 w-6 flex-shrink-0"
                     style="min-width: 1.5rem; min-height: 1.5rem; display: block !important; visibility: visible !important; opacity: 1 !important;"
                     :class="sidebarCollapsed ? '' : 'mr-3'"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                </svg>
                <span class="transition-opacity duration-300 whitespace-nowrap" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden absolute' : 'opacity-100'">
                    Dashboard
                </span>
            </a>
        </div>
        @endif

        <!-- Content Management -->
        @if(auth()->user()->canAccessContentManagement())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-content-management') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-content-management', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>Content Management</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/quizzes') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('quizzes.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Quizzes' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Quizzes
                    </span>
                </a>
                <a href="{{ url('/admin/manual-grading') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.manual-grading') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Manual Grading' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Manual Grading
                    </span>
                </a>
                <a href="{{ url('/admin/forum') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.forum.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Forum' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Forum
                    </span>
                </a>
                <a href="{{ url('/admin/news') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.news.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'News' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        News
                    </span>
                </a>
                <a href="{{ url('/admin/evaluations') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.evaluations.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Evaluation Question' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-9v18m9-9A9 9 0 1112 3a9 9 0 019 9z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Evaluation Question
                    </span>
                </a>
            </div>
        </div>
        @endif

        <!-- CONFESSION (Say-it) -->
        @if(auth()->user()->canAccessConfession())
        <div class="mb-6 user-features-section" x-data="{
            open: (localStorage.getItem('nav-confession') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-confession', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>CONFESSION</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/confession') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->path() === 'admin/confession' ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Contents' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012 2v6M5 11v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2M5 11V5a2 2 0 012-2m0 0h14"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Contents</span>
                </a>
                <a href="{{ url('/admin/confession/dashboard') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->path() === 'admin/confession/dashboard' ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Dashboard' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Dashboard</span>
                </a>
                <a href="{{ url('admin/confession/banned-words') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->path() === 'admin/confession/banned-words' ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Banned words' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Banned words</span>
                </a>
                <a href="{{ url('admin/confession/topics') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->path() === 'admin/confession/topics' ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Topics' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Topics</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Analytics & Reports -->
        @if(auth()->user()->canAccessAnalyticsReports())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-analytics-reports') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-analytics-reports', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>Analytics & Reports</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/analytics') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.analytics.index') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Analytics' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Analytics
                    </span>
                </a>
                <a href="{{ url('/admin/analytics/error-logs') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.analytics.error-logs') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Error Logs' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 9V5.25m0 13.5v-6M4.5 3.75h15a.75.75 0 01.75.75v15a.75.75 0 01-.75.75h-15A.75.75 0 013.75 19.5v-15a.75.75 0 01.75-.75z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Error Logs
                    </span>
                </a>
                <a href="{{ url('/admin/user-activity') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.user-activity.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'User Activity' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        User Activity
                    </span>
                </a>
                <a href="{{ url('/admin/analytics/students-review') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.evaluations.reviews') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Students Review' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8M8 14h5m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Students Review
                    </span>
                </a>
            </div>
        </div>
        @endif

        <!-- KPI -->
        @if(auth()->user()->isSuperAdmin())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-kpi') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-kpi', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>KPI</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/kpi/dashboard') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.kpi.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'KPI Dashboard' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Dashboard
                    </span>
                </a>
            </div>
        </div>
        @endif

        <!-- Employee Management -->
        @if(auth()->user()->canAccessEmployeeManagement())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-employee-management') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-employee-management', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>Employee Management</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/employee-dashboard') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.employee-dashboard.index') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Employee Dashboard' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m3 6V7m3 10v-4m5 8H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v14a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Employee Dashboard
                    </span>
                </a>
                <a href="{{ url('/admin/dtr') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.dtr.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'DTR' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        DTR (Time Records)
                    </span>
                </a>
                <a href="{{ url('/admin/time-report') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.time-report.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Time Report' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Time Report
                    </span>
                </a>
                <a href="{{ url('/admin/leave-requests') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.leave-requests.index') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Leave Requests' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Leave Requests
                    </span>
                </a>
                <a href="{{ url('/admin/leave-calendar') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.leave-requests.calendar') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Leave Calendar' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Leave Calendar
                    </span>
                </a>
            </div>
        </div>
        @endif

        <!-- Student Management -->
        @if(auth()->user()->canAccessStudentManagement())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-student-management') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-student-management', this.open);
            }
        }"
        x-init="if ({{ request()->routeIs('admin.student-management.*') || request()->routeIs('admin.student-dtr.*') || request()->routeIs('admin.student-leave-requests.*') || request()->routeIs('admin.time-requests.*') ? 'true' : 'false' }}) { open = true; }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>Student Management</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/student-management/students') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-management.students') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Students' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Students
                    </span>
                </a>
                <a href="{{ url('/admin/student-management/dashboard') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-management.dashboard') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Student Time Dashboard' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18M4 8h16M6 13h12M9 18h6"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Student Time Dashboard
                    </span>
                </a>
                <a href="{{ url('/admin/student-dtr') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-dtr.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Student DTR' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        DTR (Time Records)
                    </span>
                </a>
                <a href="{{ url('/admin/student-leave-requests') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-leave-requests.index') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Student Leave Requests' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Student Leave Requests
                    </span>
                </a>
                <a href="{{ url('/admin/student-leave-calendar') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.student-leave-requests.calendar') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Student Leave Calendar' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Student Leave Calendar
                    </span>
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->hasAnyAdminPermission())
                <a href="{{ url('/admin/time-requests') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.time-requests.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Time Requests' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Time Requests
                    </span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Hiring Process -->
        @if(auth()->user()->canAccessHiringProcess())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-hiring-process') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-hiring-process', this.open);
            }
        }"
        x-init="if ({{ request()->routeIs('admin.hiring-process.*') || request()->routeIs('admin.hiring-positions.*') || request()->routeIs('admin.hiring-applications.*') ? 'true' : 'false' }}) { open = true; }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>Hiring Process</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/hiring-process') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hiring-process.index') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Hiring Process' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Hiring Process
                    </span>
                </a>
                <a href="{{ url('/admin/hiring-positions') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hiring-positions.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Positions' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Positions
                    </span>
                </a>
                <a href="{{ url('/admin/hiring-applications') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hiring-applications.index') || request()->routeIs('admin.hiring-applications.show') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Applications' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Applications
                    </span>
                </a>
                <a href="{{ url('/admin/hiring-process/applicants?view=hired') }}#hired-applicants"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/hiring-process/applicants') && request('view') === 'hired' ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Hired Applicants' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Hired Applicants
                    </span>
                </a>
                <a href="{{ url('/admin/hiring-applications/calendar') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.hiring-applications.calendar') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Calendar Interview' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Calendar Interview
                    </span>
                </a>
            </div>
        </div>
        @endif

        <!-- Communication -->
        @if(auth()->user()->canAccessCommunication() || auth()->user()->canAccessFeedback())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-communication') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-communication', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>Communication</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @if(auth()->user()->canAccessCommunication())
                <a href="{{ url('/admin/contact-messages') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('contact-messages.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Messages' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Messages
                    </span>
                </a>
                @endif
                @if(auth()->user()->canAccessCommunication())
                <a href="{{ url('/admin/live-chat') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('live-chat.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Live Chat' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Live Chat
                    </span>
                </a>
                @endif
                @if(auth()->user()->canAccessFeedback())
                <a href="{{ url('/admin/feedback') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.feedback.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Feedback' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2h-5m-1-13l-2 2m0 0l-2-2m2 2v4"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Feedback
                    </span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Tickets (Report a Problem) -->
        @if(auth()->user()->canAccessCommunication())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-tickets') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-tickets', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>Tickets</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/tickets') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/tickets') && !request()->is('admin/tickets/*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Dashboard' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Dashboard</span>
                </a>
                <a href="{{ url('/admin/tickets/open') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/tickets/open') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Open Tickets' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Open Tickets</span>
                </a>
                <a href="{{ url('/admin/tickets/closed') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/tickets/closed') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Closed Tickets' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Closed Tickets</span>
                </a>
                <a href="{{ url('/admin/tickets/problem-types') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/tickets/problem-types*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Problem Types' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Problem Types</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Subscriptions (linked_accounts or billing permission) -->
        @if(auth()->user()->canAccessLinkedAccounts() || auth()->user()->canAccessBilling())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-linked-accounts') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-linked-accounts', this.open);
            }
        }"
        x-init="if ({{ request()->routeIs('admin.linked-accounts.*') || request()->routeIs('admin.starlinks.*') || request()->routeIs('admin.omadas.*') || request()->is('admin/subscription-plan-types*') || request()->is('admin/billing*') ? 'true' : 'false' }}) { open = true; }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>Subscriptions</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @if(auth()->user()->canAccessLinkedAccounts())
                <a href="{{ url('/admin/linked-accounts') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/linked-accounts') && !request()->is('admin/linked-accounts/*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Dashboard' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Dashboard</span>
                </a>
                @endif
                @if(auth()->user()->canAccessLinkedAccounts())
                <a href="{{ url('/admin/starlinks') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/starlinks*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Starlinks' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Starlinks</span>
                </a>
                @endif
                @if(auth()->user()->canAccessLinkedAccounts())
                <a href="{{ url('/admin/omadas') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/omadas*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Omada' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2m14-8V6a2 2 0 00-2-2m-4 0a2 2 0 00-2 2v4a2 2 0 002 2m4 0h2"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Omada</span>
                </a>
                @endif
                @if(auth()->user()->canAccessBilling())
                <a href="{{ url('/admin/billing') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/billing') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Billing' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Billing</span>
                </a>
                @endif
                @if(auth()->user()->canAccessLinkedAccounts())
                <a href="{{ url('/admin/subscription-plan-types') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/subscription-plan-types*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Plan Types' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">Plan Types</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- User Management -->
        @if(auth()->user()->canAccessUserManagement())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-user-management') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-user-management', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>User Management</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/users') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Users' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Users
                    </span>
                </a>
                <a href="{{ url('/admin/universities') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.universities.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Universities' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Universities
                    </span>
                </a>
                <a href="{{ url('/admin/departments') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.departments.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Departments' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Departments
                    </span>
                </a>
            </div>
        </div>
        @endif

        <!-- File Storage -->
        @if(auth()->user()->canAccessFiles())
        <div class="mb-6">
            <a href="{{ url('/admin/files') }}"
               class="flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.files.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
               :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'"
               :title="sidebarCollapsed ? 'File Storage' : ''">
                <svg class="h-6 w-6 flex-shrink-0"
                     style="min-width: 1.5rem; min-height: 1.5rem; display: block !important; visibility: visible !important; opacity: 1 !important;"
                     :class="sidebarCollapsed ? '' : 'mr-3'"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                <span class="transition-opacity duration-300 whitespace-nowrap" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden absolute' : 'opacity-100'">
                    File Storage
                </span>
            </a>
        </div>
        @endif

        <!-- TASK TO DO -->
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-task-assign') || '{{ request()->routeIs('admin.tasks.*') ? 'true' : 'false' }}') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-task-assign', this.open);
            }
        }"
        x-init="if ({{ request()->routeIs('admin.tasks.*') ? 'true' : 'false' }}) { open = true; }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'"
                    :title="sidebarCollapsed ? 'TASK TO DO' : ''">
                <div class="flex items-center">
                    <span class="transition-opacity duration-300 whitespace-nowrap" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden absolute' : 'opacity-100'">
                        TASK TO DO
                    </span>
                </div>
                <svg class="h-4 w-4 transition-transform duration-200 flex-shrink-0"
                     :class="[
                         open ? 'rotate-90' : '',
                         sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'
                     ]"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 :class="sidebarCollapsed ? 'hidden' : ''"
                 class="ml-6 mt-1 space-y-1">
                @if(auth()->user()->isSuperAdmin())
                <a href="{{ url('/admin/tasks/dashboard') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.tasks.dashboard') ? 'bg-indigo-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}"
                   :title="sidebarCollapsed ? 'Task Dashboard' : ''">
                    <svg class="h-5 w-5 flex-shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Task Dashboard
                    </span>
                </a>
                @endif
                <a href="{{ url('/admin/tasks?type=personal') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.tasks.index') && request('type') == 'personal' ? 'bg-indigo-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}"
                   :title="sidebarCollapsed ? 'My Tasks' : ''">
                    <svg class="h-5 w-5 flex-shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        My Tasks
                    </span>
                </a>
                <a href="{{ url('/admin/tasks?type=group') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.tasks.index') && request('type') == 'group' ? 'bg-indigo-700 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-gray-300' }}"
                   :title="sidebarCollapsed ? 'Group Tasks' : ''">
                    <svg class="h-5 w-5 flex-shrink-0 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Group Tasks
                    </span>
                </a>
            </div>
        </div>

        <!-- System -->
        @if(auth()->user()->canAccessSystem())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-system') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-system', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>System</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <a href="{{ url('/admin/system/rules') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->is('admin/system/rules') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Rules' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Rules
                    </span>
                </a>
                <a href="{{ url('/admin/settings') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 settings-link {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Settings' : ''"
                   id="sidebar-settings-link">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Settings
                    </span>
                </a>
                <a href="{{ url('/admin/landing-page') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.landing-page.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Landing Page' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Landing Page
                    </span>
                </a>
                <a href="{{ url('/admin/stacks') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.stacks.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Stacks' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Stacks
                    </span>
                </a>
                @if(auth()->user()->canAccessSystem())
                <a href="{{ url('/admin/admin-permissions') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('admin.admin-permissions.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Admin Permissions' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Admin Permissions
                    </span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- User Features (Default Access) - Only visible to employees, not admins -->
        @if(auth()->user()->isEmployee())
        <div class="mb-6" x-data="{
            open: (localStorage.getItem('nav-user-features') || 'true') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('nav-user-features', this.open);
            }
        }">
            <button @click="toggle()"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-300 transition-opacity duration-300"
                    :class="sidebarCollapsed ? 'opacity-0 h-0 overflow-hidden pointer-events-none' : 'opacity-100'">
                <span>User Features</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="space-y-1" x-show="sidebarCollapsed ? true : open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <!-- User Dashboard -->
                <a href="{{ url('/dashboard') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.dashboard') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'User Dashboard' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        User Dashboard
                    </span>
                </a>

                <!-- Quizzes -->
                <a href="{{ url('/quizzes') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.quizzes.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Quizzes' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Quizzes
                    </span>
                </a>

                <!-- DTR (Employee Only) -->
                @if(auth()->user()->role === 'employee')
                <a href="{{ url('/dtr') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.dtr.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'DTR' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        DTR
                    </span>
                </a>
                @endif

                <!-- Leave Requests (Employee & Student) -->
                @if(in_array(auth()->user()->role, ['employee', 'student']))
                <a href="{{ url('/leave-requests') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.leave-requests.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Leave Requests' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Leave Requests
                    </span>
                </a>
                @endif

                <!-- Chat -->
                <a href="{{ url('/user-chat') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user-chat.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Chat' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Chat
                    </span>
                </a>

                <!-- Forum -->
                <a href="{{ url('/forum') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('forum.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Forum' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Forum
                    </span>
                </a>

                <!-- Feedback -->
                <a href="{{ url('/feedback') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.feedback.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Feedback' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Feedback
                    </span>
                </a>

                <!-- Application (Applicant) -->
                @if(auth()->user()->role === 'applicant')
                <a href="{{ url('/hiring-application') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('user.hiring-application.*') ? 'bg-indigo-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''"
                   :title="sidebarCollapsed ? 'Application' : ''">
                    <svg class="h-5 w-5 flex-shrink-0" :class="sidebarCollapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100'">
                        Application
                    </span>
                </a>
                @endif
            </div>
        </div>
        @endif
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
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->getRoleLabel() }}</p>
            </div>
                <form method="POST" action="{{ url('/logout') }}" :class="sidebarCollapsed ? 'ml-0' : ''">
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
</div>
