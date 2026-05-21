@php
    $mgBaseUrl = url('/admin/manual-grading');
    $mgQuizzes = collect($groupsByQuiz)->map(function ($g) {
        $title = $g['quiz']->title ?? 'Quiz';
        $latest = $g['latest_attempt_at'] ?? null;

        return [
            'id' => (int) $g['quiz']->id,
            'title' => $title,
            'pending' => (int) $g['pending_count'],
            'studentCount' => $g['students']->count(),
            'latestTs' => $latest ? \Illuminate\Support\Carbon::parse($latest)->timestamp : 0,
            'searchText' => strtolower($title.' '.$g['students']->map(fn ($s) => $s['user']->name ?? '')->implode(' ')),
        ];
    })->values();
    $mgStudentsForSelected = $selectedQuizGroup
        ? $selectedQuizGroup['students']
            ->filter(fn ($g) => ($g['pending_count'] ?? 0) > 0)
            ->map(function ($g) {
            $name = $g['user']->name ?? 'Unknown';
            $latest = $g['latest_attempt_at'] ?? null;

            return [
                'id' => (int) $g['user']->id,
                'name' => $name,
                'email' => $g['user']->email ?? '',
                'initial' => strtoupper(substr($name, 0, 1)),
                'pending' => (int) $g['pending_count'],
                'latestTs' => $latest ? \Illuminate\Support\Carbon::parse($latest)->timestamp : 0,
            ];
        })->values()
        : collect();
@endphp
<div class="flex flex-col lg:flex-row flex-1 min-h-0 h-full overflow-hidden"
     id="manual-grading-by-quiz"
     x-data="mgByQuiz()"
     @manual-grading-quiz-complete.window="backToStudents()">

    <aside class="mg-sidebar-panel w-full lg:w-80 xl:w-96 shrink-0 border-b lg:border-b-0 lg:border-r border-gray-200 bg-slate-50/90">
        <div class="shrink-0 px-4 py-4 border-b border-gray-200 bg-white/80">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-bold text-gray-900">Quizzes</h2>
                    <p class="text-xs text-gray-500">Step 1 — select a quiz</p>
                </div>
                <span class="shrink-0 text-xs font-medium text-gray-500 tabular-nums" x-text="filteredSortedQuizzes.length + ' shown'"></span>
            </div>
            <div class="relative mt-3">
                <input type="search"
                       x-model="quizSearch"
                       placeholder="Search quizzes…"
                       class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <label class="mt-2 block">
                <span class="sr-only">Sort quizzes</span>
                <select x-model="quizSort"
                        @change="saveQuizSort()"
                        class="w-full mt-1 text-xs border border-gray-300 rounded-lg bg-white py-2 pl-2 pr-8 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="latest_desc">Latest attempt (newest first)</option>
                    <option value="latest_asc">Latest attempt (oldest first)</option>
                    <option value="name_asc">Title A → Z</option>
                    <option value="name_desc">Title Z → A</option>
                    <option value="pending_desc">Pending high → low</option>
                    <option value="pending_asc">Pending low → high</option>
                </select>
            </label>
        </div>

        <div class="mg-student-list-panel mg-sidebar-scroll" role="list" aria-label="Quizzes pending manual grading">
            <div class="p-3 space-y-2 min-h-0">
            <template x-if="filteredSortedQuizzes.length === 0">
                <p class="text-center text-sm text-gray-500 py-8">No quizzes match your search.</p>
            </template>
            <template x-for="quiz in filteredSortedQuizzes" :key="quiz.id">
                <button type="button"
                        @click="selectQuiz(quiz.id)"
                        :class="selectedQuizId === quiz.id
                            ? 'bg-blue-600 text-white border-blue-600 shadow-md ring-2 ring-blue-200'
                            : 'bg-white text-gray-900 border-gray-200 hover:border-blue-300 hover:shadow-sm'"
                        class="w-full text-left rounded-xl border p-3 transition-all duration-150">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold truncate" x-text="quiz.title"></p>
                            <p class="text-xs truncate opacity-80" x-text="quiz.studentCount + (quiz.studentCount === 1 ? ' taker' : ' takers')"></p>
                        </div>
                        <span class="shrink-0 min-w-[1.75rem] text-center rounded-full px-2 py-0.5 text-xs font-bold tabular-nums"
                              :class="selectedQuizId === quiz.id ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-900'"
                              x-text="quiz.pending"></span>
                    </div>
                </button>
            </template>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden bg-gradient-to-br from-slate-50 to-gray-100/80">
            @if($selectedQuizGroup && $selectedUserId)
                @php
                    $quiz = $selectedQuizGroup['quiz'];
                    $quizTitle = $quiz->title ?? 'Quiz';
                    $studentGroup = $selectedQuizGroup['students']->first(fn ($g) => (int) $g['user']->id === (int) $selectedUserId);
                    $student = $studentGroup['user'] ?? null;
                    $studentName = $student->name ?? 'Unknown';
                    $pendingLeft = $studentGroup['pending_count'] ?? $gradingAttempts->count();
                @endphp
                @if($student)
                    <div class="flex-1 flex flex-col min-h-0 p-4 sm:p-6 lg:p-8 max-w-6xl mx-auto w-full"
                         data-quiz-grading-panel
                         data-user-id="{{ (int) $student->id }}"
                         data-quiz-id="{{ (int) $quiz->id }}">
                        <nav class="mg-grading-breadcrumb shrink-0 flex flex-wrap items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm ring-1 ring-gray-900/5">
                            <button type="button" @click="backToQuizzes()" class="text-sm font-medium text-gray-500 hover:text-blue-600">Quizzes</button>
                            <span class="text-gray-300">/</span>
                            <button type="button" @click="backToStudents()" class="text-sm font-medium text-blue-600 hover:text-blue-800 truncate max-w-[10rem]">{{ $quizTitle }}</button>
                            <span class="text-gray-300">/</span>
                            <span class="text-sm font-bold text-gray-900">{{ $studentName }}</span>
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
                <div class="max-w-6xl mx-auto w-full space-y-4 flex flex-col min-h-0">
                    <nav class="shrink-0 flex flex-wrap items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm">
                        <button type="button" @click="backToQuizzes()" class="text-sm font-medium text-blue-600 hover:text-blue-800">← Quizzes</button>
                        <span class="text-gray-300">/</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $quizTitle }}</span>
                        <span class="ml-auto text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">Step 2 — pick a taker</span>
                    </nav>

                    <div class="shrink-0 flex flex-wrap items-center gap-2">
                        <label class="text-xs font-medium text-gray-600">Sort takers:</label>
                        <select x-model="studentSort"
                                @change="saveStudentSort()"
                                class="text-xs border border-gray-300 rounded-lg bg-white py-1.5 pl-2 pr-7 focus:ring-2 focus:ring-indigo-500">
                            <option value="latest_desc">Latest attempt (newest)</option>
                            <option value="latest_asc">Latest attempt (oldest)</option>
                            <option value="name_asc">Name A → Z</option>
                            <option value="name_desc">Name Z → A</option>
                            <option value="pending_desc">Pending high → low</option>
                            <option value="pending_asc">Pending low → high</option>
                        </select>
                    </div>

                    <div class="flex-1 min-h-0 overflow-y-auto mg-sidebar-scroll pr-1 max-h-[min(60vh,36rem)] lg:max-h-[calc(100vh-18rem)]">
                        <template x-if="sortedStudents.length === 0">
                            <p class="text-sm text-gray-500 py-8 text-center">No pending answers for this quiz.</p>
                        </template>
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 pb-2">
                            <template x-for="student in sortedStudents" :key="student.id">
                                <button type="button"
                                        @click="selectStudent(student.id)"
                                        class="group text-left rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-400 hover:shadow-md hover:ring-2 hover:ring-indigo-100 transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold group-hover:bg-indigo-600 group-hover:text-white transition-colors"
                                                  x-text="student.initial"></span>
                                            <div class="min-w-0">
                                                <h3 class="text-sm font-bold text-gray-900 truncate" x-text="student.name"></h3>
                                                <p class="text-xs text-gray-500 truncate" x-text="student.email"></p>
                                            </div>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800" x-text="student.pending"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            @endif
        </div>
            @endif
    </div>
</div>

@push('scripts')
<script>
function mgByQuiz() {
    return {
        selectedQuizId: @json($selectedQuizId),
        selectedUserId: @json($selectedUserId),
        quizzes: @json($mgQuizzes),
        students: @json($mgStudentsForSelected),
        mgBaseUrl: @json($mgBaseUrl),
        quizSearch: '',
        quizSort: localStorage.getItem('mgQuizListSort') || 'latest_desc',
        studentSort: localStorage.getItem('mgQuizStudentSort') || 'latest_desc',
        mgUrl(params) {
            const q = params.toString();
            return q ? this.mgBaseUrl + '?' + q : this.mgBaseUrl + '?view=quiz';
        },
        sortList(items, sortKey, nameField) {
            const list = [...items];
            const sorters = {
                latest_desc: (a, b) => (b.latestTs || 0) - (a.latestTs || 0),
                latest_asc: (a, b) => (a.latestTs || 0) - (b.latestTs || 0),
                name_asc: (a, b) => (a[nameField] || '').localeCompare(b[nameField] || '', undefined, { sensitivity: 'base' }),
                name_desc: (a, b) => (b[nameField] || '').localeCompare(a[nameField] || '', undefined, { sensitivity: 'base' }),
                pending_desc: (a, b) => (b.pending || 0) - (a.pending || 0),
                pending_asc: (a, b) => (a.pending || 0) - (b.pending || 0),
            };
            return list.sort(sorters[sortKey] || sorters.latest_desc);
        },
        get filteredSortedQuizzes() {
            const q = this.quizSearch.trim().toLowerCase();
            const filtered = q ? this.quizzes.filter(x => x.searchText.includes(q)) : this.quizzes;
            return this.sortList(filtered, this.quizSort, 'title');
        },
        get sortedStudents() {
            return this.sortList(this.students, this.studentSort, 'name');
        },
        saveQuizSort() { localStorage.setItem('mgQuizListSort', this.quizSort); },
        saveStudentSort() { localStorage.setItem('mgQuizStudentSort', this.studentSort); },
        selectQuiz(quizId) {
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'quiz');
            params.set('quiz_id', quizId);
            params.delete('user_id');
            window.location.href = this.mgUrl(params);
        },
        selectStudent(userId) {
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'quiz');
            params.set('user_id', userId);
            window.location.href = this.mgUrl(params);
        },
        backToQuizzes() {
            window.location.href = this.mgBaseUrl + '?view=quiz';
        },
        backToStudents() {
            const params = new URLSearchParams(window.location.search);
            params.delete('user_id');
            window.location.href = this.mgUrl(params);
        },
    };
}
</script>
@endpush
