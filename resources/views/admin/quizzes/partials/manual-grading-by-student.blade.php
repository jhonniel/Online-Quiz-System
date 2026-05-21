@php
    $mgBaseUrl = url('/admin/manual-grading');
    $mgStudents = collect($groupsByStudent)->map(function ($g) {
        $name = $g['user']->name ?? 'Unknown';
        $latest = $g['latest_attempt_at'] ?? null;

        $user = $g['user'];

        return [
            'id' => (int) $user->id,
            'name' => $name,
            'email' => $user->email ?? '',
            'role' => $user->role ?? '',
            'roleLabel' => $user->getRoleLabel(),
            'roleBadgeClass' => $user->getRoleBadgeClass(),
            'initial' => strtoupper(substr($name, 0, 1)),
            'quizCount' => $g['quizzes']->count(),
            'pending' => (int) $g['pending_count'],
            'latestTs' => $latest ? \Illuminate\Support\Carbon::parse($latest)->timestamp : 0,
            'searchText' => strtolower($name.' '.($user->email ?? '').' '.($user->role ?? '').' '.$g['quizzes']->map(fn ($q) => $q['quiz']->title ?? '')->implode(' ')),
        ];
    })->values();
    $mgQuizzesForSelected = $selectedStudentGroup
        ? $selectedStudentGroup['quizzes']
            ->filter(fn ($g) => ($g['pending_count'] ?? 0) > 0)
            ->map(function ($g) {
            $title = $g['quiz']->title ?? 'Quiz';
            $latest = $g['latest_attempt_at'] ?? null;

            return [
                'id' => (int) $g['quiz']->id,
                'title' => $title,
                'pending' => (int) $g['pending_count'],
                'latestTs' => $latest ? \Illuminate\Support\Carbon::parse($latest)->timestamp : 0,
            ];
        })->values()
        : collect();
