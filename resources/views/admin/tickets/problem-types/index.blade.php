@extends('layouts.admin')

@section('title', 'Ticket Problem Types')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Problem Types</h1>
            <p class="mt-1 text-sm text-gray-500">Types of problem users can select when reporting a ticket at /report-problem.</p>
        </div>
        <a href="{{ url('admin/tickets') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">← Tickets Dashboard</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Add new type</h2>
        </div>
        <form action="{{ url('admin/tickets/problem-types') }}" method="POST" class="p-4 flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[200px]">
                <label for="label" class="block text-sm font-medium text-gray-700">Label</label>
                <input type="text" name="label" id="label" value="{{ old('label') }}" required maxlength="255" placeholder="e.g. Hardware Issue" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                @error('label')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Add type</button>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Current types</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Label</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($types as $type)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $type->label }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $type->slug }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <a href="{{ url('admin/tickets/problem-types/' . $type->id . '/edit') }}" class="text-indigo-600 hover:underline mr-3">Edit</a>
                            <form method="POST" action="{{ url('admin/tickets/problem-types/' . $type->id) }}" class="inline" onsubmit="return confirm('Remove this type? Existing tickets with this type will keep it.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">No problem types yet. Add one above.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
