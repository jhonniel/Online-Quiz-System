@extends('layouts.user')

@section('title', $material['name'])

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ $material['name'] }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $subtitle }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="w-full" style="height: calc(100vh - 200px); min-height: 600px;">
                <iframe
                    src="{{ $pdfUrl }}"
                    class="w-full h-full border-0"
                    title="{{ $material['name'] }}"
                    style="min-height: 600px;">
                </iframe>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <a href="{{ $downloadUrl }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download PDF
            </a>
        </div>
    </div>
</div>
@endsection
