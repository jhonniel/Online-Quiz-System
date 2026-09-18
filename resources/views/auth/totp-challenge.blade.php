@php
    $settings = \App\Models\Setting::getAll();
    $settings = array_merge([
        'system_name' => 'System',
        'system_description' => 'Online Management System',
        'primary_color' => '#4F46E5',
        'secondary_color' => '#6B7280',
    ], is_array($settings) ? $settings : []);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Authentication — {{ $settings['system_name'] }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: {{ $settings['primary_color'] }};
            --secondary: {{ $settings['secondary_color'] }};
        }
        body { font-family: Figtree, ui-sans-serif, system-ui, sans-serif; }
        .bg-brand { background-color: var(--primary); }
        .text-brand { color: var(--primary); }
        .ring-brand:focus {
            --tw-ring-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-brand {
            background-color: var(--primary);
        }
        .btn-brand:hover {
            filter: brightness(0.92);
        }
        .otp-input {
            letter-spacing: 0.35em;
            font-variant-numeric: tabular-nums;
        }
        .otp-input::placeholder {
            letter-spacing: 0.2em;
            color: #9ca3af;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="relative min-h-screen flex flex-col justify-center px-4 py-12 sm:px-6 lg:px-8 overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-100 via-white to-slate-100"></div>
            <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full opacity-[0.12] blur-3xl bg-brand"></div>
            <div class="absolute -bottom-28 -left-20 h-80 w-80 rounded-full opacity-[0.08] blur-3xl bg-brand"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-md">
            <div class="mb-8 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    {{ $settings['system_name'] }}
                </p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                    Verify your identity
                </h1>
                <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                    Enter the 6-digit code from your authenticator app to continue to the admin panel.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-xl shadow-slate-200/60">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Two-factor authentication</p>
                            <p class="mt-0.5 text-xs text-slate-500">Admin access requires an additional verification step.</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6 sm:px-8 sm:py-7">
                    @if($errors->any())
                        <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-3.5 py-3 text-sm text-red-700" role="alert">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/admin/two-factor-challenge') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label for="code" class="block text-sm font-medium text-slate-700 mb-2">
                                Authentication code
                            </label>
                            <input
                                id="code"
                                name="code"
                                type="text"
                                inputmode="text"
                                autocomplete="one-time-code"
                                autofocus
                                required
                                maxlength="32"
                                spellcheck="false"
                                autocapitalize="characters"
                                class="otp-input block w-full rounded-xl border {{ $errors->any() ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-slate-300 ring-brand' }} bg-white px-4 py-3.5 text-center text-lg font-semibold text-slate-900 shadow-sm placeholder:font-medium placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-0 transition"
                                placeholder="000000"
                                value="{{ old('code') }}"
                            >
                            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                You can also enter a one-time recovery code if you no longer have access to your authenticator app.
                            </p>
                        </div>

                        <button type="submit"
                                class="btn-brand inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 ring-brand">
                            <span>Verify &amp; continue</span>
                            <svg class="h-4 w-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="border-t border-slate-100 px-6 py-4 sm:px-8">
                    <form method="POST" action="{{ url('/admin/two-factor-challenge/cancel') }}" class="text-center">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
                            Sign out instead
                        </button>
                    </form>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">
                Protected admin session · Codes expire every 30 seconds
            </p>
        </div>
    </div>
</body>
</html>
