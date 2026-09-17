@extends('layouts.admin')

@section('page-title', 'Security')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 py-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Security</h1>
        <p class="mt-1 text-sm text-gray-600">
            Authenticator app two-factor authentication (TOTP) for your administrator account only.
        </p>
    </div>

    @if(!empty($plainRecoveryCodes))
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-5">
            <h2 class="text-base font-semibold text-amber-900">Save your recovery codes</h2>
            <p class="mt-1 text-sm text-amber-800">
                These codes are shown once. Each code can be used one time if you lose access to your authenticator app.
            </p>
            <ul class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 font-mono text-sm text-amber-950">
                @foreach($plainRecoveryCodes as $code)
                    <li class="rounded-lg bg-white/80 border border-amber-200 px-3 py-2">{{ $code }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
            <h2 class="text-base font-semibold text-gray-900">Google Authenticator (TOTP)</h2>
            <p class="mt-0.5 text-sm text-gray-500">Works with Google Authenticator, Authy, 1Password, and other TOTP apps.</p>
        </div>
        <div class="p-5 space-y-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">Status</p>
                    @if($enabled)
                        <p class="text-sm text-emerald-700 mt-0.5">Enabled — required at every admin login</p>
                    @elseif($pending)
                        <p class="text-sm text-amber-700 mt-0.5">Setup started — confirm with a code to finish</p>
                    @else
                        <p class="text-sm text-gray-500 mt-0.5">Disabled</p>
                    @endif
                </div>
                @if($enabled)
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Active</span>
                @elseif($pending)
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Pending</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Off</span>
                @endif
            </div>

            @if(! $enabled && ! $pending)
                <form method="POST" action="{{ url('/admin/security/totp/start') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                        Enable authenticator 2FA
                    </button>
                </form>
            @endif

            @if($pending && $qrDataUri && $manualSecret)
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 space-y-4">
                    <div class="flex flex-col sm:flex-row gap-5 items-start">
                        <div class="bg-white p-3 rounded-lg border border-indigo-100 shrink-0">
                            <img src="{{ $qrDataUri }}" alt="Authenticator QR code" class="w-48 h-48">
                        </div>
                        <div class="space-y-3 min-w-0">
                            <p class="text-sm text-gray-700">
                                1. Open Google Authenticator (or another TOTP app)<br>
                                2. Scan this QR code<br>
                                3. Enter the 6-digit code below to confirm
                            </p>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Manual entry key</p>
                                <p class="mt-1 font-mono text-sm break-all bg-white border border-gray-200 rounded-lg px-3 py-2">{{ $manualSecret }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ url('/admin/security/totp/confirm') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="confirm_code" class="block text-sm font-medium text-gray-700 mb-1">Authenticator code</label>
                            <input type="text" name="code" id="confirm_code" inputmode="numeric" autocomplete="one-time-code"
                                   class="w-full max-w-xs rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('code') border-red-500 @enderror"
                                   placeholder="123456" required>
                            @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                                Confirm &amp; enable
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ url('/admin/security/totp/cancel') }}" onsubmit="return confirm('Cancel authenticator setup?');">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-red-700 underline">Cancel setup</button>
                    </form>
                </div>
            @endif

            @if($enabled)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <form method="POST" action="{{ url('/admin/security/totp/recovery-codes') }}" class="rounded-xl border border-gray-200 p-4 space-y-3">
                        @csrf
                        <h3 class="text-sm font-semibold text-gray-900">Regenerate recovery codes</h3>
                        <p class="text-xs text-gray-500">Invalidates previous recovery codes.</p>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Account password</label>
                            <input type="password" name="password" autocomplete="current-password" required
                                   class="w-full rounded-lg border-gray-300 text-sm @error('password') border-red-500 @enderror">
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Authenticator code</label>
                            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                                   class="w-full rounded-lg border-gray-300 text-sm @error('code') border-red-500 @enderror">
                            @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-800 text-white text-sm hover:bg-gray-900">
                            Generate new codes
                        </button>
                    </form>

                    <form method="POST" action="{{ url('/admin/security/totp/disable') }}" class="rounded-xl border border-red-200 bg-red-50/40 p-4 space-y-3"
                          onsubmit="return confirm('Disable authenticator 2FA for your admin account?');">
                        @csrf
                        <h3 class="text-sm font-semibold text-red-900">Disable authenticator 2FA</h3>
                        <p class="text-xs text-red-700">Requires your password and a current code (or unused recovery code).</p>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Account password</label>
                            <input type="password" name="password" autocomplete="current-password" required
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Authenticator or recovery code</label>
                            <input type="text" name="code" autocomplete="one-time-code" required
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700">
                            Disable 2FA
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
