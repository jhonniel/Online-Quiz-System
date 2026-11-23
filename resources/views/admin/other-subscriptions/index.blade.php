@extends('layouts.admin')

@section('title', 'Others')
@section('page-title', 'Others')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 w-full">
    <div class="mb-5 sm:mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Other Subscriptions</h1>
                <p class="mt-1 text-sm text-gray-500">Track ChatGPT, Gemini, and other online subscriptions. Emails notify when they end or renew.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('/admin/linked-accounts') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Dashboard
                </a>
                <a href="{{ route('admin.other-subscriptions.create') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Subscription
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-500">Active</p>
            <p class="text-2xl font-semibold text-emerald-700">{{ $activeCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-4">
            <p class="text-xs font-medium text-amber-700">Ending within 30 days</p>
            <p class="text-2xl font-semibold text-amber-700">{{ $upcomingCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs font-medium text-red-700">Expired</p>
            <p class="text-2xl font-semibold text-red-700">{{ $expiredCount }}</p>
        </div>
    </div>

    <div class="mb-5 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
        Reminder emails are managed in
        <a href="{{ url('/admin/settings') }}" class="font-semibold underline hover:text-indigo-700">Admin → Settings</a>
        under <span class="font-medium">Admin Notification Emails (Other Subscriptions)</span>.
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">All subscriptions</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $subscriptions->total() }} subscription{{ $subscriptions->total() !== 1 ? 's' : '' }}</p>
                </div>
                <form method="GET" action="{{ route('admin.other-subscriptions.index') }}" class="flex gap-2 w-full sm:w-auto sm:min-w-[280px]">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search name, provider, type…"
                           class="block w-full rounded-lg border border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Search</button>
                    @if(!empty($search))
                        <a href="{{ route('admin.other-subscriptions.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Type</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Price</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Start</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($subscriptions as $subscription)
                        @php $daysLeft = $subscription->daysUntilEnd(); @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 sm:px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $subscription->name }}</p>
                                <p class="text-xs text-gray-500">{{ $subscription->providerDisplayName() }}</p>
                                @if($subscription->auto_renew)
                                    <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-sky-50 text-sky-700">Auto renew</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-600 hidden sm:table-cell">{{ $subscription->billing_type_label }}</td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-600 hidden md:table-cell">{{ $subscription->formatted_price }}</td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-600 hidden lg:table-cell">{{ $subscription->starts_on?->format('M j, Y') }}</td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $subscription->ends_on?->format('M j, Y') }}
                                @if($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= ($subscription->notify_days_before ?? 7))
                                    <span class="block text-xs text-amber-600 mt-0.5">
                                        {{ $daysLeft === 0 ? 'Today' : ($daysLeft === 1 ? 'Tomorrow' : $daysLeft.' days left') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                @if($subscription->status === 'Active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Expired</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.other-subscriptions.edit', $subscription) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">Edit</a>
                                    <form action="{{ route('admin.other-subscriptions.destroy', $subscription) }}" method="POST" onsubmit="return confirm('Remove this subscription?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 sm:px-6 py-10 text-center text-sm text-gray-500">
                                No other subscriptions yet. Add ChatGPT, Gemini, or any online subscription to track renewals.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
