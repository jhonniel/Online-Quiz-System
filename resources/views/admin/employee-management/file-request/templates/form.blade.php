@extends('layouts.admin')

@section('title', ($template->exists ? 'Edit' : 'Create') . ' Template')

@section('content')
@php
    $customFields = old('custom_field_key')
        ? collect(old('custom_field_key'))->map(function ($key, $index) {
            return [
                'key' => $key,
                'label' => old('custom_field_label.'.$index),
                'default' => old('custom_field_default.'.$index),
            ];
        })->all()
        : $template->customFieldDefinitions();
    if (empty($customFields)) {
        $customFields = [['key' => '', 'label' => '', 'default' => '']];
    }
@endphp

<div class="px-3 sm:px-4 lg:px-6 xl:px-8 space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $template->exists ? 'Edit Template' : 'Create Template' }}</h1>
            <p class="text-sm text-gray-500 mt-1">Use placeholders like {{ '{{employee_name}}' }} in the body. Add custom fields for admin input when generating.</p>
        </div>
        <a href="{{ route('admin.file-request.templates.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Back to templates</a>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($template->isCertificateOfEmployment())
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
            This template uses the <strong>Certificate of Employment</strong> format (MC Monde / Infosoft letterhead, signature, and footer). Other document types should use their own templates.
        </div>
    @endif

    <form method="POST" action="{{ $template->exists ? route('admin.file-request.templates.update', $template) : route('admin.file-request.templates.store') }}" class="space-y-6">
        @csrf
        @if($template->exists)
            @method('PUT')
        @endif

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                    <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $template->slug) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="auto-generated from name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', $template->category) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $template->sort_order ?? 0) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $template->description) }}</textarea>
            </div>

            <div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Active (available for generation)
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Template Body (HTML)</label>
                <textarea name="body" rows="16" required class="w-full rounded-lg border-gray-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('body', $template->body) }}</textarea>
                <p class="mt-2 text-xs text-gray-500">
                    Built-in placeholders:
                    @foreach($placeholders as $placeholder)
                        <code class="bg-gray-100 px-1 rounded">{{ $placeholder }}</code>@if(!$loop->last), @endif
                    @endforeach
                    . Logo, contact, and address pull from System Settings when field defaults are empty.
                </p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Custom Fields</h2>
                    <p class="text-sm text-gray-500">Each field becomes <code>{{ '{{field_key}}' }}</code> in the template body.</p>
                </div>
                <button type="button" id="add-custom-field" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add field</button>
            </div>

            <div id="custom-fields-editor" class="space-y-3">
                @foreach($customFields as $index => $field)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 custom-field-row">
                        <input type="text" name="custom_field_key[]" value="{{ $field['key'] ?? '' }}" placeholder="field_key"
                               class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <input type="text" name="custom_field_label[]" value="{{ $field['label'] ?? '' }}" placeholder="Field label"
                               class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <div class="flex gap-2">
                            <input type="text" name="custom_field_default[]" value="{{ $field['default'] ?? '' }}" placeholder="Default value"
                                   class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="button" class="remove-custom-field px-3 py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.file-request.templates.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
                {{ $template->exists ? 'Update Template' : 'Create Template' }}
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editor = document.getElementById('custom-fields-editor');
    const addBtn = document.getElementById('add-custom-field');

    function bindRemoveButtons() {
        editor.querySelectorAll('.remove-custom-field').forEach(function (btn) {
            btn.onclick = function () {
                const rows = editor.querySelectorAll('.custom-field-row');
                if (rows.length <= 1) {
                    rows[0].querySelectorAll('input').forEach(function (input) { input.value = ''; });
                    return;
                }
                btn.closest('.custom-field-row').remove();
            };
        });
    }

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-3 gap-3 custom-field-row';
        row.innerHTML = `
            <input type="text" name="custom_field_key[]" placeholder="field_key" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <input type="text" name="custom_field_label[]" placeholder="Field label" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <div class="flex gap-2">
                <input type="text" name="custom_field_default[]" placeholder="Default value" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="button" class="remove-custom-field px-3 py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Remove</button>
            </div>
        `;
        editor.appendChild(row);
        bindRemoveButtons();
    });

    bindRemoveButtons();
});
</script>
@endsection
