@extends('layouts.user')

@section('title', 'Documents')

@section('content')
<div class="py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Documents</h1>
            <p class="mt-1 text-sm text-gray-600">Review, generate PDF, and sign required employee documents using your profile e-signature.</p>
        </div>

        @if(session('success'))
            <div class="rounded-md border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($documents as $document)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $document['label'] }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-gray-900">{{ $document['title'] }}</h2>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $document['signed'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $document['signed'] ? 'Signed' : 'Pending' }}
                        </span>
                    </div>
                    @if($document['signed'] && $document['signed_at'])
                        <p class="mt-3 text-xs text-gray-500">Signed {{ $document['signed_at']->format('M d, Y h:i A') }}</p>
                    @else
                        <p class="mt-3 text-xs text-gray-500">Sample document — review and sign with your e-signature.</p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('user.employee-documents.show', $document['type']) }}"
                           class="inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Open
                        </a>
                        <a href="{{ route('user.employee-documents.pdf', $document['type']) }}"
                           target="_blank"
                           class="inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                            View PDF
                        </a>
                        <a href="{{ route('user.employee-documents.pdf', ['type' => $document['type'], 'download' => 1]) }}"
                           class="inline-flex items-center justify-center px-3 py-1.5 border border-indigo-300 rounded-md text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                            Download PDF
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @unless($user->hasESignature())
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                Upload your e-signature on
                <a href="{{ route('profile.edit') }}" class="font-semibold underline hover:text-amber-700">My Profile</a>
                before signing documents.
            </div>
        @endunless
    </div>
</div>
@endsection
