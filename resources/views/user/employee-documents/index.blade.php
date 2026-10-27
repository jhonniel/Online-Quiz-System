@extends('layouts.user')

@section('title', 'Documents')

@section('content')
<x-user-page-shell fluid class="space-y-5 pb-6 -mx-3 px-3 sm:-mx-4 sm:px-4 lg:-mx-6 lg:px-6">
    <x-user-page-header title="Documents" subtitle="Review and sign required documents with your profile e-signature.">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </x-slot:icon>
    </x-user-page-header>

    @if(session('success'))
        <x-user-notice tone="success">{{ session('success') }}</x-user-notice>
    @endif

    <x-user-dashboard-section title="Required documents" description="Open each item to review and sign when ready.">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 -mt-1">
            @foreach($documents as $document)
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 shadow-sm ring-1 ring-slate-900/5">
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
                        <p class="mt-3 text-xs text-gray-500">Open the document and sign with your e-signature.</p>
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
            <x-user-notice tone="warning" class="mt-4" title="E-signature required">
                Upload your e-signature on
                <a href="{{ route('profile.edit') }}" class="font-semibold underline hover:text-amber-900">My Profile</a>
                before signing documents.
            </x-user-notice>
        @endunless
    </x-user-dashboard-section>
</x-user-page-shell>
@endsection
