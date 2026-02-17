@extends('layouts.admin')

@section('title', 'Edit Problem Type')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 max-w-xl">
    <div class="mb-6">
        <a href="{{ url('admin/tickets/problem-types') }}" class="text-sm text-gray-500 hover:text-gray-700">← Problem types</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">Edit problem type</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <form action="{{ url('admin/tickets/problem-types/' . $type->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div>
                <label for="label" class="block text-sm font-medium text-gray-700">Label</label>
                <input type="text" name="label" id="label" value="{{ old('label', $type->label) }}" required maxlength="255" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                @error('label')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <p class="mt-2 text-xs text-gray-500">Slug: <code>{{ $type->slug }}</code> (used in tickets; not changed when editing label)</p>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Save</button>
                <a href="{{ url('admin/tickets/problem-types') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
