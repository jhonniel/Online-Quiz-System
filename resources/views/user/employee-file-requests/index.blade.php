@extends('layouts.user')

@section('page-title', 'Document Requests')

@section('content')
<x-user-page-shell fluid class="space-y-5 pb-6 -mx-3 px-3 sm:-mx-4 sm:px-4 lg:-mx-6 lg:px-6">
    <x-user-page-header title="Document Requests" subtitle="Request certificates and HR documents">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </x-slot:icon>
    </x-user-page-header>

    @include('partials.user-flash')

    <x-user-dashboard-section title="New request" description="Submit a request to HR. You will be notified when your file is ready to download.">
        @if(count($documentTypes) > 0)
            <form method="POST" action="{{ route('user.employee-file-requests.store') }}" class="space-y-4 -mt-1">
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
            <x-user-notice tone="warning">
                Document requests are temporarily unavailable. Please contact HR.
            </x-user-notice>
        @endif
    </x-user-dashboard-section>

    <x-user-dashboard-section title="My requests" description="Track status and download fulfilled documents.">
        @if($requests->count() > 0)
            <div class="md:hidden mobile-card-list -mx-1 sm:mx-0">
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
            <div class="hidden md:block overflow-x-auto mobile-table-scroll -mx-1 sm:mx-0 rounded-lg border border-slate-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
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
            <div class="pt-4 border-t border-slate-100">{{ $requests->links() }}</div>
        @else
            <p class="py-8 text-center text-sm text-slate-500">No requests yet. Use the form above to request a document from HR.</p>
        @endif
    </x-user-dashboard-section>
</x-user-page-shell>
@endsection
