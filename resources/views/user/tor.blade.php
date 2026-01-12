@extends('layouts.user')

@section('title', 'Term of Reference (TOR)')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Term of Reference (TOR)</h1>
            <p class="mt-1 text-sm text-gray-600">Please read the Term of Reference document below.</p>
        </div>

        <!-- PDF Viewer -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @php
                $torPdfPath = \App\Models\Setting::get('hiring_tor_pdf');
                $torUrl = null;
                
                if ($torPdfPath) {
                    $assetDisk = 'digitalocean';
                    try {
                        if (\Illuminate\Support\Facades\Storage::disk($assetDisk)->exists($torPdfPath)) {
                            // Try to get a temporary URL for iframe display
                            if (method_exists(\Illuminate\Support\Facades\Storage::disk($assetDisk), 'temporaryUrl')) {
                                $torUrl = \Illuminate\Support\Facades\Storage::disk($assetDisk)
                                    ->temporaryUrl($torPdfPath, now()->addMinutes(60));
                            } else {
                                // Fallback: use the direct stream route
                                $torUrl = route('landing.tor-pdf');
                            }
                        } else {
                            // Fallback to public disk
                            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($torPdfPath)) {
                                $torUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($torPdfPath);
                            } else {
                                // Use the stream route as last resort
                                $torUrl = route('landing.tor-pdf');
                            }
                        }
                    } catch (\Exception $e) {
                        // Use the stream route as fallback
                        $torUrl = route('landing.tor-pdf');
                    }
                }
            @endphp

            @if($torUrl)
                <div class="w-full" style="height: calc(100vh - 200px); min-height: 600px;">
                    <iframe 
                        src="{{ $torUrl }}" 
                        class="w-full h-full border-0"
                        title="Term of Reference (TOR)"
                        style="min-height: 600px;">
                    </iframe>
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">TOR PDF not available</h3>
                    <p class="mt-1 text-sm text-gray-500">The Term of Reference document has not been uploaded yet.</p>
                </div>
            @endif
        </div>

        <!-- Download Option -->
        @if($torUrl)
        <div class="mt-4 flex justify-end">
            <a href="{{ $torUrl }}" target="_blank" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download PDF
            </a>
        </div>
        @endif
    </div>
</div>
@endsection















