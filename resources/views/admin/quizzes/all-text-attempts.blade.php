@extends('layouts.admin')

@section('page-title')
    Text and Written Answers
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
            <span class="ml-2 text-sm font-medium text-gray-500">Written Answers</span>
        </div>
    </li>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-lg shadow-sm p-5 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-white">Text &amp; Written Answers</h1>
                <p class="mt-1 text-sm text-violet-100">Review student text and fill-in-the-blank responses across all quizzes</p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ url('/admin/manual-grading') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-indigo-700 bg-white hover:bg-indigo-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Manual Grading Queue
                </a>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total responses</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total'] }}</dd>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pending review</dt>
            <dd class="mt-1 text-2xl font-semibold {{ $stats['pending'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $stats['pending'] }}</dd>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Graded</dt>
            <dd class="mt-1 text-2xl font-semibold text-emerald-600">{{ $stats['graded'] }}</dd>
        </div>
    </div>

    {{-- Table card --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">All submissions</h2>
                <p class="mt-1 text-sm text-gray-500">Filter by grading status. Grade pending items from the manual grading workflow.</p>
            </div>
            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1 text-sm font-medium shrink-0">
                <a href="{{ url('/admin/all-text-attempts?status=all') }}"
                   class="px-3 py-1.5 rounded-md transition-colors {{ $status === 'all' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-gray-200' : 'text-gray-600 hover:text-gray-900' }}">
                    All
                </a>
                <a href="{{ url('/admin/all-text-attempts?status=pending') }}"
                   class="px-3 py-1.5 rounded-md transition-colors {{ $status === 'pending' ? 'bg-white text-amber-700 shadow-sm ring-1 ring-gray-200' : 'text-gray-600 hover:text-gray-900' }}">
                    Pending
                    @if($stats['pending'] > 0)
                        <span class="ml-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full bg-amber-100 text-amber-800 text-xs font-semibold">{{ $stats['pending'] }}</span>
                    @endif
                </a>
                <a href="{{ url('/admin/all-text-attempts?status=graded') }}"
                   class="px-3 py-1.5 rounded-md transition-colors {{ $status === 'graded' ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-gray-200' : 'text-gray-600 hover:text-gray-900' }}">
                    Graded
                </a>
            </div>
        </div>

        @if($attempts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Question</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Answer</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Submitted</th>
                            <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($attempts as $attempt)
                            @php
                                $isText = $attempt->question->question_type === 'text';
                                $isGraded = (bool) $attempt->graded_at;
                            @endphp
                            <tr class="hover:bg-gray-50/80 transition-colors" id="row-attempt-{{ $attempt->id }}">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3 min-w-[180px]">
                                        @if($attempt->user->profile_picture)
                                            <img class="h-9 w-9 rounded-full object-cover ring-2 ring-white shadow-sm"
                                                 src="{{ Storage::url($attempt->user->profile_picture) }}"
                                                 alt="{{ $attempt->user->name }}">
                                        @else
                                            <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center ring-2 ring-white shadow-sm shrink-0">
                                                <span class="text-sm font-semibold text-indigo-700">{{ strtoupper(substr($attempt->user->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $attempt->user->name }}</div>
                                            <div class="text-xs text-gray-500 truncate">{{ $attempt->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-4">
                                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-800 ring-1 ring-inset ring-indigo-600/10 max-w-[160px] truncate" title="{{ $attempt->quiz->title }}">
                                        {{ $attempt->quiz->title }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 hidden lg:table-cell max-w-xs">
                                    <p class="text-sm text-gray-700 line-clamp-2" title="{{ $attempt->question->question_text }}">{{ $attempt->question->question_text }}</p>
                                    <span class="mt-1 inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium {{ $isText ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $isText ? 'Text' : 'Fill in blank' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 max-w-[220px]">
                                    <p class="text-sm text-gray-800 line-clamp-2 whitespace-pre-wrap" title="{{ $attempt->user_answer }}">{{ $attempt->user_answer }}</p>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    @if($isGraded)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Graded</span>
                                        <p class="mt-1 text-xs text-gray-500">{{ \Carbon\Carbon::parse($attempt->graded_at)->format('M j, Y g:i A') }}</p>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold {{ $attempt->points_earned > 0 ? 'text-emerald-700' : ($isGraded ? 'text-gray-700' : 'text-gray-400') }}">
                                        {{ $attempt->points_earned }}
                                    </span>
                                    <span class="text-sm text-gray-500">/ {{ $attempt->question->points }}</span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                    {{ $attempt->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        @php
                                            $modalReference = $attempt->question->question_type === 'text'
                                                ? $attempt->question->referenceAnswer()
                                                : (string) ($attempt->question->correct_answer ?? '');
                                        @endphp
                                        <button type="button"
                                                onclick="openAnswerModal(@js($attempt->user->name), @js($attempt->quiz->title), @js($attempt->question->question_text), @js($attempt->question->question_type), @js($attempt->user_answer), @js($modalReference ?: null), @js($attempt->question->points), {{ (int) $attempt->points_earned }}, {{ $isGraded ? 'true' : 'false' }}, @js($isGraded ? \Carbon\Carbon::parse($attempt->graded_at)->format('M j, Y g:i A') : null), @js($attempt->feedback))"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                            View
                                        </button>
                                        @if(!$isGraded)
                                            <a href="{{ url('/admin/manual-grading?view=student&user_id=' . $attempt->user_id . '&quiz_id=' . $attempt->quiz_id) }}"
                                               class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700">
                                                Grade
                                            </a>
                                        @else
                                            <a href="{{ url('/admin/quizzes/' . $attempt->quiz_id . '/users/' . $attempt->user_id . '/history') }}"
                                               class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200">
                                                History
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($attempts->hasPages())
                <div class="px-4 py-4 sm:px-6 border-t border-gray-200 bg-gray-50/50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <p class="text-sm text-gray-600">
                            Showing <span class="font-medium text-gray-900">{{ $attempts->firstItem() }}</span>–<span class="font-medium text-gray-900">{{ $attempts->lastItem() }}</span>
                            of <span class="font-medium text-gray-900">{{ $attempts->total() }}</span>
                        </p>
                        <div class="overflow-x-auto">
                            {{ $attempts->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-16 px-4">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                    <svg class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-semibold text-gray-900">
                    @if($status === 'pending')
                        No pending written answers
                    @elseif($status === 'graded')
                        No graded written answers yet
                    @else
                        No written answers found
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">
                    @if($status === 'pending')
                        All text and fill-in-the-blank responses have been reviewed.
                    @else
                        Student written responses will appear here once quizzes with text questions are submitted.
                    @endif
                </p>
                @if($status !== 'all')
                    <a href="{{ url('/admin/all-text-attempts') }}" class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        View all submissions
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Answer detail modal --}}
<div id="answerModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeAnswerModal()"></div>
    <div class="fixed inset-0 flex items-start justify-center p-4 sm:p-6 overflow-y-auto">
        <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-xl ring-1 ring-gray-900/5 mt-12 sm:mt-20">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Answer details</h3>
                    <p id="modalSubtitle" class="text-sm text-gray-500 mt-0.5"></p>
                </div>
                <button type="button" onclick="closeAnswerModal()" class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Question</p>
                    <p id="modalQuestion" class="text-sm text-gray-900 rounded-lg bg-gray-50 border border-gray-100 px-4 py-3 leading-relaxed"></p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Student answer</p>
                    <p id="modalAnswer" class="text-sm text-gray-900 rounded-lg bg-sky-50 border border-sky-200 px-4 py-3 leading-relaxed whitespace-pre-wrap"></p>
                </div>
                <div id="modalReferenceWrap">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Reference answer</p>
                    <p id="modalReference" class="text-sm text-gray-800 rounded-lg bg-emerald-50/80 border border-emerald-100 px-4 py-3"></p>
                </div>
                <div class="flex flex-wrap gap-3 pt-2 border-t border-gray-100">
                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                        <span class="text-xs text-gray-500">Score</span>
                        <p id="modalScore" class="text-sm font-semibold text-gray-900"></p>
                    </div>
                    <div class="rounded-lg bg-gray-50 px-3 py-2">
                        <span class="text-xs text-gray-500">Status</span>
                        <p id="modalStatus" class="text-sm font-semibold"></p>
                    </div>
                </div>
                <div id="modalFeedbackWrap" class="hidden">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Grader feedback</p>
                    <p id="modalFeedback" class="text-sm text-gray-800 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 whitespace-pre-wrap"></p>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 bg-gray-50/80 rounded-b-xl flex justify-end">
                <button type="button" onclick="closeAnswerModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
function openAnswerModal(studentName, quizTitle, questionText, questionType, userAnswer, referenceAnswer, maxPoints, pointsEarned, isGraded, gradedAt, feedback) {
    const typeLabel = questionType === 'text' ? 'Text question' : 'Fill in the blank';
    document.getElementById('modalSubtitle').textContent = studentName + ' · ' + quizTitle + ' · ' + typeLabel;
    document.getElementById('modalQuestion').textContent = questionText || '—';
    document.getElementById('modalAnswer').textContent = userAnswer || '—';

    const refWrap = document.getElementById('modalReferenceWrap');
    const refEl = document.getElementById('modalReference');
    refEl.classList.remove('text-amber-900');
    if (questionType === 'text') {
        refWrap.classList.remove('hidden');
        if (referenceAnswer) {
            refEl.textContent = referenceAnswer;
            refEl.classList.add('whitespace-pre-wrap');
        } else {
            refEl.textContent = 'No reference answers set — grade on accuracy and completeness.';
            refEl.classList.add('text-amber-900');
        }
    } else if (referenceAnswer) {
        refWrap.classList.remove('hidden');
        refEl.textContent = 'Acceptable: ' + referenceAnswer;
    } else {
        refWrap.classList.add('hidden');
    }

    document.getElementById('modalScore').textContent = pointsEarned + ' / ' + maxPoints + ' pts';
    const statusEl = document.getElementById('modalStatus');
    if (isGraded) {
        statusEl.textContent = 'Graded' + (gradedAt ? ' · ' + gradedAt : '');
        statusEl.className = 'text-sm font-semibold text-emerald-700';
    } else {
        statusEl.textContent = 'Pending review';
        statusEl.className = 'text-sm font-semibold text-amber-700';
    }

    const feedbackWrap = document.getElementById('modalFeedbackWrap');
    if (feedback) {
        feedbackWrap.classList.remove('hidden');
        document.getElementById('modalFeedback').textContent = feedback;
    } else {
        feedbackWrap.classList.add('hidden');
    }

    document.getElementById('answerModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeAnswerModal() {
    document.getElementById('answerModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    document.getElementById('modalReference').classList.remove('text-amber-900');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAnswerModal();
    }
});
</script>
@endverbatim
@endpush
