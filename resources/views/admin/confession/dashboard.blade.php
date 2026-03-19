@extends('layouts.admin')

@section('title', 'Confession – Dashboard')
@section('page-title', 'Say-it Dashboard')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ url('/admin/confession') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Confession</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-700">Dashboard</span>
        </div>
    </li>
@endsection

@section('content')
<div class="mx-2 sm:mx-3 lg:mx-4 xl:mx-6">
    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(!empty($autoDeletedCount))
        <div class="mb-6 rounded-lg bg-indigo-50 border border-indigo-200 p-4 text-sm text-indigo-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Auto-cleanup deleted {{ number_format($autoDeletedCount) }} post(s) with no likes and no comments.
        </div>
    @endif

    {{-- Page header --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Confession Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Logs, IP activity, and trending posts from Say-it. Manage content and auto-delete queue.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('/admin/confession') }}" class="inline-flex items-center px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    Contents
                </a>
                <a href="{{ url('/Say-it') }}" target="_blank" class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    View Say-it
                </a>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total posts (all-time)</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_posts_all_time']) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-emerald-200 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Active posts</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['active_posts']) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total comments</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_comments']) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Unique IPs</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['unique_ips']) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- IP activity --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">IP activity</h2>
                        <p class="text-sm text-gray-500">IPs that posted or commented, by total activity</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">IP</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Posts</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Comments</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ipLogs as $log)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-3 text-sm font-mono text-gray-900">{{ $log['ip'] }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 text-right">{{ number_format($log['posts']) }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 text-right">{{ number_format($log['comments']) }}</td>
                                <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($log['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(empty($ipLogs))
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <p class="mt-2 text-sm text-gray-500">No IP data yet</p>
                </div>
            @endif
        </div>

        {{-- Trending posts --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Trending posts</h2>
                        <p class="text-sm text-gray-500">Most engagement (comments + upvotes + downvotes)</p>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @foreach($trending as $index => $post)
                    <div class="p-4 hover:bg-gray-50/50 transition-colors">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($index < 3)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-800' : ($index === 1 ? 'bg-gray-200 text-gray-700' : 'bg-amber-700/20 text-amber-800') }}">{{ $index + 1 }}</span>
                                    @else
                                        <span class="text-xs font-medium text-gray-400">#{{ $index + 1 }}</span>
                                    @endif
                                    <span class="text-xs font-semibold text-indigo-600">{{ $post->engagement }} engagement</span>
                                </div>
                                <p class="mt-1.5 text-sm text-gray-900 line-clamp-2">{{ Str::limit($post->content ?: '[Image post]', 85) }}</p>
                                <p class="mt-1.5 text-xs text-gray-500">
                                    {{ $post->all_comments_count ?? 0 }} comments · ↑{{ $post->upvotes_count }} ↓{{ $post->downvotes_count }} · {{ $post->codename }}
                                </p>
                                <div class="mt-2 flex items-center gap-3">
                                    <a href="{{ url('/Say-it/' . $post->id) }}" target="_blank" class="inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-800">View post</a>
                                    <form method="POST" action="{{ url('admin/confession/posts/' . $post->id) }}" class="inline" onsubmit="return confirm('Delete this post and all its comments?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($trending->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                    <p class="mt-2 text-sm text-gray-500">No posts yet</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Posts scheduled for auto-delete --}}
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 bg-amber-50/80">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Posts scheduled for auto-delete</h2>
                    <p class="text-sm text-gray-500">No likes and no comments — deleted automatically 7 days after post. Oldest first. You may still force delete any post below.</p>
                </div>
            </div>
        </div>
        <div class="divide-y divide-gray-200 max-h-[28rem] overflow-y-auto">
            @forelse($scheduledForDeletion as $index => $post)
                @php
                    $autoDeleteAt = $post->created_at->copy()->addDays(7);
                    $isPastDue = $autoDeleteAt->isPast();
                @endphp
                <div class="p-4 hover:bg-gray-50/50 transition-colors flex flex-wrap justify-between items-start gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-medium text-gray-400">#{{ $index + 1 }}</span>
                            @if($isPastDue)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Overdue</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">In queue</span>
                            @endif
                        </div>
                        <p class="mt-1.5 text-sm text-gray-900 line-clamp-2">{{ Str::limit($post->content ?: '[Image post]', 100) }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $post->codename }} · Posted {{ $post->created_at->format('M j, Y g:i A') }}</p>
                        <p class="mt-1 text-xs {{ $isPastDue ? 'text-amber-700 font-medium' : 'text-gray-600' }}">
                            @if($isPastDue)
                                Auto-delete was due {{ $autoDeleteAt->format('M j, Y') }}
                            @else
                                Will be auto-deleted {{ $autoDeleteAt->diffForHumans() }} ({{ $autoDeleteAt->format('M j, Y') }})
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ url('/Say-it/' . $post->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">View</a>
                        <form method="POST" action="{{ url('admin/confession/posts/' . $post->id) }}" class="inline" onsubmit="return confirm('Force delete this post and all its comments?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-red-200 rounded-lg text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100">Force delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="mt-2 text-sm text-gray-500">No posts scheduled for auto-delete</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
