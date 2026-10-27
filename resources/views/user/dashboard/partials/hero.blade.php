@php
    $canQuizzes = ($canViewAssignedQuizzes ?? auth()->user()->canViewAssignedQuizzes());
@endphp
<header class="user-dashboard-hero bg-gradient-to-r from-indigo-600 via-indigo-600 to-indigo-700 shadow-md">
    <div class="max-w-[88rem] mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-3 min-w-0">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/20" aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-semibold text-white tracking-tight">My Dashboard</h1>
                    <p class="mt-1 text-sm text-indigo-100 leading-relaxed">
                        Welcome back, <x-user-name :user="auth()->user()" class="inline text-white font-medium" />.
                        <span class="text-indigo-200/90">Rank {{ auth()->user()->getRankText() }} · {{ auth()->user()->getTotalScore() }} pts</span>
                        @if(auth()->user()->role === 'student' && !empty($studentMeritDetails['breakdown']['total']))
                            · Merits:
                            <span class="font-semibold tabular-nums {{ (int) $studentMeritDetails['breakdown']['total'] >= 3 ? 'text-red-200' : 'text-white' }}">
                                {{ (int) $studentMeritDetails['breakdown']['total'] }}
                            </span>
                        @endif
                    </p>
                    @if(auth()->user()->isStaffMember())
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ url('/leave-requests') }}" class="user-dashboard-hero__chip">Leave requests</a>
                            <a href="{{ url('/dtr') }}" class="user-dashboard-hero__chip">Time records</a>
                        </div>
                    @endif
                </div>
            </div>

            @if($canQuizzes)
                <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto lg:min-w-[20rem] lg:max-w-md">
                    <label class="sr-only" for="search-input">Search quizzes</label>
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="search"
                               id="search-input"
                               placeholder="Search quizzes…"
                               class="user-dashboard-hero__search block w-full rounded-lg border-0 py-2.5 pl-9 pr-3 text-sm text-white placeholder-indigo-200 ring-1 ring-inset ring-white/25 focus:ring-2 focus:ring-white/40">
                    </div>
                    <button type="button"
                            onclick="openQuizCodeModal()"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white/50 shrink-0">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Quiz code
                    </button>
                </div>
            @endif
        </div>
    </div>
</header>
