@extends('layouts.admin')

@section('page-title', 'Quiz Attempt History')

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
            <span class="ml-2 text-sm font-medium text-gray-500">Attempt History</span>
        </div>
    </li>
@endsection

@section('content')
<div class="h-full flex flex-col space-y-3">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-4 flex-shrink-0 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Quiz Attempt History</h1>
                    <p class="text-indigo-100 text-sm">{{ $assignment->user->name }} - {{ $assignment->quiz->title }}</p>
                </div>
            </div>
            <a href="{{ route('quizzes.show', $assignment->quiz) }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-md transition-colors duration-200">
                Back to Quiz
            </a>
        </div>
    </div>

    <!-- Assignment Summary -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-blue-600">Total Attempts</div>
                    <div class="text-2xl font-bold text-blue-900">{{ $assignment->attempt_count }}</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-green-600">Best Score</div>
                    <div class="text-2xl font-bold text-green-900">{{ $assignment->best_score ?? 0 }}/{{ $assignment->quiz->total_questions }}</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-purple-600">Average Score</div>
                    <div class="text-2xl font-bold text-purple-900">{{ $assignment->getAverageScore() }}</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg">
                    <div class="text-sm font-medium text-orange-600">Best Percentage</div>
                    <div class="text-2xl font-bold text-orange-900">{{ $assignment->getBestScorePercentage() }}%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attempt History Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 flex-1 flex flex-col mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Attempt History</h3>
        </div>

        <div class="flex-1 overflow-y-auto">
            @php
                $attemptHistory = $assignment->attemptHistory()->get();
            @endphp
            @if($attemptHistory->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempt #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Taken</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($attemptHistory as $attempt)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $attempt->attempt_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $attempt->correct_answers }}/{{ $attempt->total_questions }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $attempt->percentage }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">{{ $attempt->percentage }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $attempt->time_taken_formatted }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $attempt->getStatusBadgeClass() }}">
                                            {{ $attempt->getStatusText() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $attempt->started_at ? \Carbon\Carbon::parse($attempt->started_at)->format('M j, Y g:i A') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $attempt->completed_at ? \Carbon\Carbon::parse($attempt->completed_at)->format('M j, Y g:i A') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <button onclick="viewAttemptDetails({{ $attempt->id }})" class="text-indigo-600 hover:text-indigo-900">
                                                View Details
                                            </button>
                                            <a href="{{ url('admin/quiz-assignments/' . $assignment->id . '/history/pdf') }}" class="text-gray-600 hover:text-gray-900" title="Download PDF">
                                                PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No attempt history</h3>
                    <p class="mt-1 text-sm text-gray-500">This user hasn't completed any quiz attempts yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Current Status:
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $assignment->getStatusBadgeClass() }}">
                        {{ $assignment->getStatusText() }}
                    </span>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ url('admin/quiz-assignments/' . $assignment->id . '/history/pdf') }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition-colors duration-200"
                       target="_blank">
                        Download PDF
                    </a>
                    @if($assignment->canRetake())
                        <form action="{{ url('admin/quiz-assignments/' . $assignment->id . '/reset') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                                Reset for Retake
                            </button>
                        </form>
                    @else
                        <form action="{{ url('admin/quiz-assignments/' . $assignment->id . '/allow-retake') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                                Allow Retake
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attempt Details Modal -->
<div id="attemptModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Quiz Attempt Details</h3>
                <button onclick="closeAttemptModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="attemptDetails" class="space-y-6">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewAttemptDetails(attemptId) {
    const attemptDetails = document.getElementById('attemptDetails');
    const detailsLoad = window.AppSkeleton && attemptDetails
        ? AppSkeleton.beginLoading(attemptDetails, 'panel')
        : null;
    if (!detailsLoad) {
        attemptDetails.innerHTML = '<p class="text-gray-500 py-8 text-center">Loading attempt details...</p>';
    }
    document.getElementById('attemptModal').classList.remove('hidden');

    // Make AJAX call to get attempt details
    const url = `{{ url('admin/quiz-attempts') }}/${attemptId}/details`;
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
    if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');

    fetch(url, { method: 'GET', headers })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error || err.message || 'Request failed'); }).catch(() => { throw new Error('Request failed (' + response.status + ')'); });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        detailsLoad?.finish();
        displayAttemptDetails(data);
    })
    .catch(error => {
        detailsLoad?.finish();
        console.error('Error:', error);
        document.getElementById('attemptDetails').innerHTML = `
            <div class="text-center py-8">
                <p class="text-red-500">Error loading attempt details</p>
                <p class="text-sm text-gray-400 mt-2">${error.message || 'Please try again later'}</p>
            </div>
        `;
    });
}

