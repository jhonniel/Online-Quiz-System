@extends('layouts.user')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-4 sm:px-6 sm:py-5">
            <!-- Quiz Header -->
            <div class="mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 break-words">{{ $quiz->title }}</h1>
                        @if($quiz->description)
                            <p class="mt-2 text-sm sm:text-base text-gray-600 break-words">{{ $quiz->description }}</p>
                        @endif
                        <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-500">
                            <span class="whitespace-nowrap">{{ $quiz->total_questions }} questions</span>
                            @if($quiz->time_limit)
                                <span class="whitespace-nowrap">{{ $quiz->time_limit }} minutes</span>
                            @endif
                            <span class="whitespace-nowrap break-all">Quiz Code: {{ $quiz->quiz_code }}</span>
                        </div>
                    </div>

                    @if($quiz->time_limit && isset($remainingTime))
                        <!-- Timer Display -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 flex-shrink-0">
                            <div class="text-center">
                                <div class="text-xs sm:text-sm font-medium text-blue-800">Time Remaining</div>
                                <div id="header-timer" class="text-xl sm:text-2xl font-bold text-blue-900 mt-1">{{ floor($remainingTime / 60) }}:{{ str_pad($remainingTime % 60, 2, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4 sm:mb-6">
                <div class="flex justify-between text-xs sm:text-sm text-gray-600 mb-2">
                    <span>Progress</span>
                    <span id="progress-text">Question 1 of {{ $quiz->total_questions }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ 100 / $quiz->total_questions }}%"></div>
                </div>
            </div>

            <form id="quiz-form" action="{{ route('user.quizzes.submit', $quiz) }}" method="POST">
                @csrf

                <!-- Question Container -->
                <div id="question-container" class="mb-6 sm:mb-8 question-transition">
                    <!-- Questions will be dynamically loaded here -->
                </div>

                <!-- Navigation Buttons -->
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 sm:gap-0">
                    <button type="button" id="cancel-quiz-btn"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="hidden sm:inline">Cancel Quiz</span>
                        <span class="sm:hidden">Cancel</span>
                    </button>

                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 sm:flex-shrink-0">
                        <button type="button" id="prev-btn"
                                class="inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 hidden">
                            <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>

                        <button type="button" id="next-btn"
                                class="inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span>Next</span>
                            <svg class="w-4 h-4 sm:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <button type="submit" id="submit-btn"
                                class="inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 hidden">
                            Submit Quiz
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quiz data from server
    const questions = @json($questions);
    const totalQuestions = questions.length;
    let currentQuestionIndex = 0;
    let userAnswers = {};

    // Initialize quiz
    function initializeQuiz() {
        showQuestion(currentQuestionIndex);
        updateProgress();
        updateNavigationButtons();
    }

    // Show specific question
    function showQuestion(index) {
        const question = questions[index];
        const container = document.getElementById('question-container');

        // Add exit animation
        container.classList.add('question-exit-active');

        setTimeout(() => {
            container.innerHTML = `
            <div class="border-b border-gray-200 pb-6 sm:pb-8">
                <div class="mb-4">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900">
                        Question ${index + 1} of ${totalQuestions}
                    </h3>
                    <p class="mt-2 text-sm sm:text-base text-gray-700 break-words">${question.question_text}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2">
                        ${question.points} point${question.points > 1 ? 's' : ''}
                    </span>
                </div>

                <div class="space-y-2 sm:space-y-3">
                    ${generateQuestionHTML(question)}
                </div>

            ${question.question_type === 'true_false' ? `
                <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-green-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-xs sm:text-sm text-green-800 font-medium">Select either True or False for this question</span>
                    </div>
                </div>
                ` : ''}
            </div>
        `;

            // Restore previous answer if exists
            restoreAnswer(question.id);

            // Add event listeners to save answers immediately when selected
            const inputs = container.querySelectorAll('input[name*="answers["], textarea[name*="answers["]');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    saveAnswer();
                    console.log('Answer saved:', question.id, '=', this.value);
                });
            });

            // Add enter animation
            container.classList.remove('question-exit-active');
            container.classList.add('question-enter-active');

            setTimeout(() => {
                container.classList.remove('question-enter-active');
            }, 300);
        }, 150);
    }

    // Generate HTML for different question types
    function generateQuestionHTML(question) {
        if (question.question_type === 'multiple_choice') {
            // Use ordered options if available, otherwise fall back to old structure
            const options = question.ordered_options || question.answers || [];
            return options.map((option, index) => {
                const optionText = option.text || option.answer_text || '';
                // Always use the label (A, B, C, D) as the value, not the text
                const optionId = option.label || option.id || ['A', 'B', 'C', 'D'][index];
                return `
                    <label class="flex items-start sm:items-center p-2.5 sm:p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200 group">
                        <input type="radio"
                               name="answers[${question.id}]"
                               value="${optionId}"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 mt-0.5 sm:mt-0 flex-shrink-0">
                        <span class="ml-2 sm:ml-3 text-sm sm:text-base text-gray-700 group-hover:text-gray-900 break-words">${optionText}</span>
                    </label>
                `;
            }).join('');
        } else if (question.question_type === 'true_false') {
            // Handle true/false questions with proper True/False options
            return `
                <label class="flex items-center p-2.5 sm:p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200 group">
                    <input type="radio"
                           name="answers[${question.id}]"
                           value="A"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 flex-shrink-0">
                    <span class="ml-2 sm:ml-3 text-sm sm:text-base text-gray-700 group-hover:text-gray-900 font-medium">True</span>
                </label>
                <label class="flex items-center p-2.5 sm:p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-200 group">
                    <input type="radio"
                           name="answers[${question.id}]"
                           value="B"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 flex-shrink-0">
                    <span class="ml-2 sm:ml-3 text-sm sm:text-base text-gray-700 group-hover:text-gray-900 font-medium">False</span>
                </label>
            `;
        } else if (question.question_type === 'text') {
            return `
                <textarea name="answers[${question.id}]"
                          rows="4"
                          class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Enter your answer here..."></textarea>
            `;
        } else if (question.question_type === 'fill_blank') {
            return `
                <div class="space-y-3 sm:space-y-4">
                    <div>
                        <label for="fill_blank_${question.id}" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Your Answer:
                        </label>
                        <input type="text"
                               name="answers[${question.id}]"
                               id="fill_blank_${question.id}"
                               class="w-full px-3 py-2 text-base sm:text-lg border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Type your answer here...">
                    </div>
                    <div class="p-2.5 sm:p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start sm:items-center">
                            <svg class="w-4 h-4 text-yellow-600 mr-2 flex-shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div class="flex-1">
                                <span class="text-xs sm:text-sm text-yellow-800 font-medium">This question requires manual grading</span>
                                <p class="text-xs text-yellow-700 mt-1">Your answer will be reviewed by an instructor after submission.</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        return '';
    }

    // Save current answer
    function saveAnswer() {
        const question = questions[currentQuestionIndex];
        const inputs = document.querySelectorAll(`input[name="answers[${question.id}]"], textarea[name="answers[${question.id}]"]`);

        if (inputs.length === 1) {
            // Text input
            userAnswers[question.id] = inputs[0].value;
        } else {
            // Radio buttons
            inputs.forEach(input => {
                if (input.checked) {
                    userAnswers[question.id] = input.value;
                }
            });
        }
    }

    // Restore previous answer
    function restoreAnswer(questionId) {
        if (userAnswers[questionId]) {
            const inputs = document.querySelectorAll(`input[name="answers[${questionId}]"], textarea[name="answers[${questionId}]"]`);

            if (inputs.length === 1) {
                // Text input
                inputs[0].value = userAnswers[questionId];
            } else {
                // Radio buttons
                inputs.forEach(input => {
                    if (input.value === userAnswers[questionId]) {
                        input.checked = true;
                    }
                });
            }
        }
    }

    // Update progress bar
    function updateProgress() {
        const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
        document.getElementById('progress-bar').style.width = progress + '%';
        document.getElementById('progress-text').textContent = `Question ${currentQuestionIndex + 1} of ${totalQuestions}`;
    }

    // Update navigation buttons
    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');

        // Show/hide previous button
        if (currentQuestionIndex === 0) {
            prevBtn.classList.add('hidden');
        } else {
            prevBtn.classList.remove('hidden');
        }

        // Show/hide next/submit button
        if (currentQuestionIndex === totalQuestions - 1) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }
    }

    // Navigation event listeners
    document.getElementById('next-btn').addEventListener('click', function() {
        saveAnswer();
        if (currentQuestionIndex < totalQuestions - 1) {
            currentQuestionIndex++;
            showQuestion(currentQuestionIndex);
            updateProgress();
            updateNavigationButtons();
        }
    });

    document.getElementById('prev-btn').addEventListener('click', function() {
        saveAnswer();
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            showQuestion(currentQuestionIndex);
            updateProgress();
            updateNavigationButtons();
        }
    });

    // Form submission
    document.getElementById('quiz-form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Save current answer before submitting
        saveAnswer();

        // Save all answers from all questions before submitting
        for (let i = 0; i < totalQuestions; i++) {
            const question = questions[i];
            const inputs = document.querySelectorAll(`input[name="answers[${question.id}]"], textarea[name="answers[${question.id}]"]`);

            if (inputs.length === 1) {
                // Text input
                userAnswers[question.id] = inputs[0].value;
            } else {
                // Radio buttons
                inputs.forEach(input => {
                    if (input.checked) {
                        userAnswers[question.id] = input.value;
                    }
                });
            }
        }

        // Check if we have any answers
        const answerCount = Object.keys(userAnswers).length;
        if (answerCount === 0) {
            ToastNotification.error('Please answer at least one question before submitting.');
            return;
        }

        // Show confirmation dialog
        if (confirm(`Are you sure you want to submit this quiz? You have answered ${answerCount} out of ${totalQuestions} questions. You cannot change your answers after submission.`)) {
            // Show loading state
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-3 w-3 sm:h-4 sm:w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="hidden sm:inline">Submitting...</span>
                <span class="sm:hidden">Submitting</span>
            `;

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Prepare form data
            const formData = new FormData();
            formData.append('_token', csrfToken);

            // Add all answers to form data
            Object.keys(userAnswers).forEach(questionId => {
                formData.append(`answers[${questionId}]`, userAnswers[questionId]);
            });

            // Debug: Log what we're sending
            console.log('Submitting answers:', userAnswers);
            console.log('Answer count:', Object.keys(userAnswers).length);

            // Submit via AJAX
            fetch('{{ route("user.quizzes.submit", $quiz) }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(JSON.stringify(errorData));
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    ToastNotification.success(data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000);
                } else {
                    ToastNotification.error(data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);

                // Try to parse error message
                try {
                    const errorData = JSON.parse(error.message);
                    if (errorData.errors) {
                        // Show validation errors
                        const errorMessages = Object.values(errorData.errors).flat();
                        ToastNotification.error('Validation Error: ' + errorMessages.join(', '));
                    } else if (errorData.message) {
                        ToastNotification.error(errorData.message);
                    } else {
                        ToastNotification.error('An error occurred while submitting the quiz. Please try again.');
                    }
                } catch (parseError) {
                    ToastNotification.error('An error occurred while submitting the quiz. Please try again.');
                }

                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }
    });

    // Cancel quiz functionality
    document.getElementById('cancel-quiz-btn').addEventListener('click', function() {
        if (confirm('Are you sure you want to cancel this quiz? Your progress will be lost and you can restart it later.')) {
                const cancelButton = this;
                const originalText = cancelButton.innerHTML;

                // Show loading state
                cancelButton.disabled = true;
                cancelButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-3 w-3 sm:h-4 sm:w-4 text-gray-600 inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="hidden sm:inline">Cancelling...</span>
                    <span class="sm:hidden">Cancelling</span>
                `;

                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch('{{ route("user.quizzes.cancel", $quiz) }}', {
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
                        }, 1000);
                    } else {
                        ToastNotification.error(data.message);
                        cancelButton.disabled = false;
                        cancelButton.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    ToastNotification.error('An error occurred while cancelling the quiz. Please try again.');
                    cancelButton.disabled = false;
                    cancelButton.innerHTML = originalText;
                });
            }
    });

    // Timer functionality (if time limit is set)
    @if($quiz->time_limit && isset($remainingTime))
        let timeLeft = {{ $remainingTime }}; // Use server-calculated remaining time
        const timerElement = document.createElement('div');
        timerElement.className = 'fixed top-2 right-2 sm:top-4 sm:right-4 bg-red-600 text-white px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg shadow-lg z-50 text-xs sm:text-sm';
        timerElement.innerHTML = `<span class="font-bold hidden sm:inline">Time Left: </span><span class="font-bold sm:hidden">Time: </span><span id="timer">${formatTime(timeLeft)}</span>`;
        document.body.appendChild(timerElement);

        // Check if time has already expired
        if (timeLeft <= 0) {
            ToastNotification.warning('Time is up! Your quiz will be submitted automatically.');
            // Trigger form submission programmatically
            document.getElementById('quiz-form').dispatchEvent(new Event('submit'));
            return;
        }

        const timer = setInterval(function() {
            timeLeft--;
            const formattedTime = formatTime(timeLeft);
            document.getElementById('timer').textContent = formattedTime;

            // Update header timer if it exists
            const headerTimer = document.getElementById('header-timer');
            if (headerTimer) {
                headerTimer.textContent = formattedTime;
            }

            // Change color when time is running low
            if (timeLeft <= 60) { // Last minute
                timerElement.className = 'fixed top-2 right-2 sm:top-4 sm:right-4 bg-red-800 text-white px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg shadow-lg z-50 text-xs sm:text-sm animate-pulse';
                if (headerTimer) {
                    headerTimer.parentElement.parentElement.className = 'bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4 animate-pulse';
                    headerTimer.className = 'text-xl sm:text-2xl font-bold text-red-900';
                }
            } else if (timeLeft <= 300) { // Last 5 minutes
                timerElement.className = 'fixed top-2 right-2 sm:top-4 sm:right-4 bg-orange-600 text-white px-2 py-1.5 sm:px-4 sm:py-2 rounded-lg shadow-lg z-50 text-xs sm:text-sm';
                if (headerTimer) {
                    headerTimer.parentElement.parentElement.className = 'bg-orange-50 border border-orange-200 rounded-lg p-3 sm:p-4';
                    headerTimer.className = 'text-xl sm:text-2xl font-bold text-orange-900';
                }
            }

            if (timeLeft <= 0) {
                clearInterval(timer);
                ToastNotification.warning('Time is up! Your quiz will be submitted automatically.');
                // Trigger form submission programmatically
                document.getElementById('quiz-form').dispatchEvent(new Event('submit'));
            }
        }, 1000);

        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        }
    @endif

    // Initialize the quiz
    initializeQuiz();
});
</script>
@endsection
