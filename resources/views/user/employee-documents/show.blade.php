@extends('layouts.user')

@section('title', $title)

@section('content')
@php
    $docSubtitle = match ($type) {
        'nda' => 'Review the Non-Disclosure Agreement below, then sign with your profile e-signature.',
        'policy' => 'Review the Company Policy Acknowledgment below, then sign with your profile e-signature.',
        default => 'Review this '.$label.' document, then sign with your profile e-signature.',
    };
@endphp
<x-user-page-shell fluid class="space-y-5 pb-6 -mx-3 px-3 sm:-mx-4 sm:px-4 lg:-mx-6 lg:px-6 max-w-5xl mx-auto w-full">
    <x-user-page-header :title="$title" :subtitle="$docSubtitle">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </x-slot:icon>
        <x-slot:subtitleSlot>
            <a href="{{ route('user.employee-documents.index') }}" class="text-indigo-100/90 hover:text-white text-sm font-medium">&larr; Back to Documents</a>
            <p class="mt-1">{{ $docSubtitle }}</p>
        </x-slot:subtitleSlot>
        <x-slot:actions>
            <span class="col-span-full inline-flex w-full items-center justify-center px-3 py-2 rounded-full text-xs font-semibold sm:col-auto sm:w-auto sm:py-1.5 {{ ($signature && $signature->isSigned()) ? 'bg-white/20 text-white ring-1 ring-white/30' : 'bg-amber-400/90 text-amber-950' }}">
                {{ ($signature && $signature->isSigned()) ? 'Signed' : 'Pending signature' }}
            </span>
            <x-user-header-btn-secondary href="{{ route('user.employee-documents.pdf', $type) }}" target="_blank">View PDF</x-user-header-btn-secondary>
            <x-user-header-btn-primary href="{{ route('user.employee-documents.pdf', ['type' => $type, 'download' => 1]) }}">Download</x-user-header-btn-primary>
        </x-slot:actions>
    </x-user-page-header>

    @if(session('success'))
        <x-user-notice tone="success">{{ session('success') }}</x-user-notice>
    @endif

    @error('signature')
        <x-user-notice tone="danger">{{ $message }}</x-user-notice>
    @enderror

    <x-user-dashboard-section title="Document" description="Read the full text before signing.">
        <div class="overflow-hidden -mx-1 sm:mx-0 rounded-lg border border-slate-200 bg-white">
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
                            <p class="mt-2 text-sm font-medium text-gray-900"><x-user-name :user="$user" /></p>
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
    </x-user-dashboard-section>
</x-user-page-shell>

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
