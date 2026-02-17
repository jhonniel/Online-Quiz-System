@extends('layouts.admin')

@section('title', 'Confession – Banned words')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Confession – Banned words</h1>
            <p class="mt-1 text-sm text-gray-500">Banned text is matched anywhere (even inside other words). Choose how it appears: full asterisks, first & last visible, or end asterisks only.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ url('/admin/confession') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Contents</a>
            <a href="{{ url('/admin/confession/dashboard') }}" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <form action="{{ url('admin/confession/banned-words') }}" method="POST" class="flex gap-3 flex-wrap items-end">
                @csrf
                <div class="min-w-[160px]">
                    <label for="word" class="block text-sm font-medium text-gray-700 mb-1">Banned word or phrase</label>
                    <input type="text" name="word" id="word" value="{{ old('word') }}" placeholder="e.g. spam" class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" maxlength="100" autocomplete="off">
                </div>
                <div class="min-w-[200px]">
                    <label for="display_style" class="block text-sm font-medium text-gray-700 mb-1">Display as</label>
                    <select name="display_style" id="display_style" class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="full" {{ old('display_style', 'full') === 'full' ? 'selected' : '' }}>Full asterisks (****)</option>
                        <option value="first_last" {{ old('display_style') === 'first_last' ? 'selected' : '' }}>First & last visible (t**t)</option>
                        <option value="end_only" {{ old('display_style') === 'end_only' ? 'selected' : '' }}>End asterisks only (te**)</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Add</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Word</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Display</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($words as $w)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-gray-900">{{ $w->word }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($w->display_style === 'first_last')
                                    First & last visible <span class="text-gray-400">(e.g. t**t)</span>
                                @elseif($w->display_style === 'end_only')
                                    End asterisks only <span class="text-gray-400">(e.g. te**)</span>
                                @else
                                    Full asterisks <span class="text-gray-400">(****)</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ url('admin/confession/banned-words/' . $w->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove this banned word?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">No banned words yet. Add one above.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Topics (Say-it post topics) --}}
    <div id="topics" class="mt-8 bg-white shadow rounded-lg overflow-hidden scroll-mt-4">
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Topics</h2>
            <p class="mt-0.5 text-sm text-gray-500">Topics used on Say-it. Created when users select or enter a topic; ordered by number of posts.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Posts</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($topics as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $t->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $t->slug }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600">{{ number_format($t->posts_count) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">No topics yet. Topics are created when users post on Say-it.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
