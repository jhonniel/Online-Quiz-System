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
        @keyframes totp-shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .totp-shake { animation: totp-shake 0.35s ease-in-out; }
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
                        <div id="totp-server-error" class="mb-5 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-3.5 py-3 text-sm text-red-700" role="alert">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/admin/two-factor-challenge') }}" class="space-y-5" id="totp-challenge-form">
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
                                Your code is checked automatically once all 6 digits are entered. Using a one-time recovery code instead? Type it and press Enter.
                            </p>
                        </div>

                        <div id="totp-status" class="hidden flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold" role="status" aria-live="polite">
                            <svg id="totp-status-spinner" class="hidden h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <svg id="totp-status-check" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span id="totp-status-text"></span>
                        </div>
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
    <script>
        (function () {
            const form = document.getElementById('totp-challenge-form');
            const input = document.getElementById('code');
            const status = document.getElementById('totp-status');
            const statusText = document.getElementById('totp-status-text');
            const spinner = document.getElementById('totp-status-spinner');
            const check = document.getElementById('totp-status-check');
            const serverError = document.getElementById('totp-server-error');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            if (!form || !input || !status) {
                return;
            }

            const statusStyles = {
                checking: 'bg-slate-100 text-slate-700',
                success: 'bg-emerald-50 text-emerald-700',
                error: 'bg-red-50 text-red-700',
            };
            const inputErrorClasses = ['border-red-300', 'focus:border-red-500', 'focus:ring-red-500'];
            let busy = false;

            function setStatus(state, message) {
                status.classList.remove('hidden', ...Object.values(statusStyles).join(' ').split(' '));
                status.classList.add(...statusStyles[state].split(' '));
                spinner.classList.toggle('hidden', state !== 'checking');
                check.classList.toggle('hidden', state !== 'success');
                statusText.textContent = message;
                input.classList.toggle('border-red-300', state === 'error');
                input.classList.toggle('focus:border-red-500', state === 'error');
                input.classList.toggle('focus:ring-red-500', state === 'error');
            }

            function showError(message) {
                busy = false;
                input.readOnly = false;
                input.value = '';
                setStatus('error', message);
                input.classList.remove('totp-shake');
                void input.offsetWidth;
                input.classList.add('totp-shake');
                input.focus();
            }

            async function verify() {
                const code = input.value.replace(/\s+/g, '');
                if (busy || code.length < 6) {
                    return;
                }
                busy = true;
                input.readOnly = true;
                if (serverError) {
                    serverError.remove();
                }
                setStatus('checking', 'Checking code…');

                const body = new FormData(form);
                body.set('code', code);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: body,
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    if (response.ok) {
                        const data = await response.json().catch(() => ({}));
                        setStatus('success', 'Verified! Opening dashboard…');
                        window.location.assign(data.redirect || @json(url('/admin/dashboard')));
                        return;
                    }
                    if (response.status === 419) {
                        showError('Your session expired. Reloading…');
                        setTimeout(() => window.location.reload(), 1200);
                        return;
                    }
                    if (response.status === 429) {
                        showError('Too many attempts. Please wait a minute and try again.');
                        return;
                    }

                    const data = await response.json().catch(() => ({}));
                    const message = (data.errors && data.errors.code && data.errors.code[0])
                        || data.message
                        || 'Invalid authenticator or recovery code.';
                    showError(message);
                } catch (e) {
                    showError('Could not reach the server. Check your connection and try again.');
                }
            }

            // Authenticator codes are exactly 6 digits; recovery codes (XXXX-XXXX) are submitted with Enter.
            input.addEventListener('input', function () {
                if (!status.classList.contains('hidden') && !busy) {
                    status.classList.add('hidden');
                    input.classList.remove(...inputErrorClasses);
                }
                const digits = input.value.replace(/\s+/g, '');
                if (/^\d{6}$/.test(digits)) {
                    input.value = digits;
                    verify();
                }
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                verify();
            });

            if (input.value) {
                input.select();
            }
        })();
    </script>
</body>
</html>
