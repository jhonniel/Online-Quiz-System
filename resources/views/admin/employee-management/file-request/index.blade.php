@extends('layouts.admin')

@section('title', 'File Request')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 xl:px-8 space-y-6">
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-lg p-6 text-white flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">File Request</h1>
            <p class="text-indigo-100 mt-1">Generate employee certificates, payslips, letters, and other documents from templates.</p>
        </div>
        <a href="{{ route('admin.file-request.templates.index') }}"
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-sm font-medium transition-colors">
            Manage Templates
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70">
                <h2 class="text-base font-semibold text-gray-900">Generate Document</h2>
                <p class="text-sm text-gray-500">Choose a template, select an employee, fill in the fields, then preview or download.</p>
            </div>
            <form id="file-request-form" class="p-6 space-y-5">
                @csrf
                <div>
                    <label for="template_id" class="block text-sm font-medium text-gray-700 mb-1">Document Template</label>
                    <select id="template_id" name="template_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select a template...</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}"
                                    data-slug="{{ $template->slug }}"
                                    data-fields='@json($template->customFieldDefinitions())'
                                    data-category="{{ $template->category }}">
                                {{ $template->name }} ({{ $template->category_label }})
                            </option>
                        @endforeach
                    </select>
                    @if($templates->isEmpty())
                        <p class="mt-2 text-sm text-amber-600">No active templates yet. <a href="{{ route('admin.file-request.templates.create') }}" class="underline">Create one</a>.</p>
                    @endif
                </div>

                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select id="employee_id" name="employee_id" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select employee...</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">
                                {{ $employee->name }}@if($employee->department) — {{ $employee->department->name }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="custom-fields-container" class="space-y-4 hidden">
                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Template Fields</h3>
                        <div id="custom-fields-list" class="space-y-4"></div>
                    </div>
                </div>

                <div id="coe-format-notice" class="hidden rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
                    <strong>Certificate of Employment</strong> uses the MC Monde / Infosoft letterhead format (logo, company header, signature block, and footer).
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="button" id="preview-btn"
                            class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Preview
                    </button>
                    <button type="button" id="generate-btn"
                            class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
                        Generate PDF
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70">
                    <h2 class="text-base font-semibold text-gray-900">Available Templates</h2>
                    <p class="text-sm text-gray-500">Quick reference for document types.</p>
                </div>
                <div class="p-4 space-y-3 max-h-[420px] overflow-y-auto">
                    @forelse($templates as $template)
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $template->name }}</p>
                                    <p class="text-xs text-indigo-600 mt-0.5">{{ $template->category_label }}</p>
                                </div>
                            @if($template->isCertificateOfEmployment())
                                <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">COE Format</span>
                            @else
                                <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded bg-gray-100 text-gray-600">Template</span>
                            @endif
                            </div>
                            @if($template->description)
                                <p class="text-xs text-gray-500 mt-2">{{ $template->description }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-6">No templates available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70">
            <h2 class="text-base font-semibold text-gray-900">Recent Generated Files</h2>
            <p class="text-sm text-gray-500">Previously generated documents for employees.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Document</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Generated By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentRequests as $request)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $request->template?->name ?? 'Document' }}</div>
                                <div class="text-xs text-gray-500">{{ $request->title }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $request->employee?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                {{ $request->generator?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                {{ $request->created_at?->format('M d, Y g:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('admin.file-request.view', $request) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    <a href="{{ route('admin.file-request.download', $request) }}" class="text-gray-600 hover:text-gray-900">Download</a>
                                    <form action="{{ route('admin.file-request.destroy', $request) }}" method="POST" class="inline" onsubmit="return confirm('Delete this generated file record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                No generated files yet. Use the form above to create your first document.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recentRequests->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $recentRequests->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('file-request-form');
    const templateSelect = document.getElementById('template_id');
    const fieldsContainer = document.getElementById('custom-fields-container');
    const fieldsList = document.getElementById('custom-fields-list');
    const previewBtn = document.getElementById('preview-btn');
    const generateBtn = document.getElementById('generate-btn');
    const coeFormatNotice = document.getElementById('coe-format-notice');
    const coeTemplateSlug = @json(\App\Models\EmployeeFileTemplate::SLUG_CERTIFICATE_OF_EMPLOYMENT);

    function renderCustomFields() {
        const option = templateSelect.selectedOptions[0];
        fieldsList.innerHTML = '';

        if (!option || !option.value) {
            fieldsContainer.classList.add('hidden');
            coeFormatNotice.classList.add('hidden');
            return;
        }

        coeFormatNotice.classList.toggle('hidden', option.dataset.slug !== coeTemplateSlug);

        let fields = [];
        try {
            fields = JSON.parse(option.dataset.fields || '[]');
        } catch (e) {
            fields = [];
        }

        if (!fields.length) {
            fieldsContainer.classList.add('hidden');
            return;
        }

        fieldsContainer.classList.remove('hidden');
        const requiredCoeFields = ['job_position', 'employment_start'];
        fields.forEach(function (field) {
            const wrapper = document.createElement('div');
            const label = document.createElement('label');
            label.className = 'block text-sm font-medium text-gray-700 mb-1';
            const isRequired = option.dataset.slug === coeTemplateSlug && requiredCoeFields.indexOf(field.key) !== -1;
            label.textContent = (field.label || field.key) + (isRequired ? ' *' : '');
            label.setAttribute('for', 'field_' + field.key);

            const input = document.createElement(field.key === 'letter_body' || field.key === 'endorsement_body' ? 'textarea' : 'input');
            input.id = 'field_' + field.key;
            input.name = 'fields[' + field.key + ']';
            input.value = field.default || '';
            input.className = 'w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
            if (isRequired) {
                input.required = true;
            }
            if (input.tagName === 'TEXTAREA') {
                input.rows = 4;
            }

            wrapper.appendChild(label);
            wrapper.appendChild(input);
            fieldsList.appendChild(wrapper);
        });
    }

    templateSelect.addEventListener('change', renderCustomFields);
    renderCustomFields();

    function submitFormTo(url) {
        if (!form.reportValidity()) {
            return;
        }

        form.action = url;
        form.method = 'POST';
        form.target = '_blank';
        form.submit();
        form.removeAttribute('action');
        form.removeAttribute('target');
    }

    previewBtn.addEventListener('click', function (event) {
        event.preventDefault();
        submitFormTo('{{ route('admin.file-request.preview') }}');
    });

    generateBtn.addEventListener('click', function (event) {
        event.preventDefault();
        submitFormTo('{{ route('admin.file-request.generate') }}');
    });
});
</script>
@endsection
