@extends('layouts.admin')

@section('title', 'Confession – Contents')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Confession – Contents</h1>
            <p class="mt-1 text-sm text-gray-500">All posts from Say-it (anonymous confessions). Full access only.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ url('/admin/confession/dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Dashboard</a>
            <a href="{{ url('/Say-it') }}" target="_blank" class="ml-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">View Say-it →</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <ul class="divide-y divide-gray-200">
            @forelse($posts as $post)
                <li class="p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs text-gray-500 mb-1">
                                <span class="font-mono">{{ $post->codename }}</span>
                                · {{ $post->created_at->format('M j, Y g:i A') }}
                                · IP: {{ $post->ip_address ?? '—' }}
                            </div>
                            @if($post->content)
                                <p class="text-gray-900 whitespace-pre-wrap">{{ Str::limit($post->content, 200) }}</p>
                            @endif
                            @if($post->image_path)
                                <p class="text-sm text-gray-500 mt-1">[Image attached]</p>
                            @endif
                            <div class="mt-2 text-sm text-gray-500">
                                ↑ {{ $post->upvotes_count }} · ↓ {{ $post->downvotes_count }}
                                · {{ $post->all_comments_count ?? 0 }} comments
                            </div>
                        </div>
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <a href="{{ url('/Say-it/' . $post->id) }}" target="_blank" class="text-indigo-600 hover:underline text-sm">View</a>
                            <form method="POST" action="{{ url('admin/confession/posts/' . $post->id) }}" class="inline" onsubmit="return confirm('Delete this post and all its comments?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </li>
            @empty
                <li class="p-8 text-center text-gray-500">No confessions yet.</li>
            @endforelse
        </ul>
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection
