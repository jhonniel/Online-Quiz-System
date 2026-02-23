@extends('layouts.admin')

@section('title', 'Edit Subscription Plan Type')
@section('page-title', 'Edit Plan Type')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ route('admin.subscription-plan-types.index') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Plan Types</a>
        </div>
    </li>
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-2 text-sm font-medium text-gray-500">Edit</span>
        </div>
    </li>
@endsection

@section('content')
<div class="w-full min-w-0 px-3 sm:px-4 lg:px-6 py-4">
    <div class="mb-6 sm:mb-8">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-xl sm:rounded-2xl shadow-lg p-4 sm:p-6 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl flex-shrink-0">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold">Edit Plan Type</h1>
                        <p class="text-indigo-100 text-sm mt-0.5">{{ $subscriptionPlanType->name }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.subscription-plan-types.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white/20 hover:bg-white/30 rounded-lg font-medium transition w-full sm:w-auto min-h-[44px] sm:min-h-0 touch-manipulation">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Plan Types
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.subscription-plan-types.update', $subscriptionPlanType) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="p-4 sm:p-6 lg:p-8">
                @include('admin.subscription-plan-types._form', ['planType' => $subscriptionPlanType])
            </div>
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 p-4 sm:p-6 lg:p-8 bg-gray-50/50">
                <a href="{{ route('admin.subscription-plan-types.index') }}" class="inline-flex justify-center items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors w-full sm:w-auto min-h-[48px] sm:min-h-0 touch-manipulation">Cancel</a>
                <button type="submit" class="inline-flex justify-center items-center px-6 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm w-full sm:w-auto min-h-[48px] sm:min-h-0 touch-manipulation">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
