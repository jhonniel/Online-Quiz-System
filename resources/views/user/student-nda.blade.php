@extends('layouts.user')

@section('title', 'Non-Disclosure Agreement (NDA)')

@section('content')
<div class="py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Non-Disclosure Agreement (NDA)</h1>
            <p class="mt-1 text-sm text-gray-600">
                Fill out the NDA form, generate the PDF, sign it, then upload the signed PDF back to the system.
            </p>
        </div>

        @if(session('success'))
            <div class="rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @php
            $hasSignedUpload = $nda && $nda->hasSignedUpload();
            $canUpload = ! $hasSignedUpload || ($nda && $nda->reupload_allowed);
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-medium text-gray-900">NDA Status</p>
                @if($hasSignedUpload)
                    <p class="text-sm text-green-700 mt-1 font-medium">Signed NDA uploaded</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $nda->signed_uploaded_at?->format('M d, Y h:i A') }}
                    </p>
                @else
                    <p class="text-sm text-gray-600 mt-1">No signed NDA uploaded yet.</p>
                @endif
                <span class="inline-flex mt-3 items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $canUpload ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $canUpload ? 'Upload Enabled' : 'Reupload Locked' }}
                </span>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm lg:col-span-2">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">NDA Details</h2>

                <form method="POST" action="{{ route('user.nda.generate-pdf') }}" class="space-y-4" id="nda-form">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="full_name" id="full_name" required maxlength="255"
                                   value="{{ old('full_name', $nda->full_name ?? $student->name) }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="id_number" class="block text-sm font-medium text-gray-700 mb-1">ID Number</label>
                            <input type="text" name="id_number" id="id_number" required maxlength="100"
                                   value="{{ old('id_number', $nda->id_number ?? '') }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('id_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="valid_id_type" class="block text-sm font-medium text-gray-700 mb-1">Valid ID Type</label>
                            <input type="text" name="valid_id_type" id="valid_id_type" required maxlength="100"
                                   value="{{ old('valid_id_type', $nda->valid_id_type ?? '') }}"
                                   placeholder="e.g. School ID, Driver's License"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('valid_id_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" name="city" id="city" required maxlength="100"
                                   value="{{ old('city', $nda->city ?? 'Davao') }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="agreement_date" class="block text-sm font-medium text-gray-700 mb-1">Agreement Date</label>
                            <input type="date" name="agreement_date" id="agreement_date" required
                                   value="{{ old('agreement_date', optional($nda?->agreement_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('agreement_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            Generate PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900 mb-2">Upload Signed NDA (PDF)</h2>
            <p class="text-sm text-gray-600 mb-4">
                After generating and signing the NDA, upload the signed PDF here. Company:
                <strong>{{ $branding['company_name'] }}</strong>
            </p>

            <form method="POST" action="{{ route('user.nda.store') }}" enctype="multipart/form-data" class="space-y-4" id="nda-upload-form">
                @csrf
                <input type="hidden" name="full_name" id="upload_full_name" value="{{ old('full_name', $nda->full_name ?? $student->name) }}">
                <input type="hidden" name="id_number" id="upload_id_number" value="{{ old('id_number', $nda->id_number ?? '') }}">
                <input type="hidden" name="valid_id_type" id="upload_valid_id_type" value="{{ old('valid_id_type', $nda->valid_id_type ?? '') }}">
                <input type="hidden" name="city" id="upload_city" value="{{ old('city', $nda->city ?? 'Davao') }}">
                <input type="hidden" name="agreement_date" id="upload_agreement_date" value="{{ old('agreement_date', optional($nda?->agreement_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">

                <div>
                    <label for="signed_pdf" class="block text-sm font-medium text-gray-700 mb-1">Signed NDA PDF</label>
                    <input type="file" name="signed_pdf" id="signed_pdf" accept="application/pdf"
                           {{ $canUpload ? '' : 'disabled' }}
                           class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500">PDF only, up to 10MB. The file must already be signed (handwritten, drawn, or e-signature) above the signature line. Unsigned generated PDFs are rejected.</p>
                    @error('signed_pdf')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                @if(! $canUpload)
                    <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                        Reupload is currently locked. Contact your administrator to allow a new upload.
                    </div>
                @endif

                @if($canUpload)
                <button type="submit"
                        id="nda-upload-submit"
                        class="hidden inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ $hasSignedUpload ? 'Reupload Signed NDA' : 'Upload Signed NDA' }}
                </button>
                @endif
            </form>
        </div>

        @if(!empty($previewUrl))
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Uploaded Signed NDA Preview</h2>
                <iframe src="{{ $previewUrl }}" class="w-full min-h-[50vh] h-[60vh] rounded border border-gray-200" title="Signed NDA Preview"></iframe>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('nda-form');
    const uploadForm = document.getElementById('nda-upload-form');
    if (!form || !uploadForm) return;

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
            if (source && target) {
                target.value = source.value;
            }
        });
    };

    form.addEventListener('input', syncFields);
    uploadForm.addEventListener('submit', syncFields);

    const fileInput = document.getElementById('signed_pdf');
    const uploadBtn = document.getElementById('nda-upload-submit');

    const toggleUploadButton = () => {
        if (!fileInput || !uploadBtn) {
            return;
        }

        if (fileInput.files && fileInput.files.length > 0) {
            uploadBtn.classList.remove('hidden');
        } else {
            uploadBtn.classList.add('hidden');
        }
    };

    if (fileInput) {
        fileInput.addEventListener('change', toggleUploadButton);
        toggleUploadButton();
    }
});
</script>
@endsection
