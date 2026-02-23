@extends('layouts.admin')

@section('title', 'Subscription Plan Types')
@section('page-title', 'Subscription Plan Types')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 w-full">
    <div class="mb-5 sm:mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Subscription Plan Types</h1>
                <p class="mt-1 text-sm text-gray-500">Manage plan types for Starlink and Omada with monthly, yearly, or custom billing.</p>
            </div>
            <a href="{{ route('admin.subscription-plan-types.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Plan Type
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($planTypes as $plan)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 sm:px-5 py-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $plan->name }}</p>
                                    @if($plan->description)
                                        <p class="text-sm text-gray-500 truncate max-w-[200px]">{{ $plan->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                @if($plan->subscription_type === 'starlink')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Starlink</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">Omada</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap text-sm text-gray-700">
                                {{ $plan->billing_type_label }}
                                @if($plan->billing_day)
                                    <span class="text-gray-500">(day {{ $plan->billing_day }})</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.subscription-plan-types.edit', $plan) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">Edit</a>
                                <form action="{{ route('admin.subscription-plan-types.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Delete this plan type?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 sm:px-5 py-14 text-center">
                                <p class="text-gray-500">No subscription plan types yet.</p>
                                <a href="{{ route('admin.subscription-plan-types.create') }}" class="mt-3 inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                                    Add Plan Type
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($planTypes->hasPages())
            <div class="px-4 sm:px-5 py-3 border-t border-gray-200 bg-gray-50/50">
                {{ $planTypes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
