@extends('layouts.admin')

@section('title', $label.' Template')

@section('page-title', $label.' Template')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-500 text-sm">Employee Documents</span>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <a href="{{ route('admin.employee-documents.'.$type) }}" class="text-gray-500 text-sm hover:text-gray-700">{{ $label }}</a>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">Template</span>
</li>
@endsection

@section('content')
<div class="document-template-page -mx-3 sm:-mx-4 lg:-mx-8 w-[calc(100%+1.5rem)] sm:w-[calc(100%+2rem)] lg:w-[calc(100%+4rem)] flex flex-col min-h-[calc(100dvh-7rem)] pb-6">

    @if(session('success'))
        <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 flex items-start gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="shrink-0 border-b border-indigo-800/20 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 px-4 sm:px-6 lg:px-8 py-5 sm:py-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">{{ $title }}</h1>
                <p class="mt-1 text-sm text-indigo-100 max-w-3xl leading-relaxed">
                    Design the {{ strtolower($label) }} document with the editor below. Use placeholders for employee-specific fields — they are filled automatically when employees view or sign.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                @if($hasCustomTemplate)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                        Custom template saved
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-indigo-100">
                        <span class="h-2 w-2 rounded-full bg-white/60"></span>
                        Built-in default (live)
                    </span>
                @endif
                <a href="{{ route('admin.employee-documents.'.$type) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-white/25 bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to {{ $label }}
                </a>
            </div>
        </div>
    </div>

    @php
        $policyPositionSectionDescription = 'All employees see the standard policy list. Add extra policies and optional content below that only appear for selected positions. Use placeholders <code class="font-mono text-[11px] bg-white px-1 rounded border border-violet-200">{{position}}</code> and <code class="font-mono text-[11px] bg-white px-1 rounded border border-violet-200">{{position_policies_content}}</code> in the HTML template when needed.';
        $contractPositionSectionDescription = 'All employees see the standard agreement text and default job description. Add position-specific job duties and optional extra clauses below. Use placeholders <code class="font-mono text-[11px] bg-white px-1 rounded border border-violet-200">{{job_description_list}}</code> and <code class="font-mono text-[11px] bg-white px-1 rounded border border-violet-200">{{position_content}}</code> in the HTML template when needed.';
    @endphp

    <form action="{{ route('admin.employee-documents.template.update', $type) }}" method="POST" id="document-template-form" class="flex flex-col flex-1 min-h-0">
        @csrf

        @if($type === 'policy')
            @include('admin.employee-documents.partials.document-position-rules', [
                'positionRulesFieldName' => 'policy_position_rules',
                'documentPositionRules' => $documentPositionRules ?? [],
                'showPoliciesField' => true,
                'sectionTitle' => 'Position-specific policy content',
                'sectionDescription' => $policyPositionSectionDescription,
                'emptyMessage' => 'No position rules yet. Everyone will only see the standard policy list.',
                'contentPlaceholder' => '<p>Additional responsibilities for this role...</p>',
            ])
        @elseif($type === 'contract')
            @include('admin.employee-documents.partials.document-position-rules', [
                'positionRulesFieldName' => 'contract_position_rules',
                'documentPositionRules' => $documentPositionRules ?? [],
                'showPoliciesField' => false,
                'showJobDescriptionField' => true,
                'sectionTitle' => 'Position-specific agreement content',
                'sectionDescription' => $contractPositionSectionDescription,
                'emptyMessage' => 'No position rules yet. Everyone will see the standard job description and agreement text.',
                'contentPlaceholder' => '<p>Additional role-specific terms...</p>',
            ])
        @endif

        <div class="shrink-0 border-b border-slate-200 bg-slate-50 px-4 sm:px-6 lg:px-8 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-2">Insert placeholders</p>
            <div class="flex flex-wrap gap-2">
                @foreach($placeholders as $placeholder)
                    <button type="button"
                            class="document-placeholder-btn inline-flex items-center rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-mono text-slate-700 shadow-sm hover:bg-slate-100 transition-colors"
                            data-placeholder="{{ $placeholder }}">
                        {{ $placeholder }}
                    </button>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-slate-500">Clear the editor and save to restore the built-in default document.</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 flex-1 min-h-0 divide-y xl:divide-y-0 xl:divide-x divide-slate-200">
            <div class="flex flex-col min-h-[420px] xl:min-h-[calc(100dvh-18rem)] bg-white">
                <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-3 border-b border-slate-100 bg-slate-50/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">WYSIWYG editor</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Format text visually — saved as HTML</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="btn-insert-default"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                            Insert default
                        </button>
                        <button type="button" id="btn-clear-editor"
                                class="inline-flex items-center rounded-lg border border-transparent px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                            Clear
                        </button>
                    </div>
                </div>
                <div class="flex flex-col flex-1 min-h-0 px-4 sm:px-6 lg:px-8 py-4">
                    <textarea name="template_html" id="template_html"
                              class="flex-1 min-h-[360px] w-full rounded-xl border border-slate-300">{{ old('template_html', $templateHtml) }}</textarea>
                    @error('template_html')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="shrink-0 border-t border-slate-200 px-4 sm:px-6 lg:px-8 py-4 bg-slate-50/60">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Page footer</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Left-aligned on every page. Use <code class="font-mono text-[11px] bg-slate-100 px-1 rounded">@{{page_number}}</code> for incremental pages.</p>
                        </div>
                        <button type="button" id="btn-insert-default-footer"
                                class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                            Restore default footer
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-2">
                        @foreach($footerPlaceholders as $placeholder)
                            <button type="button"
                                    class="footer-placeholder-btn inline-flex items-center rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-mono text-slate-700 shadow-sm hover:bg-slate-100 transition-colors"
                                    data-placeholder="{{ $placeholder }}">
                                {{ $placeholder }}
                            </button>
                        @endforeach
                    </div>
                    <textarea name="footer_html" id="footer_html" rows="2"
                              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900">{{ old('footer_html', $footerHtml) }}</textarea>
                    @error('footer_html')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col min-h-[420px] xl:min-h-[calc(100dvh-18rem)] bg-slate-50/40">
                <div class="shrink-0 px-4 sm:px-6 lg:px-8 py-3 border-b border-slate-100 bg-white flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Live preview</h2>
                        <p class="text-xs text-slate-500 mt-0.5" id="preview-status">Sample employee data shown</p>
                    </div>
                    <span id="preview-badge" class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md {{ $hasCustomTemplate ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $hasCustomTemplate ? 'Custom' : 'Default' }}
                    </span>
                </div>
                <div class="flex-1 min-h-0 overflow-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm min-h-full text-sm text-gray-900 leading-snug document-preview">
                        @include('admin.employee-documents.partials.template-preview-styles', ['type' => $type])
                        <div id="document-preview">{!! $previewHtml !!}</div>
                        <div id="document-footer-preview" class="document-page-footer mt-6 border-t border-dashed border-slate-200 pt-4"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 z-10 shrink-0 border-t border-slate-200 bg-white/95 backdrop-blur px-4 sm:px-6 lg:px-8 py-3 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 shadow-[0_-4px_12px_rgba(15,23,42,0.06)]">
            <p class="text-xs text-slate-500">
                Body: <code class="text-[11px] bg-slate-100 px-1 rounded font-mono">{{ $settingKey }}</code>
                · Footer: <code class="text-[11px] bg-slate-100 px-1 rounded font-mono">{{ $footerSettingKey }}</code>
            </p>
            <button type="submit"
                    class="inline-flex justify-center items-center gap-2 px-6 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save template
            </button>
        </div>
    </form>
