@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 bg-white/20 backdrop-blur-sm rounded-2xl p-4">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Leave Request Details</h1>
                    <p class="text-indigo-100 mt-1">Review and manage this leave request</p>
                </div>
            </div>
            <a href="{{ route('admin.leave-requests.index') }}"
               class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg text-white hover:bg-white/20 transition duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Request Information -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Request Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Employee</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $leaveRequest->user->email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Request Type</label>
                        <p class="text-sm font-semibold text-gray-900">{{ $leaveRequest->type_label }}</p>
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
                        <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $leaveRequest->days }} {{ $leaveRequest->days == 1 ? 'day' : 'days' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $leaveRequest->status_badge_class }}">
                            {{ ucfirst($leaveRequest->status) }}
                        </span>
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

                @if($leaveRequest->reason)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Reason</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            {{ $leaveRequest->reason }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Admin Notes -->
            @if($leaveRequest->admin_notes)
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Admin Notes</h2>
                    <p class="text-sm text-gray-900 bg-blue-50 p-4 rounded-lg border border-blue-200 whitespace-pre-line">
                        {{ $leaveRequest->admin_notes }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Action Panel -->
        <div class="space-y-6">
            @if($leaveRequest->isPending())
                <!-- Approve Form -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Approve Request</h3>
                    <form action="{{ route('admin.leave-requests.approve', $leaveRequest) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="approve_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="admin_notes" id="approve_notes" rows="3"
                                      placeholder="Add any notes about this approval..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Approve Request
                        </button>
                    </form>
                </div>

                <!-- Reject Form -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Request</h3>
                    <form action="{{ route('admin.leave-requests.reject', $leaveRequest) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="reject_notes" class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                            <textarea name="admin_notes" id="reject_notes" rows="3"
                                      placeholder="Please provide a reason for rejection..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reject Request
                        </button>
                    </form>
                </div>

                <!-- Resubmit Form -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Request Resubmission</h3>
                    <p class="text-sm text-gray-600 mb-4">If there are errors in the request, you can ask the employee to resubmit it.</p>
                    <form action="{{ route('admin.leave-requests.resubmit', $leaveRequest) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="resubmit_notes" class="block text-sm font-medium text-gray-700 mb-2">What needs to be corrected?</label>
                            <textarea name="admin_notes" id="resubmit_notes" rows="3"
                                      placeholder="Describe what errors need to be fixed..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                        </div>
                        <button type="submit"
                                class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Request Resubmission
                        </button>
                    </form>
                </div>
            @else
                <!-- Status Info -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Request Status</h3>
                    <div class="text-center py-4">
                        <span class="px-4 py-2 inline-flex text-lg leading-5 font-semibold rounded-full {{ $leaveRequest->status_badge_class }}">
                            {{ ucfirst($leaveRequest->status) }}
                        </span>
                        <p class="text-sm text-gray-500 mt-4">
                            This request has already been {{ $leaveRequest->status }}.
                        </p>
                        @if($leaveRequest->isRejected() || $leaveRequest->isApproved())
                            <form action="{{ route('admin.leave-requests.resubmit', $leaveRequest) }}" method="POST" class="mt-4">
                                @csrf
                                <div class="mb-4">
                                    <label for="resubmit_notes_existing" class="block text-sm font-medium text-gray-700 mb-2">Notes for Resubmission</label>
                                    <textarea name="admin_notes" id="resubmit_notes_existing" rows="3"
                                              placeholder="Add notes about what needs to be corrected..."
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                                </div>
                                <button type="submit"
                                        class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Request Resubmission
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

