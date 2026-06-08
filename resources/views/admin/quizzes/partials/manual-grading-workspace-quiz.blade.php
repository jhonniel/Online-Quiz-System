@if($selectedQuizGroup && $selectedUserId)
    @php
        $quiz = $selectedQuizGroup['quiz'];
        $quizTitle = $quiz->title ?? 'Quiz';
        $studentGroup = $selectedQuizGroup['students']->first(fn ($g) => (int) $g['user']->id === (int) $selectedUserId);
        $student = $studentGroup['user'] ?? null;
        $studentName = $student->name ?? 'Unknown';
        $pendingLeft = (int) ($studentGroup['pending_count'] ?? $gradingAttempts->whereNull('graded_at')->count());
        $totalAttempts = (int) ($gradingAttemptGroups ?? collect())->count();
    @endphp
    @if($student)
        <div class="flex-1 flex flex-col min-h-0 p-4 sm:p-6 lg:p-8 max-w-6xl mx-auto w-full"
             data-quiz-grading-panel
             data-user-id="{{ (int) $student->id }}"
             data-quiz-id="{{ (int) $quiz->id }}">
            <nav class="mg-grading-breadcrumb shrink-0 flex flex-wrap items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm ring-1 ring-gray-900/5">
                <button type="button" data-mg-action="back-to-quizzes" class="text-sm font-medium text-gray-500 hover:text-blue-600">Quizzes</button>
                <span class="text-gray-300">/</span>
                <button type="button" data-mg-action="back-to-students" class="text-sm font-medium text-blue-600 hover:text-blue-800 truncate max-w-[10rem]">{{ $quizTitle }}</button>
                <span class="text-gray-300">/</span>
                <span class="text-sm font-bold text-gray-900">{{ $studentName }}</span>
                <span class="ml-auto inline-flex items-center gap-2">
                    @if($totalAttempts > 0)
                        <span class="text-xs font-semibold text-slate-700 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">{{ $totalAttempts }} {{ $totalAttempts === 1 ? 'attempt' : 'attempts' }}</span>
                    @endif
                    <span class="text-xs font-semibold text-white bg-indigo-600 px-2.5 py-1 rounded-full">{{ $pendingLeft }} to grade</span>
                </span>
            </nav>

            <div class="flex-1 min-h-0 overflow-y-auto mg-workspace-scroll space-y-6 pt-4 mt-1" data-grading-attempts>
                @forelse(($gradingAttemptGroups ?? collect()) as $attemptNumber => $attemptGroup)
                    <section class="space-y-4">
                        <div class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5">
                            <h3 class="text-sm font-bold text-gray-900">Attempt #{{ $attemptNumber }}</h3>
                            @php
                                $groupPending = $attemptGroup->whereNull('graded_at')->count();
                                $groupGraded = $attemptGroup->whereNotNull('graded_at')->count();
                            @endphp
                            <span class="text-xs text-gray-500">{{ $attemptGroup->count() }} {{ $attemptGroup->count() === 1 ? 'answer' : 'answers' }}</span>
                            @if($groupGraded > 0)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">{{ $groupGraded }} graded</span>
                            @endif
                            @if($groupPending > 0)
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">{{ $groupPending }} pending</span>
                            @endif
                        </div>
                        @foreach($attemptGroup as $attempt)
                            @include('admin.quizzes.partials.manual-grading-attempt-card', [
                                'attempt' => $attempt,
                                'compactHeader' => true,
                            ])
                        @endforeach
                    </section>
                @empty
                    <p class="text-sm text-gray-500 py-8 text-center">No manual grading answers found for this taker yet.</p>
                @endforelse
            </div>
        </div>
    @endif
@else
    <div class="flex-1 min-h-0 overflow-y-auto mg-workspace-scroll p-4 sm:p-6 lg:p-8">
        @if(!$selectedQuizId)
            <div class="h-full min-h-[18rem] flex flex-col items-center justify-center text-center px-6">
                <div class="rounded-2xl border-2 border-dashed border-gray-300 bg-white/60 px-8 py-12 max-w-lg w-full">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Select a quiz</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">Choose a quiz from the list to see which takers need grading.</p>
                </div>
            </div>
        @elseif($selectedQuizGroup && !$selectedUserId)
            @php
                $quiz = $selectedQuizGroup['quiz'];
                $quizTitle = $quiz->title ?? 'Quiz';
            @endphp
            <div class="max-w-6xl mx-auto w-full space-y-4 flex flex-col min-h-0" data-mg-step="student-picker">
                <nav class="shrink-0 flex flex-wrap items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm">
                    <button type="button" data-mg-action="back-to-quizzes" class="text-sm font-medium text-blue-600 hover:text-blue-800">← Quizzes</button>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $quizTitle }}</span>
                    <span class="ml-auto text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">Step 2 — pick a taker</span>
                </nav>

                @php
                    $workspaceStudents = $selectedQuizGroup['students']
                        ->filter(fn ($g) => ($g['total_count'] ?? 0) > 0 || ($g['pending_count'] ?? 0) > 0);
                @endphp
                <div class="flex-1 min-h-0 overflow-y-auto mg-sidebar-scroll pr-1 max-h-[min(60vh,36rem)] lg:max-h-[calc(100vh-18rem)]">
                    @if($workspaceStudents->isEmpty())
                        <p class="text-sm text-gray-500 py-8 text-center">No pending answers for this quiz.</p>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 pb-2">
                            @foreach($workspaceStudents as $g)
                                @php
                                    $u = $g['user'];
                                    $uName = $u->name ?? 'Unknown';
                                    $uPending = (int) ($g['pending_count'] ?? 0);
                                    $uTotal = (int) ($g['total_count'] ?? 0);
                                @endphp
                                <button type="button"
                                        data-mg-action="select-student"
                                        data-user-id="{{ (int) $u->id }}"
                                        class="group text-left rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-400 hover:shadow-md hover:ring-2 hover:ring-indigo-100 transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold group-hover:bg-indigo-600 group-hover:text-white transition-colors">{{ strtoupper(substr($uName, 0, 1)) }}</span>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <h3 class="text-sm font-bold text-gray-900 truncate">{{ $uName }}</h3>
                                                    <span class="shrink-0 inline-flex rounded px-1.5 py-0.5 text-[10px] font-semibold leading-none {{ $u->getRoleBadgeClass() }}">{{ $u->getRoleLabel() }}</span>
                                                </div>
                                                <p class="text-xs text-gray-500 truncate">{{ $u->email ?? '' }}</p>
                                            </div>
                                        </div>
                                        <span class="shrink-0 flex flex-col items-end gap-1">
                                            @if($uPending > 0)
                                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">{{ $uPending }} to grade</span>
                                            @endif
                                            @if($uTotal > 0)
                                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">{{ $uTotal }} total</span>
                                            @endif
                                        </span>
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
