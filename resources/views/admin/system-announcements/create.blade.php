@extends('layouts.admin')

@section('title', 'New Announcement')

@section('page-title', 'New Announcement')

@section('breadcrumb')
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <a href="{{ route('admin.system-announcements.index') }}" class="text-gray-500 text-sm hover:text-gray-700">Announcements</a>
</li>
<li class="flex items-center">
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
    <span class="text-gray-900 font-medium text-sm">Create</span>
</li>
@endsection

@section('content')
<style>
    .employee-announcement-content .announcement-list {
        margin: 0.5rem 0 0.75rem;
        padding-left: 1.25rem;
        list-style-type: disc;
    }
    .employee-announcement-content .announcement-list-ordered { list-style-type: decimal; }
    .employee-announcement-content .announcement-list li { margin: 0.35rem 0; padding-left: 0.15rem; }
    .employee-announcement-content p { margin: 0 0 0.75rem; }
    .employee-announcement-content p:last-child { margin-bottom: 0; }
</style>

<div class="space-y-6 max-w-7xl">
    <div class="border-b border-indigo-800/20 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 rounded-xl shadow-sm px-4 sm:px-6 py-5">
        <h1 class="text-xl sm:text-2xl font-bold text-white">New Announcement</h1>
        <p class="mt-1 text-sm text-indigo-100">Compose a message with bullet points and preview the employee popup.</p>
    </div>

    @component('admin.system-announcements.partials.editor-alpine-root', [
        'titleValue' => old('title', ''),
        'contentValue' => old('content', ''),
        'featureLinksValue' => old('feature_links', []),
    ])
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 items-start">
        <div class="xl:col-span-3">
            <form method="POST" action="{{ route('admin.system-announcements.store') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                @csrf
                <div class="px-5 sm:px-6 py-4 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Announcement content</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Use bullet lines for a clean list in the employee popup.</p>
                </div>
                <div class="p-5 sm:p-6">
                    @include('admin.system-announcements.partials.content-editor')
                    <div class="mt-5 rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="publish_now" value="1" {{ old('publish_now') ? 'checked' : '' }}
                                   class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">
                                <span class="font-semibold text-gray-900 block">Publish immediately</span>
                                <span class="text-gray-500">Employees who have not agreed will see this on their next login.</span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="{{ route('admin.system-announcements.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                        Save Announcement
                    </button>
                </div>
            </form>
        </div>

        <div class="xl:col-span-2 space-y-4">
            @include('admin.system-announcements.partials.employee-preview')

            <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-5">
                <h3 class="text-sm font-semibold text-indigo-900">How it works</h3>
                <ul class="mt-3 space-y-2 text-xs text-indigo-800 leading-relaxed list-disc list-inside">
                    <li>Save as draft or publish immediately.</li>
                    <li>Employees see a modal on login until they agree.</li>
                    <li>Each announcement only needs to be agreed once per employee.</li>
                </ul>
            </div>
        </div>
    </div>
    @endcomponent
</div>
@endsection
