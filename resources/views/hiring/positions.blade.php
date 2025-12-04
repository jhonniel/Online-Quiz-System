@extends('layouts.landing')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8 sm:mb-12">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 sm:mb-4">Open Positions</h1>
            <p class="text-lg sm:text-xl text-gray-600">Browse available job opportunities</p>
        </div>

        @if($positions->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($positions as $position)
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col">
                        <!-- Card Header -->
                        <div class="p-6 pb-4">
                            <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">{{ $position->title }}</h2>
                            
                            <div class="flex flex-col gap-2 mb-4">
                                @if($position->department)
                                    <span class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span class="truncate">{{ $position->department }}</span>
                                    </span>
                                @endif
                                @if($position->location)
                                    <span class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="truncate">{{ $position->location }}</span>
                                    </span>
                                @endif
                                @if($position->employment_type)
                                    <span class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="truncate">{{ $position->employment_type }}</span>
                                    </span>
                                @endif
                            </div>

                            @if($position->description)
                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ Str::limit($position->description, 150) }}</p>
                            @endif
                        </div>

                        <!-- Card Footer -->
                        <div class="mt-auto p-6 pt-0 border-t border-gray-100">
                            @if($position->salary_min || $position->salary_max)
                                <div class="mb-3">
                                    <p class="text-sm font-semibold text-gray-900">
                                        @if($position->salary_min && $position->salary_max)
                                            ${{ number_format($position->salary_min) }} - ${{ number_format($position->salary_max) }}
                                        @elseif($position->salary_min)
                                            From ${{ number_format($position->salary_min) }}
                                        @elseif($position->salary_max)
                                            Up to ${{ number_format($position->salary_max) }}
                                        @endif
                                    </p>
                                </div>
                            @endif

                            @if($position->application_deadline)
                                <p class="text-xs text-gray-500 mb-4">
                                    Deadline: {{ $position->application_deadline->format('M j, Y') }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">{{ $position->application_count }} application(s)</span>
                                <a href="{{ url('/' . ltrim($settings['hiring_application_url'] ?? 'hiring/apply', '/') . '/' . $position->slug) }}" 
                                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    Apply Now
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
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

