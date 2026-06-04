@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center min-w-0">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-3 min-w-0">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white truncate">Document Requests</h1>
                    <p class="text-indigo-100 text-sm">Request certificates and HR documents</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-4">
        @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">New request</h2>
                <p class="text-sm text-gray-500 mt-0.5">Submit a request to HR. You will be notified when your file is ready to download.</p>
            </div>
            @if(count($documentTypes) > 0)
            <form method="POST" action="{{ route('user.employee-file-requests.store') }}" class="p-4 sm:p-6 space-y-4">
                @csrf
                <div>
                    <label for="request_type" class="block text-sm font-medium text-gray-700 mb-1">Document type <span class="text-red-500">*</span></label>
                    <select name="request_type" id="request_type" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('request_type') border-red-300 @enderror">
                        <option value="">Select document...</option>
                        @foreach($documentTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('request_type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('request_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Purpose (optional)</label>
                    <input type="text" name="purpose" id="purpose" value="{{ old('purpose') }}" maxlength="500"
                           placeholder="e.g. Bank loan application, visa requirements"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="employee_notes" class="block text-sm font-medium text-gray-700 mb-1">Additional details (optional)</label>
                    <textarea name="employee_notes" id="employee_notes" rows="3" maxlength="2000"
                              placeholder="Any special instructions or information HR should know..."
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('employee_notes') }}</textarea>
                </div>
                <button type="submit"
                        class="inline-flex w-full sm:w-auto justify-center items-center px-5 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 touch-manipulation">
                    Submit request
                </button>
            </form>
            @else
                <div class="p-4 sm:p-6 text-sm text-amber-800 bg-amber-50 border-t border-amber-100">
                    Document requests are temporarily unavailable. Please contact HR.
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <h2 class="text-base font-semibold text-gray-900">My requests</h2>
            </div>
            @if($requests->count() > 0)
                <div class="md:hidden mobile-card-list">
                    @foreach($requests as $req)
                        <div class="mobile-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="mobile-card-title">{{ $req->title }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $req->isEmployeeInitiated() ? 'Your request' : 'Sent by HR' }}
                                        · {{ $req->created_at->format('M d, Y g:i A') }}
                                    </p>
                                </div>
                                <span class="inline-flex shrink-0 px-2 py-1 text-xs font-semibold rounded-full {{ $req->statusBadgeClass() }}">
                                    {{ $req->statusLabel() }}
                                </span>
                            </div>
                            @if($req->employee_notes)
                                <p class="mt-2 text-sm text-gray-600">{{ Str::limit($req->employee_notes, 120) }}</p>
                            @endif
                            @if($req->admin_notes && $req->isRejected())
                                <p class="mt-2 text-sm text-red-700"><span class="font-medium">HR note:</span> {{ $req->admin_notes }}</p>
                            @endif
                            @if($req->isFulfilled() && $req->pdf_path)
                                <div class="mobile-card-actions">
                                    <a href="{{ route('user.employee-file-requests.view', $req) }}" class="text-sm font-semibold text-indigo-600 touch-manipulation py-1">View</a>
                                    <a href="{{ route('user.employee-file-requests.download', $req) }}" class="text-sm font-semibold text-gray-700 touch-manipulation py-1">Download</a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="hidden md:block overflow-x-auto mobile-table-scroll">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($requests as $req)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $req->title }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $req->isEmployeeInitiated() ? 'Your request' : 'Sent by HR' }}</div>
                                        @if($req->employee_notes)
                                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($req->employee_notes, 80) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $req->statusBadgeClass() }}">{{ $req->statusLabel() }}</span>
                                        @if($req->admin_notes && $req->isRejected())
                                            <p class="text-xs text-red-600 mt-1 max-w-xs">{{ Str::limit($req->admin_notes, 60) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $req->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        @if($req->isFulfilled() && $req->pdf_path)
                                            <a href="{{ route('user.employee-file-requests.view', $req) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                            <a href="{{ route('user.employee-file-requests.download', $req) }}" class="text-gray-600 hover:text-gray-900">Download</a>
                                        @elseif($req->isPending())
                                            <span class="text-gray-400">Awaiting HR</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-100">{{ $requests->links() }}</div>
            @else
                <p class="px-6 py-10 text-center text-sm text-gray-500">No requests yet. Use the form above to request a document from HR.</p>
            @endif
        </div>
    </div>
</div>
@endsection
