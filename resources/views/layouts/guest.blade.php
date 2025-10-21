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
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        {{ $slot }}
    </body>
</html>
