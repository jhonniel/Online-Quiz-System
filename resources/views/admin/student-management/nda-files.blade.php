@extends('layouts.admin')

@section('content')
@php
    $isFiltered = ($search ?? '') !== '' || ($statusFilter ?? '') !== '';
@endphp

<div x-data="{ previewOpen: false, previewUrl: '', previewStudent: '', rejectOpen: false, rejectUrl: '', rejectStudent: '' }"
     class="space-y-6">

    {{-- Page Header (matches Student Time Requests) --}}
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3 sm:p-4">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Student NDA Files</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">Review signed NDAs before students can record attendance</p>
                </div>
            </div>
            <div class="mt-2 md:mt-0 flex flex-wrap gap-3 md:ml-auto">
                <a href="{{ route('admin.student-nda-files.index', ['status' => 'pending']) }}"
                   class="bg-white/10 rounded-xl px-3 py-2 text-center hover:bg-white/20 transition-colors {{ ($statusFilter ?? '') === 'pending' ? 'ring-2 ring-white/40' : '' }}">
                    <p class="text-xs text-indigo-100 uppercase tracking-wide">Pending</p>
                    <p class="mt-1 text-lg font-semibold">{{ number_format($pendingCount ?? 0) }}</p>
                </a>
                <a href="{{ route('admin.student-nda-files.index', ['status' => 'approved']) }}"
                   class="bg-white/10 rounded-xl px-3 py-2 text-center hover:bg-white/20 transition-colors {{ ($statusFilter ?? '') === 'approved' ? 'ring-2 ring-white/40' : '' }}">
                    <p class="text-xs text-indigo-100 uppercase tracking-wide">Approved</p>
                    <p class="mt-1 text-lg font-semibold">{{ number_format($approvedCount ?? 0) }}</p>
                </a>
                <a href="{{ route('admin.student-nda-files.index', ['status' => 'rejected']) }}"
                   class="bg-white/10 rounded-xl px-3 py-2 text-center hover:bg-white/20 transition-colors {{ ($statusFilter ?? '') === 'rejected' ? 'ring-2 ring-white/40' : '' }}">
                    <p class="text-xs text-indigo-100 uppercase tracking-wide">Rejected</p>
                    <p class="mt-1 text-lg font-semibold">{{ number_format($rejectedCount ?? 0) }}</p>
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Summary cards (matches Students page) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Uploads</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalCount ?? 0) }}</p>
            <p class="mt-1 text-xs text-gray-500">All signed NDA files</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pending Review</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($pendingCount ?? 0) }}</p>
            <p class="mt-1 text-xs text-gray-500">Awaiting admin action</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Approved</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($approvedCount ?? 0) }}</p>
            <p class="mt-1 text-xs text-gray-500">Can record attendance</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Rejected</p>
            <p class="mt-2 text-2xl font-bold text-red-600">{{ number_format($rejectedCount ?? 0) }}</p>
            <p class="mt-1 text-xs text-gray-500">Sent back to student</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ route('admin.student-nda-files.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="search" name="search" id="search" value="{{ $search ?? '' }}"
                           placeholder="Student name, email, NDA name, ID number…"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="pending" @selected(($statusFilter ?? '') === 'pending')>Pending</option>
                        <option value="approved" @selected(($statusFilter ?? '') === 'approved')>Approved</option>
                        <option value="rejected" @selected(($statusFilter ?? '') === 'rejected')>Rejected</option>
                    </select>
                </div>
                <div>
                    <label for="nda-filter-submit" class="block text-sm font-medium text-gray-700 mb-2 invisible" aria-hidden="true">Filter</label>
                    <x-admin-filter-button id="nda-filter-submit" :fullWidth="true" />
                </div>
            </div>
            @if($isFiltered)
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100">
                    <span class="text-xs text-gray-500">Showing filtered results.</span>
                    <a href="{{ route('admin.student-nda-files.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Clear filters</a>
                </div>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">NDA Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Uploaded</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ndas as $nda)
                        @php
                            $previewUrl = route('admin.student-nda-files.preview', $nda);
                            $effectiveStatus = $nda->approval_status ?: \App\Models\StudentNda::STATUS_PENDING;
                        @endphp
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $nda->user?->name ?? '—' }}</div>
                                <div class="text-sm text-gray-500">{{ $nda->user?->email }}</div>
                                <div class="text-xs text-gray-400 mt-1">{{ $nda->user?->university?->name ?? 'No school on file' }}</div>
                                <div class="lg:hidden mt-2 pt-2 border-t border-gray-100">
                                    <div class="text-sm font-medium text-gray-900">{{ $nda->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $nda->valid_id_type }} · {{ $nda->id_number }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 hidden lg:table-cell">
                                <div class="text-sm font-medium text-gray-900">{{ $nda->full_name }}</div>
                                <div class="text-sm text-gray-500">{{ $nda->valid_id_type }} · {{ $nda->id_number }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $nda->agreement_date?->format('M d, Y') ?? '—' }} · {{ $nda->city }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                {{ $nda->signed_uploaded_at?->format('M d, Y g:i A') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 min-w-[11rem] max-w-xs">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $nda->approvalStatusBadgeClass() }}">
                                            {{ $nda->approvalStatusLabel() }}
                                        </span>
                                        @if($nda->reupload_allowed)
                                            <span class="inline-flex px-2 py-0.5 text-[11px] font-medium rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100">
                                                Reupload allowed
                                            </span>
                                        @endif
                                    </div>
                                    @if($nda->reviewed_at)
                                        <p class="text-xs text-gray-500 leading-snug">
                                            @if($nda->reviewer)
                                                <span class="text-gray-700"><x-user-name :user="$nda->reviewer" /></span>
                                                <span class="text-gray-400 mx-1">·</span>
                                            @endif
                                            {{ $nda->reviewed_at->format('M d, Y') }}
                                        </p>
                                    @endif
                                    @if($nda->review_notes)
                                        <p class="text-xs text-red-800 bg-red-50 border border-red-100 rounded-md px-2.5 py-1.5 leading-snug break-words">
                                            <span class="font-medium text-red-900">Note:</span> {{ $nda->review_notes }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                @include('admin.student-management.partials.nda-file-actions', [
                                    'nda' => $nda,
                                    'previewUrl' => $previewUrl,
                                    'effectiveStatus' => $effectiveStatus,
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                @if($isFiltered)
                                    No NDA submissions match your search or filters.
                                @else
                                    No signed NDA uploads yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ndas->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $ndas->links() }}
            </div>
        @endif
    </div>

    {{-- Preview modal --}}
    <div x-show="previewOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/75"
         @keydown.escape.window="previewOpen = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden"
             @click.outside="previewOpen = false">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Signed NDA — <span x-text="previewStudent"></span>
                </h3>
                <button type="button" @click="previewOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <iframe :src="previewUrl" class="w-full flex-1 min-h-[70vh] border-0" title="Signed NDA Preview"></iframe>
        </div>
    </div>

    {{-- Reject modal --}}
    <div x-show="rejectOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/75"
         @keydown.escape.window="rejectOpen = false">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden"
             @click.outside="rejectOpen = false">
            <form :action="rejectUrl" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Reject NDA</h3>
                    <p class="text-sm text-gray-600 mt-1" x-text="'Student: ' + rejectStudent"></p>
                    <p class="text-xs text-gray-500 mt-2">The signed file will be deleted from DigitalOcean Spaces. The student must upload a new NDA before attendance is unlocked.</p>
                </div>
                <div class="p-6">
                    <label for="review_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes for student (optional)</label>
                    <textarea name="review_notes" id="review_notes" rows="4" maxlength="1000"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                              placeholder="Explain what needs to be corrected…"></textarea>
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <button type="button" @click="rejectOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Reject NDA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
