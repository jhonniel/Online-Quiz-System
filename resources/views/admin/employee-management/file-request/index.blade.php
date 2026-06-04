@extends('layouts.admin')

@section('title', 'File Request')

@section('page-title', 'File Request')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-500 text-sm">Employee Management</span>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">File Request</span>
</li>
@endsection

@section('content')
@php
    $inputClass = 'block w-full rounded-lg border-gray-300 bg-white text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500';
    $labelClass = 'block text-sm font-medium text-gray-700 mb-1.5';
    $hintClass = 'mt-1.5 text-xs text-gray-500 leading-relaxed';
@endphp

<div class="file-request-page -mx-3 sm:-mx-4 lg:-mx-6 xl:-mx-8 w-[calc(100%+1.5rem)] sm:w-[calc(100%+2rem)] lg:w-[calc(100%+3rem)] xl:w-[calc(100%+4rem)] space-y-6 pb-8">

    {{-- Page header --}}
    <div class="border-b border-indigo-800/20 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 px-4 sm:px-6 lg:px-8 py-5 sm:py-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4 min-w-0">
                <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/25">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Document requests</h1>
                    <p class="mt-1 text-sm text-indigo-100 max-w-2xl leading-relaxed">
                        Review employee submissions, upload completed documents to DigitalOcean Spaces, or send files proactively.
                    </p>
                </div>
            </div>
            @if(($stats['pending'] ?? 0) > 0)
                <span class="inline-flex items-center gap-2 self-start rounded-full border border-amber-300/40 bg-amber-400/20 px-4 py-2 text-sm font-semibold text-amber-50">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-200 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-200"></span>
                    </span>
                    {{ $stats['pending'] }} pending {{ Str::plural('request', $stats['pending']) }}
                </span>
            @endif
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm text-emerald-900 flex items-start gap-3 shadow-sm" role="status">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-900 flex items-start gap-3 shadow-sm" role="alert">
                <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-900 shadow-sm" role="alert">
                <p class="font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Please fix the following
                </p>
                <ul class="mt-2 list-disc list-inside space-y-0.5 text-red-800 pl-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-xl border border-amber-200/80 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pending</p>
                <p class="mt-1.5 text-2xl font-bold text-amber-600 tabular-nums">{{ number_format($stats['pending'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200/80 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Ready</p>
                <p class="mt-1.5 text-2xl font-bold text-emerald-600 tabular-nums">{{ number_format($stats['fulfilled'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-rose-200/80 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Declined</p>
                <p class="mt-1.5 text-2xl font-bold text-rose-600 tabular-nums">{{ number_format($stats['rejected'] ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm col-span-2 lg:col-span-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">All records</p>
                <p class="mt-1.5 text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($stats['records_total'] ?? 0) }}</p>
            </div>
        </div>

        {{-- Pending queue --}}
        @if($pendingRequests->count() > 0)
            <section class="rounded-2xl border border-amber-200/90 bg-white shadow-sm overflow-hidden" aria-labelledby="pending-requests-heading">
                <div class="px-5 sm:px-6 py-4 border-b border-amber-100 bg-gradient-to-r from-amber-50 to-orange-50/50 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="pending-requests-heading" class="text-base font-semibold text-gray-900">Action required</h2>
                        <p class="text-sm text-gray-600 mt-0.5">Upload the document or decline with a note visible to the employee.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900 ring-1 ring-amber-200/80">
                        {{ $pendingRequests->count() }} in queue
                    </span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($pendingRequests as $pending)
                        @php
                            $initials = collect(explode(' ', (string) ($pending->employee?->name ?? '?')))
                                ->filter()
                                ->take(2)
                                ->map(fn ($w) => Str::upper(Str::substr($w, 0, 1)))
                                ->join('');
                        @endphp
                        <article class="p-5 sm:p-6 scroll-mt-28" id="request-{{ $pending->id }}">
                            <div class="flex flex-col lg:flex-row lg:gap-8">
                                {{-- Request details --}}
                                <div class="lg:w-[min(100%,22rem)] shrink-0 space-y-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700 ring-2 ring-white shadow-sm" aria-hidden="true">
                                            {{ $initials ?: '?' }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="text-base font-semibold text-gray-900 leading-snug">{{ $pending->title }}</h3>
                                                <span class="inline-flex px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide rounded-full bg-amber-100 text-amber-800 ring-1 ring-amber-200/60">Pending</span>
                                            </div>
                                            <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $pending->employee?->name }}</p>
                                            @if($pending->employee?->email)
                                                <p class="text-xs text-gray-500 truncate">{{ $pending->employee->email }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <dl class="grid grid-cols-1 gap-2 text-sm rounded-xl border border-gray-100 bg-gray-50/80 p-3.5">
                                        @if($pending->employee?->department)
                                            <div class="flex justify-between gap-3">
                                                <dt class="text-gray-500 shrink-0">Department</dt>
                                                <dd class="font-medium text-gray-900 text-right">{{ $pending->employee->department->name }}</dd>
                                            </div>
                                        @endif
                                        @if($pending->request_type)
                                            <div class="flex justify-between gap-3">
                                                <dt class="text-gray-500 shrink-0">Type</dt>
                                                <dd class="font-medium text-gray-900 text-right">{{ $pending->requestTypeLabel() }}</dd>
                                            </div>
                                        @endif
                                        <div class="flex justify-between gap-3">
                                            <dt class="text-gray-500 shrink-0">Submitted</dt>
                                            <dd class="font-medium text-gray-900 text-right">{{ $pending->created_at->format('M d, Y') }}<span class="text-gray-500 font-normal"> · {{ $pending->created_at->format('g:i A') }}</span></dd>
                                        </div>
                                        <div class="flex justify-between gap-3">
                                            <dt class="text-gray-500 shrink-0">Request ID</dt>
                                            <dd class="font-mono text-xs text-gray-700">#{{ $pending->id }}</dd>
                                        </div>
                                    </dl>

                                    @if($pending->employee_notes)
                                        <div class="rounded-xl border border-slate-200 bg-slate-50/90 p-4">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Employee message</p>
                                            <p class="text-sm text-slate-800 whitespace-pre-wrap leading-relaxed">{{ $pending->employee_notes }}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="flex-1 mt-6 lg:mt-0 grid grid-cols-1 xl:grid-cols-2 gap-4 min-w-0">
                                    <form method="POST" action="{{ route('admin.file-request.fulfill', $pending) }}" enctype="multipart/form-data"
                                          class="rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden flex flex-col"
                                          x-data="{ fileLabel: '' }">
                                        @csrf
                                        <div class="px-4 py-3 border-b border-indigo-100 bg-indigo-50/60 flex items-center gap-2">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-indigo-950">Fulfill request</p>
                                                <p class="text-xs text-indigo-700/90">Upload to Spaces &amp; mark ready</p>
                                            </div>
                                        </div>
                                        <div class="p-4 space-y-4 flex-1 flex flex-col">
                                            <div>
                                                <label class="{{ $labelClass }}">Document file <span class="text-red-500" aria-hidden="true">*</span></label>
                                                <label class="relative flex flex-col items-center justify-center w-full min-h-[7.5rem] rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/30 hover:border-indigo-400 hover:bg-indigo-50/50 transition-colors cursor-pointer group">
                                                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp" class="sr-only"
                                                           @change="fileLabel = $event.target.files[0]?.name || ''">
                                                    <svg class="w-8 h-8 text-indigo-400 group-hover:text-indigo-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                                    <span class="text-sm font-medium text-indigo-800">Choose file or drag here</span>
                                                    <span class="mt-1 text-xs text-gray-500 text-center px-3" x-text="fileLabel || 'PDF, Word, Excel, images · max 20 MB'"></span>
                                                </label>
                                                @error('file')
                                                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label for="fulfill-notes-{{ $pending->id }}" class="{{ $labelClass }}">Note to employee <span class="text-gray-400 font-normal">(optional)</span></label>
                                                <textarea id="fulfill-notes-{{ $pending->id }}" name="admin_notes" rows="3" maxlength="2000" class="{{ $inputClass }}" placeholder="e.g. Signed COE attached. Pick up HR copy if needed."></textarea>
                                            </div>
                                            <button type="submit" class="mt-auto w-full inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Upload &amp; mark ready
                                            </button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('admin.file-request.reject', $pending) }}"
                                          class="rounded-xl border border-rose-200 bg-white shadow-sm overflow-hidden flex flex-col"
                                          onsubmit="return confirm('Decline this request? The employee will see your note.');">
                                        @csrf
                                        <div class="px-4 py-3 border-b border-rose-100 bg-rose-50/60 flex items-center gap-2">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-600 text-white">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-rose-950">Decline request</p>
                                                <p class="text-xs text-rose-700/90">Employee will be notified</p>
                                            </div>
                                        </div>
                                        <div class="p-4 space-y-4 flex-1 flex flex-col">
                                            <div class="flex-1">
                                                <label for="reject-notes-{{ $pending->id }}" class="{{ $labelClass }}">Reason for employee <span class="text-red-500" aria-hidden="true">*</span></label>
                                                <textarea id="reject-notes-{{ $pending->id }}" name="admin_notes" rows="5" maxlength="2000" required class="{{ $inputClass }}" placeholder="Explain clearly why this cannot be fulfilled or what the employee should do instead."></textarea>
                                                <p class="{{ $hintClass }}">This note appears on the employee’s Document Requests page.</p>
                                            </div>
                                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-rose-300 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-colors">
                                                Decline request
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @else
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 px-6 py-10 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mb-3">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-900">No pending requests</p>
                <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">When employees submit document requests, they will appear here for HR action.</p>
            </div>
        @endif

        {{-- Proactive send + guidance --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h2 class="text-base font-semibold text-gray-900">Send file to employee</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Upload a document without a prior employee request.</p>
                </div>
                <form method="POST" action="{{ route('admin.file-request.store') }}" enctype="multipart/form-data" class="p-5 sm:p-6 space-y-5" x-data="{ fileLabel: '' }">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label for="employee_id" class="{{ $labelClass }}">Employee <span class="text-red-500" aria-hidden="true">*</span></label>
                            <select id="employee_id" name="employee_id" required class="{{ $inputClass }}">
                                <option value="">Select an employee…</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>
                                        {{ $employee->name }}@if($employee->department) — {{ $employee->department->name }}@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="title" class="{{ $labelClass }}">Document title <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" maxlength="255" placeholder="e.g. Certificate of Employment — 2026"
                                   class="{{ $inputClass }}">
                            <p class="{{ $hintClass }}">If empty, the uploaded file name is used.</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">File <span class="text-red-500" aria-hidden="true">*</span></label>
                            <label class="relative flex flex-col items-center justify-center w-full min-h-[8rem] rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/50 hover:border-indigo-400 hover:bg-indigo-50/30 transition-colors cursor-pointer group">
                                <input type="file" name="file" id="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp" class="sr-only"
                                       @change="fileLabel = $event.target.files[0]?.name || ''">
                                <svg class="w-9 h-9 text-gray-400 group-hover:text-indigo-500 mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-800">Click to upload document</span>
                                <span class="mt-1 text-xs text-gray-500" x-text="fileLabel || 'PDF, Word, Excel, or images · max 20 MB'"></span>
                            </label>
                            @error('file')
                                <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-1 border-t border-gray-100">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send to employee
                        </button>
                        <p class="text-xs text-gray-500">Stored on DigitalOcean Spaces · visible under employee Document Requests</p>
                    </div>
                </form>
            </div>

            <aside class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5 sm:p-6 space-y-4 h-fit">
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wide">How it works</h3>
                <ol class="space-y-3 text-sm text-slate-700">
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">1</span>
                        <span>Employee submits a request from <strong class="text-slate-900">Document Requests</strong> in their portal.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">2</span>
                        <span>HR uploads the signed document and marks it <strong class="text-slate-900">ready</strong>, or declines with a note.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">3</span>
                        <span>Employees download ready files; proactive uploads skip the pending step.</span>
                    </li>
                </ol>
                <div class="rounded-lg border border-slate-200 bg-white p-3 text-xs text-slate-600 leading-relaxed space-y-2">
                    <p><strong class="text-slate-800">Accepted formats:</strong> PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, JPEG, WEBP (max 20&nbsp;MB).</p>
                    <p><strong class="text-slate-800">Document types:</strong> Add, reorder, or disable types in <a href="{{ url('/admin/settings?tab=general') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">System Settings → General</a>.</p>
                </div>
            </aside>
        </div>

        {{-- All records --}}
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden" aria-labelledby="all-records-heading">
            <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-gray-50/70">
                <div>
                    <h2 id="all-records-heading" class="text-base font-semibold text-gray-900">All records</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Employee requests and files sent by HR.</p>
                </div>
                @if($recentRequests->total() > 0)
                    <p class="text-xs font-medium text-gray-500 tabular-nums">
                        Showing {{ $recentRequests->firstItem() }}–{{ $recentRequests->lastItem() }} of {{ number_format($recentRequests->total()) }}
                    </p>
                @endif
            </div>

            <x-responsive-data-panel class="border-0 shadow-none rounded-none">
                <x-slot:mobile>
                    @forelse($recentRequests as $request)
                        <div class="mobile-card {{ $request->isPending() ? 'bg-amber-50/50' : '' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $request->title }}</p>
                                    <p class="text-xs text-gray-600 mt-0.5">{{ $request->employee?->name ?? '—' }}</p>
                                    @if($request->isEmployeeInitiated())
                                        <span class="inline-flex mt-1.5 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide rounded bg-indigo-50 text-indigo-700">Employee request</span>
                                    @endif
                                </div>
                                <span class="inline-flex shrink-0 px-2 py-1 text-xs font-semibold rounded-full {{ $request->statusBadgeClass() }}">{{ $request->statusLabel() }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ $request->created_at?->format('M d, Y g:i A') }}</p>
                            @if($request->original_filename && $request->original_filename !== $request->title)
                                <p class="text-xs text-gray-500 mt-1 truncate">{{ $request->original_filename }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if($request->isPending())
                                    <a href="#request-{{ $request->id }}" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-100 text-xs font-semibold text-amber-900 hover:bg-amber-200">Process</a>
                                @endif
                                @if($request->pdf_path)
                                    <a href="{{ route('admin.file-request.view', $request) }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-medium text-indigo-700 hover:bg-gray-50">View</a>
                                    <a href="{{ route('admin.file-request.download', $request) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50">Download</a>
                                @endif
                                <form action="{{ route('admin.file-request.destroy', $request) }}" method="POST" class="inline" onsubmit="return confirm('Delete this file record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-red-200 bg-white text-xs font-medium text-red-700 hover:bg-red-50">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <p class="text-sm font-medium text-gray-900">No records yet</p>
                            <p class="text-sm text-gray-500 mt-1">Uploads and employee requests will appear here.</p>
                        </div>
                    @endforelse
                </x-slot:mobile>

                <x-slot:desktop>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Document</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Employee</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($recentRequests as $request)
                                <tr class="hover:bg-gray-50/80 transition-colors {{ $request->isPending() ? 'bg-amber-50/30' : '' }}">
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $request->title }}</div>
                                        @if($request->isEmployeeInitiated())
                                            <span class="inline-flex mt-1 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide rounded bg-indigo-50 text-indigo-700">Employee request</span>
                                        @endif
                                        @if($request->original_filename && $request->original_filename !== $request->title)
                                            <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $request->original_filename }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $request->employee?->name ?? '—' }}</div>
                                        @if($request->employee?->department)
                                            <div class="text-xs text-gray-500">{{ $request->employee->department->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $request->statusBadgeClass() }}">{{ $request->statusLabel() }}</span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600 tabular-nums">
                                        {{ $request->created_at?->format('M d, Y') }}<br>
                                        <span class="text-xs text-gray-400">{{ $request->created_at?->format('g:i A') }}</span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <div class="inline-flex items-center justify-end gap-1 flex-wrap">
                                            @if($request->isPending())
                                                <a href="#request-{{ $request->id }}" class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-semibold text-amber-800 bg-amber-100 hover:bg-amber-200">Process</a>
                                            @endif
                                            @if($request->pdf_path)
                                                <a href="{{ route('admin.file-request.view', $request) }}" target="_blank" rel="noopener" class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium text-indigo-700 hover:bg-indigo-50">View</a>
                                                <a href="{{ route('admin.file-request.download', $request) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium text-gray-700 hover:bg-gray-100">Download</a>
                                            @endif
                                            <form action="{{ route('admin.file-request.destroy', $request) }}" method="POST" class="inline" onsubmit="return confirm('Delete this file record?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium text-red-700 hover:bg-red-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-14 text-center">
                                        <p class="text-sm font-medium text-gray-900">No records yet</p>
                                        <p class="text-sm text-gray-500 mt-1">Uploads and employee requests will appear here.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-slot:desktop>
            </x-responsive-data-panel>

            @if($recentRequests->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">{{ $recentRequests->links() }}</div>
            @endif
        </section>
    </div>
</div>
@endsection
