@php
    $settings = \App\Models\Setting::getAll();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', $settings['system_name'] . ' - Authentication')</title>
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
            .border-primary {
                border-color: {{ $settings['primary_color'] }};
            }

            /* Floating Space Animations */
            @keyframes float-slow {
                0%, 100% {
                    transform: translateY(0px) translateX(0px) rotate(0deg);
                }
                25% {
                    transform: translateY(-20px) translateX(10px) rotate(1deg);
                }
                50% {
                    transform: translateY(-10px) translateX(-5px) rotate(-1deg);
                }
                75% {
                    transform: translateY(-15px) translateX(8px) rotate(0.5deg);
                }
            }

            @keyframes float-medium {
                0%, 100% {
                    transform: translateY(0px) translateX(0px) rotate(0deg);
                }
                33% {
                    transform: translateY(-15px) translateX(-8px) rotate(-0.5deg);
                }
                66% {
                    transform: translateY(-25px) translateX(12px) rotate(1deg);
                }
            }

            @keyframes float-fast {
                0%, 100% {
                    transform: translateY(0px) translateX(0px) rotate(0deg);
                }
                20% {
                    transform: translateY(-12px) translateX(6px) rotate(0.8deg);
                }
                40% {
                    transform: translateY(-8px) translateX(-4px) rotate(-0.6deg);
                }
                60% {
                    transform: translateY(-18px) translateX(10px) rotate(1.2deg);
                }
                80% {
                    transform: translateY(-6px) translateX(-8px) rotate(-0.4deg);
                }
            }

            @keyframes float-gentle {
                0%, 100% {
                    transform: translateY(0px) rotate(0deg);
                }
                50% {
                    transform: translateY(-8px) rotate(0.3deg);
                }
            }

            @keyframes space-drift {
                0%, 100% {
                    transform: translateX(0px) translateY(0px) rotate(0deg);
                }
                25% {
                    transform: translateX(15px) translateY(-10px) rotate(2deg);
                }
                50% {
                    transform: translateX(-10px) translateY(-20px) rotate(-1deg);
                }
                75% {
                    transform: translateX(20px) translateY(-5px) rotate(1.5deg);
                }
            }

            @keyframes twinkle {
                0%, 100% {
                    opacity: 0.3;
                    transform: scale(1);
                }
                50% {
                    opacity: 1;
                    transform: scale(1.2);
                }
            }

            /* Animation Classes */
            .animate-float-slow {
                animation: float-slow 8s ease-in-out infinite;
            }

            .animate-float-medium {
                animation: float-medium 6s ease-in-out infinite;
            }

            .animate-float-fast {
                animation: float-fast 4s ease-in-out infinite;
            }

            .animate-float-gentle {
                animation: float-gentle 3s ease-in-out infinite;
            }

            .animate-space-drift {
                animation: space-drift 10s ease-in-out infinite;
            }

            .animate-twinkle {
                animation: twinkle 2s ease-in-out infinite;
            }

            /* Staggered animations for multiple elements */
            .animate-float-slow:nth-child(1) { animation-delay: 0s; }
            .animate-float-slow:nth-child(2) { animation-delay: 1s; }
            .animate-float-slow:nth-child(3) { animation-delay: 2s; }

            .animate-float-medium:nth-child(1) { animation-delay: 0.5s; }
            .animate-float-medium:nth-child(2) { animation-delay: 1.5s; }
            .animate-float-medium:nth-child(3) { animation-delay: 2.5s; }

            .animate-float-fast:nth-child(1) { animation-delay: 0.2s; }
            .animate-float-fast:nth-child(2) { animation-delay: 0.8s; }
            .animate-float-fast:nth-child(3) { animation-delay: 1.4s; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        {{ $slot }}
        @include('components.seasonal-effects')
    </body>
</html>
