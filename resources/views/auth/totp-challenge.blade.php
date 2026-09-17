<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg border border-gray-200 p-6 sm:p-8">
        <div class="text-center mb-6">
            <div class="mx-auto mb-3 h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Two-factor authentication</h1>
            <p class="mt-1 text-sm text-gray-600">Enter the code from your authenticator app to continue as admin.</p>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/admin/two-factor-challenge') }}" class="space-y-4">
            @csrf
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Authenticator or recovery code</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" autofocus required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="123456">
            </div>
            <button type="submit"
                    class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                Verify &amp; continue
            </button>
        </form>

        <form method="POST" action="{{ url('/admin/two-factor-challenge/cancel') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-800 underline">
                Sign out instead
            </button>
        </form>
    </div>
</body>
</html>
