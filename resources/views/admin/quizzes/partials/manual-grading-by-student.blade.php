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
            'quizzesTaken' => (int) ($g['quizzes_taken'] ?? 0),
            'quizzesAssigned' => (int) ($g['quizzes_assigned'] ?? 0),
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

        <div class="mg-student-list-panel mg-sidebar-scroll relative" role="list" aria-label="Takers pending manual grading">
            <div x-show="!pageReady" x-cloak class="absolute inset-0 z-10 bg-slate-50/95 overflow-hidden">
                @include('admin.quizzes.partials.manual-grading-skeletons', ['variant' => 'sidebar'])
            </div>
            <div class="p-3 space-y-2 min-h-0" :class="!pageReady && 'opacity-0'">
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
                            <p class="text-xs mt-0.5 tabular-nums"
                               :class="selectedUserId === student.id ? 'text-indigo-100' : 'text-gray-600'">
                                <span class="font-semibold" x-text="student.quizzesTaken + '/' + student.quizzesAssigned"></span>
                                <span class="opacity-80"> quizzes taken</span>
                            </p>
                            <p class="text-xs opacity-70">
                                <span x-text="student.pending + ' to grade'"></span>
                                <span x-show="student.pending === 0" class="font-medium"
                                      :class="selectedUserId === student.id ? 'text-emerald-200' : 'text-green-600'"> · all graded</span>
                            </p>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-1">
                            <span class="min-w-[2.5rem] text-center rounded-md px-2 py-0.5 text-xs font-bold tabular-nums border"
                                  :class="selectedUserId === student.id
                                      ? 'border-white/30 bg-white/15 text-white'
                                      : 'border-slate-200 bg-slate-50 text-slate-700'"
                                  x-text="student.quizzesTaken + '/' + student.quizzesAssigned"
                                  title="Quizzes taken / assigned"></span>
                            <span class="min-w-[1.75rem] text-center rounded-full px-2 py-0.5 text-xs font-bold tabular-nums"
                                  :class="selectedUserId === student.id ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-900'"
                                  :data-pending-badge="'student-' + student.id"
                                  x-text="student.pending"
                                  title="Answers pending grading"></span>
                        </div>
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
            @include('admin.quizzes.partials.manual-grading-workspace-student')
        </div>
    </div>
</div>

@push('scripts')
<script>
function mgByStudent() {
    return {
        ...mgNavigationMixin('student'),
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
            this.selectedUserId = userId;
            this.selectedQuizId = null;
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'student');
            params.set('user_id', userId);
            params.delete('quiz_id');
            this.loadWorkspace(params);
        },
        selectQuiz(quizId) {
            if (!this.selectedUserId) return;
            this.selectedQuizId = quizId;
            const params = new URLSearchParams(window.location.search);
            params.set('view', 'student');
            params.set('user_id', this.selectedUserId);
            params.set('quiz_id', quizId);
            this.loadWorkspace(params);
        },
        backToStudents() {
            this.selectedUserId = null;
            this.selectedQuizId = null;
            this.loadWorkspace(new URLSearchParams('view=student'));
        },
        backToQuizzes() {
            this.selectedQuizId = null;
            const params = new URLSearchParams();
            params.set('view', 'student');
            if (this.selectedUserId) {
                params.set('user_id', this.selectedUserId);
            }
            this.loadWorkspace(params);
        },
    };
}
</script>
@endpush
