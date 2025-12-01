@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Leave Request Details</h1>
                    <p class="text-indigo-100 text-sm">View your leave request information</p>
                </div>
            </div>
            <a href="{{ route('user.leave-requests.index') }}"
               class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white hover:bg-white/30 transition duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>
    </div>

    <!-- Details -->
    <div class="flex-1 overflow-y-auto p-4">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 space-y-6">
                <!-- Status Badge -->
                <div class="flex items-center justify-between">
                    <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full {{ $leaveRequest->status_badge_class }}">
                        {{ ucfirst($leaveRequest->status) }}
                    </span>
                    @if($leaveRequest->isPending())
                        <form action="{{ route('user.leave-requests.destroy', $leaveRequest) }}" method="POST" class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this leave request?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Request
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Request Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Request Type</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->type_label }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $leaveRequest->days }} {{ $leaveRequest->days == 1 ? 'day' : 'days' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Start Date</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->start_date->format('F d, Y') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">End Date</label>
                        <p class="text-sm font-semibold text-gray-900">
                            @if($leaveRequest->end_date)
                                {{ $leaveRequest->end_date->format('F d, Y') }}
                            @else
                                <span class="text-gray-400">Same day</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Submitted On</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->created_at->format('F d, Y g:i A') }}</p>
                    </div>

                    @if($leaveRequest->reviewed_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Reviewed On</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->reviewed_at->format('F d, Y g:i A') }}</p>
                        </div>

                        @if($leaveRequest->reviewer)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Reviewed By</label>
                                <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->reviewer->name }}</p>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Reason -->
                @if($leaveRequest->reason)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Reason</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            {{ $leaveRequest->reason }}
                        </p>
                    </div>
                @endif

                <!-- Admin Notes -->
                @if($leaveRequest->admin_notes)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Admin Notes</label>
                        <p class="text-sm text-gray-900 bg-blue-50 p-4 rounded-lg border border-blue-200">
                            {{ $leaveRequest->admin_notes }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

