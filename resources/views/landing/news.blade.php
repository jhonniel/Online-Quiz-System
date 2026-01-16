@extends('layouts.landing')

@section('title', 'News & Updates - ' . $settings['system_name'])
@section('description', 'Be in the know with what\'s up and about at ' . $settings['system_name'])

@section('content')
<!-- Hero Banner Section -->
@if($news->count() > 0)
<section class="pt-32 pb-20 relative overflow-hidden">
    @php
        $featuredNews = $news->first();
    @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative rounded-2xl overflow-hidden shadow-2xl">
            <!-- Background Image with Blur -->
            @php
                $featuredImageUrl = $featuredNews->image_url;
                if ($featuredNews->image_path && !$featuredImageUrl) {
                    $featuredImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($featuredNews->image_path);
                }
            @endphp
            @if($featuredImageUrl)
                <div class="absolute inset-0">
                    <img src="{{ $featuredImageUrl }}" alt="{{ $featuredNews->title }}" class="w-full h-full object-cover filter blur-md scale-110">
                </div>
            @endif
            
            <!-- Dark Overlay -->
            <div class="absolute inset-0 bg-black/60"></div>
            
            <!-- Content -->
            <div class="relative px-6 sm:px-8 md:px-12 lg:px-16 py-12 sm:py-16 md:py-20 lg:py-24">
                <div class="max-w-3xl">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 sm:mb-6 leading-tight">
                        {{ $featuredNews->title }}
                    </h1>
                    <p class="text-white/90 text-sm sm:text-base mb-4">
                        by {{ $featuredNews->author ?? ($featuredNews->creator ? $featuredNews->creator->name : 'Admin') }} | 
                        {{ $featuredNews->published_at ? $featuredNews->published_at->format('M d, Y') : $featuredNews->created_at->format('M d, Y') }}
                    </p>
                    <p class="text-white text-base sm:text-lg mb-6 sm:mb-8 leading-relaxed">
                        {{ Str::limit(strip_tags($featuredNews->content), 200) }}
                    </p>
                    <a href="#news-articles" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors shadow-lg">
                        Read Full Article
                    </a>
                </div>
                
                <!-- Carousel Navigation -->
                <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex items-center gap-3">
                    <div class="flex gap-2">
                        <div class="w-2 h-2 rounded-full bg-white"></div>
                        <div class="w-2 h-2 rounded-full border-2 border-white"></div>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <button class="text-white hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button class="text-white hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- News & Updates Section -->
<section id="news-articles" class="py-12 sm:py-16 md:py-20 bg-white relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-4xl sm:text-5xl md:text-6xl font-bold text-blue-900 mb-3 sm:mb-4">News & Updates</h2>
            <p class="text-lg sm:text-xl text-gray-500">Be in the know with what's up and about at {{ $settings['system_name'] }}</p>
        </div>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-4 mb-8 sm:mb-12">
            <div class="flex-1">
                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-gray-700 bg-white">
                    <option>Select category</option>
                    <option>General News</option>
                    <option>Announcements</option>
                    <option>Events</option>
                </select>
            </div>
            <div class="flex-1">
                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-gray-700 bg-white">
                    <option>Date Published (Descending)</option>
                    <option>Date Published (Ascending)</option>
                    <option>Most Viewed</option>
                </select>
            </div>
        </div>

        <!-- News Articles List -->
        @if($news->count() > 0)
            <div class="space-y-8 sm:space-y-12">
                @foreach($news as $index => $item)
                    <article class="flex flex-col md:flex-row gap-6 sm:gap-8 pb-8 sm:pb-12 border-b border-gray-200 last:border-b-0 relative">

                        <!-- Article Image -->
                        <div class="flex-shrink-0 w-full md:w-80 lg:w-96">
                            @php
                                $articleImageUrl = $item->image_url;
                                if ($item->image_path && !$articleImageUrl) {
                                    $articleImageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path);
                                }
                            @endphp
                            @if($articleImageUrl)
                                <img src="{{ $articleImageUrl }}" alt="{{ $item->title }}" class="w-full h-64 sm:h-80 object-cover rounded-lg shadow-md">
                            @else
                                <div class="w-full h-64 sm:h-80 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg shadow-md flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Article Content -->
                        <div class="flex-1 pt-2">
                            <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3 sm:mb-4 leading-tight">
                                {{ $item->title }}
                            </h3>
                            <p class="text-sm text-gray-500 mb-4">
                                by {{ $item->author ?? ($item->creator ? $item->creator->name : 'Admin') }} | 
                                {{ $item->published_at ? $item->published_at->format('M d, Y') : $item->created_at->format('M d, Y') }}
                            </p>
                            <p class="text-gray-700 text-base sm:text-lg mb-4 sm:mb-6 leading-relaxed">
                                {{ Str::limit(strip_tags($item->content), 300) }}
                            </p>
                            <div class="flex flex-wrap items-center gap-4">
                                @if($item->category)
                                    <span class="inline-block px-4 py-2 bg-orange-100 text-orange-800 rounded-lg text-sm font-medium">
                                        {{ $item->category }}
                                    </span>
                                @endif
                                <a href="#" class="text-red-700 hover:text-red-800 font-medium underline text-sm sm:text-base">
                                    Read Full Article
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($news->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $news->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-16">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No news articles yet</h3>
                <p class="mt-2 text-sm text-gray-500">Check back soon for the latest updates and announcements.</p>
            </div>
        @endif
    </div>
</section>
@endsection
