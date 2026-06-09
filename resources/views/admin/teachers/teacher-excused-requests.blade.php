@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl px-4 py-6 sm:px-6 sm:py-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-3 sm:p-4">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Teacher Excused Requests</h1>
                    <p class="text-sm sm:text-base text-indigo-100 mt-1">
                        One row per teacher filing; all students included in that request are listed together
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-2 sm:p-3">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Filings</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-2 sm:p-3">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Pending</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-2 sm:p-3">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Approved</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-lg p-2 sm:p-3">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Rejected</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 sm:p-6">
        <form method="GET" action="{{ url('/admin/teachers-management/teacher-excused-requests') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text"
                           name="search"
                           id="search"
                           value="{{ $search }}"
                           placeholder="Teacher or student name or email"
                           autocomplete="off"
                           class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="" {{ $status === '' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="for_more_verification" {{ $status === 'for_more_verification' ? 'selected' : '' }}>For More Verification</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 mb-2">Per page</label>
                    <select name="per_page" id="per_page" class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach ([10, 20, 50, 100] as $n)
                            <option value="{{ $n }}" {{ (int) $perPage === $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="teacher-excused-filter-submit" class="block text-sm font-medium text-gray-700 mb-2 invisible" aria-hidden="true">Filter</label>
                    <x-admin-filter-button id="teacher-excused-filter-submit" :fullWidth="true" />
                </div>
            </div>

            @if($search !== '' || $status !== '' || (int) $perPage !== 20)
                <div class="flex justify-end">
                    <a href="{{ url('/admin/teachers-management/teacher-excused-requests') }}"
                       class="text-sm text-gray-600 hover:text-gray-900 underline">
                        Clear filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Filings Table -->
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        @if($filings->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filed</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teacher</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students included</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Dates</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Reason</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($filings as $filing)
                            @php
                                $teacher = $filing['teacher'];
                                $requests = $filing['requests'];
                                $representative = $requests->first();
                            @endphp
                            <tr class="hover:bg-gray-50 align-top">
                                <td class="px-3 sm:px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                                    {{ $filing['filed_at']?->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-3 sm:px-6 py-4 text-sm">
                                    @if($teacher)
                                        <div class="font-medium text-gray-900">{{ $teacher->name }}</div>
                                        <div class="text-gray-500 text-xs">{{ $teacher->email }}</div>
                                    @else
                                        <span class="text-gray-400 italic">Unknown</span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-4 text-sm text-gray-800 max-w-md">
                                    @php
                                        $studentNames = $requests
                                            ->map(fn ($lr) => $lr->user?->name)
                                            ->filter()
                                            ->values();
                                    @endphp
                                    <p class="text-gray-900 leading-relaxed">
                                        @if($studentNames->isNotEmpty())
                                            {{ $studentNames->implode(', ') }}
                                        @else
                                            <span class="text-gray-400 italic">No students listed</span>
                                        @endif
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ $requests->count() }} {{ \Illuminate\Support\Str::plural('student', $requests->count()) }} on this request
                                    </p>
                                </td>
                                <td class="px-3 sm:px-6 py-4 hidden sm:table-cell text-sm text-gray-900 whitespace-nowrap">
                                    {{ $filing['start_date']?->format('M j, Y') }}
                                    @if($filing['end_date'] && $filing['start_date'] && ! $filing['start_date']->equalTo($filing['end_date']))
                                        – {{ $filing['end_date']->format('M j, Y') }}
                                    @endif
                                </td>
                                <td class="px-3 sm:px-6 py-4 hidden lg:table-cell text-sm text-gray-600 max-w-xs">
                                    <span class="line-clamp-3" title="{{ $filing['reason'] }}">{{ \Illuminate\Support\Str::limit($filing['reason'], 160) }}</span>
                                </td>
                                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $filing['status_badge_class'] }}">
                                        {{ $filing['status_label'] }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-6 py-4 text-sm text-right whitespace-nowrap">
                                    @if($representative)
                                        <a href="{{ route('admin.leave-requests.show', ['leaveRequest' => $representative->id, 'from' => 'student', 'return' => request()->fullUrl()]) }}"
                                           class="text-indigo-600 hover:text-indigo-800 font-medium">
                                            Open request
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                {{ $filings->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No teacher excused requests</h3>
                <p class="mt-1 text-sm text-gray-500">No requests match your filters.</p>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[action="{{ url('/admin/teachers-management/teacher-excused-requests') }}"]');
        const searchInput = document.getElementById('search');

        let t = null;
        if (form && searchInput) {
            searchInput.addEventListener('input', function () {
                if (t) clearTimeout(t);
                t = setTimeout(() => form.submit(), 350);
            });
        }
    });
</script>
@endsection
