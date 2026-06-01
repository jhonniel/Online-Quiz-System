@extends('layouts.admin')

@section('page-title', 'Teacher Excused Requests')

@section('content')
<div class="space-y-4 px-3 sm:px-4 lg:px-6">
    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-900">Teacher Excused Requests</h1>
        <p class="text-sm text-gray-600 mt-1">
            Student excused (absent) requests submitted by teachers, grouped by teacher and filing. Each row lists all students included in that submission.
        </p>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <form method="GET" action="{{ url('/admin/teachers-management/teacher-excused-requests') }}" class="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-end">
            <div class="flex-1 min-w-[12rem]">
                <label for="search" class="block text-xs font-medium text-gray-500 mb-1">Search teacher or student</label>
                <input id="search" type="text" name="search" value="{{ $search }}" placeholder="Name or email" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div class="w-full sm:w-44">
                <label for="status" class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select id="status" name="status" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="" {{ $status === '' ? 'selected' : '' }}>All</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="for_more_verification" {{ $status === 'for_more_verification' ? 'selected' : '' }}>For more verification</option>
                </select>
            </div>
            <div class="w-full sm:w-32">
                <label for="per_page" class="block text-xs font-medium text-gray-500 mb-1">Per page</label>
                <select id="per_page" name="per_page" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @foreach ([10, 20, 50, 100] as $n)
                        <option value="{{ $n }}" {{ (int) $perPage === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">Apply</button>
                <a href="{{ url('/admin/teachers-management/teacher-excused-requests') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-300">Clear</a>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filed</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students included</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($groupedRequests as $group)
                        @php
                            $teacher = $group['teacher'];
                            $students = $group['students'];
                            $studentCount = $students->count();
                        @endphp
                        <tr class="align-top">
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                {{ optional($group['filed_at'])->format('M j, Y g:i A') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($teacher)
                                    <div class="font-medium text-gray-900">{{ $teacher->name }}</div>
                                    <div class="text-gray-500">{{ $teacher->email }}</div>
                                @else
                                    <span class="text-gray-400 italic">Unknown</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <p class="text-xs font-medium text-gray-500 mb-1.5">
                                    {{ $studentCount }} {{ $studentCount === 1 ? 'student' : 'students' }}
                                </p>
                                <ul class="space-y-2 max-w-md">
                                    @foreach($students as $entry)
                                        @php
                                            $student = $entry['user'];
                                            $leaveRequest = $entry['leave_request'];
                                        @endphp
                                        <li class="rounded-lg border border-gray-100 bg-gray-50/80 px-2.5 py-2">
                                            @if($student)
                                                <div class="font-medium text-gray-900">{{ $student->name }}</div>
                                                <div class="text-gray-500 text-xs">{{ $student->email }}</div>
                                                <div class="text-xs text-gray-400 mt-0.5">{{ optional($student->university)->name ?? '—' }}</div>
                                            @else
                                                <span class="text-gray-400 italic">Unknown student</span>
                                            @endif
                                            @if($group['status_summary'] === 'Mixed')
                                                <span class="mt-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $leaveRequest->status_badge_class }}">
                                                    {{ $leaveRequest->display_status }}
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                {{ $group['start_date']?->format('M j, Y') }}
                                @if($group['end_date'] && $group['start_date'] && ! $group['start_date']->equalTo($group['end_date']))
                                    – {{ $group['end_date']->format('M j, Y') }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-xs">
                                <span class="line-clamp-2" title="{{ $group['reason'] }}">{{ \Illuminate\Support\Str::limit($group['reason'], 120) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $group['status_badge_class'] }}">
                                    {{ $group['status_summary'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <ul class="space-y-1">
                                    @foreach($students as $entry)
                                        @php $leaveRequest = $entry['leave_request']; @endphp
                                        <li>
                                            <a href="{{ url('/admin/leave-requests/'.$leaveRequest->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap">
                                                Review{{ $studentCount > 1 ? ' — '.(\Illuminate\Support\Str::limit(optional($entry['user'])->name ?? 'Student', 18)) : '' }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                                No teacher-filed excused requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($groupedRequests->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $groupedRequests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
