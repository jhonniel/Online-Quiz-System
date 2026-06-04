@extends('layouts.admin')

@section('title', 'Student rules & regulations — ' . ($settings['system_name'] ?? 'Admin'))

@section('page-title', 'Student rules & regulations')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-500 text-sm">System</span>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">Rules</span>
</li>
@endsection

@section('content')
@php
    $defaultRulesHtml = $default_rules_html ?? '';
@endphp
<div class="rules-regulations-page -mx-3 sm:-mx-4 lg:-mx-8 w-[calc(100%+1.5rem)] sm:w-[calc(100%+2rem)] lg:w-[calc(100%+4rem)] flex flex-col min-h-[calc(100dvh-7rem)] pb-6">

    @if(session('success'))
        <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Page header --}}
    <div class="shrink-0 border-b border-indigo-800/20 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-start gap-4 min-w-0">
                <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white ring-1 ring-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Rules &amp; regulations</h1>
                    <p class="mt-1 text-sm text-indigo-100 max-w-3xl leading-relaxed">
                        Edit the HTML shown below the fixed title in the student agreement modal. Merit-based warnings and per-student overrides are managed here and under <span class="font-medium text-white">Users → edit student</span>.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                @if(!empty($has_custom_rules))
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                        Custom content saved
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-indigo-100">
                        <span class="h-2 w-2 rounded-full bg-white/60"></span>
                        Built-in default (live)
                    </span>
                @endif
                <a href="{{ url('/admin/settings?tab=general') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-white/25 bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    System settings
                </a>
            </div>
        </div>
    </div>

    {{-- Merit-based automatic notices --}}
    <div class="shrink-0 border-b border-amber-200/80 bg-amber-50/70 px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
        <form action="{{ url('/admin/system/rules/merit-notices') }}" method="POST">
            @csrf
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-slate-900 uppercase tracking-wide">Automatic merit notices</h2>
                    <p class="mt-1 text-xs sm:text-sm text-slate-600 max-w-4xl">
                        Turn on rules violation warning or final notice from each student’s total merits (under-time filings, absences, manual merits).
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1.4fr)_minmax(120px,0.8fr)_minmax(120px,0.8fr)_auto] gap-3 xl:gap-4 xl:flex-1 xl:max-w-5xl xl:ml-8">
                    <div>
                        <label for="student_merit_auto_notices_enabled" class="block text-xs font-semibold text-slate-700 mb-1">Automatic notices</label>
                        <select name="student_merit_auto_notices_enabled" id="student_merit_auto_notices_enabled"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            <option value="enabled" {{ old('student_merit_auto_notices_enabled', ($merit_auto_notices_enabled ?? true) ? 'enabled' : 'disabled') === 'enabled' ? 'selected' : '' }}>Enabled for all eligible students</option>
                            <option value="disabled" {{ old('student_merit_auto_notices_enabled', ($merit_auto_notices_enabled ?? true) ? 'enabled' : 'disabled') === 'disabled' ? 'selected' : '' }}>Disabled — no auto merit notices</option>
                        </select>
                    </div>
                    <div>
                        <label for="student_merit_violation_warning_threshold" class="block text-xs font-semibold text-slate-700 mb-1">Warning at (merits)</label>
                        <input type="number" name="student_merit_violation_warning_threshold" id="student_merit_violation_warning_threshold"
                               min="1" max="999" step="1" required
                               value="{{ old('student_merit_violation_warning_threshold', $merit_violation_warning_threshold ?? 1) }}"
                               class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('student_merit_violation_warning_threshold') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="student_merit_final_notice_threshold" class="block text-xs font-semibold text-slate-700 mb-1">Final notice at (merits)</label>
                        <input type="number" name="student_merit_final_notice_threshold" id="student_merit_final_notice_threshold"
                               min="1" max="999" step="1" required
                               value="{{ old('student_merit_final_notice_threshold', $merit_final_notice_threshold ?? 3) }}"
                               class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('student_merit_final_notice_threshold') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2 xl:col-span-1 flex items-end">
                        <button type="submit"
                                class="w-full xl:w-auto inline-flex justify-center items-center gap-2 rounded-lg bg-amber-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700 transition-colors">
                            Save merit settings
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Editor + preview workspace --}}
    <form action="{{ url('/admin/system/rules') }}" method="POST" id="rules-regulations-form" class="flex flex-col flex-1 min-h-0">
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-2 flex-1 min-h-0 divide-y xl:divide-y-0 xl:divide-x divide-slate-200">
            {{-- Editor --}}
            <div class="flex flex-col min-h-[420px] xl:min-h-[calc(100dvh-18rem)] bg-white">
                <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-3 border-b border-slate-100 bg-slate-50/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">HTML editor</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Mono-spaced source · scripts removed on student view</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="btn-insert-default"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Insert default
                        </button>
                        <button type="button" id="btn-clear-editor"
                                class="inline-flex items-center rounded-lg border border-transparent px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                            Clear
                        </button>
                    </div>
                </div>
                <div class="flex flex-col flex-1 min-h-0 px-4 sm:px-6 lg:px-8 py-4">
                    <label for="student_rules_regulations_html" class="sr-only">Rules HTML</label>
                    <textarea name="student_rules_regulations_html" id="student_rules_regulations_html"
                              class="flex-1 min-h-[320px] w-full resize-none rounded-xl border border-slate-300 bg-slate-50/50 px-4 py-3 font-mono text-[13px] leading-relaxed text-slate-800 shadow-inner focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white @error('student_rules_regulations_html') border-red-400 @enderror"
                              placeholder="Leave empty to keep using the built-in default rules, or paste HTML here…">{{ old('student_rules_regulations_html', $student_rules_regulations_html ?? '') }}</textarea>
                    @error('student_rules_regulations_html')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-3 text-xs text-slate-500">
                        Allowed: semantic HTML (<code class="text-[11px] bg-slate-100 px-1 rounded">&lt;p&gt;</code>, <code class="text-[11px] bg-slate-100 px-1 rounded">&lt;section&gt;</code>, <code class="text-[11px] bg-slate-100 px-1 rounded">&lt;ul&gt;</code>).
                        Empty editor restores the built-in default for students.
                    </p>
                </div>
            </div>

            {{-- Preview --}}
            <div class="flex flex-col min-h-[420px] xl:min-h-[calc(100dvh-18rem)] bg-slate-50/40">
                <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-3 border-b border-slate-100 bg-white flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Live preview</h2>
                        <p class="text-xs text-slate-500 mt-0.5" id="preview-status">Showing what students see in the modal body</p>
                    </div>
                    <span id="preview-badge" class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md bg-slate-100 text-slate-600">Default</span>
                </div>
                <div class="flex-1 min-h-0 overflow-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm min-h-full">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4 pb-3 border-b border-slate-100">Agreement modal · body only</p>
                        <div id="rules-preview" class="prose prose-sm max-w-none text-slate-700 prose-headings:text-slate-900 prose-p:leading-relaxed prose-li:marker:text-indigo-500">
                            @if(trim($student_rules_regulations_html ?? '') !== '')
                                {!! $student_rules_regulations_html !!}
                            @else
                                {!! $defaultRulesHtml !!}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="default-rules-source" class="hidden" aria-hidden="true">{!! $defaultRulesHtml !!}</div>

        {{-- Sticky save bar --}}
        <div class="sticky bottom-0 z-10 shrink-0 border-t border-slate-200 bg-white/95 backdrop-blur px-4 sm:px-6 lg:px-8 py-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 shadow-[0_-4px_12px_rgba(15,23,42,0.06)]">
            <p class="text-xs text-slate-500">
                Stored as <code class="text-[11px] bg-slate-100 px-1 rounded font-mono">student_rules_regulations_html</code> · applies on next student modal
            </p>
            <button type="submit"
                    class="inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save rules content
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const ta = document.getElementById('student_rules_regulations_html');
    const preview = document.getElementById('rules-preview');
    const defaultSource = document.getElementById('default-rules-source');
    const badge = document.getElementById('preview-badge');
    const statusEl = document.getElementById('preview-status');
    const defaultHtml = @json($defaultRulesHtml);

    function stripScripts(html) {
        return html.replace(/<\s*script\b[^>]*>[\s\S]*?<\s*\/\s*script\s*>/gi, '');
    }

    function syncPreview() {
        const raw = ta.value.trim();
        if (!preview || !defaultSource) return;
        if (raw === '') {
            preview.innerHTML = defaultSource.innerHTML;
            badge.textContent = 'Built-in default';
            badge.className = 'text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md bg-slate-100 text-slate-600';
            statusEl.textContent = 'Empty editor — students see the built-in default (below).';
        } else {
            preview.innerHTML = stripScripts(ta.value);
            badge.textContent = 'Custom';
            badge.className = 'text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md bg-indigo-100 text-indigo-800';
            statusEl.textContent = 'Preview matches your HTML (scripts stripped for students).';
        }
    }

    document.getElementById('btn-insert-default')?.addEventListener('click', function () {
        if (!ta) return;
        ta.value = defaultHtml;
        ta.dispatchEvent(new Event('input', { bubbles: true }));
        syncPreview();
        ta.focus();
    });

    document.getElementById('btn-clear-editor')?.addEventListener('click', function () {
        if (!ta || !confirm('Clear the editor? Saving will use the built-in default for students.')) return;
        ta.value = '';
        syncPreview();
    });

    ta?.addEventListener('input', syncPreview);
    ta?.addEventListener('change', syncPreview);
    syncPreview();
})();
</script>
@endpush
@endsection
