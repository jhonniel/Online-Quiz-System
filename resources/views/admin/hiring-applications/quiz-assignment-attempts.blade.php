@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.hiring-applications.show', $application) }}" class="text-sm text-indigo-100 hover:text-white">← Back to application</a>
                <h1 class="mt-2 text-xl font-bold text-white">Quiz attempts</h1>
                <p class="text-indigo-100 text-sm mt-1">{{ $application->full_name }} — {{ $assignment->quiz->title ?? 'Quiz' }}</p>
            </div>
            <div class="text-right text-sm text-indigo-100">
                <div>Best score: <span class="text-white font-semibold">{{ (int) ($assignment->best_score ?? 0) }}</span> / {{ (int) ($assignment->quiz->total_questions ?? 0) }}</div>
                <div class="mt-1">Assignment status: <span class="text-white font-medium">{{ $assignment->getStatusText() }}</span></div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">All recorded attempts</h2>
            <p class="mt-1 text-sm text-gray-500">Question-by-question review is available under Quizzes admin if you have Content management access.</p>
        </div>
        <div class="p-6">
            @if($attemptHistory->isEmpty())
                <p class="text-sm text-gray-500 text-center py-8">No attempts recorded yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($attemptHistory as $attempt)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">{{ $attempt->attempt_number }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ (int) $attempt->correct_answers }}/{{ (int) $attempt->total_questions }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ $attempt->percentage }}%</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ $attempt->time_taken_formatted }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $attempt->getStatusBadgeClass() }}">{{ $attempt->getStatusText() }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $attempt->started_at ? $attempt->started_at->format('M j, Y g:i A') : '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $attempt->completed_at ? $attempt->completed_at->format('M j, Y g:i A') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
