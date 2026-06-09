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

    <div class="flex flex-wrap gap-2">
        @foreach(['nda' => 'NDA', 'contract' => 'Contract', 'policy' => 'Policy'] as $docType => $docLabel)
            @if(auth()->user()->canAccessEmployeeFeature('employee_'.$docType))
                <a href="{{ route('admin.employee-documents.'.$docType) }}"
                   class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium {{ $type === $docType ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                    {{ $docLabel }}
                </a>
            @endif
        @endforeach
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
