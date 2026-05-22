@extends('layouts.admin')

@section('page-title', 'Manual Grading')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Quizzes</span>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Manual Grading</span>
        </div>
    </li>
@endsection

@section('content')
<div class="manual-grading-page -mx-3 sm:-mx-4 lg:-mx-8 w-[calc(100%+1.5rem)] sm:w-[calc(100%+2rem)] lg:w-[calc(100%+4rem)] flex flex-col h-[calc(100dvh-7rem)] max-h-[calc(100dvh-7rem)] min-h-0 overflow-hidden">

    {{-- Top bar --}}
    <div class="shrink-0 border-b border-gray-200 bg-white px-4 sm:px-6 lg:px-8 py-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Manual Grading</h1>
                <p class="mt-1 text-sm text-gray-600 max-w-3xl">
                    Lists everyone who took a quiz with text or fill-in-the-blank questions—including students, applicants, and internship candidates. The badge is how many answers still need grading. Select a {{ $viewMode === 'student' ? 'taker, then a quiz' : 'quiz, then a taker' }} to review every pending response.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3" x-data="{ statsReady: false }" x-init="$nextTick(() => { statsReady = true })">
                <div x-show="!statsReady" x-cloak class="flex flex-wrap gap-2">
                    @include('admin.quizzes.partials.manual-grading-skeletons', ['variant' => 'stats'])
                </div>
                <div x-show="statsReady" x-cloak class="flex flex-wrap gap-2">
                    <div class="inline-flex items-center gap-2 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2">
                        <span class="text-xs font-medium text-amber-800 uppercase tracking-wide">Pending</span>
                        <span class="text-lg font-bold text-amber-900 tabular-nums">{{ $totalPending }}</span>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2">
                        <span class="text-xs font-medium text-slate-600 uppercase tracking-wide">Takers</span>
                        <span class="text-lg font-bold text-slate-900 tabular-nums">{{ count($groupsByStudent) }}</span>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2">
                        <span class="text-xs font-medium text-slate-600 uppercase tracking-wide">Quizzes</span>
                        <span class="text-lg font-bold text-slate-900 tabular-nums">{{ count($groupsByQuiz) }}</span>
                    </div>
                </div>
                <a href="{{ url('/admin/all-text-attempts') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    All text attempts
                </a>
            </div>
        </div>

        @if(count($groupsByStudent) > 0)
            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="inline-flex p-1 rounded-lg bg-gray-100 border border-gray-200">
                    <a href="{{ url('/admin/manual-grading?view=student') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-md transition-all {{ $viewMode === 'student' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-gray-200' : 'text-gray-600 hover:text-gray-900' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        By Taker
                    </a>
                    <a href="{{ url('/admin/manual-grading?view=quiz') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-md transition-all {{ $viewMode === 'quiz' ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-gray-200' : 'text-gray-600 hover:text-gray-900' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        By Quiz
                    </a>
                </div>
                <p class="text-xs text-gray-500">
                    <span class="font-medium text-gray-700">Step 1</span> Choose from the list
                    <span class="mx-1 text-gray-300">→</span>
                    <span class="font-medium text-gray-700">Step 2</span> Pick {{ $viewMode === 'student' ? 'quiz' : 'taker' }}
                    <span class="mx-1 text-gray-300">→</span>
                    <span class="font-medium text-gray-700">Step 3</span> Grade questions
                </p>
            </div>
        @endif
    </div>

    @if(count($groupsByStudent) > 0)
        <div class="flex-1 flex flex-col min-h-0 min-w-0 px-4 sm:px-6 lg:px-8 py-4 overflow-hidden">
            <div class="flex-1 flex flex-col min-h-0 min-w-0 rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                @if($viewMode === 'student')
                    @include('admin.quizzes.partials.manual-grading-by-student')
                @else
                    @include('admin.quizzes.partials.manual-grading-by-quiz')
                @endif
            </div>
        </div>
    @else
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center max-w-md">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">No manual grading quizzes yet</h3>
                <p class="mt-2 text-sm text-gray-500">No takers have completed a quiz with text or fill-in-the-blank questions yet.</p>
            </div>
        </div>
    @endif
</div>

<style>
    [x-cloak] { display: none !important; }
    /* Keep scroll inside the student/quiz list, not the admin main area */
    main:has(.manual-grading-page) {
        overflow: hidden !important;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    main:has(.manual-grading-page) .manual-grading-page {
        flex: 1 1 auto;
        min-height: 0;
    }
    .manual-grading-page #manual-grading-by-student,
    .manual-grading-page #manual-grading-by-quiz {
        display: flex;
        flex: 1 1 auto;
        min-height: 0;
        height: 100%;
        max-height: 100%;
        overflow: hidden;
    }
    .manual-grading-page .mg-sidebar-panel {
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        min-height: 0;
        height: 100%;
        max-height: 100%;
        overflow: hidden;
    }
    .manual-grading-page .mg-student-list-panel {
        flex: 1 1 0;
        height: 0;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: scroll;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-y;
    }
    @media (max-width: 1023px) {
        .manual-grading-page #manual-grading-by-student,
        .manual-grading-page #manual-grading-by-quiz {
            flex-direction: column;
            height: 100%;
        }
        .manual-grading-page .mg-sidebar-panel {
            height: auto;
            max-height: none;
            flex: 0 0 auto;
        }
        .manual-grading-page .mg-student-list-panel {
            flex: 0 0 auto;
            height: auto;
            max-height: min(42vh, 22rem);
            min-height: 12rem;
        }
    }
    .manual-grading-page .mg-sidebar-scroll {
        scrollbar-width: thin;
        scrollbar-color: #94a3b8 #f1f5f9;
    }
    .manual-grading-page .mg-sidebar-scroll::-webkit-scrollbar { width: 8px; }
    .manual-grading-page .mg-sidebar-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 9999px;
    }
    .manual-grading-page .mg-sidebar-scroll::-webkit-scrollbar-thumb {
        background-color: #94a3b8;
        border-radius: 9999px;
    }
    .manual-grading-page .mg-sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #64748b;
    }
    .manual-grading-page .mg-workspace-scroll {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }
    .manual-grading-page .mg-grading-breadcrumb {
        position: relative;
        z-index: 20;
        margin-bottom: 0;
    }
</style>

@include('admin.quizzes.partials.manual-grading-shared-scripts')
@endsection
