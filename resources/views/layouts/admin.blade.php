<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $settings['system_name'] ?? 'Admin')</title>

    <!-- Favicon -->
    @if(isset($settings['system_icon']) && $settings['system_icon'] && isset($settings['system_icon_url']) && $settings['system_icon_url'])
        @php
            $iconPath = $settings['system_icon'] ?? '';
            $extension = strtolower(pathinfo($iconPath, PATHINFO_EXTENSION));
            $mimeType = match($extension) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
                'ico' => 'image/x-icon',
                default => 'image/x-icon'
            };
        @endphp
        <link rel="icon" type="{{ $mimeType }}" href="{{ $settings['system_icon_url'] }}">
        <link rel="shortcut icon" type="{{ $mimeType }}" href="{{ $settings['system_icon_url'] }}">
        <link rel="apple-touch-icon" href="{{ $settings['system_icon_url'] }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js?v={{ time() }}"></script>

    <!-- Custom Scrollbar Styles -->
    <style>
        /* Hide Alpine components until they are initialized (prevents modal flash) */
        [x-cloak] { display: none !important; }

        /* CRITICAL FIX: Ensure main content has proper left padding to not appear behind sidebar */
        html body .main-content-wrapper {
            margin-left: 16rem !important;
            width: calc(100% - 16rem) !important;
        }
        
        html body .main-content-wrapper > main {
            padding-left: 2rem !important;
            padding-right: 2rem !important;
        }
        
        @media (max-width: 1023px) {
            html body .main-content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
            html body .main-content-wrapper > main {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
        }
        
        @media (min-width: 640px) and (max-width: 1023px) {
            html body .main-content-wrapper > main {
                padding-left: 1.5rem !important;
                padding-right: 1.5rem !important;
            }
        }
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sidebar scrollbar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: #374151;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #6b7280;
            border-radius: 3px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Main content spacing - force it to work */
        body {
            position: relative;
        }
        
        body .main-content-wrapper {
            position: relative !important;
            margin-left: 16rem !important;
            width: calc(100% - 16rem) !important;
            min-width: 0 !important;
            z-index: 1 !important;
        }
        
        @media (min-width: 1024px) {
            body .main-content-wrapper {
                margin-left: 16rem !important;
                width: calc(100% - 16rem) !important;
            }
        }
        
        @media (max-width: 1023px) {
            body .main-content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }
        
        /* Ensure content inside has proper spacing */
        .main-content-wrapper > main {
            margin-left: 0 !important;
            position: relative !important;
        }
        
        /* Ensure sidebar stays on top */
        .fixed.inset-y-0.left-0.z-50 {
            z-index: 50 !important;
        }
        
        /* Force main content to start after sidebar on desktop */
        @media (min-width: 1024px) {
            body .main-content-wrapper {
                margin-left: 16rem !important;
                width: calc(100% - 16rem) !important;
                padding-left: 0 !important;
                left: 0 !important;
            }
            
            /* Ensure sidebar is visible on desktop */
            .fixed.inset-y-0.left-0.z-50 {
                transform: translateX(0) !important;
            }
            
            /* Force padding on main content */
            .main-content-wrapper > main {
                padding-left: 2rem !important;
                padding-right: 2rem !important;
            }
        }
        
        @media (min-width: 640px) and (max-width: 1023px) {
            .main-content-wrapper > main {
                padding-left: 1.5rem !important;
                padding-right: 1.5rem !important;
            }
        }
        
        @media (max-width: 639px) {
            .main-content-wrapper > main {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
        }
        
        /* Ensure all content inside main has proper positioning and padding */
        body .main-content-wrapper main {
            position: relative !important;
            z-index: 1 !important;
            margin-left: 0 !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
            box-sizing: border-box !important;
        }
        
        @media (min-width: 640px) {
            body .main-content-wrapper main {
                padding-left: 1.5rem !important;
                padding-right: 1.5rem !important;
            }
        }
        
        @media (min-width: 1024px) {
            body .main-content-wrapper main {
                padding-left: 2rem !important;
                padding-right: 2rem !important;
            }
        }
        
        /* Force analytics page content to respect margin */
        .main-content-wrapper .space-y-4,
        .main-content-wrapper .space-y-6 {
            position: relative !important;
            margin-left: 0 !important;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100" 
      x-data="{ sidebarCollapsed: false }"
      x-init="
          $store.sidebar = { collapsed: sidebarCollapsed }; 
          $watch('sidebarCollapsed', value => {
              $store.sidebar = { collapsed: value };
              window.dispatchEvent(new CustomEvent('sidebar-collapse-changed', { detail: value }));
          });
      ">
    <!-- Admin Sidebar -->
    @include('components.admin-sidebar')

    <!-- Main Content Area -->
    <div class="main-content-wrapper flex-1 flex flex-col overflow-hidden"
         style="position: relative !important; margin-left: 16rem !important; width: calc(100% - 16rem) !important; min-width: 0 !important; z-index: 1 !important; left: 0 !important;"
         x-init="
             const updateMargin = () => {
                 if (window.innerWidth >= 1024) {
                     const margin = sidebarCollapsed ? '4rem' : '16rem';
                     $el.style.setProperty('margin-left', margin, 'important');
                     $el.style.setProperty('width', 'calc(100% - ' + margin + ')', 'important');
                     $el.style.setProperty('position', 'relative', 'important');
                     $el.style.setProperty('z-index', '1', 'important');
                     $el.style.setProperty('left', '0', 'important');
                 } else {
                     $el.style.setProperty('margin-left', '0', 'important');
                     $el.style.setProperty('width', '100%', 'important');
                     $el.style.setProperty('position', 'relative', 'important');
                 }
             };
             updateMargin();
             $watch('sidebarCollapsed', updateMargin);
             window.addEventListener('resize', updateMargin);
         ">
            <!-- Top Navigation Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 flex-shrink-0 sticky top-0 z-30">
                <div class="flex items-center justify-between h-14 px-3 sm:px-4 lg:px-6">
                    <div class="flex items-center space-x-3">
                        <!-- Mobile menu button -->
                        <button @click="$dispatch('sidebar-toggle')" class="lg:hidden text-gray-500 hover:text-gray-600 focus:outline-none focus:text-gray-600 p-1">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Desktop sidebar toggle button -->
                        <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors duration-200">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <!-- Page Title -->
                        <div class="flex-1 min-w-0">
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-900 truncate">@yield('page-title', 'Admin Dashboard')</h1>
                        </div>
                    </div>

                    <!-- Right side actions -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <!-- Breadcrumb -->
                        <nav class="hidden lg:flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-1">
                                <li>
                                    <a href="{{ url('/admin/dashboard') }}" class="text-gray-500 hover:text-gray-700 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                        </svg>
                                    </a>
                                </li>
                                @yield('breadcrumb')
                            </ol>
                        </nav>

                        <!-- Quick Send Notification Button -->
                        <a href="{{ url('/admin/notifications/create') }}"
                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                           title="Send Notification">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <span class="hidden sm:inline">Send</span>
                        </a>

                        <!-- Notification Bell -->
                        <div class="relative" x-data="notificationBell()">
                            <button @click="toggleNotifications()" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span x-show="unreadCount > 0" x-text="unreadCount"
                                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
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
                                    <a href="{{ url('/admin/notifications') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View all notifications</a>
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 p-1">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-xs sm:text-sm font-medium">
                                        {{ auth()->user()->name[0] }}
                                    </span>
                                </div>
                                <span class="hidden sm:block text-gray-700 font-medium text-sm">{{ auth()->user()->name }}</span>
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <a href="{{ url('/admin/dashboard') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    </svg>
                                    Dashboard
                                </a>
                                <a href="{{ url('/admin/settings') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Settings
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
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50" style="padding-left: 2rem !important; padding-right: 2rem !important; min-width: 0 !important; box-sizing: border-box !important; display: block !important; position: relative !important;">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-3 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-3 bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-3 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-2 rounded text-sm">
                        {{ session('warning') }}
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-3 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-2 rounded text-sm">
                        {{ session('info') }}
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
    @stack('scripts')

    <!-- Notification Bell Component -->
    <script>
        function notificationBell() {
            return {
                showNotifications: false,
                notifications: [],
                unreadCount: 0,
                isLoading: false,
                
                init() {
                    this.fetchNotifications();
                    // Poll for new notifications every 30 seconds
                    setInterval(() => this.fetchNotifications(), 30000);
                },
                
                toggleNotifications() {
                    this.showNotifications = !this.showNotifications;
                    if (this.showNotifications) {
                        this.fetchNotifications();
                    }
                },
                
                fetchNotifications() {
                    fetch('{{ url('/admin/notifications/unread') }}', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                throw new Error('Response is not JSON');
                            }
                            return response.json();
                        })
                        .then(data => {
                            this.notifications = data.notifications || [];
                            this.unreadCount = data.unread_count || 0;
                        })
                        .catch(error => {
                            console.error('Error fetching notifications:', error);
                            this.notifications = [];
                            this.unreadCount = 0;
                        });
                },
                
                markAsRead(notificationId) {
                    fetch(`/admin/notifications/${notificationId}/mark-read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(() => {
                        this.fetchNotifications();
                    })
                    .catch(error => console.error('Error marking notification as read:', error));
                },
                
                markAllAsRead() {
                    fetch('{{ url('/admin/notifications/mark-all-read') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(() => {
                        this.fetchNotifications();
                    })
                    .catch(error => console.error('Error marking all notifications as read:', error));
                }
            }
        }
    </script>
    @include('components.seasonal-effects')
</body>
</html>
