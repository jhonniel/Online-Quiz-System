@extends('layouts.landing')

@section('title', 'Invalid Invite Link')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-200 p-6 sm:p-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900">Invite Link Not Available</h1>
        <p class="text-sm text-gray-600 mt-2">This teacher invite link is invalid, already used, expired, or disabled.</p>
        <a href="{{ url('/login') }}" class="mt-6 inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
            Go to Login
        </a>
    </div>
</div>
@endsection

