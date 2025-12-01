@extends('layouts.landing')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Success Message -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div class="mb-6">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Congratulations!</h1>
            <p class="text-xl text-gray-600 mb-2">Your application has been accepted, {{ $application->first_name }}!</p>
            <p class="text-gray-500 mb-8">We're excited to move forward with your application. Please create an account to proceed to the interview stage.</p>

            <!-- Create Account Section -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Next Steps</h2>
                <ol class="list-decimal list-inside space-y-2 text-left text-gray-700 mb-6">
                    <li>Create your account using the button below</li>
                    <li>Complete your profile</li>
                    <li>Take the required quiz assessment</li>
                    <li>Schedule your interview</li>
                </ol>
                <a href="{{ route('register', ['token' => $application->acceptance_token, 'email' => $application->email]) }}" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create Account & Continue
                </a>
            </div>

            <!-- Application Details -->
            <div class="border-t border-gray-200 pt-6 text-left">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Application Details</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong>Name:</strong> {{ $application->full_name }}</p>
                    <p><strong>Email:</strong> {{ $application->email }}</p>
                    @if($application->position_applied)
                        <p><strong>Position:</strong> {{ $application->position_applied }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

