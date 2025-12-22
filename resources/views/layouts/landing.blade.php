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
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Custom Styles -->
        <style>
            * {
                font-family: 'Inter', sans-serif;
            }

            .gradient-bg {
                background: linear-gradient(135deg, {{ $settings['primary_color'] }} 0%, {{ $settings['secondary_color'] ?? $settings['primary_color'] }} 100%);
            }

            .gradient-bg-light {
                background: linear-gradient(135deg, rgba({{ hexdec(substr($settings['primary_color'], 1, 2)) }}, {{ hexdec(substr($settings['primary_color'], 3, 2)) }}, {{ hexdec(substr($settings['primary_color'], 5, 2)) }}, 0.05) 0%, rgba({{ hexdec(substr($settings['secondary_color'] ?? $settings['primary_color'], 1, 2)) }}, {{ hexdec(substr($settings['secondary_color'] ?? $settings['primary_color'], 3, 2)) }}, {{ hexdec(substr($settings['secondary_color'] ?? $settings['primary_color'], 5, 2)) }}, 0.05) 100%);
            }

            .text-primary {
                color: {{ $settings['primary_color'] }};
            }

            .bg-primary {
                background-color: {{ $settings['primary_color'] }};
            }

            .border-primary {
                border-color: {{ $settings['primary_color'] }};
            }

            .hover\:bg-primary:hover {
                background-color: {{ $settings['primary_color'] }};
            }

            /* Smooth scrolling */
            html {
                scroll-behavior: smooth;
            }

            /* Navigation styles */
            nav.scrolled {
                background-color: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            /* Animations */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fade-in-up {
                animation: fadeInUp 0.6s ease-out;
            }

            .animate-delay-100 {
                animation-delay: 0.1s;
            }

            .animate-delay-200 {
                animation-delay: 0.2s;
            }

            .animate-delay-300 {
                animation-delay: 0.3s;
            }

            /* Counter Animation */
            .counter-card {
                opacity: 0;
                transform: translateY(30px);
                transition: all 0.6s ease-out;
            }

            .counter-card.animate-in {
                opacity: 1;
                transform: translateY(0);
            }

            /* Hover effects */
            .hover-lift {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .hover-lift:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            /* Glass morphism effect */
            .glass {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-white">
        <!-- Navigation -->
        <nav id="main-nav" class="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('landing.index') }}" class="flex items-center space-x-3 group">
                            @if($settings['system_logo'])
                                <img src="{{ $settings['system_logo_url'] ?? '' }}"
                                     alt="{{ $settings['system_name'] }}"
                                     class="h-10 w-auto object-contain transition-transform group-hover:scale-105">
                            @else
                                <div class="h-12 w-12 bg-primary rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow">
                                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            @endif
                            <span class="text-2xl font-bold text-gray-900">{{ $settings['system_name'] }}</span>
                        </a>
                    </div>

                    <!-- Desktop Navigation Links -->
                    <div class="hidden lg:flex items-center space-x-1">
                        <a href="{{ route('landing.index') }}" class="px-4 py-2 text-gray-700 hover:text-primary font-medium transition-colors rounded-lg hover:bg-gray-50">Home</a>
                        <a href="{{ route('landing.projects') }}" class="px-4 py-2 text-gray-700 hover:text-primary font-medium transition-colors rounded-lg hover:bg-gray-50">Projects</a>
                        <a href="{{ route('landing.about') }}" class="px-4 py-2 text-gray-700 hover:text-primary font-medium transition-colors rounded-lg hover:bg-gray-50">About</a>
                        <a href="{{ route('landing.contact') }}" class="px-4 py-2 text-gray-700 hover:text-primary font-medium transition-colors rounded-lg hover:bg-gray-50">Contact</a>
                        @if(($settings['hiring_application_public_access'] ?? 'disabled') === 'enabled')
                            <a href="{{ url('/' . ltrim($settings['hiring_application_url'] ?? 'hiring/apply', '/')) }}" class="px-4 py-2 text-gray-700 hover:text-primary font-medium transition-colors rounded-lg hover:bg-gray-50">Careers</a>
                        @endif

                        @auth
                            @if(auth()->user()->isAdmin() || (auth()->user()->isEmployee() && auth()->user()->hasAnyAdminPermission()))
                                <a href="{{ route('admin.dashboard') }}" class="ml-4 px-6 py-2 bg-primary text-white rounded-lg font-semibold hover:opacity-90 transition-opacity shadow-md hover:shadow-lg">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('user.dashboard') }}" class="ml-4 px-6 py-2 bg-primary text-white rounded-lg font-semibold hover:opacity-90 transition-opacity shadow-md hover:shadow-lg">
                                    Dashboard
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="ml-4 px-6 py-2 bg-primary text-white rounded-lg font-semibold hover:opacity-90 transition-opacity shadow-md hover:shadow-lg">
                                Sign In
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="lg:hidden flex items-center">
                        <button type="button" class="text-gray-700 hover:text-primary focus:outline-none p-2" id="mobile-menu-button" aria-label="Toggle menu">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="lg:hidden hidden bg-white border-t shadow-lg" id="mobile-menu">
                <div class="px-4 pt-2 pb-4 space-y-1">
                    <a href="{{ route('landing.index') }}" class="block px-4 py-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg font-medium transition-colors">Home</a>
                    <a href="{{ route('landing.projects') }}" class="block px-4 py-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg font-medium transition-colors">Projects</a>
                    <a href="{{ route('landing.about') }}" class="block px-4 py-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg font-medium transition-colors">About</a>
                    <a href="{{ route('landing.contact') }}" class="block px-4 py-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg font-medium transition-colors">Contact</a>
                    @if(($settings['hiring_application_public_access'] ?? 'disabled') === 'enabled')
                        <a href="{{ url('/' . ltrim($settings['hiring_application_url'] ?? 'hiring/apply', '/')) }}" class="block px-4 py-3 text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg font-medium transition-colors">Careers</a>
                    @endif

                    @auth
                        @if(auth()->user()->isAdmin() || (auth()->user()->isEmployee() && auth()->user()->hasAnyAdminPermission()))
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 bg-primary text-white rounded-lg font-semibold text-center mt-4">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="block px-4 py-3 bg-primary text-white rounded-lg font-semibold text-center mt-4">
                                Go to Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="block px-4 py-3 bg-primary text-white rounded-lg font-semibold text-center mt-4">
                            Sign In
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pt-20">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                    <!-- Company Info -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            @if($settings['system_logo'])
                                <img src="{{ $settings['system_logo_url'] ?? '' }}"
                                     alt="{{ $settings['system_name'] }}"
                                     class="h-10 w-auto object-contain">
                            @endif
                            <span class="text-2xl font-bold">{{ $settings['system_name'] }}</span>
                        </div>
                        <p class="text-gray-400 mb-6 max-w-md">{{ $settings['system_description'] }}</p>
                        <div class="flex space-x-4">
                            <!-- Social media icons can be added here -->
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="{{ route('landing.index') }}" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                            <li><a href="{{ route('landing.projects') }}" class="text-gray-400 hover:text-white transition-colors">Projects</a></li>
                            <li><a href="{{ route('landing.about') }}" class="text-gray-400 hover:text-white transition-colors">About</a></li>
                            <li><a href="{{ route('landing.contact') }}" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                        </ul>
                    </div>

                    <!-- Access -->
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

                <div class="border-t border-gray-800 pt-8">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} {{ $settings['system_name'] }}. All rights reserved.</p>
                        <div class="flex space-x-6 mt-4 md:mt-0">
                            <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a>
                            <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Scripts -->
        <script>
            // Mobile menu toggle
            document.addEventListener('DOMContentLoaded', function() {
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');
                const nav = document.getElementById('main-nav');

                if (mobileMenuButton && mobileMenu) {
                    mobileMenuButton.addEventListener('click', function() {
                        mobileMenu.classList.toggle('hidden');
                    });
                }

                // Navbar scroll effect
                if (nav) {
                    window.addEventListener('scroll', function() {
                        if (window.scrollY > 50) {
                            nav.classList.add('scrolled');
                        } else {
                            nav.classList.remove('scrolled');
                        }
                    });
                }
            });
        </script>

        <!-- Seasonal Effects -->
        @include('components.seasonal-effects')

        @yield('scripts')
    </body>
</html>
