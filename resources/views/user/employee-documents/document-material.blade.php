@extends('layouts.user')

@section('title', $material['name'])

@section('content')
<x-user-page-shell fluid class="space-y-5 pb-6 -mx-3 px-3 sm:-mx-4 sm:px-4 lg:-mx-6 lg:px-6">
    <x-user-page-header :title="$material['name']" :subtitle="$subtitle">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </x-slot:icon>
        <x-slot:actions>
            <x-user-header-btn-primary href="{{ $downloadUrl }}">
                Download PDF
            </x-user-header-btn-primary>
        </x-slot:actions>
    </x-user-page-header>

    <x-user-dashboard-section title="Document viewer" description="Scroll or use your browser controls to read the full PDF.">
        <div class="overflow-hidden rounded-lg border border-slate-200 -mx-1 sm:mx-0">
            <div class="w-full" style="height: min(70dvh, calc(100vh - 280px)); min-height: 320px;">
                <iframe
                    src="{{ $pdfUrl }}"
                    class="h-full w-full border-0"
                    title="{{ $material['name'] }}"
                    style="min-height: 480px;">
                </iframe>
            </div>
        </div>
    </x-user-dashboard-section>
</x-user-page-shell>
@endsection
