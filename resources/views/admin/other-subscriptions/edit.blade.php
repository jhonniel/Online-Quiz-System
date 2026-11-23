@extends('layouts.admin')

@section('title', 'Edit Other Subscription')
@section('page-title', 'Edit Other Subscription')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ route('admin.other-subscriptions.index') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Others</a>
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
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold">Edit Other Subscription</h1>
                    <p class="text-indigo-100 text-sm mt-0.5 truncate">{{ $subscription->name }}</p>
                </div>
                <a href="{{ route('admin.other-subscriptions.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-white/20 hover:bg-white/30 rounded-lg font-medium transition w-full sm:w-auto">
                    Back to Others
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.other-subscriptions.update', $subscription) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="p-4 sm:p-6 lg:p-8">
                @include('admin.other-subscriptions._form', ['subscription' => $subscription, 'providers' => $providers ?? collect()])
            </div>
            <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 p-4 sm:p-6 lg:p-8 bg-gray-50/50">
                <a href="{{ route('admin.other-subscriptions.index') }}" class="inline-flex justify-center items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                <button type="submit" class="inline-flex justify-center items-center px-6 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
