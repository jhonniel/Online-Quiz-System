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
            ->filter(fn ($g) => ($g['total_count'] ?? 0) > 0 || ($g['pending_count'] ?? 0) > 0)
            ->map(function ($g) {
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

        <div class="mg-student-list-panel mg-sidebar-scroll relative" role="list" aria-label="Quizzes pending manual grading">
            <div x-show="!pageReady" x-cloak class="absolute inset-0 z-10 bg-slate-50/95 overflow-hidden">
                @include('admin.quizzes.partials.manual-grading-skeletons', ['variant' => 'sidebar'])
            </div>
            <div class="p-3 space-y-2 min-h-0" :class="!pageReady && 'opacity-0'">
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

    <div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden bg-gradient-to-br from-slate-50 to-gray-100/80 relative">
        <div x-show="workspaceLoading"
             x-cloak
             class="absolute inset-0 z-20 flex flex-col min-h-0 overflow-hidden bg-gradient-to-br from-slate-50 to-gray-100/80 p-4 sm:p-6 lg:p-8">
            <div x-show="workspaceSkeleton === 'grading'" class="flex-1 min-h-0">@include('admin.quizzes.partials.manual-grading-skeletons', ['variant' => 'grading'])</div>
            <div x-show="workspaceSkeleton !== 'grading'" class="flex-1 min-h-0">@include('admin.quizzes.partials.manual-grading-skeletons', ['variant' => 'quiz-grid'])</div>
        </div>
        <div x-ref="workspace"
             x-show="!workspaceLoading"
             @click="handleWorkspaceClick($event)"
             class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">
            @include('admin.quizzes.partials.manual-grading-workspace-quiz')
        </div>
    </div>
</div>

@push('scripts')
<script>
function mgByQuiz() {
    return {
        ...mgNavigationMixin('quiz'),
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
            this.selectedQuizId = quizId;
            this.selectedUserId = null;
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'quiz');
            params.set('quiz_id', quizId);
            params.delete('user_id');
            this.loadWorkspace(params);
        },
        selectStudent(userId) {
            this.selectedUserId = userId;
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'quiz');
            params.set('user_id', userId);
            if (this.selectedQuizId) {
                params.set('quiz_id', this.selectedQuizId);
            }
            this.loadWorkspace(params);
        },
        backToQuizzes() {
            this.selectedQuizId = null;
            this.selectedUserId = null;
            this.loadWorkspace(new URLSearchParams('view=quiz'));
        },
        backToStudents() {
            this.selectedUserId = null;
            const params = new URLSearchParams();
            params.set('view', 'quiz');
            if (this.selectedQuizId) {
                params.set('quiz_id', this.selectedQuizId);
            }
            this.loadWorkspace(params);
        },
    };
}
</script>
@endpush
