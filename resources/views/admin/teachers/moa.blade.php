@extends('layouts.admin')

@section('page-title', 'Teachers MOA')

@section('content')
<div x-data="{ previewOpen: false, previewUrl: '', previewTeacher: '' }" class="space-y-4 px-3 sm:px-4 lg:px-6">
    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-900">Teachers MOA</h1>
        <p class="text-sm text-gray-600 mt-1">Review teacher MOA uploads and allow reupload when needed.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <form method="GET" action="{{ url('/admin/teachers-management/moa') }}" class="flex flex-col sm:flex-row gap-2">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search teacher name or email" class="w-full sm:max-w-md rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Search</button>
                <a href="{{ url('/admin/teachers-management/moa') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300">Clear</a>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MOA Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($teachers as $teacher)
                        @php
                            $hasMoa = !empty($teacher->moa_document_path);
                            $moaUrl = $hasMoa ? url('/admin/teachers-management/moa/'.$teacher->id.'/preview') : '';
                            $moaUploadedAt = null;
                            if (!empty($teacher->moa_uploaded_at)) {
                                $moaUploadedAt = $teacher->moa_uploaded_at instanceof \Carbon\Carbon
                                    ? $teacher->moa_uploaded_at
                                    : \Carbon\Carbon::parse($teacher->moa_uploaded_at);
                            }
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-gray-900">{{ $teacher->name }}</div>
                                <div class="text-gray-500">{{ $teacher->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ optional($teacher->university)->name ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($hasMoa)
                                    <div class="text-green-700 font-medium">Uploaded</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $moaUploadedAt ? $moaUploadedAt->format('M d, Y h:i A') : 'Date unavailable' }}
                                    </div>
                                    <div class="text-xs mt-1 {{ $teacher->moa_reupload_allowed ? 'text-green-700' : 'text-amber-700' }}">
                                        {{ $teacher->moa_reupload_allowed ? 'Reupload allowed' : 'Reupload locked' }}
                                    </div>
                                @else
                                    <span class="text-gray-500">Not uploaded</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($hasMoa && $moaUrl)
                                    <button
                                        type="button"
                                        @click="previewUrl = '{{ $moaUrl }}'; previewTeacher = '{{ e($teacher->name) }}'; previewOpen = true"
                                        class="text-indigo-600 hover:text-indigo-800"
                                    >
                                        Open PDF
                                    </button>
                                @else
                                    <span class="text-gray-400">No file</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <form method="POST" action="{{ url('/admin/teachers-management/moa/'.$teacher->id.'/allow-reupload') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        {{ $teacher->moa_reupload_allowed ? 'disabled' : '' }}
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Allow Reupload
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No teachers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $teachers->links() }}
        </div>
    </div>
<div
    x-show="previewOpen"
    x-transition:enter="transition-opacity duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-3 sm:p-6"
    @click.self="previewOpen = false"
    style="display: none;"
>
    <div class="w-full max-w-5xl bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm sm:text-base font-semibold text-gray-900">
                MOA Preview - <span x-text="previewTeacher"></span>
            </h3>
            <button type="button" @click="previewOpen = false" class="text-gray-500 hover:text-gray-700 text-sm">Close</button>
        </div>
        <iframe :src="previewUrl" class="w-full h-[70vh]" title="Teacher MOA Preview"></iframe>
    </div>
</div>
</div>
@endsection