</div>

@php
    $footerPreviewPlaceholders = [
        '{{page_number}}' => '1',
        '{{company_name}}' => \App\Support\EmployeeDocumentFooter::defaultCompanyName(),
        '{{document_title}}' => $title,
    ];
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
<script>
(function () {
    const textarea = document.getElementById('template_html');
    const preview = document.getElementById('document-preview');
    const badge = document.getElementById('preview-badge');
    const statusEl = document.getElementById('preview-status');
    const defaultHtml = @json($defaultTemplateHtml);
    const defaultFooterHtml = @json($defaultFooterHtml);
    const placeholderMap = @json($previewPlaceholders);
    const footerTextarea = document.getElementById('footer_html');
    const footerPreview = document.getElementById('document-footer-preview');
    const footerPreviewMap = @json($footerPreviewPlaceholders);

    function stripScripts(html) {
        return html.replace(/<\s*script\b[^>]*>[\s\S]*?<\s*\/\s*script\s*>/gi, '');
    }

    function applyPreviewPlaceholders(html) {
        let output = html;
        Object.entries(placeholderMap).forEach(([token, value]) => {
            output = output.split(token).join(value);
        });
        return stripScripts(output);
    }

    function applyFooterPreviewPlaceholders(html) {
        let output = html;
        Object.entries(footerPreviewMap).forEach(([token, value]) => {
            output = output.split(token).join(value);
        });
        return stripScripts(output);
    }

    function syncFooterPreview(html) {
        if (!footerPreview) return;
        const raw = (html ?? footerTextarea?.value ?? '').trim();
        footerPreview.innerHTML = applyFooterPreviewPlaceholders(raw !== '' ? raw : defaultFooterHtml);
    }

    function syncPreview(html) {
        const raw = (html ?? '').trim();
        if (!preview) return;
        if (raw === '') {
            preview.innerHTML = applyPreviewPlaceholders(defaultHtml);
            badge.textContent = 'Built-in default';
            badge.className = 'text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md bg-slate-100 text-slate-600';
            statusEl.textContent = 'Empty editor — employees see the built-in default.';
        } else {
            preview.innerHTML = applyPreviewPlaceholders(raw);
            badge.textContent = 'Custom';
            badge.className = 'text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-md bg-indigo-100 text-indigo-800';
            statusEl.textContent = 'Preview with sample employee data.';
        }
        syncFooterPreview();
    }

    let editorInstance = null;

    tinymce.init({
        selector: '#template_html',
        height: '100%',
        min_height: 360,
        menubar: false,
        plugins: 'lists link autolink code',
        toolbar: 'undo redo | fontsize | bold italic underline | alignleft aligncenter alignright | bullist numlist | removeformat code',
        font_size_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt',
        content_style: 'body { font-family: Figtree, Arial, sans-serif; font-size: 12pt; line-height: 1.45; color: #111827; }',
        setup: function (editor) {
            editorInstance = editor;
            editor.on('init', function () {
                editor.setContent(textarea.value || '');
                syncPreview(editor.getContent());
            });
            editor.on('input change undo redo SetContent', function () {
                syncPreview(editor.getContent());
            });
        },
    });

    document.getElementById('document-template-form')?.addEventListener('submit', function () {
        if (editorInstance) {
            editorInstance.save();
        }
    });

    document.getElementById('btn-insert-default')?.addEventListener('click', function () {
        if (!editorInstance) return;
        editorInstance.setContent(defaultHtml);
        syncPreview(defaultHtml);
        editorInstance.focus();
    });

    document.getElementById('btn-clear-editor')?.addEventListener('click', function () {
        if (!editorInstance || !confirm('Clear the editor? Saving will restore the built-in default for employees.')) return;
        editorInstance.setContent('');
        syncPreview('');
    });

    document.querySelectorAll('.document-placeholder-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!editorInstance) return;
            const token = button.getAttribute('data-placeholder') || '';
            editorInstance.insertContent(token);
            editorInstance.focus();
        });
    });

    footerTextarea?.addEventListener('input', function () {
        syncFooterPreview(footerTextarea.value);
    });

    document.getElementById('btn-insert-default-footer')?.addEventListener('click', function () {
        if (!footerTextarea) return;
        footerTextarea.value = defaultFooterHtml;
        syncFooterPreview(defaultFooterHtml);
        footerTextarea.focus();
    });

    document.querySelectorAll('.footer-placeholder-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!footerTextarea) return;
            const token = button.getAttribute('data-placeholder') || '';
            const start = footerTextarea.selectionStart ?? footerTextarea.value.length;
            const end = footerTextarea.selectionEnd ?? footerTextarea.value.length;
            footerTextarea.value = footerTextarea.value.slice(0, start) + token + footerTextarea.value.slice(end);
            syncFooterPreview(footerTextarea.value);
            footerTextarea.focus();
        });
    });

    syncFooterPreview();
})();
</script>
@endpush
@endsection
