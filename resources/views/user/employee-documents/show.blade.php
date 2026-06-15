@extends('layouts.user')

@section('title', $title)

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('user.employee-documents.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Documents</a>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $title }}</h1>
                <p class="mt-1 text-sm text-gray-600">
                    @if($type === 'nda')
                        Review the Non-Disclosure Agreement below, then sign with your profile e-signature.
                    @elseif($type === 'policy')
                        Review the Company Policy Acknowledgment below, then sign with your profile e-signature.
                    @else
                        Review this {{ $label }} document, then sign with your profile e-signature.
                    @endif
                </p>
            </div>
            <div class="flex flex-col items-end gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ ($signature && $signature->isSigned()) ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ ($signature && $signature->isSigned()) ? 'Signed' : 'Pending Signature' }}
                </span>
                <div class="flex flex-wrap justify-end gap-2">
                    <a href="{{ route('user.employee-documents.pdf', $type) }}"
                       target="_blank"
                       class="inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                        View PDF
                    </a>
                    <a href="{{ route('user.employee-documents.pdf', ['type' => $type, 'download' => 1]) }}"
                       class="inline-flex items-center justify-center px-3 py-1.5 border border-indigo-300 rounded-md text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                        Download PDF
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @error('signature')
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
        @enderror

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            @if($type === 'contract' && $contractView)
                @include('user.employee-documents.partials.contract-content', $contractView)
            @elseif($type === 'nda' && $ndaView)
                @include('user.employee-documents.partials.nda-content', $ndaView)
            @elseif($type === 'policy' && $policyView)
                @include('user.employee-documents.partials.policy-content', $policyView)
            @elseif($type === 'handbook' && $handbookView)
                @include('user.employee-documents.partials.handbook-content', $handbookView)
            @else
                <div class="px-6 py-6 space-y-4 text-sm text-gray-800 leading-relaxed">
                    @foreach($paragraphs as $paragraph)
                        <p class="text-justify">{{ $paragraph }}</p>
                    @endforeach
                    @if($signature && $signature->isSigned() && $user->hasESignature())
                        <div class="pt-6 border-t border-gray-100">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Employee E-Signature</p>
                            <img src="{{ $user->getESignatureUrl() }}" alt="E-Signature" class="max-h-16 object-contain">
                        </div>
                    @endif
                </div>
            @endif
            <div class="border-t border-gray-100 px-6 py-5 bg-gray-50">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Employee Signature</p>
                @if($signature && $signature->isSigned())
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            @if($user->hasESignature())
                                <img src="{{ $user->getESignatureUrl() }}" alt="E-Signature" class="max-h-16 object-contain">
                            @endif
                            <p class="mt-2 text-sm font-medium text-gray-900">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">Signed {{ $signature->signed_at?->format('F j, Y h:i A') }}</p>
                        </div>
                        <a href="{{ route('user.employee-documents.pdf', $type) }}"
                           target="_blank"
                           class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            View Signed PDF
                        </a>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="space-y-3">
                            <div class="rounded-lg border border-dashed border-gray-300 bg-white px-4 py-3">
                                <p class="text-xs text-gray-500">Your e-signature will appear on this document after you sign.</p>
                            </div>
                            @if($user->hasESignature())
                                <div>
                                    <p class="text-xs font-medium text-gray-600 mb-2">E-signature to be applied:</p>
                                    <img src="{{ $user->getESignatureUrl() }}" alt="E-Signature preview" class="max-h-16 object-contain opacity-80">
                                </div>
                            @else
                                <p class="text-sm text-amber-700">
                                    No e-signature on file.
                                    <a href="{{ route('profile.edit') }}" class="font-semibold underline">Upload on your profile</a>
                                    before you can sign this document.
                                </p>
                            @endif
                        </div>
                        @if($user->hasESignature())
                            <button type="button"
                                    onclick="openDocumentSignModal()"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shrink-0">
                                Sign Document
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($user->hasESignature() && ! ($signature && $signature->isSigned()))
    <div id="document-sign-modal"
         class="fixed inset-0 z-50 hidden"
         aria-hidden="true"
         data-password-input-id="{{ $user->hasP12Certificate() ? 'document_sign_p12_password' : 'document_sign_password' }}"
         data-reopen-on-error="{{ ($errors->has('password') || $errors->has('p12_certificate_password')) ? '1' : '0' }}">
        <div class="absolute inset-0 bg-gray-900/50" onclick="closeDocumentSignModal()"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-lg bg-white shadow-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Sign {{ $label }}</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($user->hasP12Certificate())
                            Enter your P12 certificate password to confirm signing this document.
                        @else
                            Enter your account password to confirm signing this document with your e-signature.
                        @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('user.employee-documents.sign', $type) }}" class="px-6 py-5 space-y-4">
                    @csrf
                    @if($user->hasP12Certificate())
                        <div>
                            <label for="document_sign_p12_password" class="block text-sm font-medium text-gray-700 mb-1">P12 Certificate Password</label>
                            <input type="password"
                                   id="document_sign_p12_password"
                                   name="p12_certificate_password"
                                   required
                                   autocomplete="off"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('p12_certificate_password') border-red-500 @enderror">
                            @error('p12_certificate_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div>
                            <label for="document_sign_password" class="block text-sm font-medium text-gray-700 mb-1">Your Password</label>
                            <input type="password"
                                   id="document_sign_password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button"
                                onclick="closeDocumentSignModal()"
                                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Confirm and Sign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDocumentSignModal() {
            const modal = document.getElementById('document-sign-modal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            const passwordInputId = modal.dataset.passwordInputId;
            const passwordInput = passwordInputId ? document.getElementById(passwordInputId) : null;
            if (passwordInput) passwordInput.focus();
        }

        function closeDocumentSignModal() {
            const modal = document.getElementById('document-sign-modal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('document-sign-modal');
            if (modal && modal.dataset.reopenOnError === '1') {
                openDocumentSignModal();
            }
        });
    </script>
@endif
@endsection
