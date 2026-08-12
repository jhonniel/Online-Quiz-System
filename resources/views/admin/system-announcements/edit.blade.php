@extends('layouts.admin')

@section('title', 'Edit Announcement')

@section('page-title', 'Edit Announcement')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <a href="{{ route('admin.system-announcements.index') }}" class="text-gray-500 text-sm hover:text-gray-700">Announcements</a>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm truncate max-w-[12rem]">{{ $announcement->title }}</span>
</li>
@endsection

@section('content')
<style>
    .employee-announcement-content .announcement-list {
        margin: 0.5rem 0 0.75rem;
        padding-left: 1.25rem;
        list-style-type: disc;
    }
    .employee-announcement-content .announcement-list-ordered {
        list-style-type: decimal;
    }
    .employee-announcement-content .announcement-list li {
        margin: 0.35rem 0;
        padding-left: 0.15rem;
    }
    .employee-announcement-content p {
        margin: 0 0 0.75rem;
    }
    .employee-announcement-content p:last-child {
        margin-bottom: 0;
    }
</style>

<div class="space-y-6 max-w-7xl">
    <div class="border-b border-indigo-800/20 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 rounded-xl shadow-sm px-4 sm:px-6 py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 flex items-start gap-4">
                <div class="hidden sm:flex w-12 h-12 rounded-xl bg-white/15 border border-white/20 items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-white truncate">{{ $announcement->title }}</h1>
                    <p class="mt-1 text-sm text-indigo-100">Edit content, preview the employee popup, and manage publishing.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if($announcement->is_published)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-white border border-emerald-300/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                        Published
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-500/20 text-white border border-amber-300/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                        Draft
                    </span>
                @endif
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-indigo-50 border border-white/20">
                    {{ number_format($announcement->acknowledgments_count) }} agreed
                </span>
            </div>
        </div>
    </div>

    @component('admin.system-announcements.partials.editor-alpine-root', [
        'titleValue' => $announcement->title,
        'contentValue' => $announcement->content,
        'featureLinksValue' => $announcement->feature_links ?? [],
    ])
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 items-start">
        <div class="xl:col-span-3 space-y-6">
            <form method="POST" action="{{ route('admin.system-announcements.update', $announcement) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                @csrf
                @method('PUT')
                <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Announcement content</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Use bullet lines for a clean list in the employee popup.</p>
                    </div>
                </div>
                <div class="p-5 sm:p-6">
                    @include('admin.system-announcements.partials.content-editor')
                </div>
                <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="{{ route('admin.system-announcements.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to list
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                        Save Changes
                    </button>
                </div>
            </form>

            @unless($announcement->is_published)
                <div class="rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-white p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-emerald-900">Ready to publish?</h3>
                            <p class="text-xs text-emerald-800 mt-1 leading-relaxed">Employees who have not agreed will see this announcement on their next login.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.system-announcements.publish', $announcement) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm whitespace-nowrap">
                            Publish now
                        </button>
                    </form>
                </div>
            @endunless
        </div>

        <div class="xl:col-span-2 space-y-4">
            @include('admin.system-announcements.partials.employee-preview')

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Details</h3>
                </div>
                <dl class="px-5 py-4 space-y-3 text-sm divide-y divide-gray-100">
                    <div class="flex justify-between gap-3 pb-3">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-semibold {{ $announcement->is_published ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $announcement->is_published ? 'Published' : 'Draft' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3 py-3">
                        <dt class="text-gray-500">Published at</dt>
                        <dd class="font-medium text-gray-900 text-right">{{ $announcement->published_at?->format('M d, Y g:i A') ?: '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 py-3">
                        <dt class="text-gray-500">Feature links</dt>
                        <dd class="font-medium text-gray-900 text-right text-xs max-w-[14rem]">
                            @if($announcement->hasFeatureLinks())
                                {{ $announcement->featureLinksCount() }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3 py-3">
                        <dt class="text-gray-500">Agreements</dt>
                        <dd class="font-semibold text-indigo-700">{{ number_format($announcement->acknowledgments_count) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 py-3">
                        <dt class="text-gray-500">Created</dt>
                        <dd class="font-medium text-gray-900 text-right">{{ $announcement->created_at?->format('M d, Y') }}</dd>
                    </div>
                    @if($announcement->creator)
                        <div class="flex justify-between gap-3 pt-3">
                            <dt class="text-gray-500">Author</dt>
                            <dd class="font-medium text-gray-900 text-right"><x-user-name :user="$announcement->creator" class="inline justify-end" /></dd>
                        </div>
                    @endif
                </dl>
            </div>

            @if($announcement->is_published)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <h3 class="text-sm font-semibold text-amber-900">Unpublish</h3>
                    <p class="text-xs text-amber-800 mt-1 leading-relaxed">Hide this from employees who have not agreed yet.</p>
                    <form method="POST" action="{{ route('admin.system-announcements.unpublish', $announcement) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-amber-900 bg-white border border-amber-200 hover:bg-amber-100">
                            Unpublish announcement
                        </button>
                    </form>
                </div>
            @endif

            <div class="rounded-xl border border-red-200 bg-red-50/50 p-5">
                <h3 class="text-sm font-semibold text-red-900">Delete announcement</h3>
                <p class="text-xs text-red-700 mt-1 leading-relaxed">This permanently removes the announcement and all agreement records.</p>
                <form method="POST" action="{{ route('admin.system-announcements.destroy', $announcement) }}" class="mt-3" onsubmit="return confirm('Delete this announcement? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-red-700 bg-white border border-red-200 hover:bg-red-100">
                        Delete permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endcomponent
</div>
@endsection
