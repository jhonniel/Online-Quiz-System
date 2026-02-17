@extends('layouts.admin')

@section('title', 'Confession – Topics')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Confession – Topics</h1>
            <p class="mt-1 text-sm text-gray-500">Topics used on Say-it. Created when users select or enter a topic; ordered by number of posts.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ url('/admin/confession') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Contents</a>
            <a href="{{ url('/admin/confession/dashboard') }}" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Dashboard</a>
            <a href="{{ url('admin/confession/banned-words') }}" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Banned words</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
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
