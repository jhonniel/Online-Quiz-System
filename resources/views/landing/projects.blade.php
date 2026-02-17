@extends('layouts.landing')

@section('title', 'Our Projects - ' . $settings['system_name'])
@section('description', 'Explore the solutions and projects we\'ve delivered')

@section('content')
<!-- Hero Section -->
<section class="gradient-bg text-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-4 sm:mb-6">Our Projects</h1>
            <p class="text-lg sm:text-xl text-gray-100 max-w-2xl mx-auto">Explore the solutions and projects we've delivered</p>
        </div>
    </div>
</section>

<!-- Projects Grid -->
<section class="py-12 sm:py-16 md:py-20 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(count($projects) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-8">
                @foreach($projects as $project)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover-lift border border-gray-100 group">
                        <!-- Project Image -->
                        <div class="relative h-64 bg-gray-100 overflow-hidden">
                            @if(!empty($project['image_url']))
                                <img src="{{ $project['image_url'] }}"
                                     alt="{{ $project['name'] }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                                    <div class="text-center p-6">
                                        <svg class="w-16 h-16 text-primary/50 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        <p class="text-gray-500 text-sm">No image</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Project Content -->
                        <div class="p-6">
                            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">{{ $project['name'] }}</h3>
                            @if(!empty($project['description']))
                                <p class="text-gray-600 mb-4 line-clamp-3">{{ $project['description'] }}</p>
                            @endif

                            @if(!empty($project['url']) && $project['url'] !== '#')
                                <a href="{{ $project['url'] }}"
                                   target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg font-semibold hover:opacity-90 transition-opacity">
                                    Visit Site
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 mb-8">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">No Projects Yet</h3>
                <p class="text-gray-600">Projects will be displayed here once they are added.</p>
            </div>
        @endif

        @auth
            @if(auth()->user()->isAdmin())
                <div class="text-center pt-8 border-t border-gray-200">
                    <a href="{{ route('admin.landing-page.index') }}"
                       class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:opacity-90 transition-opacity shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add New Project
                    </a>
                </div>
            @endif
        @endauth
    </div>
</section>
@endsection

