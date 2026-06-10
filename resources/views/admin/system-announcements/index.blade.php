@extends('layouts.admin')

@section('title', 'Announcements')

@section('page-title', 'Announcements')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-500 text-sm">Content Management</span>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">Announcements</span>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <div class="border-b border-indigo-800/20 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 rounded-xl shadow-sm px-4 sm:px-6 py-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-white">Employee Announcements</h1>
                <p class="mt-1 text-sm text-indigo-100 max-w-2xl">
                    Publish system updates for employees. Published items appear as a login modal until each employee agrees.
                </p>
            </div>
            <a href="{{ route('admin.system-announcements.create') }}"
               class="inline-flex items-center justify-center shrink-0 px-4 py-2.5 rounded-lg text-sm font-semibold text-indigo-700 bg-white hover:bg-indigo-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Announcement
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Published</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ number_format($stats['published']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Drafts</p>
            <p class="mt-1 text-2xl font-bold text-amber-700">{{ number_format($stats['drafts']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 col-span-2 lg:col-span-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Agreements</p>
            <p class="mt-1 text-2xl font-bold text-indigo-700">{{ number_format($stats['acknowledgments']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">All announcements</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ $announcements->total() }} record(s)</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Announcement</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Published</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Agreed</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($announcements as $announcement)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $announcement->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $announcement->title }}</p>
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ Str::limit(strip_tags($announcement->content), 140) }}</p>
                                        @if($announcement->creator)
                                            <p class="text-[11px] text-gray-400 mt-1.5">By {{ $announcement->creator->name }} · {{ $announcement->created_at?->format('M d, Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                @if($announcement->published_at)
                                    <p class="text-sm text-gray-900">{{ $announcement->published_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $announcement->published_at->format('g:i A') }}</p>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ number_format($announcement->acknowledgments_count) }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                @if($announcement->is_published)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right">
                                <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('admin.system-announcements.edit', $announcement) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100">
                                        Edit
                                    </a>
                                    @if($announcement->is_published)
                                        <form method="POST" action="{{ route('admin.system-announcements.unpublish', $announcement) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-100">
                                                Unpublish
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.system-announcements.publish', $announcement) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-100">
                                                Publish
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.system-announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 border border-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="mx-auto w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-900">No announcements yet</p>
                                <p class="text-sm text-gray-500 mt-1">Create your first announcement to notify employees on login.</p>
                                <a href="{{ route('admin.system-announcements.create') }}"
                                   class="mt-4 inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                    New Announcement
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
