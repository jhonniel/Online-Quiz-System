<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Say it')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .say-it-feed { max-width: 36rem; }
    </style>
    @stack('styles')
</head>
<body class="bg-[#f0f2f5] min-h-screen antialiased">
    <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-gray-200/80 shadow-sm">
        <div class="say-it-feed mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ url('/Say-it') }}" class="flex items-center gap-2 text-gray-900 no-underline">
                <span class="text-xl font-bold tracking-tight">Say it</span>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">Anonymous</span>
            </a>
        </div>
    </header>

    <main class="say-it-feed mx-auto px-4 py-6 pb-12">
        @if(session('success'))
            <div class="mb-4 py-3 px-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 py-3 px-4 rounded-xl bg-red-50 text-red-800 text-sm font-medium border border-red-200">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 py-3 px-4 rounded-xl bg-red-50 text-red-800 text-sm border border-red-200">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
