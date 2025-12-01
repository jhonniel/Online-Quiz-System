@extends('layouts.landing')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Open Positions</h1>
            <p class="text-xl text-gray-600">Browse available job opportunities</p>
        </div>

        @if($positions->count() > 0)
            <div class="space-y-6">
                @foreach($positions as $position)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $position->title }}</h2>
                                
                                <div class="flex flex-wrap items-center gap-4 mb-4 text-sm text-gray-600">
                                    @if($position->department)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            {{ $position->department }}
                                        </span>
                                    @endif
                                    @if($position->location)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            {{ $position->location }}
                                        </span>
                                    @endif
                                    @if($position->employment_type)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $position->employment_type }}
                                        </span>
                                    @endif
                                </div>

                                @if($position->description)
                                    <p class="text-gray-700 mb-4 line-clamp-3">{{ Str::limit($position->description, 200) }}</p>
                                @endif

                                @if($position->salary_min || $position->salary_max)
                                    <p class="text-sm font-medium text-gray-900 mb-4">
                                        @if($position->salary_min && $position->salary_max)
                                            Salary: ${{ number_format($position->salary_min) }} - ${{ number_format($position->salary_max) }}
                                        @elseif($position->salary_min)
                                            Salary: From ${{ number_format($position->salary_min) }}
                                        @elseif($position->salary_max)
                                            Salary: Up to ${{ number_format($position->salary_max) }}
                                        @endif
                                    </p>
                                @endif

                                @if($position->application_deadline)
                                    <p class="text-sm text-gray-500 mb-4">
                                        Application deadline: {{ $position->application_deadline->format('M j, Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <span class="text-sm text-gray-500">{{ $position->application_count }} application(s)</span>
                            <a href="{{ url('/' . ltrim($settings['hiring_application_url'] ?? 'hiring/apply', '/') . '/' . $position->slug) }}" 
                               class="inline-flex items-center px-6 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                Apply Now
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No positions available</h3>
                <p class="mt-1 text-sm text-gray-500">There are currently no open positions. Please check back later.</p>
            </div>
        @endif
    </div>
</div>
@endsection

