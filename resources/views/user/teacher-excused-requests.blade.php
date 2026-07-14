@extends('layouts.user')

@section('page-title', 'Student Excused Requests')

@section('content')
<div
    class="h-full flex flex-col min-h-0 min-w-0"
    x-data="{
        formOpen: {{ $errors->any() ? 'true' : 'false' }},
        reasonLen: {{ strlen((string) old('reason', '')) }}
    }"
    @keydown.escape.window="formOpen = false"
>
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Student Excused Requests</h1>
                <p class="text-indigo-100 text-sm mt-1">
                    @if($schoolName)
                        File a request to excuse an ongoing intern from {{ $schoolName }}
                    @else
                        File a request to excuse a student from company duty
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:justify-end sm:pt-0.5">
                <a
                    href="{{ url('/teacher/students') }}"
                    class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-white/40 text-white text-sm font-medium hover:bg-white/10 transition-colors"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    My students
                </a>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 border-t border-gray-200 flex-1 min-h-0 overflow-auto">
        <div class="w-full p-4 sm:p-6 space-y-4">
            @if(session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-start gap-2 text-sm text-gray-600">
                    <svg class="h-5 w-5 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Each submission creates a pending request for every selected student. An administrator will review and approve or reject each one.</span>
                </div>
            </div>

            <!-- Modal: filing form -->
            <div
                x-show="formOpen"
                x-cloak
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="excused-request-modal-title"
            >
                <div
                    class="absolute inset-0 bg-slate-900/65 backdrop-blur-sm"
                    @click="formOpen = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                ></div>

                <div
                    class="relative w-full sm:max-w-4xl h-[min(90vh,860px)] sm:h-[min(92vh,920px)] flex flex-col overflow-hidden sm:mx-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-2xl ring-1 ring-black/5"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-[0.98]"
                    @click.stop
                >
                    <form
                        method="POST"
                        action="{{ url('/teacher/excused-requests') }}"
                        enctype="multipart/form-data"
                        class="flex min-h-0 flex-1 h-full flex-col overflow-hidden"
                        @if($students->isNotEmpty())
                            onsubmit="if (!this.querySelector('[data-student-checkbox]:checked')) { event.preventDefault(); alert('Please select at least one student.'); return false; }"
                        @endif
                    >
                        @csrf

                        <header class="flex-shrink-0 border-b border-gray-200 bg-white px-5 py-4 sm:px-6 sm:py-5">
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-700 text-white shadow-md shadow-indigo-600/25">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <h2 id="excused-request-modal-title" class="text-base sm:text-lg font-semibold tracking-tight text-gray-900">File excused request</h2>
                                    <p class="mt-1 text-sm text-gray-500 leading-snug">
                                        One submission creates a separate pending request for each selected student. An administrator reviews every request.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="formOpen = false"
                                    class="shrink-0 rounded-xl p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700"
                                    aria-label="Close dialog"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </header>

                        <div class="min-h-0 flex-1 overflow-y-auto bg-white px-4 py-5 sm:px-6 sm:py-6">
                            <div class="mx-auto max-w-3xl space-y-8">
                                <div class="space-y-5">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">Students &amp; dates</h3>
                                        <p class="mt-0.5 text-xs text-gray-500">Tick each student this excuse applies to, then pick the absence date or range.</p>
                                    </div>
                                    <div>
                                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                            <span id="students-field-label" class="text-sm font-medium text-gray-800">Students</span>
                                            @if($students->isEmpty())
                                                <span class="text-xs font-medium text-amber-700">No ongoing interns</span>
                                            @else
                                                <div class="flex flex-wrap items-center gap-3 text-xs">
                                                    <button
                                                        type="button"
                                                        class="font-medium text-indigo-600 hover:text-indigo-800"
                                                        onclick="document.querySelectorAll('[data-student-checkbox]').forEach(function (el) { el.checked = true; })"
                                                    >
                                                        Select all
                                                    </button>
                                                    <span class="text-gray-300" aria-hidden="true">|</span>
                                                    <button
                                                        type="button"
                                                        class="font-medium text-gray-600 hover:text-gray-900"
                                                        onclick="document.querySelectorAll('[data-student-checkbox]').forEach(function (el) { el.checked = false; })"
                                                    >
                                                        Clear
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        @if($students->isNotEmpty())
                                            <fieldset
                                                class="min-h-[12rem] max-h-64 overflow-y-auto rounded-lg border border-gray-300 bg-white p-1 shadow-sm"
                                                aria-labelledby="students-field-label"
                                            >
                                                <legend class="sr-only">Choose one or more students</legend>
                                                <ul class="divide-y divide-gray-100" role="list">
                                                    @foreach($students as $student)
                                                        <li>
                                                            <label class="flex cursor-pointer items-start gap-3 px-3 py-2.5 transition-colors hover:bg-gray-50 has-[:checked]:bg-indigo-50/70">
                                                                <input
                                                                    type="checkbox"
                                                                    name="student_ids[]"
                                                                    value="{{ $student->id }}"
                                                                    data-student-checkbox
                                                                    @checked(in_array((string) $student->id, array_map('strval', (array) old('student_ids', [])), true))
                                                                    class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                                >
                                                                <span class="min-w-0 flex-1 text-sm leading-snug">
                                                                    <span class="font-medium text-gray-900">{{ $student->name }}</span>
                                                                    <span class="block text-xs text-gray-500">{{ $student->email }}</span>
                                                                </span>
                                                            </label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </fieldset>
                                        @endif
                                        @if($students->isEmpty())
                                            <p class="mt-2 text-xs text-amber-800">There are no ongoing interns at your school right now. Only students who still have required training hours to complete can be excused here.</p>
                                        @endif
                                        @error('student_ids')
                                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                        @error('student_ids.*')
                                            <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <div class="space-y-1.5">
                                            <label for="start_date" class="text-sm font-medium text-gray-800">Start date<span class="text-red-500">*</span></label>
                                            <input
                                                id="start_date"
                                                type="date"
                                                name="start_date"
                                                value="{{ old('start_date') }}"
                                                required
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            >
                                            @error('start_date')
                                                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="space-y-1.5">
                                            <label for="end_date" class="text-sm font-medium text-gray-800">
                                                End date <span class="font-normal text-gray-500">(optional)</span>
                                            </label>
                                            <input
                                                id="end_date"
                                                type="date"
                                                name="end_date"
                                                value="{{ old('end_date') }}"
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            >
                                            @error('end_date')
                                                <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                            @enderror
                                            <p class="text-xs text-gray-500">Leave blank to use only the start date.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 pt-8 space-y-5">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">Reason &amp; attachments</h3>
                                        <p class="mt-0.5 text-xs text-gray-500">Be clear and concise; admins rely on this when they review the request.</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                                            <label for="reason" class="text-sm font-medium text-gray-800">Explanation<span class="text-red-500">*</span></label>
                                            <span class="text-xs tabular-nums text-gray-500">
                                                <span x-text="1000 - reasonLen">1000</span> characters left
                                            </span>
                                        </div>
                                        <textarea
                                            id="reason"
                                            name="reason"
                                            rows="5"
                                            required
                                            maxlength="1000"
                                            placeholder="Describe the circumstance (e.g. medical, official school activity). Avoid unnecessary personal detail."
                                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm leading-relaxed focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 placeholder:text-gray-400"
                                            @input="reasonLen = $event.target.value.length"
                                        >{{ old('reason') }}</textarea>
                                        @error('reason')
                                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label for="supporting_documents" class="text-sm font-medium text-gray-800">
                                            Attachments <span class="font-normal text-gray-500">(optional)</span>
                                        </label>
                                        <label
                                            for="supporting_documents"
                                            class="flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-lg border border-dashed border-gray-300 bg-gray-50/50 px-4 py-6 text-center transition-colors hover:border-indigo-400 hover:bg-indigo-50/30"
                                        >
                                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                            </svg>
                                            <span class="text-sm text-gray-700">Click to choose files</span>
                                            <span class="text-xs text-gray-500">PDF, JPG, or PNG · up to 5 files · 5 MB each</span>
                                        </label>
                                        <input
                                            id="supporting_documents"
                                            type="file"
                                            name="supporting_documents[]"
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            multiple
                                            class="sr-only"
                                        >
                                        @error('supporting_documents')
                                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                        @error('supporting_documents.*')
                                            <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <footer class="flex-shrink-0 border-t border-gray-200 bg-white px-4 py-4 sm:px-6 sm:py-4">
                            <div class="mx-auto flex max-w-3xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-center text-xs text-gray-500 sm:text-left">
                                    Cancel returns you to the list. Nothing is saved until you submit.
                                </p>
                                <div class="flex items-center justify-center gap-2 sm:justify-end">
                                    <button
                                        type="button"
                                        @click="formOpen = false"
                                        class="inline-flex min-h-[2.5rem] min-w-[5.5rem] items-center justify-center rounded-xl border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex min-h-[2.5rem] items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white shadow-md shadow-indigo-600/25 transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    >
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Submit requests
                                    </button>
                                </div>
                            </div>
                        </footer>
                    </form>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">My filed requests</h2>
                        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Past student excused requests and their latest status.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            @click="formOpen = true"
                            class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 transition-colors"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Student Excused Requests
                        </button>
                        <form method="GET" action="{{ url('/teacher/excused-requests') }}" class="flex items-center gap-2">
                            <label for="per_page" class="text-xs text-gray-500 whitespace-nowrap">Rows</label>
                            <select id="per_page" name="per_page" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                                @foreach([10, 20, 50] as $size)
                                    <option value="{{ $size }}" @selected((int) $perPage === $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="md:hidden divide-y divide-gray-100 bg-white">
                    @forelse($pastRequests as $requestItem)
                        @php
                            $cleanReason = preg_replace('/^Teacher excused request by .+\n\n/s', '', (string) $requestItem->reason, 1);
                            if ($cleanReason === '') {
                                $cleanReason = (string) $requestItem->reason;
                            }
                        @endphp
                        <div class="px-4 py-4 space-y-3">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $requestItem->status_badge_class }}">
                                    {{ $requestItem->display_status }}
                                </span>
                                <time datetime="{{ optional($requestItem->created_at)->toAtomString() }}" class="text-xs text-gray-500 whitespace-nowrap">
                                    {{ optional($requestItem->created_at)->format('M j, Y g:i A') }}
                                </time>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Student</p>
                                <p class="text-sm font-semibold text-gray-900 break-words">{{ optional($requestItem->user)->name ?? 'Unknown student' }}</p>
                                @if(optional($requestItem->user)->email)
                                    <p class="text-xs text-gray-500 break-all">{{ $requestItem->user->email }}</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Type</p>
                                <p class="text-sm font-semibold text-indigo-700">{{ $requestItem->type_label }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Dates</p>
                                <p class="text-sm text-gray-800">
                                    {{ $requestItem->start_date?->format('M j, Y') }}
                                    @if($requestItem->end_date && $requestItem->start_date && ! $requestItem->start_date->equalTo($requestItem->end_date))
                                        — {{ $requestItem->end_date->format('M j, Y') }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Reason</p>
                                <p class="text-sm text-gray-700 break-words">{{ \Illuminate\Support\Str::limit($cleanReason, 280) }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-12 text-center">
                            <p class="text-sm text-gray-500">No requests filed yet.</p>
                        </div>
                    @endforelse
                </div>
                <div class="hidden md:block overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filed</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($pastRequests as $requestItem)
                                @php
                                    $cleanReason = preg_replace('/^Teacher excused request by .+\n\n/s', '', (string) $requestItem->reason, 1);
                                    if ($cleanReason === '') {
                                        $cleanReason = (string) $requestItem->reason;
                                    }
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                        {{ optional($requestItem->created_at)->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ optional($requestItem->user)->name ?? 'Unknown student' }}</div>
                                        @if(optional($requestItem->user)->email)
                                            <div class="text-gray-500">{{ $requestItem->user->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-indigo-700 whitespace-nowrap">
                                        {{ $requestItem->type_label }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                        {{ $requestItem->start_date?->format('M j, Y') }}
                                        @if($requestItem->end_date && $requestItem->start_date && ! $requestItem->start_date->equalTo($requestItem->end_date))
                                            - {{ $requestItem->end_date->format('M j, Y') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs">
                                        <span class="line-clamp-2" title="{{ $cleanReason }}">
                                            {{ \Illuminate\Support\Str::limit($cleanReason, 120) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $requestItem->status_badge_class }}">
                                            {{ $requestItem->display_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <p class="text-sm text-gray-500">No requests filed yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pastRequests->hasPages())
                    <div class="px-3 sm:px-4 py-3 border-t border-gray-100 bg-gray-50 overflow-x-auto">
                        {{ $pastRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
