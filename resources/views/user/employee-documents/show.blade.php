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
                        Same Non-Disclosure Agreement format used for students. Sign with your profile e-signature.
                    @elseif($type === 'policy')
                        Company Policy Acknowledgment. Sign with your profile e-signature.
                    @else
                        Sample {{ $label }} document for employee acknowledgment.
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
                    @if($user->hasESignature())
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
                            View PDF
                        </a>
                        <a href="{{ route('user.employee-documents.pdf', ['type' => $type, 'download' => 1]) }}"
                           class="inline-flex items-center justify-center px-4 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                            Download PDF
                        </a>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            @if($user->hasESignature())
                                <img src="{{ $user->getESignatureUrl() }}" alt="E-Signature preview" class="max-h-16 object-contain opacity-80">
                                <p class="mt-2 text-xs text-gray-500">Your profile e-signature is applied automatically when you open this document.</p>
                            @else
                                <p class="text-sm text-amber-700">
                                    No e-signature on file.
                                    <a href="{{ route('profile.edit') }}" class="font-semibold underline">Upload on your profile</a>
                                    to sign this document.
                                </p>
                            @endif
                        </div>
                        @if($user->hasESignature())
                            <form method="POST" action="{{ route('user.employee-documents.sign', $type) }}"
                                  onsubmit="return confirm('Sign this {{ $label }} with your e-signature?');">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                    Sign with E-Signature
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
