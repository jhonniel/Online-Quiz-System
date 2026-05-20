<div class="flex flex-col lg:flex-row flex-1 min-h-0 min-h-[32rem] lg:min-h-[calc(100vh-14rem)]"
     id="manual-grading-by-quiz"
     x-data="{
         selectedQuizId: {{ $selectedQuizId ?? 'null' }},
         selectedUserId: {{ $selectedUserId ?? 'null' }},
         quizSearch: '',
         selectQuiz(quizId) {
             this.selectedQuizId = quizId;
             this.selectedUserId = null;
             this.syncUrl();
         },
         selectStudent(userId) {
             this.selectedUserId = userId;
             this.syncUrl();
         },
         backToQuizzes() {
             this.selectedQuizId = null;
             this.selectedUserId = null;
             this.syncUrl();
         },
         backToStudents() {
             this.selectedUserId = null;
             this.syncUrl();
         },
         syncUrl() {
             const params = new URLSearchParams(window.location.search);
             params.set('view', 'quiz');
             if (this.selectedQuizId) params.set('quiz_id', this.selectedQuizId);
             else params.delete('quiz_id');
             if (this.selectedUserId) params.set('user_id', this.selectedUserId);
             else params.delete('user_id');
             history.replaceState(null, '', params.toString() ? '?' + params.toString() : '{{ url('/admin/manual-grading') }}?view=quiz');
         },
         quizVisible(title, studentNames) {
             const q = this.quizSearch.trim().toLowerCase();
             if (!q) return true;
             return (title + ' ' + studentNames).toLowerCase().includes(q);
         }
     }"
     @manual-grading-quiz-complete.window="backToStudents()">

    <aside class="w-full lg:w-80 xl:w-96 shrink-0 flex flex-col border-b lg:border-b-0 lg:border-r border-gray-200 bg-slate-50/90">
        <div class="shrink-0 px-4 py-4 border-b border-gray-200 bg-white/80">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Quizzes</h2>
                    <p class="text-xs text-gray-500">Step 1 — select a quiz</p>
                </div>
            </div>
            <div class="relative mt-3">
                <input type="search"
                       x-model="quizSearch"
                       placeholder="Search quizzes…"
                       class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto mg-sidebar-scroll p-3 space-y-2 min-h-[12rem] lg:min-h-0">
            @foreach($groupsByQuiz as $quizGroup)
                @php
                    $quiz = $quizGroup['quiz'];
                    $quizTitle = $quiz->title ?? 'Quiz';
                    $studentNames = $quizGroup['students']->map(fn ($g) => $g['user']->name ?? '')->implode(' ');
                @endphp
                <button type="button"
                        @click="selectQuiz({{ (int) $quiz->id }})"
                        x-show="quizVisible(@js($quizTitle), @js($studentNames))"
                        :class="selectedQuizId === {{ (int) $quiz->id }}
                            ? 'bg-blue-600 text-white border-blue-600 shadow-md ring-2 ring-blue-200'
                            : 'bg-white text-gray-900 border-gray-200 hover:border-blue-300 hover:shadow-sm'"
                        class="w-full text-left rounded-xl border p-3 transition-all duration-150">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold truncate">{{ $quizTitle }}</p>
                            <p class="text-xs truncate opacity-80">{{ $quizGroup['students']->count() }} {{ $quizGroup['students']->count() === 1 ? 'student' : 'students' }}</p>
                        </div>
                        <span class="shrink-0 min-w-[1.75rem] text-center rounded-full px-2 py-0.5 text-xs font-bold tabular-nums"
                              :class="selectedQuizId === {{ (int) $quiz->id }} ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-900'">
                            {{ $quizGroup['pending_count'] }}
                        </span>
                    </div>
                </button>
            @endforeach
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 min-h-[20rem] lg:min-h-0 bg-gradient-to-br from-slate-50 to-gray-100/80">
        <div class="flex-1 overflow-y-auto mg-workspace-scroll p-4 sm:p-6 lg:p-8">

            <div x-show="!selectedQuizId" x-cloak class="h-full min-h-[18rem] flex flex-col items-center justify-center text-center px-6">
                <div class="rounded-2xl border-2 border-dashed border-gray-300 bg-white/60 px-8 py-12 max-w-lg w-full">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Select a quiz</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">Choose a quiz from the list to see which students need grading.</p>
                </div>
            </div>

            @foreach($groupsByQuiz as $quizGroup)
                @php
                    $quiz = $quizGroup['quiz'];
                    $quizTitle = $quiz->title ?? 'Quiz';
                @endphp

                <div x-show="selectedQuizId === {{ (int) $quiz->id }} && !selectedUserId"
                     x-cloak
                     class="max-w-6xl mx-auto w-full space-y-6">
                    <nav class="flex flex-wrap items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm">
                        <button type="button" @click="backToQuizzes()" class="text-sm font-medium text-blue-600 hover:text-blue-800">← Quizzes</button>
                        <span class="text-gray-300">/</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $quizTitle }}</span>
                        <span class="ml-auto text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">Step 2 — pick a student</span>
                    </nav>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($quizGroup['students'] as $studentGroup)
                            @php
                                $student = $studentGroup['user'];
                                $studentName = $student->name ?? 'Unknown';
                            @endphp
                            <button type="button"
                                    @click="selectStudent({{ (int) $student->id }})"
                                    class="group text-left rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-400 hover:shadow-md hover:ring-2 hover:ring-indigo-100 transition-all">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                            {{ strtoupper(substr($studentName, 0, 1)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-bold text-gray-900 truncate">{{ $studentName }}</h3>
                                            <p class="text-xs text-gray-500 truncate">{{ $student->email ?? '' }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800">
                                        {{ $studentGroup['pending_count'] }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                @foreach($quizGroup['students'] as $studentGroup)
                    @php
                        $student = $studentGroup['user'];
                        $studentName = $student->name ?? 'Unknown';
                    @endphp
                    <div x-show="selectedQuizId === {{ (int) $quiz->id }} && selectedUserId === {{ (int) $student->id }}"
                         x-cloak
                         class="max-w-6xl mx-auto w-full space-y-5"
                         data-quiz-grading-panel
                         data-user-id="{{ (int) $student->id }}"
                         data-quiz-id="{{ (int) $quiz->id }}">
                        <nav class="sticky top-0 z-10 flex flex-wrap items-center gap-2 rounded-xl bg-white/95 backdrop-blur border border-gray-200 px-4 py-3 shadow-sm">
                            <button type="button" @click="backToQuizzes()" class="text-sm font-medium text-gray-500 hover:text-blue-600">Quizzes</button>
                            <span class="text-gray-300">/</span>
                            <button type="button" @click="backToStudents()" class="text-sm font-medium text-blue-600 hover:text-blue-800 truncate max-w-[10rem]">{{ $quizTitle }}</button>
                            <span class="text-gray-300">/</span>
                            <span class="text-sm font-bold text-gray-900">{{ $studentName }}</span>
                            <span class="ml-auto text-xs font-semibold text-white bg-indigo-600 px-2.5 py-1 rounded-full">
                                {{ $studentGroup['pending_count'] }} left
                            </span>
                        </nav>

                        <div class="space-y-4" data-grading-attempts>
                            @foreach($studentGroup['attempts'] as $attempt)
                                @include('admin.quizzes.partials.manual-grading-attempt-card', [
                                    'attempt' => $attempt,
                                    'compactHeader' => true,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</div>
