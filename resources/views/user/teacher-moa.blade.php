@extends('layouts.user')

@section('page-title', 'Upload MOA')

@section('content')
<div class="h-full flex flex-col min-h-0 min-w-0">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-3 sm:p-4 flex-shrink-0">
        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white break-words">Upload MOA</h1>
        <p class="text-indigo-100 text-sm mt-1 leading-snug break-words max-w-none">Upload your MOA PDF document. After you upload, replacing it stays locked until the company reopens MOA upload.</p>
    </div>

    <div class="bg-gray-50 border-t border-gray-200 flex-1 min-h-0 overflow-auto">
        <div class="w-full p-4 sm:p-6 space-y-4">
            @if(session('success'))
                <div class="rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $hasMoa = !empty($teacher->moa_document_path);
                $canUpload = !$hasMoa || (bool) $teacher->moa_reupload_allowed;
                $moaUploadedAt = null;
                if (!empty($teacher->moa_uploaded_at)) {
                    $moaUploadedAt = $teacher->moa_uploaded_at instanceof \Carbon\Carbon
                        ? $teacher->moa_uploaded_at
                        : \Carbon\Carbon::parse($teacher->moa_uploaded_at);
                }
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-1">
                    <div class="flex flex-col gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>MOA Status</span>
                            </p>
                            @if($hasMoa)
                                <p class="text-sm text-gray-600 mt-1">Uploaded{{ $moaUploadedAt ? ' on '.$moaUploadedAt->format('M d, Y h:i A') : '' }}</p>
                            @else
                                <p class="text-sm text-gray-600 mt-1">No MOA uploaded yet.</p>
                            @endif
                        </div>
                        <span class="inline-flex w-fit items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $canUpload ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $canUpload ? 'Upload Enabled' : 'Reupload Locked' }}
                        </span>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm lg:col-span-2">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V8m0 0l-3 3m3-3l3 3M5 19h14"></path>
                        </svg>
                        <span>{{ $hasMoa ? 'Replace MOA' : 'Submit MOA' }}</span>
                    </h2>
                    <form method="POST" action="{{ url('/teacher/moa') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label for="moa_pdf" class="block text-sm font-medium text-gray-700 mb-1">MOA PDF</label>
                            <label for="moa_pdf" class="w-full flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-lg border border-dashed border-indigo-300 bg-indigo-50/60 px-3 sm:px-4 py-3 text-sm text-indigo-700 cursor-pointer hover:bg-indigo-50 transition-colors">
                                <span class="flex items-center gap-2 min-w-0">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V8m0 0l-3 3m3-3l3 3M5 19h14"></path>
                                    </svg>
                                    <span>Select PDF file</span>
                                </span>
                                <span class="text-xs text-indigo-600">Max 10MB</span>
                            </label>
                            <input
                                id="moa_pdf"
                                type="file"
                                name="moa_pdf"
                                accept="application/pdf"
                                {{ $canUpload ? '' : 'disabled' }}
                                class="sr-only"
                            >
                            <p class="mt-2 text-xs text-gray-500">PDF only, up to 10MB.</p>
                            @error('moa_pdf')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @if(!$canUpload)
                            <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                                Reupload is currently locked. Ask the company to reopen MOA upload.
                            </div>
                        @endif
                        <button
                            type="submit"
                            {{ $canUpload ? '' : 'disabled' }}
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ $hasMoa ? 'Reupload MOA' : 'Upload MOA' }}
                        </button>
                    </form>
                </div>
            </div>

            @if(!empty($moaUrl))
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>Current MOA Preview</span>
                    </h2>
                    <iframe src="{{ $moaUrl }}" class="w-full min-h-[40vh] h-[52vh] sm:h-[60vh] md:h-[72vh] max-h-[85vh] rounded border border-gray-200" title="MOA PDF Preview"></iframe>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                    No MOA preview available yet. Upload a PDF to preview it here.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

