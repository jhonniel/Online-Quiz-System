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

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 mb-6">
        <h2 class="text-base font-semibold text-gray-900">Anon Name</h2>
        <p class="mt-1 text-sm text-gray-500">
            Choose which name sources can be used for Say-it. The system randomly picks one source for each new session.
        </p>
        <form method="POST" action="{{ url('/admin/confession/anon-name-settings') }}" class="mt-4 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <label class="flex items-start gap-2 rounded-md border border-gray-200 p-3">
                    <input
                        type="checkbox"
                        name="anon_name_sources[]"
                        value="default_codename"
                        class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        {{ in_array('default_codename', $selectedAnonSources ?? [], true) ? 'checked' : '' }}
                    >
                    <span>
                        <span class="block text-sm font-medium text-gray-900">Default codename</span>
                        <span class="block text-xs text-gray-500">Current format (example: anonymous_fox_12345)</span>
                    </span>
                </label>

                @foreach(($roleOptions ?? collect()) as $role)
                    @php($sourceKey = 'role_' . $role)
                    <label class="flex items-start gap-2 rounded-md border border-gray-200 p-3">
                        <input
                            type="checkbox"
                            name="anon_name_sources[]"
                            value="{{ $sourceKey }}"
                            class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            {{ in_array($sourceKey, $selectedAnonSources ?? [], true) ? 'checked' : '' }}
                        >
                        <span class="text-sm font-medium text-gray-900">{{ ucfirst($role) }} names</span>
                    </label>
                @endforeach
            </div>

            @error('anon_name_sources')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                Save Anon Name Settings
            </button>
        </form>
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