@endphp
<div class="flex flex-col lg:flex-row flex-1 min-h-0 h-full overflow-hidden"
     id="manual-grading-by-student"
     x-data="mgByStudent()"
     @manual-grading-quiz-complete.window="backToQuizzes()">

    <aside class="mg-sidebar-panel w-full lg:w-80 xl:w-96 shrink-0 border-b lg:border-b-0 lg:border-r border-gray-200 bg-slate-50/90">
        <div class="shrink-0 px-4 py-4 border-b border-gray-200 bg-white/80">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-bold text-gray-900">Takers</h2>
                    <p class="text-xs text-gray-500">Step 1 — select a taker to grade</p>
                </div>
                <span class="shrink-0 text-xs font-medium text-gray-500 tabular-nums" x-text="filteredSortedStudents.length + ' shown'"></span>
            </div>
            <div class="relative mt-3">
                <input type="search"
                       x-model="studentSearch"
                       placeholder="Search by name or email…"
                       class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <label class="mt-2 block">
                <span class="sr-only">Sort takers</span>
                <select x-model="studentSort"
                        @change="saveStudentSort()"
                        class="w-full mt-1 text-xs border border-gray-300 rounded-lg bg-white py-2 pl-2 pr-8 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="latest_desc">Latest attempt (newest first)</option>
                    <option value="latest_asc">Latest attempt (oldest first)</option>
                    <option value="name_asc">Name A → Z</option>
                    <option value="name_desc">Name Z → A</option>
                    <option value="pending_desc">Pending high → low</option>
                    <option value="pending_asc">Pending low → high</option>
                </select>
            </label>
        </div>

        <div class="mg-student-list-panel mg-sidebar-scroll" role="list" aria-label="Takers pending manual grading">
            <div class="p-3 space-y-2 min-h-0">
            <template x-if="filteredSortedStudents.length === 0">
                <p class="text-center text-sm text-gray-500 py-8">No takers match your search.</p>
            </template>
            <template x-for="student in filteredSortedStudents" :key="student.id">
                <button type="button"
                        @click="selectStudent(student.id)"
                        :class="selectedUserId === student.id
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-md ring-2 ring-indigo-200'
                            : 'bg-white text-gray-900 border-gray-200 hover:border-indigo-300 hover:shadow-sm'"
                        class="w-full text-left rounded-xl border p-3 transition-all duration-150">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                              :class="selectedUserId === student.id ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-700'"
                              x-text="student.initial"></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <p class="text-sm font-semibold truncate" x-text="student.name"></p>
                                <span class="shrink-0 inline-flex rounded px-1.5 py-0.5 text-[10px] font-semibold leading-none"
                                      :class="student.roleBadgeClass"
                                      x-text="student.roleLabel"></span>
                            </div>
                            <p class="text-xs truncate opacity-80" x-text="student.email"></p>
                            <p class="text-xs mt-0.5 opacity-70">
                                <span x-text="student.pending + ' to grade'"></span>
                                <span x-show="student.pending === 0" class="text-green-600 font-medium"> · all graded</span>
                            </p>
                        </div>
                        <span class="shrink-0 min-w-[1.75rem] text-center rounded-full px-2 py-0.5 text-xs font-bold tabular-nums"
                              :class="selectedUserId === student.id ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-900'"
                              :data-pending-badge="'student-' + student.id"
                              x-text="student.pending"></span>
                    </div>
                </button>
            </template>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden bg-gradient-to-br from-slate-50 to-gray-100/80">
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
                            <button type="button" @click="backToStudents()" class="text-sm font-medium text-gray-500 hover:text-indigo-600">Takers</button>
                            <span class="text-gray-300">/</span>
                            <button type="button" @click="backToQuizzes()" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ $studentName }}</button>
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
                @endphp
                <div class="max-w-6xl mx-auto w-full space-y-4 flex flex-col min-h-0 max-h-full">
                    <nav class="shrink-0 flex flex-wrap items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-3 shadow-sm">
                        <button type="button" @click="backToStudents()" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← Takers</button>
                        <span class="text-gray-300">/</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $studentName }}</span>
                        <span class="ml-auto text-xs font-medium text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full">Step 2 — pick a quiz</span>
                    </nav>

                    <div class="shrink-0 flex flex-wrap items-center gap-2">
                        <label class="text-xs font-medium text-gray-600">Sort quizzes:</label>
                        <select x-model="quizSort"
                                @change="saveQuizSort()"
                                class="text-xs border border-gray-300 rounded-lg bg-white py-1.5 pl-2 pr-7 focus:ring-2 focus:ring-indigo-500">
                            <option value="latest_desc">Latest attempt (newest)</option>
                            <option value="latest_asc">Latest attempt (oldest)</option>
                            <option value="name_asc">Title A → Z</option>
                            <option value="name_desc">Title Z → A</option>
                            <option value="pending_desc">Pending high → low</option>
                            <option value="pending_asc">Pending low → high</option>
                        </select>
                    </div>

                    <div class="flex-1 min-h-0 overflow-y-auto mg-sidebar-scroll pr-1 -mr-1 max-h-[min(60vh,36rem)] lg:max-h-[calc(100vh-18rem)]">
                        <template x-if="sortedQuizzes.length === 0">
                            <p class="text-sm text-gray-500 py-8 text-center">No pending answers for this taker.</p>
                        </template>
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 pb-2">
                            <template x-for="quiz in sortedQuizzes" :key="quiz.id">
                                <button type="button"
                                        @click="selectQuiz(quiz.id)"
                                        class="group text-left rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-indigo-400 hover:shadow-md hover:ring-2 hover:ring-indigo-100 transition-all duration-150">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                            </div>
                                            <h3 class="mt-3 text-base font-bold text-gray-900 group-hover:text-indigo-700" x-text="quiz.title"></h3>
                                            <p class="mt-1 text-xs text-gray-500">Grade all pending questions for this quiz</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800"
                                              :data-pending-badge="'quiz-{{ (int) $student->id }}-' + quiz.id"
                                              x-text="quiz.pending"></span>
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
function mgByStudent() {
    return {
        selectedUserId: @json($selectedUserId),
        selectedQuizId: @json($selectedQuizId),
        students: @json($mgStudents),
        quizzes: @json($mgQuizzesForSelected),
        mgBaseUrl: @json($mgBaseUrl),
        studentSearch: '',
        studentSort: localStorage.getItem('mgStudentSort') || 'latest_desc',
        quizSort: localStorage.getItem('mgQuizSort') || 'latest_desc',
        mgUrl(params) {
            const q = params.toString();
            return q ? this.mgBaseUrl + '?' + q : this.mgBaseUrl + '?view=student';
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
        get filteredSortedStudents() {
            const q = this.studentSearch.trim().toLowerCase();
            const filtered = q
                ? this.students.filter(s => s.searchText.includes(q))
                : this.students;
            return this.sortList(filtered, this.studentSort, 'name');
        },
        get sortedQuizzes() {
            return this.sortList(this.quizzes, this.quizSort, 'title');
        },
        saveStudentSort() { localStorage.setItem('mgStudentSort', this.studentSort); },
        saveQuizSort() { localStorage.setItem('mgQuizSort', this.quizSort); },
        selectStudent(userId) {
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'student');
            params.set('user_id', userId);
            params.delete('quiz_id');
            window.location.href = this.mgUrl(params);
        },
        selectQuiz(quizId) {
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'student');
            params.set('quiz_id', quizId);
            window.location.href = this.mgUrl(params);
        },
        backToStudents() {
            window.location.href = this.mgBaseUrl + '?view=student';
        },
        backToQuizzes() {
            const params = new URLSearchParams(window.location.search);
            params.delete('quiz_id');
            window.location.href = this.mgUrl(params);
        },
    };
}
</script>
@endpush
