@extends('layouts.user')

@section('page-title', 'My Quizzes')

@section('content')
@php
    $totalQuizzes = $assignedQuizzes->count();
    $completedQuizzes = $assignedQuizzes->filter(fn ($a) => $a->is_completed)->count();
    $inProgressQuizzes = $assignedQuizzes->filter(fn ($a) => ! $a->is_completed && in_array($a->status, ['in_progress', 'cancelled'], true))->count();
    $notStartedQuizzes = $assignedQuizzes->filter(fn ($a) => ! $a->is_completed && ! in_array($a->status, ['in_progress', 'cancelled'], true))->count();
@endphp

<x-user-page-shell fluid class="pb-6 -mx-3 px-3 sm:-mx-4 sm:px-4 lg:-mx-6 lg:px-6">
    <x-user-page-header title="My Quizzes" subtitle="View and take your assigned quizzes.">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </x-slot:icon>
        <x-slot:actions>
            <a href="{{ route('user.quizzes.enter-code') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                Enter quiz code
            </a>
        </x-slot:actions>
    </x-user-page-header>

    <x-user-dashboard-section title="Your assignments" description="Track progress and open quizzes assigned to you.">
        @if($assignedQuizzes->count() > 0)
            <x-user-kpi-grid columns="grid-cols-2 divide-y sm:grid-cols-4 sm:divide-x sm:divide-y-0">
                <x-user-kpi-cell label="Total" :value="$totalQuizzes" />
                <x-user-kpi-cell label="In progress" :value="$inProgressQuizzes" :value-class="$inProgressQuizzes > 0 ? 'text-amber-600' : 'text-slate-900'" />
                <x-user-kpi-cell label="Completed" :value="$completedQuizzes" value-class="text-emerald-700" />
                <x-user-kpi-cell label="Not started" :value="$notStartedQuizzes" class="col-span-2 sm:col-span-1" />
            </x-user-kpi-grid>

            <div class="flex flex-col gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">Search by quiz title or description.</p>
                <label class="sr-only" for="quiz-search-input">Search quizzes</label>
                <div class="relative w-full sm:max-w-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="search"
                           id="quiz-search-input"
                           placeholder="Search assignments…"
                           class="block w-full rounded-lg border-slate-300 py-2 pl-9 pr-3 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <x-user-panel title="Quiz list" subtitle="Take, resume, or view results." :padding="false" embedded class="overflow-hidden border-0 shadow-none ring-0">
                <x-responsive-data-panel class="rounded-lg border border-slate-200/90 shadow-none">
                    <x-slot:mobile>
                        @foreach($assignedQuizzes as $assignment)
                            <div class="mobile-card quiz-assignment-row" data-search="{{ strtolower($assignment->quiz->title.' '.($assignment->quiz->description ?? '')) }}">
                                <div class="mobile-card-row">
                                    <div class="min-w-0 flex-1">
                                        <p class="mobile-card-title">{{ $assignment->quiz->title }}</p>
                                        @if($assignment->quiz->description)
                                            <p class="mobile-card-subtitle">{{ Str::limit($assignment->quiz->description, 80) }}</p>
                                        @endif
                                        <div class="mobile-card-meta">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $assignment->getStatusBadgeClass() }}">
                                                {{ $assignment->getStatusText() }}
                                            </span>
                                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                                {{ $assignment->quiz->total_questions }} questions
                                            </span>
                                            @if($assignment->quiz->time_limit)
                                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                                    {{ $assignment->quiz->time_limit }} min
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="mobile-card-actions">
                                    @if(! $assignment->is_completed)
                                        <a href="{{ url('/quizzes/' . $assignment->quiz->id . '/take') }}"
                                           class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 touch-manipulation">
                                            {{ in_array($assignment->status, ['in_progress', 'cancelled'], true) ? 'Resume quiz' : 'Take quiz' }}
                                        </a>
                                    @else
                                        <a href="{{ url('/quizzes/' . $assignment->quiz->id . '/result') }}"
                                           class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700 touch-manipulation">
                                            View result
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </x-slot:mobile>

                    <x-slot:desktop>
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/95">
                                <tr>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Quiz</th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Questions</th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Time limit</th>
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                    <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($assignedQuizzes as $assignment)
                                    <tr class="quiz-assignment-row transition-colors hover:bg-slate-50/80" data-search="{{ strtolower($assignment->quiz->title.' '.($assignment->quiz->description ?? '')) }}">
                                        <td class="px-5 py-4">
                                            <div class="text-sm font-semibold text-slate-900">{{ $assignment->quiz->title }}</div>
                                            @if($assignment->quiz->description)
                                                <div class="mt-0.5 max-w-lg truncate text-sm text-slate-500">{{ Str::limit($assignment->quiz->description, 72) }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-sm tabular-nums text-slate-600">
                                            {{ $assignment->quiz->total_questions }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                            {{ $assignment->quiz->time_limit ? $assignment->quiz->time_limit.' min' : 'No limit' }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $assignment->getStatusBadgeClass() }}">
                                                {{ $assignment->getStatusText() }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                            @if(! $assignment->is_completed)
                                                <a href="{{ url('/quizzes/' . $assignment->quiz->id . '/take') }}"
                                                   class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-indigo-700">
                                                    {{ in_array($assignment->status, ['in_progress', 'cancelled'], true) ? 'Resume' : 'Take quiz' }}
                                                </a>
                                            @else
                                                <a href="{{ url('/quizzes/' . $assignment->quiz->id . '/result') }}"
                                                   class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-800 hover:bg-emerald-100">
                                                    View result
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-slot:desktop>
                </x-responsive-data-panel>
            </x-user-panel>
        @else
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-14 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200">
                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-semibold text-slate-900">No quizzes assigned</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-600">
                    @if(auth()->user()->isApplicant())
                        Internship quizzes will appear here after an administrator assigns them.
                    @else
                        You do not have any active quiz assignments yet. Use a quiz code if your instructor shared one.
                    @endif
                </p>
                @if(! auth()->user()->isApplicant())
                    <a href="{{ route('user.quizzes.enter-code') }}"
                       class="mt-6 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Enter quiz code
                    </a>
                @endif
            </div>
        @endif
    </x-user-dashboard-section>
</x-user-page-shell>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('quiz-search-input');
    const rows = document.querySelectorAll('.quiz-assignment-row');
    if (!input || !rows.length) {
        return;
    }
    input.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        rows.forEach(function (row) {
            const haystack = row.getAttribute('data-search') || '';
            row.classList.toggle('hidden', term !== '' && !haystack.includes(term));
        });
    });
});
</script>
@endsection
