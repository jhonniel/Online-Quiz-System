@extends('layouts.admin')

@section('title', $label)

@section('page-title', $label)

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-500 text-sm">Employee Documents</span>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">{{ $label }}</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <div class="border-b border-indigo-800/20 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 rounded-lg shadow-sm px-4 sm:px-6 py-5">
        <h1 class="text-xl sm:text-2xl font-bold text-white">{{ $title }}</h1>
        <p class="mt-1 text-sm text-indigo-100">View employee {{ strtolower($label) }} documents and signed PDFs.</p>
    </div>

    @include('admin.employee-documents.partials.tabs', ['active' => $type])

    @if(session('success'))
        <div class="rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($type === 'handbook')
        <div class="rounded-lg border border-violet-200 bg-violet-50 px-4 sm:px-6 py-5 space-y-4">
            <div>
                <h2 class="text-sm font-semibold text-violet-950">Employee Handbook material (PDF)</h2>
                <p class="mt-1 text-xs text-violet-900/80 leading-relaxed">
                    Upload the complete handbook PDF for employees. It appears only on the employee Documents menu as a read-only viewer — separate from the handbook acknowledgment employees sign.
                </p>
            </div>

            @if($handbookMaterialAvailable ?? false)
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">PDF uploaded</span>
                    <form action="{{ route('admin.employee-documents.handbook-material.remove') }}" method="POST"
                          onsubmit="return confirm('Remove the uploaded handbook PDF? Employees will no longer be able to view it.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center rounded-lg border border-red-300 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 transition-colors">
                            Remove PDF
                        </button>
                    </form>
                </div>
            @endif

            <form action="{{ route('admin.employee-documents.handbook-material.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label for="handbook_material_pdf" class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ ($handbookMaterialAvailable ?? false) ? 'Replace handbook PDF' : 'Upload handbook PDF' }}
                    </label>
                    <input type="file"
                           name="handbook_material_pdf"
                           id="handbook_material_pdf"
                           accept=".pdf,application/pdf"
                           class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-violet-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-violet-700">
                    <p class="mt-1 text-[11px] text-gray-500">PDF only, up to 20MB.</p>
                    @error('handbook_material_pdf')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700 transition-colors">
                    {{ ($handbookMaterialAvailable ?? false) ? 'Replace PDF' : 'Upload PDF' }}
                </button>
            </form>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3">
        <div>
            <p class="text-sm font-semibold text-indigo-900">Document template</p>
            <p class="text-xs text-indigo-700 mt-0.5">Edit the {{ strtolower($label) }} employees sign using the visual editor.</p>
        </div>
        <a href="{{ route('admin.employee-documents.template', $type) }}"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit template
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search employee..."
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 md:col-span-2">
                <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="signed" {{ $status === 'signed' ? 'selected' : '' }}>Signed</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <x-admin-filter-button />
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Signed At</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($employees as $employee)
                        @php
                            $signature = $employee->employeeDocumentSignatures->first();
                            $isSigned = $signature?->isSigned() ?? false;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">{{ $employee->name ?: '—' }}</div>
                                <div class="text-gray-500">{{ $employee->email ?: '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $employee->department?->name ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $isSigned ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $isSigned ? 'Signed' : 'Pending' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $signature?->signed_at?->format('M d, Y h:i A') ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm text-right">
                                <a href="{{ route('admin.employee-documents.employee-preview', ['type' => $type, 'employee' => $employee]) }}" target="_blank"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium">
                                    {{ $isSigned ? 'View Signed PDF' : 'View Document' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $employees->links() }}</div>
        @endif
    </div>
</div>
@endsection
