@extends('layouts.admin')

@section('content')
<div class="p-4 sm:p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Student Evaluation Forms</h1>
        <div class="flex items-center gap-2">
            <a href="{{ url('/admin/evaluations/create') }}"
               class="inline-flex items-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                Create Form
            </a>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-2">Force Send Evaluation</h2>
        <p class="text-xs text-gray-500 mb-3">Send evaluation access to a student even before required hours are completed.</p>
        <form method="POST" action="{{ url('/admin/evaluations/force-send') }}" class="flex flex-col sm:flex-row gap-2 sm:items-center">
            @csrf
            <select name="student_id" required
                    class="w-full sm:w-80 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="">Select student</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}">
                        {{ $student->name }} ({{ $student->email }}){{ $student->evaluation_forced_at ? ' - forced' : '' }}
                    </option>
                @endforeach
            </select>
            <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-amber-600 text-white text-sm font-medium hover:bg-amber-700">
                Force Send
            </button>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Questions</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submissions</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($forms as $item)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $item->title }}</div>
                            <div class="text-xs text-gray-500">{{ $item->description }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ count((array) $item->questions) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $item->submissions_count }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end items-center gap-3 text-sm">
                                <a href="{{ url('/admin/evaluations/' . $item->id . '/submissions') }}" class="text-indigo-600 hover:text-indigo-700">Responses</a>
                                <a href="{{ url('/admin/evaluations/' . $item->id . '/edit') }}" class="text-blue-600 hover:text-blue-700">Edit</a>
                                <form method="POST" action="{{ url('/admin/evaluations/' . $item->id . '/activate') }}">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-700">Activate</button>
                                </form>
                                <form method="POST" action="{{ url('/admin/evaluations/' . $item->id) }}" onsubmit="return confirm('Delete this form?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No evaluation forms yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
