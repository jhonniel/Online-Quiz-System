@extends('layouts.landing')

@section('title', 'Activate Teacher Account')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-gray-200 p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-gray-900">Activate Teacher Account</h1>
        <p class="text-sm text-gray-600 mt-1">Complete your teacher account setup using this invite link.</p>

        @if($errors->has('invite'))
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first('invite') }}
            </div>
        @endif

        <div class="mt-4 rounded-lg border border-indigo-100 bg-indigo-50 p-3 text-xs text-indigo-800">
            <div><strong>School:</strong> {{ optional($invite->university)->name ?? 'Not specified' }}</div>
            <div><strong>Department:</strong> {{ optional($invite->department)->name ?? 'Not specified' }}</div>
            @if($invite->expires_at)
                <div><strong>Expires:</strong> {{ $invite->expires_at->format('M d, Y h:i A') }}</div>
            @endif
        </div>

        <form method="POST" action="{{ url('/teacher/invite/'.$token) }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" required aria-required="true" value="{{ old('name') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input id="email" name="email" type="email" required aria-required="true" value="{{ old('email') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number <span class="text-red-500">*</span></label>
                <input id="contact_number" name="contact_number" type="tel" required aria-required="true" value="{{ old('contact_number') }}" autocomplete="tel" placeholder="Phone number" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                @error('contact_number')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                Activate Account
            </button>
        </form>
    </div>
</div>
@endsection

