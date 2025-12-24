@extends('layouts.user')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-4 sm:px-6 sm:py-5">
            <!-- Quiz Header -->
            <div class="mb-6 sm:mb-8">
                <div class="text-center mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">{{ $quiz->title }}</h1>
                    @if($quiz->description)
                        <p class="mt-3 text-base sm:text-lg text-gray-600 break-words max-w-2xl mx-auto">{{ $quiz->description }}</p>
                    @endif
                </div>

                <!-- Quiz Information Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <div class="text-sm font-medium text-blue-800 mb-1">Total Questions</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $quiz->total_questions }}</div>
                    </div>
                    @if($quiz->time_limit)
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                            <div class="text-sm font-medium text-orange-800 mb-1">Time Limit</div>
                            <div class="text-2xl font-bold text-orange-900">{{ $quiz->time_limit }} min</div>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                            <div class="text-sm font-medium text-gray-800 mb-1">Time Limit</div>
                            <div class="text-2xl font-bold text-gray-900">No limit</div>
                        </div>
                    @endif
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <div class="text-sm font-medium text-purple-800 mb-1">Quiz Code</div>
                        <div class="text-xl font-bold text-purple-900 break-all">{{ $quiz->quiz_code }}</div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mb-6 sm:mb-8">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">Instructions</h2>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 sm:p-6">
                    <ul class="space-y-3 text-sm sm:text-base text-gray-700">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Read each question carefully before answering.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>You can navigate between questions using the Previous and Next buttons.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Your answers are saved automatically as you progress through the quiz.</span>
                        </li>
                        @if($quiz->time_limit)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><strong>Important:</strong> This quiz has a time limit of {{ $quiz->time_limit }} minutes. The timer will start once you click "Start Quiz".</span>
                            </li>
                        @endif
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Once you submit the quiz, you cannot change your answers.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-indigo-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Make sure you have a stable internet connection before starting.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-6">
                <a href="{{ route('user.quizzes.index') }}"
                   class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Quizzes
                </a>

                <button type="button" id="start-quiz-btn"
                        class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Start Quiz
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startQuizBtn = document.getElementById('start-quiz-btn');

    startQuizBtn.addEventListener('click', function() {
        // Show confirmation dialog
        if (confirm('Are you sure you want to start this quiz? Once you start, the timer will begin (if applicable) and you will need to complete the quiz.')) {
            // Show loading state
            const originalHTML = startQuizBtn.innerHTML;
            startQuizBtn.disabled = true;
            startQuizBtn.innerHTML = `
                <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Starting...
            `;

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Start the quiz via AJAX
            fetch('{{ route("user.quizzes.start", $quiz) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ToastNotification.success(data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 500);
                } else {
                    ToastNotification.error(data.message);
                    startQuizBtn.disabled = false;
                    startQuizBtn.innerHTML = originalHTML;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                ToastNotification.error('An error occurred while starting the quiz. Please try again.');
                startQuizBtn.disabled = false;
                startQuizBtn.innerHTML = originalHTML;
            });
        }
    });
});
</script>
@endsection
