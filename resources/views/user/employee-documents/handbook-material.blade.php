@extends('layouts.user')

@section('title', 'Employee Handbook')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Employee Handbook</h1>
            <p class="mt-1 text-sm text-gray-600">Read the complete employee handbook material below.</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @if($handbookPdfUrl)
                <div class="w-full" style="height: calc(100vh - 200px); min-height: 600px;">
                    <iframe
                        src="{{ $handbookPdfUrl }}"
                        class="w-full h-full border-0"
                        title="Employee Handbook"
                        style="min-height: 600px;">
                    </iframe>
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Handbook not available yet</h3>
                    <p class="mt-1 text-sm text-gray-500">The complete employee handbook has not been uploaded yet. Please check back later.</p>
                </div>
            @endif
        </div>

        @if($handbookPdfUrl)
            <div class="mt-4 flex justify-end">
                <a href="{{ route('user.employee-documents.handbook-material.pdf', ['download' => 1]) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download PDF
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
