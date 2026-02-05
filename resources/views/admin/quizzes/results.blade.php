@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                Quiz Results: {{ $quiz->title }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                View detailed results and performance analytics for this quiz
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('quizzes.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Quizzes
            </a>
        </div>
    </div>

    <!-- Quiz Information -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quiz Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Quiz Code</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $quiz->quiz_code }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Time Limit</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $quiz->time_limit ? $quiz->time_limit . ' minutes' : 'No limit' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total Questions</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $quiz->total_questions }}</dd>
                </div>
            </div>
            @if($quiz->description)
                <div class="mt-4">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $quiz->description }}</dd>
                </div>
            @endif
        </div>
    </div>

    <!-- Results Summary -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Attempts</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $attempts->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Unique Students</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $attempts->keys()->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Average Score</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                {{ $attempts->count() > 0 ? number_format($attempts->flatten()->avg('points_earned'), 2) : '0' }} pts
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Highest Score</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                {{ $attempts->count() > 0 ? $attempts->flatten()->max('points_earned') : '0' }} pts
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Results -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Student Results</h3>

            @if($attempts->count() > 0)
                <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">University</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Best Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Attempt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($attempts as $userId => $userAttempts)
                                @php
                                    $user = $userAttempts->first()->user;
                                    $bestScore = $userAttempts->max('points_earned');
                                    $totalScore = $userAttempts->sum('points_earned');
                                    $attemptCount = $userAttempts->count();
                                    $lastAttempt = $userAttempts->sortByDesc('created_at')->first();
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                @if($user->profile_picture)
                                                    <img class="h-8 w-8 rounded-full" src="{{ Storage::url($user->profile_picture) }}" alt="{{ $user->name }}">
                                                @else
                                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ substr($user->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $user->university->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bestScore >= 80 ? 'bg-green-100 text-green-800' : ($bestScore >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $bestScore }} pts
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $attemptCount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $bestScore }} pts
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $lastAttempt->created_at->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button
                                                type="button"
                                                onclick="showStudentDetails(
                                                    {{ $userId }},
                                                    @js($user->name),
                                                    @js($user->email),
                                                    {{ $attemptCount }},
                                                    {{ $bestScore }},
                                                    @js($lastAttempt->created_at->format('M j, Y g:i A'))
                                                )"
                                                class="text-indigo-600 hover:text-indigo-900 cursor-pointer bg-transparent border-0 p-0">
                                                View Details
                                            </button>
                                            <span class="text-gray-300">|</span>
                                            <a href="{{ route('admin.quizzes.user-history', ['quizId' => $quiz->id, 'userId' => $userId]) }}"
                                               class="text-green-600 hover:text-green-800 font-medium"
                                               style="color: #16a34a !important; text-decoration: none !important; display: inline-block !important;">
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No attempts yet</h3>
                    <p class="mt-1 text-sm text-gray-500">This quiz hasn't been taken by any students yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Student Details Modal -->
<div id="studentDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Student Quiz Details</h3>
                <button onclick="closeStudentDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="studentDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showStudentDetails(userId, name, email, attemptCount, bestScore, lastAttemptAt) {
    const modal = document.getElementById('studentDetailsModal');
    const content = document.getElementById('studentDetailsContent');

    modal.classList.remove('hidden');

    // Simple summary details for now
    content.innerHTML = `
        <div class="space-y-4">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-sm font-semibold text-indigo-700">
                        ${name ? name.charAt(0).toUpperCase() : '?'}
                    </span>
                </div>
                <div>
                    <div class="text-base font-semibold text-gray-900">${name || 'Student'}</div>
                    <div class="text-sm text-gray-500">${email || ''}</div>
                    <div class="text-xs text-gray-400 mt-1">Student ID: ${userId}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Best Score</div>
                    <div class="mt-1 text-xl font-bold text-gray-900">${bestScore ?? 0} pts</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Attempts</div>
                    <div class="mt-1 text-xl font-bold text-gray-900">${attemptCount ?? 0}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Attempt</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">${lastAttemptAt || 'N/A'}</div>
                </div>
            </div>

            <div class="text-xs text-gray-500">
                For full question-by-question history, use the <span class="font-semibold">View History</span> link in the table.
            </div>
        </div>
    `;
}

function closeStudentDetailsModal() {
    document.getElementById('studentDetailsModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('studentDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeStudentDetailsModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStudentDetailsModal();
    }
});
</script>
@endsection