function displayAttemptDetails(data) {
    const { attempt, quiz, user, questions, user_answers } = data;

    // Calculate percentage
    const percentage = attempt.total_questions > 0 ? Math.round((attempt.correct_answers / attempt.total_questions) * 100) : 0;

    // Format time taken
    const timeTaken = attempt.time_taken_seconds ? formatTime(attempt.time_taken_seconds) : 'N/A';

    let questionsHtml = '';
    questions.forEach((question, index) => {
        const userAnswer = user_answers[question.id] || 'No answer';
        const isCorrect = userAnswer === question.correct_answer;
        const correctAnswerText = getAnswerText(question, question.correct_answer);
        const userAnswerText = getAnswerText(question, userAnswer);

        questionsHtml += `
            <div class="border border-gray-200 rounded-lg p-4 ${isCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}">
                <div class="flex items-start justify-between mb-3">
                    <h4 class="text-lg font-medium text-gray-900">Question ${index + 1}</h4>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-500">${question.points} pts</span>
                        ${isCorrect ?
                            '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Correct</span>' :
                            '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Incorrect</span>'
                        }
                    </div>
                </div>

                <p class="text-gray-700 mb-4">${question.question_text}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h5 class="text-sm font-medium text-gray-500 mb-2">Answer Options:</h5>
                        <div class="space-y-2">
                            ${question.option_a ? `<div class="flex items-center space-x-2"><span class="w-6 text-sm font-medium">A:</span><span class="text-sm ${question.correct_answer === 'A' ? 'text-green-600 font-medium' : ''}">${question.option_a}</span></div>` : ''}
                            ${question.option_b ? `<div class="flex items-center space-x-2"><span class="w-6 text-sm font-medium">B:</span><span class="text-sm ${question.correct_answer === 'B' ? 'text-green-600 font-medium' : ''}">${question.option_b}</span></div>` : ''}
                            ${question.option_c ? `<div class="flex items-center space-x-2"><span class="w-6 text-sm font-medium">C:</span><span class="text-sm ${question.correct_answer === 'C' ? 'text-green-600 font-medium' : ''}">${question.option_c}</span></div>` : ''}
                            ${question.option_d ? `<div class="flex items-center space-x-2"><span class="w-6 text-sm font-medium">D:</span><span class="text-sm ${question.correct_answer === 'D' ? 'text-green-600 font-medium' : ''}">${question.option_d}</span></div>` : ''}
                        </div>
                    </div>

                    <div>
                        <h5 class="text-sm font-medium text-gray-500 mb-2">Student's Answer:</h5>
                        <div class="p-3 rounded-md ${isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            <span class="font-medium">${userAnswerText}</span>
                        </div>

                        <h5 class="text-sm font-medium text-gray-500 mb-2 mt-3">Correct Answer:</h5>
                        <div class="p-3 rounded-md bg-green-100 text-green-800">
                            <span class="font-medium">${correctAnswerText}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    document.getElementById('attemptDetails').innerHTML = `
        <!-- Attempt Summary -->
        <div class="bg-gray-50 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Student</h4>
                    <p class="text-lg font-semibold text-gray-900">${user.name}</p>
                    <p class="text-sm text-gray-500">${user.email}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Quiz</h4>
                    <p class="text-lg font-semibold text-gray-900">${quiz.title}</p>
                    <p class="text-sm text-gray-500">${quiz.description || 'No description'}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Attempt #${attempt.attempt_number}</h4>
                    <p class="text-lg font-semibold text-gray-900">${attempt.correct_answers}/${attempt.total_questions} (${percentage}%)</p>
                    <p class="text-sm text-gray-500">Time: ${timeTaken}</p>
                </div>
            </div>
        </div>

        <!-- Questions and Answers -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Questions & Answers Review</h3>
            ${questionsHtml}
        </div>
    `;
}

function getAnswerText(question, answerKey) {
    if (!answerKey) return 'No answer';

    switch(answerKey) {
        case 'A': return question.option_a || 'N/A';
        case 'B': return question.option_b || 'N/A';
        case 'C': return question.option_c || 'N/A';
        case 'D': return question.option_d || 'N/A';
        default: return answerKey;
    }
}

function formatTime(seconds) {
    if (!seconds) return 'N/A';

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours}h ${minutes}m ${secs}s`;
    } else if (minutes > 0) {
        return `${minutes}m ${secs}s`;
    } else {
        return `${secs}s`;
    }
}

function closeAttemptModal() {
    document.getElementById('attemptModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('attemptModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAttemptModal();
    }
});
</script>
@endsection
