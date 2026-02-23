@extends('layouts.admin')

@section('title', 'Billing Statement #' . $billingStatement->id)
@section('page-title', 'Billing Statement')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 w-full">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Billing Statement #{{ $billingStatement->id }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $billingStatement->type === 'advance' ? 'Advance payment' : ucfirst($billingStatement->type) . ' billing' }} • {{ $billingStatement->created_at->format('M j, Y g:i A') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('/admin/billing/statement/' . $billingStatement->id . '/pdf') }}" target="_blank"
                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download PDF
                </a>
                <a href="{{ url('/admin/billing') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                    Back to Billing
                </a>
            </div>
        </div>

        {{-- Marked by / Advance payment by --}}
        <div class="mb-6 rounded-lg bg-slate-50 border border-slate-200 p-4">
            <p class="text-sm text-gray-600">
                <span class="font-medium text-gray-900">{{ $billingStatement->type === 'advance' ? 'Advance payment by:' : 'Marked as paid by:' }}</span>
                {{ $billingStatement->markedByUser?->name ?? $billingStatement->markedByUser?->email ?? '—' }}
                <span class="text-gray-500">on {{ $billingStatement->created_at->format('M j, Y g:i A') }}</span>
            </p>
        </div>

        {{-- Paid / Advanced devices --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-green-50/80">
                <h2 class="text-sm font-semibold text-gray-900">{{ count($starlinks) }} device(s) {{ $billingStatement->type === 'advance' ? 'advanced' : 'paid' }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                            <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                            <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                            <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $billingStatement->type === 'advance' ? 'Advanced until' : 'Billing period paid' }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($starlinks as $starlink)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 sm:px-5 py-2 font-medium text-gray-900">{{ $starlink->starlink_id ?: $starlink->serial_number ?: '—' }}</td>
                                <td class="px-4 sm:px-5 py-2 text-sm text-gray-600">{{ $starlink->account_linked_email ?? $starlink->linkedAccount?->email ?? '—' }}</td>
                                <td class="px-4 sm:px-5 py-2 text-sm text-gray-600">{{ $starlink->subscriptionPlanType?->name ?? $starlink->plan ?? '—' }}</td>
                                <td class="px-4 sm:px-5 py-2 text-sm text-gray-700">{{ $billingStatement->type === 'advance' ? ($starlink->advance_payment_until?->format('M j, Y') ?? $billingStatement->advance_payment_until?->format('M j, Y') ?? '—') : ($starlink->last_paid_date?->format('M j, Y') ?? '—') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
