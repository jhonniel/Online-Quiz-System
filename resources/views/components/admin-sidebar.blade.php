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

        @if(auth()->user()->hasAnyAdminPermission() && !auth()->user()->isSuperAdmin())
        @include('components.nav.user-features-nav')
        <div class="pt-2 pb-2 mb-2 border-t border-gray-700" :class="sidebarCollapsed ? 'hidden' : ''">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-purple-300">Admin Features</p>
        </div>
        @endif

        @include('components.nav.admin-nav-sections')

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
