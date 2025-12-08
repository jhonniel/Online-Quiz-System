<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', $settings['system_name'] . ' - Online Quiz Management System')</title>
        <meta name="description" content="@yield('description', $settings['system_description'])">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Custom Styles -->
        <style>
            .gradient-bg {
                background: linear-gradient(135deg, {{ $settings['primary_color'] }} 0%, {{ $settings['secondary_color'] }} 100%);
            }
            .text-primary {
                color: {{ $settings['primary_color'] }};
            }
            .bg-primary {
                background-color: {{ $settings['primary_color'] }};
            }

            /* Hide scrollbar for horizontal scroll */
            .scrollbar-hide {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
            .scrollbar-hide::-webkit-scrollbar {
                display: none;
            }

            /* Smooth scrolling */
            .overflow-x-auto {
                scroll-behavior: smooth;
            }
            .border-primary {
                border-color: {{ $settings['primary_color'] }};
            }

            /* Counter Animation Styles */
            .counter-card {
                opacity: 0;
                transform: translateY(30px);
                transition: all 0.6s ease-out;
            }

            .counter-card.animate-in {
                opacity: 1;
                transform: translateY(0);
            }

            .counter-number {
                transition: all 0.3s ease-out;
            }

            .counter-number.animating {
                transform: scale(1.1);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <a href="{{ route('landing.index') }}" class="flex items-center space-x-2">
                            @if($settings['system_logo'])
                                <img src="{{ Storage::url($settings['system_logo']) }}"
                                     alt="{{ $settings['system_name'] }}"
                                     class="h-10 w-auto object-contain">
                            @else
                                <div class="h-10 w-10 bg-primary rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            @endif
                            <span class="text-xl font-bold text-gray-900">{{ $settings['system_name'] }}</span>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="{{ route('landing.index') }}" class="text-gray-700 hover:text-primary transition-colors">Home</a>
                        <a href="{{ route('landing.features') }}" class="text-gray-700 hover:text-primary transition-colors">Features</a>
                        <a href="{{ route('landing.about') }}" class="text-gray-700 hover:text-primary transition-colors">About</a>
                        <a href="{{ route('landing.contact') }}" class="text-gray-700 hover:text-primary transition-colors">Contact</a>
                        @if(($settings['hiring_application_public_access'] ?? 'disabled') === 'enabled')
                            <a href="{{ url('/' . ltrim($settings['hiring_application_url'] ?? 'hiring/apply', '/')) }}" class="text-gray-700 hover:text-primary transition-colors font-medium">Careers</a>
                        @endif

                        @auth
                            @if(auth()->user()->isAdmin() || (auth()->user()->isEmployee() && auth()->user()->hasAnyAdminPermission()))
                                <a href="{{ route('admin.dashboard') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                                    Go To Dashboard
                                </a>
                            @else
                                <a href="{{ route('user.dashboard') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                                    Go To Dashboard
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                                Login
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button type="button" class="text-gray-700 hover:text-primary focus:outline-none focus:text-primary" id="mobile-menu-button">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t">
                    <a href="{{ route('landing.index') }}" class="block px-3 py-2 text-gray-700 hover:text-primary">Home</a>
                    <a href="{{ route('landing.features') }}" class="block px-3 py-2 text-gray-700 hover:text-primary">Features</a>
                    <a href="{{ route('landing.about') }}" class="block px-3 py-2 text-gray-700 hover:text-primary">About</a>
                    <a href="{{ route('landing.contact') }}" class="block px-3 py-2 text-gray-700 hover:text-primary">Contact</a>
                    @if(($settings['hiring_application_public_access'] ?? 'disabled') === 'enabled')
                        <a href="{{ url('/' . ltrim($settings['hiring_application_url'] ?? 'hiring/apply', '/')) }}" class="block px-3 py-2 text-gray-700 hover:text-primary font-medium">Careers</a>
                    @endif

                    @auth
                        @if(auth()->user()->isAdmin() || (auth()->user()->isEmployee() && auth()->user()->hasAnyAdminPermission()))
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 bg-primary text-white rounded-lg mx-3 text-center">
                                Go To Dashboard
                            </a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="block px-3 py-2 bg-primary text-white rounded-lg mx-3 text-center">
                                Go To Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block px-3 py-2 bg-primary text-white rounded-lg mx-3 text-center">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center space-x-2 mb-4">
                            @if($settings['system_logo'])
                                <img src="{{ Storage::url($settings['system_logo']) }}"
                                     alt="{{ $settings['system_name'] }}"
                                     class="h-8 w-auto object-contain">
                            @endif
                            <span class="text-xl font-bold">{{ $settings['system_name'] }}</span>
                        </div>
                        <p class="text-gray-400 mb-4">{{ $settings['system_description'] }}</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="{{ route('landing.index') }}" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                            <li><a href="{{ route('landing.features') }}" class="text-gray-400 hover:text-white transition-colors">Features</a></li>
                            <li><a href="{{ route('landing.about') }}" class="text-gray-400 hover:text-white transition-colors">About</a></li>
                            <li><a href="{{ route('landing.contact') }}" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Access</h3>
                        <ul class="space-y-2">
                            @auth
                                @if(auth()->user()->isAdmin() || (auth()->user()->isEmployee() && auth()->user()->hasAnyAdminPermission()))
                                    <li><a href="{{ route('admin.dashboard') }}" class="text-gray-400 hover:text-white transition-colors">Admin Dashboard</a></li>
                                @else
                                    <li><a href="{{ route('user.dashboard') }}" class="text-gray-400 hover:text-white transition-colors">User Dashboard</a></li>
                                @endif
                            @else
                                <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-white transition-colors">Login</a></li>
                            @endauth
                        </ul>
                    </div>
                </div>

                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; {{ date('Y') }} {{ $settings['system_name'] }}. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <!-- Mobile menu script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');

                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            });
        </script>

        <!-- Seasonal Effects -->
        @include('components.seasonal-effects')

        @yield('scripts')
    </body>
</html>
