@extends('layouts.user')

@section('content')
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Enter Quiz Code
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Enter the quiz code to start taking a quiz
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form class="space-y-6" action="{{ route('user.quizzes.validate-code') }}" method="POST">
                @csrf

                <div>
                    <label for="quiz_code" class="block text-sm font-medium text-gray-700">
                        Quiz Code
                    </label>
                    <div class="mt-1">
                        <input id="quiz_code" name="quiz_code" type="text" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm uppercase"
                               placeholder="Enter quiz code"
                               maxlength="20"
                               value="{{ old('quiz_code') }}">
                    </div>
                    @if(isset($errors) && $errors->has('quiz_code'))
                        <p class="mt-2 text-sm text-red-600">{{ $errors->first('quiz_code') }}</p>
                    @endif
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Start Quiz
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300" />
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or</span>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="button"
                            id="back-to-dashboard-btn"
                            onclick="goToDashboard()"
                            class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global function for dashboard navigation
function goToDashboard() {
    console.log('Navigating to dashboard...');
    try {
        // Try multiple navigation methods
        window.location.href = '{{ route("user.dashboard") }}';
    } catch (error) {
        console.error('Navigation error:', error);
        // Fallback to direct URL
        window.location.href = '/dashboard';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const quizCodeInput = document.getElementById('quiz_code');
    const backToDashboardBtn = document.getElementById('back-to-dashboard-btn');

    // Auto-uppercase and limit to 20 characters
    quizCodeInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });

    // Ensure back to dashboard button works
    if (backToDashboardBtn) {
        backToDashboardBtn.addEventListener('click', function(e) {
            console.log('Back to dashboard button clicked');
            goToDashboard();
        });

        // Add hover effect
        backToDashboardBtn.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f9fafb';
        });

        backToDashboardBtn.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    }

    // Debug: Log the dashboard URL
    console.log('Dashboard URL: {{ route("user.dashboard") }}');
});
</script>
@endsection
