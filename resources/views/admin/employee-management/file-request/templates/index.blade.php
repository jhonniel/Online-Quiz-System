@extends('layouts.admin')

@section('title', 'File Request Templates')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 xl:px-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Document Templates</h1>
            <p class="text-sm text-gray-500 mt-1">Manage certificate, payslip, and letter templates used in File Request.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.file-request.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to File Request
            </a>
            <a href="{{ route('admin.file-request.templates.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
                Add Template
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Fields</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $template->name }}</div>
                                @if($template->description)
                                    <div class="text-xs text-gray-500 mt-1">{{ Str::limit($template->description, 80) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 hidden md:table-cell">{{ $template->category_label }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 hidden lg:table-cell">{{ count($template->customFieldDefinitions()) }}</td>
                            <td class="px-6 py-4 hidden sm:table-cell">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium {{ $template->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="inline-flex items-center gap-3">
                                    <a href="{{ route('admin.file-request.templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    <form action="{{ route('admin.file-request.templates.destroy', $template) }}" method="POST" class="inline" onsubmit="return confirm('Delete this template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">No templates yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
