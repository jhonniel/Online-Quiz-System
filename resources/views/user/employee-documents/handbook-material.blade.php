@extends('layouts.user')

@section('title', $material['name'])

@section('content')
<x-user-page-shell fluid class="space-y-5 pb-6 -mx-3 px-3 sm:-mx-4 sm:px-4 lg:-mx-6 lg:px-6">
    <x-user-page-header :title="$material['name']" :subtitle="$subtitle">
        <x-slot:icon>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </x-slot:icon>
        <x-slot:actions>
            <x-user-header-btn-primary href="{{ $downloadUrl }}">
                Download PDF
            </x-user-header-btn-primary>
        </x-slot:actions>
    </x-user-page-header>

    <x-user-dashboard-section title="Handbook" description="Review company handbook material below.">
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
