@extends('layouts.admin')

@section('title', 'Employee Signatures')

@section('page-title', 'Employee Signatures')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-500 text-sm">Employee Documents</span>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">Signatures</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <div class="border-b border-indigo-800/20 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 rounded-lg shadow-sm px-4 sm:px-6 py-5">
        <h1 class="text-xl sm:text-2xl font-bold text-white">Employee E-Signatures</h1>
        <p class="mt-1 text-sm text-indigo-100">Profile e-signatures uploaded by employees for document signing.</p>
    </div>

    @include('admin.employee-documents.partials.tabs', ['active' => 'signatures'])

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search employee..."
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 md:col-span-2">
                <select name="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All employees</option>
                    <option value="with_signature" {{ $status === 'with_signature' ? 'selected' : '' }}>With signature</option>
                    <option value="without_signature" {{ $status === 'without_signature' ? 'selected' : '' }}>No signature</option>
                </select>
                <x-admin-filter-button />
            </form>
        </div>

        @if($employees->count() > 0)
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($employees as $employee)
                    <div class="rounded-xl border border-gray-200 bg-gray-50/50 overflow-hidden flex flex-col">
                        <div class="px-4 pt-4 pb-2">
                            <p class="font-medium text-gray-900 truncate" title="{{ $employee->name }}">{{ $employee->name ?: '—' }}</p>
                            <p class="text-xs text-gray-500 truncate" title="{{ $employee->email }}">{{ $employee->email ?: '—' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $employee->department?->name ?: 'No department' }}</p>
                        </div>
                        <div class="flex-1 flex flex-col items-center justify-center px-4 py-4 min-h-[7rem] bg-white border-t border-gray-100 mx-4 mb-4 rounded-lg gap-3">
                            @if($employee->hasESignature())
                                <img src="{{ $employee->getESignatureUrl() }}"
                                     alt="E-signature for {{ $employee->name }}"
                                     class="max-h-20 max-w-full object-contain">
                            @else
                                <p class="text-sm text-gray-400 text-center">No e-signature on file</p>
                            @endif
                            @if($employee->hasP12Certificate())
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 border border-green-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    P12 on file
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center text-sm text-gray-500">No employees found.</div>
        @endif

        @if($employees->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $employees->links() }}</div>
        @endif
    </div>
</div>
@endsection
