@extends('layouts.user')

@section('page-title', 'Announcements')

@section('content')
<div class="h-full flex flex-col min-h-0 min-w-0">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-3 sm:p-4 flex-shrink-0">
        <div class="flex items-start sm:items-center gap-2 min-w-0">
            <div class="flex-shrink-0 mt-0.5 sm:mt-0">
                <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
            </div>
            <div class="ml-0 sm:ml-3 min-w-0 flex-1">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white break-words">Announcements</h1>
                <p class="text-indigo-100 text-sm mt-0.5 break-words">Latest updates posted by admins.</p>
            </div>
        </div>
    </div>

    <div class="flex-1 min-h-0 min-w-0 overflow-auto p-3 sm:p-4 space-y-4">
        @forelse($news as $item)
            @php
                $imageUrl = $item->image_url;
                if (!$imageUrl && $item->image_path) {
                    $encodedPath = base64_encode(trim($item->image_path));
                    $imageUrl = url('/image-proxy/' . str_replace(['+', '/', '='], ['%2B', '%2F', '%3D'], $encodedPath));
                }
            @endphp
            <article class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" alt="{{ $item->title }}" class="w-full h-48 object-cover">
                @endif
                <div class="p-4">
                    <div class="flex flex-wrap items-center gap-2 mb-2 text-xs text-gray-500">
                        @if($item->category)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-medium">
                                {{ $item->category }}
                            </span>
                        @endif
                        <span>{{ optional($item->published_at)->format('M d, Y h:i A') ?? optional($item->created_at)->format('M d, Y h:i A') }}</span>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2 break-words">{{ $item->title }}</h2>
                    <div class="prose prose-sm max-w-none text-gray-700 overflow-x-auto [&_img]:max-w-full [&_img]:h-auto [&_iframe]:max-w-full">
                        {!! $item->content !!}
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-white border border-gray-200 rounded-lg p-8 text-center text-gray-500">
                No announcements yet.
            </div>
        @endforelse

        @if($news->hasPages())
            <div class="pt-2 overflow-x-auto">
                {{ $news->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
