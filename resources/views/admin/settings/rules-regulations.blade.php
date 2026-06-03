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
<div class="max-w-7xl mx-auto space-y-8 pb-10">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Page header --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-600"></div>
        <div class="px-6 py-8 sm:px-10 sm:py-10">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div class="flex gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/25">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">RULES AND REGULATIONS</h1>
                        <p class="mt-2 text-sm sm:text-base text-slate-600 max-w-2xl leading-relaxed">
                            Control the HTML shown <strong class="font-semibold text-slate-800">below the fixed title</strong> in the student agreement modal (after login until they acknowledge).
                            <span class="block mt-2 text-xs sm:text-sm text-slate-500">When you save, this HTML is written to the <strong class="font-medium text-slate-700">settings</strong> table (<code class="text-[11px] bg-slate-100 px-1.5 py-0.5 rounded font-mono">student_rules_regulations_html</code>) and loaded for every student modal.</span>
                            Per-account warnings and banners are managed separately under <span class="font-medium text-slate-800">Users → edit student</span>.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col items-start gap-2 shrink-0">
                    @if(!empty($has_custom_rules))
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-800">
                            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                            Custom content saved
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                            <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                            Built-in default (live)
                        </span>
                    @endif
                    <p class="text-xs text-slate-500 max-w-xs leading-snug">
                        If the editor is empty, students see the same default rules as in <code class="text-[11px] bg-slate-100 px-1 rounded">student-rules-regulations-default-body</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Merit-based automatic notices (system-wide) --}}
    <div class="rounded-2xl border border-amber-200 bg-amber-50/40 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200/80 bg-amber-50/80">
            <h2 class="text-lg font-semibold text-slate-900">Automatic rules notices (merit counts)</h2>
            <p class="text-sm text-slate-600 mt-1 max-w-3xl">
                Control when the system turns on <strong>rules violation warning</strong> or <strong>final notice</strong> from each student’s total merits
                (under-time filings, excess absences, and manual merits). Per-student overrides are on <strong>Users → edit student</strong>.
            </p>
        </div>
        <form action="{{ url('/admin/system/rules/merit-notices') }}" method="POST" class="p-5 sm:p-6 space-y-5">
            @csrf
            <div>
                <label for="student_merit_auto_notices_enabled" class="block text-sm font-semibold text-slate-800 mb-1.5">Automatic notices</label>
                <select name="student_merit_auto_notices_enabled" id="student_merit_auto_notices_enabled"
                        class="block w-full max-w-md rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="enabled" {{ old('student_merit_auto_notices_enabled', ($merit_auto_notices_enabled ?? true) ? 'enabled' : 'disabled') === 'enabled' ? 'selected' : '' }}>Enabled — apply thresholds below for all eligible students</option>
                    <option value="disabled" {{ old('student_merit_auto_notices_enabled', ($merit_auto_notices_enabled ?? true) ? 'enabled' : 'disabled') === 'disabled' ? 'selected' : '' }}>Disabled — never auto-enable or auto-clear merit-based notices</option>
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-2xl">
                <div>
                    <label for="student_merit_violation_warning_threshold" class="block text-sm font-semibold text-slate-800 mb-1.5">Violation warning at (merits)</label>
                    <input type="number" name="student_merit_violation_warning_threshold" id="student_merit_violation_warning_threshold"
                           min="1" max="999" step="1" required
                           value="{{ old('student_merit_violation_warning_threshold', $merit_violation_warning_threshold ?? 1) }}"
                           class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">Yellow rules violation warning when total merits are at least this number (and below final threshold).</p>
                    @error('student_merit_violation_warning_threshold') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="student_merit_final_notice_threshold" class="block text-sm font-semibold text-slate-800 mb-1.5">Final notice at (merits)</label>
                    <input type="number" name="student_merit_final_notice_threshold" id="student_merit_final_notice_threshold"
                           min="1" max="999" step="1" required
                           value="{{ old('student_merit_final_notice_threshold', $merit_final_notice_threshold ?? 3) }}"
                           class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-slate-500">Red final notice banner when total merits reach this number or higher.</p>
                    @error('student_merit_final_notice_threshold') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700">
                    Save merit notice settings
                </button>
            </div>
        </form>
    </div>

    <form action="{{ url('/admin/system/rules') }}" method="POST" id="rules-regulations-form" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 xl:gap-8 items-start">
            {{-- Editor column --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col min-h-[480px]">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/90 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">HTML editor</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Mono-spaced source · Scripts removed on student view</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="btn-insert-default"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Insert built-in default
                        </button>
                        <button type="button" id="btn-clear-editor"
                                class="inline-flex items-center rounded-lg border border-transparent px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                            Clear editor
                        </button>
                    </div>
                </div>
                <div class="p-4 sm:p-5 flex-1 flex flex-col">
                    <label for="student_rules_regulations_html" class="sr-only">Rules HTML</label>
                    <textarea name="student_rules_regulations_html" id="student_rules_regulations_html" rows="20"
                              class="flex-1 min-h-[380px] w-full resize-y rounded-xl border border-slate-300 bg-slate-50/50 px-4 py-3 font-mono text-[13px] leading-relaxed text-slate-800 shadow-inner focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:bg-white @error('student_rules_regulations_html') border-red-400 @enderror"
                              placeholder="Leave empty to keep using the built-in default rules, or paste HTML here…">{{ old('student_rules_regulations_html', $student_rules_regulations_html ?? '') }}</textarea>
                    @error('student_rules_regulations_html')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-3 text-xs text-slate-500 flex items-start gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>
                            Allowed: semantic HTML (<code class="text-[11px] bg-slate-100 px-1 rounded">&lt;p&gt;</code>, <code class="text-[11px] bg-slate-100 px-1 rounded">&lt;section&gt;</code>, <code class="text-[11px] bg-slate-100 px-1 rounded">&lt;ul&gt;</code>, etc.).
                            Saving an empty editor restores the live built-in default for students.
                        </span>
                    </p>
                </div>
            </div>

            {{-- Preview column --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col min-h-[480px] xl:sticky xl:top-24">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Live preview</h2>
                        <p class="text-xs text-slate-500 mt-0.5" id="preview-status">Showing what students see in the modal body</p>
                    </div>
                    <span id="preview-badge" class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md bg-slate-100 text-slate-600">Default</span>
                </div>
                <div class="p-5 sm:p-6 flex-1 overflow-auto max-h-[min(70vh,720px)] bg-[linear-gradient(180deg,#f8fafc_0%,#ffffff_40%)]">
                    <div class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-4 pb-3 border-b border-slate-100">Agreement modal · body only</p>
                        <div id="rules-preview" class="prose prose-sm max-w-none text-slate-700 prose-headings:text-slate-900 prose-p:leading-relaxed prose-li:marker:text-indigo-500">
                            {{-- Filled by JS; SSR fallback --}}
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

        {{-- Hidden: default HTML for preview swap & strip baseline --}}
        <div id="default-rules-source" class="hidden" aria-hidden="true">{!! $defaultRulesHtml !!}</div>

        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4">
            <a href="{{ url('/admin/settings?tab=general') }}"
               class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 text-sm font-medium shadow-sm hover:bg-slate-50 transition-colors">
                ← Back to System Settings
            </a>
            <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                <span class="text-xs text-slate-500 hidden sm:inline">Changes apply on next student modal display.</span>
                <button type="submit"
                        class="inline-flex justify-center items-center gap-2 px-7 py-3 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-md shadow-indigo-600/20 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save rules content
                </button>
            </div>
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
