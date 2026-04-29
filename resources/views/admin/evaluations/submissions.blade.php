@extends('layouts.admin')

@section('content')
<div class="p-4 sm:p-6 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Evaluation Responses</h1>
            <p class="text-sm text-gray-600">{{ $evaluation->title }}</p>
        </div>
        <a href="{{ url('/admin/evaluations') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Back to forms</a>
    </div>

    <div class="space-y-3">
        @forelse($submissions as $submission)
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $submission->user->name ?? 'Unknown User' }}</p>
                        <p class="text-xs text-gray-500">{{ $submission->user->email ?? '' }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ optional($submission->submitted_at)->format('M d, Y h:i A') }}</span>
                </div>
                <div class="space-y-2">
                    @foreach((array) $submission->responses as $response)
                        <div class="rounded-md border border-gray-100 bg-gray-50 p-3">
                            <p class="text-xs font-semibold text-gray-700">{{ $response['question'] ?? '' }}</p>
                            <p class="text-sm text-gray-900 mt-1">
                                {{ ($response['type'] ?? '') === 'rating' ? (($response['answer'] ?? '-') . ' / 5') : ($response['answer'] ?? '-') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-lg p-8 text-center text-sm text-gray-500">
                No submissions yet.
            </div>
        @endforelse
    </div>

    <div>
        {{ $submissions->links() }}
    </div>
</div>
@endsection
