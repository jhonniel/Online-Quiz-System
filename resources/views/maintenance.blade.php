<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Maintenance - {{ $settings['system_name'] ?? 'Quiz System' }}</title>

    <!-- Favicon -->
    @if(isset($settings['system_icon']) && $settings['system_icon'])
        <link rel="icon" type="image/x-icon" href="{{ Storage::url($settings['system_icon']) }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ Storage::url($settings['system_icon']) }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }

        .maintenance-animation {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 min-h-screen flex items-center justify-center">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <!-- Logo -->
            <div class="mb-8">
                @if(isset($settings['system_logo']) && $settings['system_logo'])
                    <img src="{{ Storage::url($settings['system_logo']) }}"
                         alt="{{ $settings['system_name'] ?? 'Quiz System' }}"
                         class="h-20 w-auto mx-auto maintenance-animation">
                @else
                    <div class="h-20 w-20 mx-auto bg-white rounded-full flex items-center justify-center maintenance-animation">
                        <svg class="h-10 w-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Maintenance Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white bg-opacity-20 rounded-full pulse-animation">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                System Maintenance
            </h1>

            <!-- Subtitle -->
            <h2 class="text-xl md:text-2xl text-indigo-200 mb-8">
                {{ $settings['system_name'] ?? 'Quiz System' }} is temporarily unavailable
            </h2>

            <!-- Message -->
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-2xl p-8 mb-8 max-w-2xl mx-auto">
                <p class="text-lg text-white leading-relaxed">
                    {{ $settings['maintenance_message'] ?? 'We are currently performing scheduled maintenance to improve your experience. Please check back later.' }}
                </p>
            </div>

            <!-- Status -->
            <div class="flex items-center justify-center space-x-2 text-indigo-200 mb-8">
                <div class="w-3 h-3 bg-yellow-400 rounded-full pulse-animation"></div>
                <span class="text-sm font-medium">Maintenance in Progress</span>
            </div>

            <!-- Contact Info -->
            <div class="text-center text-indigo-200">
                <p class="text-sm">
                    If you have any urgent questions, please contact our support team.
                </p>
                <p class="text-xs mt-2 opacity-75">
                    We apologize for any inconvenience and appreciate your patience.
                </p>
            </div>

            <!-- Refresh Button -->
            <div class="mt-8">
                <button onclick="window.location.reload()"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0020 13a8 8 0 01-9.144 6.772L9 21.5V17.5m0 0l-1.5 1.5M9 17.5l1.5 1.5M10.5 7.5h9m-9 3h9m-9 3h9m-9 3h9"></path>
                    </svg>
                    Refresh Page
                </button>
            </div>
        </div>
    </div>

    <!-- Auto-refresh every 30 seconds -->
    <script>
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
