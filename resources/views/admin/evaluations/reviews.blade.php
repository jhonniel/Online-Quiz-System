@extends('layouts.admin')

@section('title', 'Students Review')
@section('page-title', 'Students Review')

@section('content')
<div class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
        <h1 class="text-lg font-semibold text-gray-900">Students Review</h1>
        <p class="text-sm text-gray-500 mt-1">All submitted student evaluations and feedback.</p>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Evaluation Form</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($reviews as $review)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $review->user->name ?? 'Unknown student' }}</p>
                                <p class="text-xs text-gray-500">{{ $review->user->email ?? 'N/A' }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $review->form->title ?? 'Untitled form' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ optional($review->submitted_at)->format('M d, Y h:i A') ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($review->evaluation_form_id)
                                    <a href="{{ url('/admin/evaluations/' . $review->evaluation_form_id . '/submissions') }}"
                                       class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                                        View Responses
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                                No student reviews found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($reviews, 'links'))
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
