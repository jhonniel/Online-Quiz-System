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

                    @if($quiz->time_limit)
                        <!-- Timer Display -->
                        <div id="timer-display" class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 flex-shrink-0 {{ isset($remainingTime) ? '' : 'hidden' }}">
                            <div class="text-center">
                                <div class="text-xs sm:text-sm font-medium text-blue-800">Time Remaining</div>
                                <div id="header-timer" class="text-xl sm:text-2xl font-bold text-blue-900 mt-1">
                                    @if(isset($remainingTime))
                                        {{ floor($remainingTime / 60) }}:{{ str_pad($remainingTime % 60, 2, '0', STR_PAD_LEFT) }}
                                    @else
                                        --
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Progress Bar (hidden initially) -->
            <div id="progress-section" class="mb-4 sm:mb-6 hidden">
                <div class="flex justify-between text-xs sm:text-sm text-gray-600 mb-2">
                    <span>Progress</span>
                    <span id="progress-text">Question 1 of {{ $quiz->total_questions }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <!-- Start Quiz Button (shown initially) -->
            <div id="start-quiz-section" class="mb-6 sm:mb-8">
                <div class="text-center py-8 sm:py-12">
                    <button type="button" id="start-quiz-btn"
                            class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base sm:text-lg font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span id="start-quiz-text">Start Quiz</span>
                    </button>
                </div>
            </div>

            <form id="quiz-form" action="{{ url('/quizzes/' . $quiz->id . '/submit') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="user_answers" id="user_answers_input">

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
                                class="inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 hidden disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="submit-icon" class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span id="submit-text">Submit Quiz</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quiz data (will be loaded from API)
    let questions = [];
    let totalQuestions = 0;
    let currentQuestionIndex = 0;
    let userAnswers = {};
    let remainingTime = null;
    let isAutoSubmit = false; // Flag to track if submission is triggered by timer

    // Start Quiz Button Handler
    const startQuizBtn = document.getElementById('start-quiz-btn');
    const startQuizSection = document.getElementById('start-quiz-section');
    const progressSection = document.getElementById('progress-section');
    const quizForm = document.getElementById('quiz-form');

        startQuizBtn.addEventListener('click', function() {
        const originalHTML = startQuizBtn.innerHTML;
        startQuizBtn.disabled = true;
        startQuizBtn.innerHTML = '<span>Loading questions…</span>';

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Fetch questions from API
        fetch('{{ url("/quizzes/" . $quiz->id . "/questions") }}', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Failed to fetch questions');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                questions = data.questions;
                totalQuestions = questions.length;
                remainingTime = data.remaining_time;
                if (data.saved_progress && typeof data.saved_progress === 'object') {
                    Object.assign(userAnswers, data.saved_progress);
                }

                // Hide start button section
                startQuizSection.classList.add('hidden');

                // Show progress bar and quiz form
                progressSection.classList.remove('hidden');
                quizForm.classList.remove('hidden');

                // Update progress text with actual question count
                document.getElementById('progress-text').textContent = `Question 1 of ${totalQuestions}`;

                // Show and run live countdown timer when time limit exists
                if (remainingTime !== null && remainingTime !== undefined) {
                    const timerDisplay = document.getElementById('timer-display');
                    if (timerDisplay) timerDisplay.classList.remove('hidden');
                    initializeTimer(remainingTime);
                }

                // Initialize quiz
                initializeQuiz();
            } else {
                throw new Error(data.message || 'Failed to load questions');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            ToastNotification.error(error.message || 'An error occurred while loading questions. Please try again.');
            startQuizBtn.disabled = false;
            startQuizBtn.innerHTML = originalHTML;
        });
    });

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
                const persistAnswer = function() {
                    saveAnswer();
                };
                input.addEventListener('change', persistAnswer);
                if (input.tagName === 'TEXTAREA' || input.type === 'text') {
                    input.addEventListener('input', persistAnswer);
                }
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

    function hasAnswerValue(value) {
        return value !== undefined && value !== null && String(value).trim() !== '';
    }

    function countAnsweredQuestions() {
        return Object.values(userAnswers).filter(hasAnswerValue).length;
    }

    // Save current answer
    function saveAnswer() {
        const question = questions[currentQuestionIndex];
        if (!question) {
            return;
        }

        const inputs = document.querySelectorAll(`input[name="answers[${question.id}]"], textarea[name="answers[${question.id}]"]`);

        if (inputs.length === 1) {
            const value = inputs[0].value;
            if (hasAnswerValue(value)) {
                userAnswers[question.id] = value;
            } else {
                delete userAnswers[question.id];
            }
        } else {
            let selected = false;
            inputs.forEach(input => {
                if (input.checked) {
                    userAnswers[question.id] = input.value;
                    selected = true;
                }
            });
            if (!selected) {
                delete userAnswers[question.id];
            }
        }
    }

    // Persist progress to server (called when moving to next/previous question)
    function saveProgressToServer() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) return;
        const formData = new FormData();
        formData.append('_token', csrfToken);
        Object.keys(userAnswers).forEach(questionId => {
            formData.append(`answers[${questionId}]`, userAnswers[questionId]);
        });
        fetch('{{ url("/quizzes/" . $quiz->id . "/save-progress") }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            body: formData
        }).catch(() => {});
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
        saveProgressToServer();
        if (currentQuestionIndex < totalQuestions - 1) {
            currentQuestionIndex++;
            showQuestion(currentQuestionIndex);
            updateProgress();
            updateNavigationButtons();
        }
    });

    document.getElementById('prev-btn').addEventListener('click', function() {
        saveAnswer();
        saveProgressToServer();
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            showQuestion(currentQuestionIndex);
            updateProgress();
            updateNavigationButtons();
        }
    });

    function resetSubmitButton() {
        const submitBtn = document.getElementById('submit-btn');
        const submitIcon = document.getElementById('submit-icon');
        const submitText = document.getElementById('submit-text');
        if (submitBtn) {
            submitBtn.disabled = false;
        }
        if (submitIcon) {
            submitIcon.outerHTML = '<svg id="submit-icon" class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        }
        if (submitText) {
            submitText.textContent = 'Submit Quiz';
        }
    }

    function setSubmitButtonLoading(isLoading) {
        const submitBtn = document.getElementById('submit-btn');
        const submitIcon = document.getElementById('submit-icon');
        const submitText = document.getElementById('submit-text');
        if (!submitBtn || !submitIcon || !submitText) {
            return;
        }
        submitBtn.disabled = isLoading;
        if (isLoading) {
            submitIcon.outerHTML = '<svg id="submit-icon" class="animate-spin w-4 h-4 sm:mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            submitText.textContent = 'Submitting...';
        } else {
            resetSubmitButton();
        }
    }

    async function parseJsonResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            return response.json();
        }

        const text = await response.text();
        throw new Error(JSON.stringify({
            message: 'Unexpected server response. Please refresh the page and try again.',
            debug: text.slice(0, 200),
        }));
    }

    // Form submission
    document.getElementById('quiz-form').addEventListener('submit', function(e) {
        e.preventDefault();

        saveAnswer();

        const answerCount = countAnsweredQuestions();
        if (answerCount === 0) {
            ToastNotification.error('Please answer at least one question before submitting.');
            return;
        }

        const shouldSubmit = isAutoSubmit || confirm(`Are you sure you want to submit this quiz? You have answered ${answerCount} out of ${totalQuestions} questions. You cannot change your answers after submission.`);

        if (!shouldSubmit) {
            return;
        }

        isAutoSubmit = false;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            ToastNotification.error('Security token missing. Please refresh the page and try again.');
            return;
        }

        const formData = new FormData();
        formData.append('_token', csrfToken);
        Object.entries(userAnswers).forEach(([questionId, value]) => {
            if (hasAnswerValue(value)) {
                formData.append(`answers[${questionId}]`, value);
            }
        });

        setSubmitButtonLoading(true);

        fetch('/quizzes/{{ $quiz->id }}/submit', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin',
        })
        .then(async response => {
            const data = await parseJsonResponse(response);
            if (!response.ok) {
                throw new Error(JSON.stringify(data));
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                ToastNotification.success(data.message);
                setTimeout(() => {
                    window.location.href = data.redirect_url || '/quizzes/{{ $quiz->id }}/result';
                }, 1000);
            } else {
                ToastNotification.error(data.message || 'Unable to submit the quiz.');
                resetSubmitButton();
            }
        })
        .catch(error => {
            console.error('Quiz submit error:', error);

            try {
                const errorData = JSON.parse(error.message);
                if (errorData.errors) {
                    const errorMessages = Object.values(errorData.errors).flat();
                    ToastNotification.error('Validation error: ' + errorMessages.join(', '));
                } else if (errorData.message) {
                    ToastNotification.error(errorData.message);
                } else {
                    ToastNotification.error('An error occurred while submitting the quiz. Please try again.');
                }
            } catch (parseError) {
                ToastNotification.error('An error occurred while submitting the quiz. Please try again.');
            }

            resetSubmitButton();
        });
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

                fetch('{{ url("/quizzes/" . $quiz->id . "/cancel") }}', {
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

    function formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return m + ':' + String(s).padStart(2, '0');
    }

    // Timer: live countdown in header + auto-submit when time expires
    function initializeTimer(timeLeft) {
        const headerTimer = document.getElementById('header-timer');
        function updateDisplay() {
            if (headerTimer) headerTimer.textContent = formatTime(timeLeft);
        }

        if (timeLeft <= 0) {
            ToastNotification.warning('Time is up! Your quiz will be submitted automatically.');
            isAutoSubmit = true;
            document.getElementById('quiz-form').dispatchEvent(new Event('submit'));
            return;
        }

        updateDisplay();

        const timer = setInterval(function() {
            timeLeft--;
            updateDisplay();
            if (timeLeft <= 60 && headerTimer) {
                headerTimer.closest('#timer-display').classList.add('animate-pulse');
                headerTimer.closest('#timer-display').classList.remove('bg-blue-50', 'border-blue-200');
                headerTimer.closest('#timer-display').classList.add('bg-red-50', 'border-red-200');
                headerTimer.classList.add('text-red-900');
            }
            if (timeLeft <= 0) {
                clearInterval(timer);
                ToastNotification.warning('Time is up! Your quiz will be submitted automatically.');
                isAutoSubmit = true;
                document.getElementById('quiz-form').dispatchEvent(new Event('submit'));
            }
        }, 1000);
    }
});
</script>
@endsection
