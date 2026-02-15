@extends('layouts.admin')

@section('title', 'Confession – Dashboard')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Confession – Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Logs, IPs, and trending posts from Say-it.</p>
        </div>
        <a href="{{ url('/admin/confession') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Contents</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="text-sm font-medium text-gray-500">Total posts</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_posts'] }}</div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="text-sm font-medium text-gray-500">Total comments</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_comments'] }}</div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="text-sm font-medium text-gray-500">Unique IPs</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['unique_ips'] }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- IP logs --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">IP activity</h2>
                <p class="text-sm text-gray-500">IPs that posted or commented (by total activity)</p>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Posts</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Comments</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($ipLogs as $log)
                            <tr>
                                <td class="px-4 py-2 text-sm font-mono text-gray-900">{{ $log['ip'] }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 text-right">{{ $log['posts'] }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600 text-right">{{ $log['comments'] }}</td>
                                <td class="px-4 py-2 text-sm font-medium text-gray-900 text-right">{{ $log['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(empty($ipLogs))
                <p class="p-4 text-center text-gray-500 text-sm">No IP data yet.</p>
            @endif
        </div>

        {{-- Trending --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Trending posts</h2>
                <p class="text-sm text-gray-500">Most comments + upvotes + downvotes</p>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @foreach($trending as $index => $post)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-400">#{{ $index + 1 }}</span>
                                <p class="text-sm text-gray-900 mt-0.5">{{ Str::limit($post->content ?: '[Image post]', 80) }}</p>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $post->all_comments_count ?? 0 }} comments · ↑{{ $post->upvotes_count }} ↓{{ $post->downvotes_count }} · {{ $post->codename }}
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-indigo-600">{{ $post->engagement }} engagement</span>
                        </div>
                        <a href="{{ url('/Say-it/' . $post->id) }}" target="_blank" class="inline-block mt-2 text-xs text-indigo-600 hover:underline">View</a>
                    </div>
                @endforeach
            </div>
            @if($trending->isEmpty())
                <p class="p-4 text-center text-gray-500 text-sm">No posts yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
