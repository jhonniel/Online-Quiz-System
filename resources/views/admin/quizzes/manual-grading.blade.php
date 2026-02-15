@extends('layouts.admin')

@section('page-title', 'Manual Grading')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Quizzes</span>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Manual Grading</span>
        </div>
    </li>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manual Grading</h1>
                <p class="mt-2 text-gray-600">Grade text answer and fill-in-the-blank questions that require manual review.</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-500">
                    <span class="font-medium">{{ $attempts->total() }}</span> attempts pending review
                </div>
                <a href="{{ url('/admin/all-text-attempts') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    View All Text Attempts
                </a>
            </div>
        </div>
    </div>

    @if($attempts->count() > 0)
        <!-- Attempts List -->
        <div class="space-y-6">
            @foreach($attempts as $attempt)
                <div class="bg-white rounded-lg shadow border border-gray-200" id="attempt-{{ $attempt->id }}">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-4 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $attempt->user->name }}
                                    </h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $attempt->quiz->title }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $attempt->question->points }} points
                                    </span>
                                    @if($attempt->question->question_type === 'text')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Text Answer
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Fill in the Blank
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500">
                                    Submitted: {{ $attempt->created_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>

                        <!-- Question -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Question:</h4>
                            <p class="text-gray-900 bg-gray-50 p-3 rounded-md">{{ $attempt->question->question_text }}</p>
                        </div>

                        <!-- Student's Answer -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Student's Answer:</h4>
                            <p class="text-gray-900 bg-blue-50 p-3 rounded-md border border-blue-200">{{ $attempt->user_answer }}</p>
                        </div>

                        <!-- Correct Answer(s) -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Correct Answer(s):</h4>
                            @if(in_array($attempt->question->question_type, ['text', 'fill_blank']))
                                <div class="space-y-3">
                                    <!-- Show correct answers if they exist -->
                                    @if($attempt->question->correct_answer || $attempt->question->alternative_answer_1 || $attempt->question->alternative_answer_2 || $attempt->question->alternative_answer_3)
                                        <div class="space-y-2">
                                            @if($attempt->question->correct_answer)
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-green-600">Main Answer:</span>
                                                    <span class="text-gray-900 bg-green-50 p-2 rounded border border-green-200">{{ $attempt->question->correct_answer }}</span>
                                                </div>
                                            @endif
                                            @if($attempt->question->alternative_answer_1)
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-green-600">Alternative 1:</span>
                                                    <span class="text-gray-900 bg-green-50 p-2 rounded border border-green-200">{{ $attempt->question->alternative_answer_1 }}</span>
                                                </div>
                                            @endif
                                            @if($attempt->question->alternative_answer_2)
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-green-600">Alternative 2:</span>
                                                    <span class="text-gray-900 bg-green-50 p-2 rounded border border-green-200">{{ $attempt->question->alternative_answer_2 }}</span>
                                                </div>
                                            @endif
                                            @if($attempt->question->alternative_answer_3)
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-green-600">Alternative 3:</span>
                                                    <span class="text-gray-900 bg-green-50 p-2 rounded border border-green-200">{{ $attempt->question->alternative_answer_3 }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Show generic message if no correct answers are set -->
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                                            <p class="text-sm text-yellow-800">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $attempt->question->question_type)) }} Question:</strong> This question requires manual evaluation.
                                                Review the student's answer and grade based on content, accuracy, and completeness.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-green-600">Main:</span>
                                        <span class="text-gray-900 bg-green-50 p-2 rounded border border-green-200">{{ $attempt->question->correct_answer }}</span>
                                    </div>
                                    @if($attempt->question->alternative_answer_1)
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-green-600">Alt 1:</span>
                                            <span class="text-gray-900 bg-green-50 p-2 rounded border border-green-200">{{ $attempt->question->alternative_answer_1 }}</span>
                                        </div>
                                    @endif
                                    @if($attempt->question->alternative_answer_2)
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-green-600">Alt 2:</span>
                                            <span class="text-gray-900 bg-green-50 p-2 rounded border border-green-200">{{ $attempt->question->alternative_answer_2 }}</span>
                                        </div>
                                    @endif
                                    @if($attempt->question->alternative_answer_3)
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-green-600">Alt 3:</span>
                                            <span class="text-gray-900 bg-green-50 p-2 rounded border border-green-200">{{ $attempt->question->alternative_answer_3 }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Grading Form -->
                        <form class="grade-form" data-attempt-id="{{ $attempt->id }}">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Grade:</label>
                                    <div class="flex items-center space-x-4">
                                        <label class="flex items-center">
                                            <input type="radio" name="is_correct" value="1" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                                            <span class="ml-2 text-sm text-gray-700">Correct</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="is_correct" value="0" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300" checked>
                                            <span class="ml-2 text-sm text-gray-700">Incorrect</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label for="points_earned_{{ $attempt->id }}" class="block text-sm font-medium text-gray-700 mb-2">Points Earned:</label>
                                    <input type="number"
                                           name="points_earned"
                                           id="points_earned_{{ $attempt->id }}"
                                           min="0"
                                           max="{{ $attempt->question->points }}"
                                           value="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <p class="text-xs text-gray-500 mt-1">Max: {{ $attempt->question->points }} points</p>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="feedback_{{ $attempt->id }}" class="block text-sm font-medium text-gray-700 mb-2">Feedback (Optional):</label>
                                <textarea name="feedback"
                                          id="feedback_{{ $attempt->id }}"
                                          rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="Provide feedback to the student..."></textarea>
                            </div>
                            <div class="flex justify-end space-x-3">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Submit Grade
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $attempts->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No attempts to grade</h3>
            <p class="mt-1 text-sm text-gray-500">There are currently no fill-in-the-blank questions awaiting manual grading.</p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle grade change to update points automatically
    document.querySelectorAll('input[name="is_correct"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const form = this.closest('form');
            const pointsInput = form.querySelector('input[name="points_earned"]');
            const maxPoints = parseInt(pointsInput.getAttribute('max'));

            if (this.value === '1') {
                // Correct - set to full points
                pointsInput.value = maxPoints;
            } else {
                // Incorrect - set to 0
                pointsInput.value = '0';
            }
        });
    });

    // Initialize points for forms with "Incorrect" selected by default
    document.querySelectorAll('.grade-form').forEach(form => {
        const pointsInput = form.querySelector('input[name="points_earned"]');
        const maxPoints = parseInt(pointsInput.getAttribute('max'));
        const isCorrectRadio = form.querySelector('input[name="is_correct"]:checked');

        if (isCorrectRadio && isCorrectRadio.value === '0') {
            // Incorrect is selected by default, set points to 0
            pointsInput.value = '0';
        }

        // Ensure at least one radio button is always selected
        const allRadios = form.querySelectorAll('input[name="is_correct"]');
        const checkedRadio = form.querySelector('input[name="is_correct"]:checked');
        if (!checkedRadio && allRadios.length > 0) {
            // If no radio is selected, select the first one (Incorrect)
            allRadios[0].checked = true;
            pointsInput.value = '0';
        }
    });

    // Handle form submissions
    document.querySelectorAll('.grade-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const attemptId = this.dataset.attemptId;
            const formData = new FormData(this);

            // Check if form is already being submitted
            if (this.dataset.submitting === 'true') {
                console.log('Form is already being submitted, ignoring...');
                return;
            }

            // Mark form as submitting
            this.dataset.submitting = 'true';

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Grading...
            `;

            // Debug: Check if is_correct is selected
            const isCorrectRadio = this.querySelector('input[name="is_correct"]:checked');
            console.log('Selected is_correct radio:', isCorrectRadio ? isCorrectRadio.value : 'NONE SELECTED');
            console.log('Radio button element:', isCorrectRadio);

            // Debug: Check all radio buttons
            const allRadios = this.querySelectorAll('input[name="is_correct"]');
            console.log('All radio buttons:', allRadios);
            allRadios.forEach((radio, index) => {
                console.log(`Radio ${index}: value=${radio.value}, checked=${radio.checked}`);
            });

            // Debug: Log form data
            console.log('Submitting form data:', {
                attemptId: attemptId,
                is_correct: formData.get('is_correct'),
                points_earned: formData.get('points_earned'),
                feedback: formData.get('feedback'),
                csrf_token: formData.get('_token')
            });

            // Debug: Log all form data
            console.log('All form data entries:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            // Ensure is_correct is always set
            let isCorrectValue = formData.get('is_correct');
            if (!isCorrectValue) {
                console.error('is_correct field is missing from form data!');

                // Try to get the value from the selected radio button
                if (isCorrectRadio) {
                    isCorrectValue = isCorrectRadio.value;
                    console.log('Manually adding is_correct to form data:', isCorrectValue);
                    formData.set('is_correct', isCorrectValue);
                } else {
                    // Fallback: select the first radio button (Incorrect)
                    const firstRadio = this.querySelector('input[name="is_correct"]');
                    if (firstRadio) {
                        firstRadio.checked = true;
                        isCorrectValue = firstRadio.value;
                        formData.set('is_correct', isCorrectValue);
                        console.log('Fallback: selected first radio button with value:', isCorrectValue);
                    } else {
                        alert('Please select either Correct or Incorrect before submitting.');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        return;
                    }
                }
            }

            // Ensure points_earned is set correctly based on is_correct
            const pointsValue = formData.get('points_earned');
            if (isCorrectValue === '1' && pointsValue === '0') {
                // If correct is selected but points is 0, set to max points
                const maxPoints = parseInt(this.querySelector('input[name="points_earned"]').getAttribute('max'));
                formData.set('points_earned', maxPoints.toString());
                console.log('Updated points to max for correct answer:', maxPoints);
            } else if (isCorrectValue === '0' && pointsValue !== '0') {
                // If incorrect is selected but points is not 0, set to 0
                formData.set('points_earned', '0');
                console.log('Updated points to 0 for incorrect answer');
            }

            console.log('Form validation passed, submitting...');

            fetch(`/admin/quiz-attempts/${attemptId}/grade`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    console.log('Grading successful, removing form...');

                    // Show success message
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.success(data.message);
                    } else {
                        alert('Success: ' + data.message);
                    }

                    // Find the form container and remove it using the specific ID
                    const attemptId = this.dataset.attemptId;
                    const formContainer = document.getElementById(`attempt-${attemptId}`);

                    console.log('Looking for container with ID:', `attempt-${attemptId}`);
                    console.log('Form container found:', formContainer);

                    if (formContainer) {
                        // Add a visual effect before removing
                        formContainer.style.opacity = '0.5';
                        formContainer.style.transition = 'opacity 0.3s ease';

                        setTimeout(() => {
                            formContainer.remove();
                            console.log('Form removed successfully');
                        }, 300);
                    } else {
                        console.error('Could not find form container with ID:', `attempt-${attemptId}`);
                        // Fallback: try to find by class
                        const fallbackContainer = this.closest('.bg-white.rounded-lg.shadow.border.border-gray-200');
                        if (fallbackContainer) {
                            console.log('Using fallback container removal');
                            fallbackContainer.style.opacity = '0.5';
                            fallbackContainer.style.transition = 'opacity 0.3s ease';
                            setTimeout(() => {
                                fallbackContainer.remove();
                                console.log('Form removed via fallback');
                            }, 300);
                        } else {
                            console.error('No container found for removal');
                        }
                    }

                    // Check if there are any more attempts
                    const remainingAttempts = document.querySelectorAll('.grade-form');
                    console.log('Remaining attempts:', remainingAttempts.length);

                    if (remainingAttempts.length === 0) {
                        console.log('No more attempts, reloading page...');
                        // Reload the page to show empty state
                        window.location.reload();
                    }
                } else {
                    console.log('Grading failed:', data.message);
                    if (typeof ToastNotification !== 'undefined') {
                        ToastNotification.error(data.message || 'An error occurred while grading.');
                    } else {
                        alert('Error: ' + (data.message || 'An error occurred while grading.'));
                    }
                    // Reset submitting flag and button state
                    this.dataset.submitting = 'false';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof ToastNotification !== 'undefined') {
                    ToastNotification.error(error.message || 'An error occurred while grading. Please try again.');
                } else {
                    alert('Error: ' + (error.message || 'An error occurred while grading. Please try again.'));
                }
                // Reset submitting flag and button state
                this.dataset.submitting = 'false';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });
});
</script>
@endsection
