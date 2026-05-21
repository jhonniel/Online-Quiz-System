@if($selectedStudentGroup && $selectedQuizId)
    @php
        $student = $selectedStudentGroup['user'];
        $studentName = $student->name ?? 'Unknown';
        $quizGroup = $selectedStudentGroup['quizzes']->first(fn ($g) => (int) $g['quiz']->id === (int) $selectedQuizId);
        $quiz = $quizGroup['quiz'] ?? null;
        $pendingLeft = $quizGroup['pending_count'] ?? $gradingAttempts->count();
    @endphp
    @if($quiz)
        <div class="flex-1 flex flex-col min-h-0 p-4 sm:p-6 lg:p-8 max-w-6xl mx-auto w-full"
             data-quiz-grading-panel
             data-user-id="{{ (int) $student->id }}"
             data-quiz-id="{{ (int) $quiz->id }}">
            <nav class="mg-grading-breadcrumb shrink-0 flex flex-wrap items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm ring-1 ring-gray-900/5">
                <button type="button" data-mg-action="back-to-students" class="text-sm font-medium text-gray-500 hover:text-indigo-600">Takers</button>
                <span class="text-gray-300">/</span>
                <button type="button" data-mg-action="back-to-quizzes" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ $studentName }}</button>
                <span class="text-gray-300">/</span>
                <span class="text-sm font-bold text-gray-900 truncate max-w-[12rem] sm:max-w-none">{{ $quiz->title ?? 'Quiz' }}</span>
                <span class="ml-auto text-xs font-semibold text-white bg-indigo-600 px-2.5 py-1 rounded-full">
                    {{ $pendingLeft }} left
                </span>
            </nav>

            <div class="flex-1 min-h-0 overflow-y-auto mg-workspace-scroll space-y-4 pt-4 mt-1" data-grading-attempts>
                @foreach($gradingAttempts as $attempt)
                    @include('admin.quizzes.partials.manual-grading-attempt-card', [
                        'attempt' => $attempt,
                        'compactHeader' => true,
                    ])
                @endforeach
            </div>
        </div>
    @endif
@else
    <div class="flex-1 min-h-0 overflow-y-auto mg-workspace-scroll p-4 sm:p-6 lg:p-8">
        @if(!$selectedUserId)
            <div class="h-full min-h-[18rem] flex flex-col items-center justify-center text-center px-6">
                <div class="rounded-2xl border-2 border-dashed border-gray-300 bg-white/60 px-8 py-12 max-w-lg w-full">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Select a taker</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">Choose a taker from the list on the left to see which quizzes need manual grading.</p>
                </div>
            </div>
        @elseif($selectedStudentGroup && !$selectedQuizId)
            @php
                $student = $selectedStudentGroup['user'];
                $studentName = $student->name ?? 'Unknown';
                $takerTaken = (int) ($selectedStudentGroup['quizzes_taken'] ?? 0);
                $takerAssigned = (int) ($selectedStudentGroup['quizzes_assigned'] ?? 0);
            @endphp
            <div class="max-w-6xl mx-auto w-full space-y-4 flex flex-col min-h-0 max-h-full" data-mg-step="quiz-picker">
                <nav class="shrink-0 flex flex-wrap items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm">
                    <button type="button" data-mg-action="back-to-students" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← Takers</button>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $studentName }}</span>
                    <span class="ml-auto inline-flex items-center gap-2">
                        <span class="text-xs font-semibold tabular-nums text-slate-700 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">{{ $takerTaken }}/{{ $takerAssigned }} quizzes taken</span>
                        <span class="text-xs font-medium text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full">Step 2 — pick a quiz</span>
                    </span>
                </nav>

                @php
                    $workspaceQuizzes = $selectedStudentGroup['quizzes']
                        ->filter(fn ($g) => ($g['pending_count'] ?? 0) > 0);
                @endphp
                <div class="flex-1 min-h-0 overflow-y-auto mg-sidebar-scroll pr-1 -mr-1 max-h-[min(60vh,36rem)] lg:max-h-[calc(100vh-18rem)]">
                    @if($workspaceQuizzes->isEmpty())
                        <p class="text-sm text-gray-500 py-8 text-center">No pending answers for this taker.</p>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 pb-2">
                            @foreach($workspaceQuizzes as $g)
                                @php
                                    $q = $g['quiz'];
                                    $qTitle = $q->title ?? 'Quiz';
                                    $qPending = (int) ($g['pending_count'] ?? 0);
                                @endphp
                                <button type="button"
                                        data-mg-action="select-quiz"
                                        data-quiz-id="{{ (int) $q->id }}"
                                        class="group text-left rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-400 hover:shadow-md hover:ring-2 hover:ring-indigo-100 transition-all duration-150">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                            </div>
                                            <h3 class="mt-3 text-base font-bold text-gray-900 group-hover:text-indigo-700">{{ $qTitle }}</h3>
                                            <p class="mt-1 text-xs text-gray-500">Grade all pending questions for this quiz</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800"
                                              data-pending-badge="quiz-{{ (int) $student->id }}-{{ (int) $q->id }}">{{ $qPending }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endif
