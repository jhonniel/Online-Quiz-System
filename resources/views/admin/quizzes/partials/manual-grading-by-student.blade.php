<div class="flex flex-col lg:flex-row flex-1 min-h-0 min-h-[32rem] lg:min-h-[calc(100vh-14rem)]"
     id="manual-grading-by-student"
     x-data="{
         selectedUserId: {{ $selectedUserId ?? 'null' }},
         selectedQuizId: {{ $selectedQuizId ?? 'null' }},
         studentSearch: '',
         selectStudent(userId) {
             this.selectedUserId = userId;
             this.selectedQuizId = null;
             this.syncUrl();
         },
         selectQuiz(quizId) {
             this.selectedQuizId = quizId;
             this.syncUrl();
         },
         backToStudents() {
             this.selectedUserId = null;
             this.selectedQuizId = null;
             this.syncUrl();
         },
         backToQuizzes() {
             this.selectedQuizId = null;
             this.syncUrl();
         },
         syncUrl() {
             const params = new URLSearchParams(window.location.search);
             params.set('view', 'student');
             if (this.selectedUserId) params.set('user_id', this.selectedUserId);
             else params.delete('user_id');
             if (this.selectedQuizId) params.set('quiz_id', this.selectedQuizId);
             else params.delete('quiz_id');
             history.replaceState(null, '', params.toString() ? '?' + params.toString() : '{{ url('/admin/manual-grading') }}?view=student');
         },
         studentVisible(name, email, quizTitles) {
             const q = this.studentSearch.trim().toLowerCase();
             if (!q) return true;
             return (name + ' ' + email + ' ' + quizTitles).toLowerCase().includes(q);
         }
     }"
     @manual-grading-quiz-complete.window="backToQuizzes()">

    {{-- Sidebar: students --}}
    <aside class="w-full lg:w-80 xl:w-96 shrink-0 flex flex-col border-b lg:border-b-0 lg:border-r border-gray-200 bg-slate-50/90">
        <div class="shrink-0 px-4 py-4 border-b border-gray-200 bg-white/80">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Students</h2>
                    <p class="text-xs text-gray-500">Step 1 — select who to grade</p>
                </div>
            </div>
            <div class="relative mt-3">
                <input type="search"
                       x-model="studentSearch"
                       placeholder="Search by name or email…"
                       class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto mg-sidebar-scroll p-3 space-y-2 min-h-[12rem] lg:min-h-0">
            @foreach($groupsByStudent as $studentGroup)
                @php
                    $student = $studentGroup['user'];
                    $studentName = $student->name ?? 'Unknown';
                    $quizTitles = $studentGroup['quizzes']->map(fn ($g) => $g['quiz']->title ?? '')->implode(' ');
                @endphp
                <button type="button"
                        @click="selectStudent({{ (int) $student->id }})"
                        x-show="studentVisible(@js($studentName), @js($student->email ?? ''), @js($quizTitles))"
                        :class="selectedUserId === {{ (int) $student->id }}
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-md ring-2 ring-indigo-200'
                            : 'bg-white text-gray-900 border-gray-200 hover:border-indigo-300 hover:shadow-sm'"
                        class="w-full text-left rounded-xl border p-3 transition-all duration-150">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                              :class="selectedUserId === {{ (int) $student->id }} ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-700'">
                            {{ strtoupper(substr($studentName, 0, 1)) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold truncate">{{ $studentName }}</p>
                            <p class="text-xs truncate opacity-80">{{ $student->email ?? '' }}</p>
                            <p class="text-xs mt-0.5 opacity-70">{{ $studentGroup['quizzes']->count() }} {{ $studentGroup['quizzes']->count() === 1 ? 'quiz' : 'quizzes' }}</p>
                        </div>
                        <span class="shrink-0 min-w-[1.75rem] text-center rounded-full px-2 py-0.5 text-xs font-bold tabular-nums"
                              :class="selectedUserId === {{ (int) $student->id }} ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-900'"
                              data-pending-badge="student-{{ (int) $student->id }}">
                            {{ $studentGroup['pending_count'] }}
                        </span>
                    </div>
                </button>
            @endforeach
        </div>
    </aside>

    {{-- Main workspace --}}
    <div class="flex-1 flex flex-col min-w-0 min-h-[20rem] lg:min-h-0 bg-gradient-to-br from-slate-50 to-gray-100/80">
        <div class="flex-1 overflow-y-auto mg-workspace-scroll p-4 sm:p-6 lg:p-8">

            <div x-show="!selectedUserId" x-cloak class="h-full min-h-[18rem] flex flex-col items-center justify-center text-center px-6">
                <div class="rounded-2xl border-2 border-dashed border-gray-300 bg-white/60 px-8 py-12 max-w-lg w-full">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Select a student</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">Choose someone from the list on the left to see which quizzes need manual grading.</p>
                </div>
            </div>

            @foreach($groupsByStudent as $studentGroup)
                @php
                    $student = $studentGroup['user'];
                    $studentName = $student->name ?? 'Unknown';
                @endphp

                {{-- Step 2: Quiz picker --}}
                <div x-show="selectedUserId === {{ (int) $student->id }} && !selectedQuizId"
                     x-cloak
                     class="max-w-6xl mx-auto w-full space-y-6">
                    <nav class="flex flex-wrap items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm">
                        <button type="button" @click="backToStudents()" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← Students</button>
                        <span class="text-gray-300">/</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $studentName }}</span>
                        <span class="ml-auto text-xs font-medium text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full">Step 2 — pick a quiz</span>
                    </nav>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($studentGroup['quizzes'] as $quizGroup)
                            @php $quiz = $quizGroup['quiz']; @endphp
                            <button type="button"
                                    @click="selectQuiz({{ (int) $quiz->id }})"
                                    class="group text-left rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-400 hover:shadow-md hover:ring-2 hover:ring-indigo-100 transition-all duration-150">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        </div>
                                        <h3 class="mt-3 text-base font-bold text-gray-900 group-hover:text-indigo-700">{{ $quiz->title ?? 'Quiz' }}</h3>
                                        <p class="mt-1 text-xs text-gray-500">Grade all pending questions for this quiz</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800"
                                          data-pending-badge="quiz-{{ (int) $student->id }}-{{ (int) $quiz->id }}">
                                        {{ $quizGroup['pending_count'] }}
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Step 3: Grading --}}
                @foreach($studentGroup['quizzes'] as $quizGroup)
                    @php $quiz = $quizGroup['quiz']; @endphp
                    <div x-show="selectedUserId === {{ (int) $student->id }} && selectedQuizId === {{ (int) $quiz->id }}"
                         x-cloak
                         class="max-w-6xl mx-auto w-full space-y-5"
                         data-quiz-grading-panel
                         data-user-id="{{ (int) $student->id }}"
                         data-quiz-id="{{ (int) $quiz->id }}">
                        <nav class="sticky top-0 z-10 flex flex-wrap items-center gap-2 rounded-xl bg-white/95 backdrop-blur border border-gray-200 px-4 py-3 shadow-sm">
                            <button type="button" @click="backToStudents()" class="text-sm font-medium text-gray-500 hover:text-indigo-600">Students</button>
                            <span class="text-gray-300">/</span>
                            <button type="button" @click="backToQuizzes()" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ $studentName }}</button>
                            <span class="text-gray-300">/</span>
                            <span class="text-sm font-bold text-gray-900 truncate max-w-[12rem] sm:max-w-none">{{ $quiz->title ?? 'Quiz' }}</span>
                            <span class="ml-auto text-xs font-semibold text-white bg-indigo-600 px-2.5 py-1 rounded-full">
                                {{ $quizGroup['pending_count'] }} left
                            </span>
                        </nav>

                        <div class="space-y-4" data-grading-attempts>
                            @foreach($quizGroup['attempts'] as $attempt)
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
