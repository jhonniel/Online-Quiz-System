@extends('layouts.user')

@section('title', 'Non-Disclosure Agreement (NDA)')
@section('page-title', 'Non-Disclosure Agreement (NDA)')

@section('content')
@php
    $hasSignedUpload = $nda && $nda->hasViewableSignedUpload();
    $canUpload = ! $nda || $nda->canUploadSignedDocument();
    $canEditDetails = ! $nda || $nda->canEditNdaDetails();
    $wasRejected = $nda && $nda->isRejected() && ! $nda->hasSignedUpload();
    $isApproved = $nda && $nda->isApprovedForAttendance();
    $isPending = $nda && $nda->isPendingApproval();
    $inputClass = 'w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500';
    $inputDisabledClass = 'w-full px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 bg-gray-50 cursor-not-allowed';
    $labelClass = 'block text-sm font-medium text-gray-700 mb-1.5';
@endphp

<div class="h-full flex flex-col min-h-0 min-w-0 -mx-3 sm:-mx-4 lg:-mx-6 w-[calc(100%+1.5rem)] sm:w-[calc(100%+2rem)] lg:w-[calc(100%+3rem)]">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 sm:p-5 flex-shrink-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0">
                <div class="hidden sm:flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Non-Disclosure Agreement</h1>
                    <p class="text-indigo-100 text-sm mt-1 leading-snug">
                        Generate, sign, and upload your NDA. An administrator must approve it before you can record attendance.
                    </p>
                </div>
            </div>
            @if($isApproved)
                <a href="{{ url('/dtr') }}"
                   class="inline-flex items-center justify-center gap-2 self-start rounded-lg bg-white/15 border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/25 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Go to DTR
                </a>
            @endif
        </div>
    </div>

    <div class="bg-gray-50 border-t border-gray-200 flex-1 min-h-0 overflow-auto">
        <div class="w-full p-4 sm:p-6 lg:px-8 space-y-5">

            @if(session('success'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 flex items-start gap-3">
                    <svg class="h-5 w-5 text-green-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <p class="text-sm font-medium text-red-800">Please fix the following:</p>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-700 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Steps --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">How it works</p>
                <ol class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @php
                        $steps = [
                            ['step' => '1', 'title' => 'Fill in details', 'done' => filled($nda?->full_name)],
                            ['step' => '2', 'title' => 'Generate & sign PDF', 'done' => $hasSignedUpload || $isPending || $isApproved],
                            ['step' => '3', 'title' => 'Upload signed NDA', 'done' => $hasSignedUpload || $isPending || $isApproved],
                            ['step' => '4', 'title' => 'Admin approval', 'done' => $isApproved],
                        ];
                    @endphp
                    @foreach($steps as $item)
                        <li class="flex items-center gap-3 rounded-lg border {{ $item['done'] ? 'border-emerald-200 bg-emerald-50/50' : 'border-gray-100 bg-gray-50/50' }} px-3 py-2.5">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $item['done'] ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                                @if($item['done'])
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    {{ $item['step'] }}
                                @endif
                            </span>
                            <span class="text-sm font-medium {{ $item['done'] ? 'text-emerald-900' : 'text-gray-700' }}">{{ $item['title'] }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                {{-- Status card --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h2 class="text-sm font-semibold text-gray-900">Submission status</h2>
                    </div>

                    @if($hasSignedUpload)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $nda->approvalStatusBadgeClass() }}">
                            {{ $nda->approvalStatusLabel() }}
                        </span>
                        <p class="text-xs text-gray-500 mt-2">
                            Uploaded {{ $nda->signed_uploaded_at?->format('M d, Y · g:i A') }}
                        </p>
                        @if($isApproved)
                            <p class="mt-3 text-sm text-emerald-700 font-medium rounded-lg bg-emerald-50 border border-emerald-100 px-3 py-2">
                                You can record attendance on your DTR page.
                            </p>
                        @elseif($isPending)
                            <p class="mt-3 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-100 px-3 py-2">
                                Waiting for administrator approval.
                            </p>
                        @elseif($nda->isRejected())
                            <p class="mt-3 text-sm text-red-800 rounded-lg bg-red-50 border border-red-100 px-3 py-2">
                                Please upload a corrected signed NDA.
                            </p>
                            @if($nda->review_notes)
                                <p class="mt-2 text-xs text-red-700 bg-red-50/80 border border-red-100 rounded-lg px-3 py-2">
                                    <span class="font-semibold">Admin note:</span> {{ $nda->review_notes }}
                                </p>
                            @endif
                        @endif
                    @elseif($wasRejected)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $nda->approvalStatusBadgeClass() }}">
                            {{ $nda->approvalStatusLabel() }}
                        </span>
                        <p class="mt-3 text-sm text-red-800 rounded-lg bg-red-50 border border-red-100 px-3 py-2">
                            Your previous signed NDA was rejected and removed. Upload a corrected signed PDF below.
                        </p>
                        @if($nda->review_notes)
                            <p class="mt-2 text-xs text-red-700 bg-red-50/80 border border-red-100 rounded-lg px-3 py-2">
                                <span class="font-semibold">Admin note:</span> {{ $nda->review_notes }}
                            </p>
                        @endif
                    @else
                        <p class="text-sm text-gray-600">No signed NDA uploaded yet.</p>
                        <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                            Complete the form, download the PDF, sign it, then upload the signed copy here.
                        </p>
                    @endif

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        @if($isApproved)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                NDA locked — approved
                            </span>
                            <p class="text-xs text-gray-500 mt-2">Details and uploads cannot be changed unless an administrator reopens your submission.</p>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $canUpload ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $canUpload ? 'Upload enabled' : 'Reupload locked' }}
                            </span>
                            @if(! $canUpload)
                                <p class="text-xs text-gray-500 mt-2">Contact your administrator to allow a new upload.</p>
                            @endif
                        @endif
                    </div>

                    <p class="mt-4 text-xs text-gray-500">
                        Company: <span class="font-medium text-gray-700">{{ $branding['company_name'] ?? '—' }}</span>
                    </p>
                </div>

                {{-- NDA form --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm lg:col-span-3 xl:col-span-4">
                    <h2 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">1</span>
                        NDA details
                    </h2>
                    <p class="text-xs text-gray-500 mb-4 ml-8">
                        @if($canEditDetails)
                            These fields appear on your generated NDA document.
                        @else
                            Your approved NDA details are read-only and cannot be changed.
                        @endif
                    </p>

                    @if(! $canEditDetails)
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 mb-4">
                            This NDA is approved. You cannot edit details or generate a new unsigned PDF.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.nda.generate-pdf') }}" class="space-y-4" id="nda-form">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label for="full_name" class="{{ $labelClass }}">Full name</label>
                                <input type="text" name="full_name" id="full_name" {{ $canEditDetails ? 'required' : 'disabled readonly' }} maxlength="255"
                                       value="{{ old('full_name', $nda->full_name ?? $student->name) }}"
                                       class="{{ $canEditDetails ? $inputClass : $inputDisabledClass }}">
                                @error('full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="id_number" class="{{ $labelClass }}">ID number</label>
                                <input type="text" name="id_number" id="id_number" {{ $canEditDetails ? 'required' : 'disabled readonly' }} maxlength="100"
                                       value="{{ old('id_number', $nda->id_number ?? '') }}"
                                       class="{{ $canEditDetails ? $inputClass : $inputDisabledClass }}">
                                @error('id_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="valid_id_type" class="{{ $labelClass }}">Valid ID type</label>
                                <input type="text" name="valid_id_type" id="valid_id_type" {{ $canEditDetails ? 'required' : 'disabled readonly' }} maxlength="100"
                                       value="{{ old('valid_id_type', $nda->valid_id_type ?? '') }}"
                                       placeholder="e.g. School ID, Driver's License"
                                       class="{{ $canEditDetails ? $inputClass : $inputDisabledClass }}">
                                @error('valid_id_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="city" class="{{ $labelClass }}">City</label>
                                <input type="text" name="city" id="city" {{ $canEditDetails ? 'required' : 'disabled readonly' }} maxlength="100"
                                       value="{{ old('city', $nda->city ?? 'Davao') }}"
                                       class="{{ $canEditDetails ? $inputClass : $inputDisabledClass }}">
                                @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="agreement_date" class="{{ $labelClass }}">Agreement date</label>
                                <input type="date" name="agreement_date" id="agreement_date" {{ $canEditDetails ? 'required' : 'disabled readonly' }}
                                       value="{{ old('agreement_date', optional($nda?->agreement_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                       class="{{ $canEditDetails ? $inputClass : $inputDisabledClass }}">
                                @error('agreement_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 pt-1 border-t border-gray-100">
                            @if($canEditDetails)
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 shadow-sm transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Download unsigned PDF
                                </button>
                                <p class="self-center text-xs text-gray-500 w-full sm:w-auto">Sign the PDF before uploading in step 2.</p>
                            @else
                                <button type="button" disabled
                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gray-200 text-gray-500 text-sm font-medium cursor-not-allowed">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Download unsigned PDF
                                </button>
                                <p class="self-center text-xs text-gray-500 w-full sm:w-auto">PDF generation is disabled for approved NDAs.</p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Upload --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">2</span>
                    Upload signed NDA
                </h2>
                <p class="text-xs text-gray-500 mb-4 ml-8">
                    @if($isApproved)
                        Your approved signed NDA is on file. Uploads are locked.
                    @else
                        The PDF must include your signature above the signature line. Unsigned files are rejected.
                    @endif
                </p>

                @if($isApproved)
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                        Upload is disabled because your NDA has been approved.
                    </div>
                @else
                <form method="POST" action="{{ route('user.nda.store') }}" enctype="multipart/form-data" class="space-y-4" id="nda-upload-form">
                    @csrf
                    <input type="hidden" name="full_name" id="upload_full_name" value="{{ old('full_name', $nda->full_name ?? $student->name) }}">
                    <input type="hidden" name="id_number" id="upload_id_number" value="{{ old('id_number', $nda->id_number ?? '') }}">
                    <input type="hidden" name="valid_id_type" id="upload_valid_id_type" value="{{ old('valid_id_type', $nda->valid_id_type ?? '') }}">
                    <input type="hidden" name="city" id="upload_city" value="{{ old('city', $nda->city ?? 'Davao') }}">
                    <input type="hidden" name="agreement_date" id="upload_agreement_date" value="{{ old('agreement_date', optional($nda?->agreement_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">

                    <div>
                        <label for="signed_pdf" class="{{ $labelClass }}">Signed NDA (PDF)</label>
                        <label for="signed_pdf"
                               class="w-full flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-xl border-2 border-dashed {{ $canUpload ? 'border-indigo-300 bg-indigo-50/50 hover:bg-indigo-50 cursor-pointer' : 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-70' }} px-4 py-5 text-sm transition-colors">
                            <span class="flex items-center gap-3 min-w-0 text-gray-700">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white border border-indigo-200 text-indigo-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </span>
                                <span>
                                    <span id="signed-pdf-label" class="font-medium text-indigo-700 block">Choose signed PDF file</span>
                                    <span class="text-xs text-gray-500">PDF only · max 10 MB</span>
                                </span>
                            </span>
                        </label>
                        <input type="file" name="signed_pdf" id="signed_pdf" accept="application/pdf"
                               {{ $canUpload ? '' : 'disabled' }}
                               class="sr-only">
                        @error('signed_pdf')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    @if(! $canUpload)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            Reupload is locked until an administrator allows it.
                        </div>
                    @elseif($canUpload)
                        <button type="submit"
                                id="nda-upload-submit"
                                disabled
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span id="nda-upload-submit-label">{{ $hasSignedUpload ? 'Reupload signed NDA' : 'Upload signed NDA' }}</span>
                        </button>
                    @endif
                </form>
                @endif
            </div>

            @if(!empty($previewUrl))
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="px-4 sm:px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between gap-2">
                        <h2 class="text-sm font-semibold text-gray-900">Your uploaded NDA</h2>
                        <a href="{{ $previewUrl }}" target="_blank" rel="noopener"
                           class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Open in new tab</a>
                    </div>
                    <div class="p-3 sm:p-4 bg-gray-100">
                        <iframe src="{{ $previewUrl }}" class="w-full rounded-lg border border-gray-200 bg-white min-h-[420px] h-[55vh]" title="Signed NDA Preview"></iframe>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('nda-form');
    const uploadForm = document.getElementById('nda-upload-form');
    if (!form) return;
    if (!uploadForm) return;

    const syncFields = () => {
        const map = {
            full_name: 'upload_full_name',
            id_number: 'upload_id_number',
            valid_id_type: 'upload_valid_id_type',
            city: 'upload_city',
            agreement_date: 'upload_agreement_date',
        };
        Object.entries(map).forEach(([sourceName, targetId]) => {
            const source = form.querySelector(`[name="${sourceName}"]`);
            const target = document.getElementById(targetId);
            if (source && target) target.value = source.value;
        });
    };

    form.addEventListener('input', syncFields);
    uploadForm.addEventListener('submit', syncFields);
    syncFields();

    const fileInput = document.getElementById('signed_pdf');
    const uploadBtn = document.getElementById('nda-upload-submit');
    const fileLabel = document.getElementById('signed-pdf-label');

    const toggleUploadButton = () => {
        if (!fileInput || !uploadBtn) return;
        const hasFile = fileInput.files && fileInput.files.length > 0;
        uploadBtn.disabled = !hasFile;
        if (fileLabel && hasFile) {
            fileLabel.textContent = fileInput.files[0].name;
        } else if (fileLabel) {
            fileLabel.textContent = 'Choose signed PDF file';
        }
    };

    if (fileInput) {
        fileInput.addEventListener('change', toggleUploadButton);
        toggleUploadButton();
    }
});
</script>
@endsection
