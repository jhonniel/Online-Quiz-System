<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', $settings['system_name'] )</title>
        <meta name="description" content="@yield('description', $settings['system_description'])">

        <!-- Favicon -->
        @if(isset($settings['system_icon']) && $settings['system_icon'] && isset($settings['system_icon_url']) && $settings['system_icon_url'])
            @php
                $iconUrl = $settings['system_icon_url'];
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
            <link rel="icon" type="{{ $mimeType }}" href="{{ $iconUrl }}">
            <link rel="shortcut icon" type="{{ $mimeType }}" href="{{ $iconUrl }}">
            <link rel="apple-touch-icon" href="{{ $iconUrl }}">
        @endif

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
                            @php
                                $socialFacebook = $settings['social_facebook'] ?? '';
                                $socialTwitter = $settings['social_twitter'] ?? '';
                                $socialLinkedIn = $settings['social_linkedin'] ?? '';
                                $socialInstagram = $settings['social_instagram'] ?? '';
                                $socialYouTube = $settings['social_youtube'] ?? '';
                            @endphp
                            @if($socialFacebook)
                                <a href="{{ $socialFacebook }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors" aria-label="Facebook">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialTwitter)
                                <a href="{{ $socialTwitter }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors" aria-label="Twitter">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialLinkedIn)
                                <a href="{{ $socialLinkedIn }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors" aria-label="LinkedIn">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialInstagram)
                                <a href="{{ $socialInstagram }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors" aria-label="Instagram">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialYouTube)
                                <a href="{{ $socialYouTube }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition-colors" aria-label="YouTube">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                </a>
                            @endif
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
                            @php
                                $privacyPolicyPdfPath = $settings['privacy_policy_pdf'] ?? null;
                            @endphp
                            @if($privacyPolicyPdfPath)
                                <a href="{{ route('landing.privacy-policy') }}" target="_blank" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a>
                            @else
                                <a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a>
                            @endif
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
