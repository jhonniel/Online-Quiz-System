@extends('layouts.admin')

@section('title', 'File Request')

@section('content')
{{-- file-request: upload-only (no template generation) --}}
<div class="px-3 sm:px-4 lg:px-6 xl:px-8 space-y-6">
    <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-lg p-6 text-white">
        <h1 class="text-2xl font-bold">File Request</h1>
        <p class="text-indigo-100 mt-1">Upload a document and send it directly to an employee.</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70">
            <h2 class="text-base font-semibold text-gray-900">Send file to employee</h2>
            <p class="text-sm text-gray-500">Choose the employee, upload the file, then send.</p>
        </div>
        <form method="POST" action="{{ route('admin.file-request.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee <span class="text-red-500">*</span></label>
                <select id="employee_id" name="employee_id" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('employee_id') border-red-300 @enderror">
                    <option value="">Select employee...</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) old('employee_id') === (string) $employee->id)>
                            {{ $employee->name }}@if($employee->department) — {{ $employee->department->name }}@endif
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Document title (optional)</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" maxlength="255"
                       placeholder="e.g. Certificate of Employment — Juan Dela Cruz"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('title') border-red-300 @enderror">
                <p class="mt-1 text-xs text-gray-500">Leave blank to use the uploaded file name.</p>
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                <input type="file" name="file" id="file" required
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp"
                       class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 @error('file') border-red-300 @enderror">
                <p class="mt-1 text-xs text-gray-500">PDF, Word, Excel, or images. Max 20 MB.</p>
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
                    Send to employee
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70">
            <h2 class="text-base font-semibold text-gray-900">Sent files</h2>
            <p class="text-sm text-gray-500">Files uploaded and assigned to employees.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Document</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Sent by</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentRequests as $request)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $request->title }}</div>
                                @if($request->original_filename && $request->original_filename !== $request->title)
                                    <div class="text-xs text-gray-500">{{ $request->original_filename }}</div>
                                @endif
                                @if($request->template)
                                    <div class="text-xs text-amber-600 mt-0.5">Legacy template: {{ $request->template->name }}</div>
                                @endif
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
                                    @if($request->pdf_path)
                                        <a href="{{ route('admin.file-request.view', $request) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        <a href="{{ route('admin.file-request.download', $request) }}" class="text-gray-600 hover:text-gray-900">Download</a>
                                    @endif
                                    <form action="{{ route('admin.file-request.destroy', $request) }}" method="POST" class="inline" onsubmit="return confirm('Delete this file record?');">
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
                                No files sent yet. Upload a file above to send it to an employee.
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
@endsection
