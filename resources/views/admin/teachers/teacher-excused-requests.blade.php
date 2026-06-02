@extends('layouts.admin')

@section('page-title', 'Teacher Excused Requests')

@section('content')
<div class="space-y-4 px-3 sm:px-4 lg:px-6">
    <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
        <h1 class="text-lg sm:text-xl font-semibold text-gray-900">Teacher Excused Requests</h1>
        <p class="text-sm text-gray-600 mt-1">
            Each row is one student from a teacher’s excused filing. When a teacher selects multiple students in one form, each student still appears on their own row for review.
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($leaveRequests as $leaveRequest)
                        @php
                            $teacher = $leaveRequest->filedByTeacher();
                            $student = $leaveRequest->user;
                            $cleanReason = $leaveRequest->cleanTeacherExcusedReason();
                        @endphp
                        <tr class="hover:bg-gray-50/80">
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                {{ $leaveRequest->created_at?->format('M j, Y g:i A') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($teacher)
                                    <div class="font-medium text-gray-900">{{ $teacher->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $teacher->email }}</div>
                                @else
                                    <span class="text-gray-400 italic">Unknown</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($student)
                                    <div class="font-medium text-gray-900">{{ $student->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $student->email }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ optional($student->university)->name ?? '—' }}</div>
                                @else
                                    <span class="text-gray-400 italic">Unknown student</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                {{ $leaveRequest->start_date?->format('M j, Y') }}
                                @if($leaveRequest->end_date && $leaveRequest->start_date && ! $leaveRequest->start_date->equalTo($leaveRequest->end_date))
                                    – {{ $leaveRequest->end_date->format('M j, Y') }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-xs">
                                <span class="line-clamp-2" title="{{ $cleanReason }}">{{ \Illuminate\Support\Str::limit($cleanReason, 120) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $leaveRequest->status_badge_class }}">
                                    {{ $leaveRequest->display_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                <a href="{{ route('admin.leave-requests.show', ['leaveRequest' => $leaveRequest->id, 'from' => 'student', 'return' => request()->fullUrl()]) }}"
                                   class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    Review
                                </a>
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
        @if($leaveRequests->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
