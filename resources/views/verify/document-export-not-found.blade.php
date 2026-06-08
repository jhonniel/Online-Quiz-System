@extends('layouts.landing')

@section('title', 'Document Not Verified - ' . ($settings['system_name'] ?? 'System'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
    <div class="max-w-md w-full text-center">
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-200">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Document Not Verified</h1>
            <p class="text-gray-600 mb-6">This verification code is invalid or does not exist in our system. The document may not be authentic.</p>
            <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                Go to Home
            </a>
        </div>
    </div>
</div>
@endsection
