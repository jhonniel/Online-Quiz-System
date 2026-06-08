@extends('layouts.admin')

@section('page-title', 'NDA Files')

@section('content')
<div x-data="{ previewOpen: false, previewUrl: '', previewStudent: '' }" class="space-y-4 px-3 sm:px-4 lg:px-6">
    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-900">NDA Files</h1>
        <p class="text-sm text-gray-600 mt-1">View signed Non-Disclosure Agreements uploaded by students.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <form method="GET" action="{{ route('admin.student-nda-files.index') }}" class="flex flex-col sm:flex-row gap-2">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search student, name, or ID number"
                   class="w-full sm:max-w-md rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Search</button>
                <a href="{{ route('admin.student-nda-files.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300">Clear</a>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NDA Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Details</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agreement Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($ndas as $nda)
                        @php
                            $previewUrl = route('admin.student-nda-files.preview', $nda);
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-gray-900">{{ $nda->user?->name ?? '—' }}</div>
                                <div class="text-gray-500">{{ $nda->user?->email ?? '' }}</div>
                                <div class="text-xs text-gray-400">{{ $nda->user?->university?->name ?? 'No school' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $nda->full_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div>{{ $nda->id_number }}</div>
                                <div class="text-xs text-gray-500">{{ $nda->valid_id_type }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $nda->agreement_date?->format('M d, Y') ?? '—' }}
                                <div class="text-xs text-gray-500">City of {{ $nda->city }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $nda->signed_uploaded_at?->format('M d, Y h:i A') ?? '—' }}
                                <div class="text-xs mt-1 {{ $nda->reupload_allowed ? 'text-green-700' : 'text-amber-700' }}">
                                    {{ $nda->reupload_allowed ? 'Reupload allowed' : 'Reupload locked' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <button type="button"
                                        @click="previewUrl = '{{ $previewUrl }}'; previewStudent = '{{ e($nda->user?->name ?? 'Student') }}'; previewOpen = true"
                                        class="text-indigo-600 hover:text-indigo-800">
                                    Open PDF
                                </button>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <form method="POST" action="{{ route('admin.student-nda-files.allow-reupload', $nda) }}">
                                    @csrf
                                    <button type="submit"
                                            {{ $nda->reupload_allowed ? 'disabled' : '' }}
                                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Allow Reupload
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No signed NDA files uploaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ndas->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $ndas->links() }}
            </div>
        @endif
    </div>

    <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="previewOpen = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col" @click.outside="previewOpen = false">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900" x-text="'Signed NDA — ' + previewStudent"></h3>
                <button type="button" @click="previewOpen = false" class="text-gray-500 hover:text-gray-700">Close</button>
            </div>
            <iframe :src="previewUrl" class="w-full flex-1 min-h-[70vh] border-0" title="Signed NDA Preview"></iframe>
        </div>
    </div>
</div>
@endsection
