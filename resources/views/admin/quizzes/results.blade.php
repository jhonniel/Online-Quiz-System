@extends('layouts.admin')

@section('page-title')
    Quiz Results
@endsection

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ url('/admin/quizzes') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Quizzes</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Results</span>
        </div>
    </li>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-5 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-white truncate">{{ $quiz->title }}</h1>
                <p class="mt-1 text-sm text-indigo-100">Quiz results and student performance</p>
                <div class="mt-3 flex flex-wrap gap-3 text-xs text-indigo-100">
                    <span class="inline-flex items-center gap-1 rounded-md bg-white/10 px-2.5 py-1 font-mono">{{ $quiz->quiz_code }}</span>
                    @if($quiz->time_limit)
                        <span class="inline-flex items-center gap-1 rounded-md bg-white/10 px-2.5 py-1">{{ $quiz->time_limit }} min limit</span>
                    @endif
                    <span class="inline-flex items-center gap-1 rounded-md bg-white/10 px-2.5 py-1">{{ $quiz->total_questions }} questions</span>
                    <span class="inline-flex items-center gap-1 rounded-md bg-white/10 px-2.5 py-1">{{ $maxPoints }} pts max</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('quizzes.show', $quiz) }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 transition-colors">
                    Quiz Details
                </a>
                <a href="{{ url('/admin/quizzes') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-white border border-white/40 hover:bg-white/10 transition-colors">
                    Back to Quizzes
                </a>
            </div>
        </div>
    </div>

    @if($quiz->description)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-5">
            <p class="text-sm text-gray-600">{{ $quiz->description }}</p>
        </div>
    @endif

    {{-- Summary stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Students</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $summary['total_students'] }}</dd>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Avg. Best Score</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-900">
                {{ $summary['average_best_score'] }}<span class="text-base font-normal text-gray-500"> / {{ $maxPoints }}</span>
            </dd>
            <p class="text-xs text-gray-500 mt-0.5">{{ $summary['average_best_percent'] }}%</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Highest Score</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-900">
                {{ $summary['highest_score'] }}<span class="text-base font-normal text-gray-500"> / {{ $maxPoints }}</span>
            </dd>
            <p class="text-xs text-gray-500 mt-0.5">{{ $summary['highest_percent'] }}%</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pending Review</dt>
            <dd class="mt-1 text-2xl font-semibold {{ $summary['pending_review_count'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                {{ $summary['pending_review_count'] }}
            </dd>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 flex flex-col justify-center">
            <a href="{{ url('/admin/manual-grading?quiz_id=' . $quiz->id) }}"
               class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                Manual Grading
            </a>
        </div>
    </div>

    {{-- Student results table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Student Results</h2>
            <p class="mt-1 text-sm text-gray-500">Scores include manual grading. Percentages are based on {{ $maxPoints }} total points.</p>
        </div>

        @if($studentResults->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">University</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Latest Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Best Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Attempt</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($studentResults as $row)
                            @php
                                $user = $row['user'];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-9 w-9">
                                            @if($user->profile_picture)
                                                <img class="h-9 w-9 rounded-full object-cover" src="{{ Storage::url($user->profile_picture) }}" alt="{{ $user->name }}">
                                            @else
                                                <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <span class="text-sm font-semibold text-indigo-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-3 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500 truncate">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $user->university->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $row['latest_percent'] >= 80 ? 'bg-green-100 text-green-800' : ($row['latest_percent'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $row['latest_score'] }} / {{ $maxPoints }} ({{ $row['latest_percent'] }}%)
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="font-semibold">{{ $row['best_score'] }}</span>
                                    <span class="text-gray-500">/ {{ $maxPoints }} ({{ $row['best_percent'] }}%)</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                    {{ $row['attempt_count'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($row['has_pending_manual'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            Pending review
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Graded
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $row['last_attempt_at'] ? \Carbon\Carbon::parse($row['last_attempt_at'])->format('M j, Y g:i A') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button
                                            type="button"
                                            onclick="showStudentDetails(
                                                {{ $user->id }},
                                                @js($user->name),
                                                @js($user->email),
                                                {{ $row['attempt_count'] }},
                                                {{ $row['best_score'] }},
                                                {{ $maxPoints }},
                                                {{ $row['best_percent'] }},
                                                {{ $row['latest_score'] }},
                                                {{ $row['latest_percent'] }},
                                                @js($row['has_pending_manual'] ? 'Pending review' : 'Graded'),
                                                @js($row['last_attempt_at'] ? \Carbon\Carbon::parse($row['last_attempt_at'])->format('M j, Y g:i A') : 'N/A')
                                            )"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200">
                                            Details
                                        </button>
                                        <a href="{{ url('admin/quizzes/' . $quiz->id . '/users/' . $user->id . '/history') }}"
                                           class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                            History
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-16 px-4">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-3 text-sm font-medium text-gray-900">No attempts yet</h3>
                <p class="mt-1 text-sm text-gray-500">Students have not completed this quiz.</p>
            </div>
        @endif
    </div>
</div>

{{-- Student details modal --}}
<div id="studentDetailsModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="fixed inset-0 bg-gray-900/50" onclick="closeStudentDetailsModal()"></div>
    <div class="fixed inset-0 flex items-start justify-center p-4 sm:p-6 overflow-y-auto">
        <div class="relative w-full max-w-lg bg-white rounded-lg shadow-xl mt-16 sm:mt-24">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Student Summary</h3>
                <button type="button" onclick="closeStudentDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="studentDetailsContent" class="px-5 py-4"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
function showStudentDetails(userId, name, email, attemptCount, bestScore, maxPoints, bestPercent, latestScore, latestPercent, status, lastAttemptAt) {
    document.getElementById('studentDetailsModal').classList.remove('hidden');
    document.getElementById('studentDetailsContent').innerHTML = `
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                    <span class="text-sm font-semibold text-indigo-700">${name ? name.charAt(0).toUpperCase() : '?'}</span>
                </div>
                <div class="min-w-0">
                    <div class="text-base font-semibold text-gray-900">${name || 'Student'}</div>
                    <div class="text-sm text-gray-500 truncate">${email || ''}</div>
                    <div class="text-xs text-gray-400 mt-0.5">ID: ${userId}</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-xs font-medium text-gray-500 uppercase">Latest</div>
                    <div class="mt-1 text-lg font-bold text-gray-900">${latestScore} / ${maxPoints}</div>
                    <div class="text-xs text-gray-500">${latestPercent}%</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-xs font-medium text-gray-500 uppercase">Best</div>
                    <div class="mt-1 text-lg font-bold text-gray-900">${bestScore} / ${maxPoints}</div>
                    <div class="text-xs text-gray-500">${bestPercent}%</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-xs font-medium text-gray-500 uppercase">Attempts</div>
                    <div class="mt-1 text-lg font-bold text-gray-900">${attemptCount}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-xs font-medium text-gray-500 uppercase">Status</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">${status}</div>
                </div>
            </div>
            <div class="text-sm text-gray-600">
                <span class="font-medium text-gray-700">Last attempt:</span> ${lastAttemptAt}
            </div>
            <p class="text-xs text-gray-500">Use <strong>History</strong> for question-by-question breakdown.</p>
        </div>
    `;
}

function closeStudentDetailsModal() {
    document.getElementById('studentDetailsModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStudentDetailsModal();
    }
});
</script>
@endverbatim
@endpush
