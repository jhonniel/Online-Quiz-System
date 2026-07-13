@extends('layouts.admin')

@section('title', 'Billing – Ongoing & Overdue')
@section('page-title', 'Billing')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 w-full" x-data="{ selectedAccount: '' }">
    {{-- Page header --}}
    <div class="mb-5 sm:mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Billing</h1>
                <p class="mt-1 text-sm text-gray-500">View overdue, ongoing, and past billing for Starlink and Omada. Overdue items are shown first.</p>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                @if(!empty($accountEmails) && count($accountEmails) > 0)
                    <div class="flex items-center gap-2">
                        <label for="account-filter" class="text-sm font-medium text-gray-700 whitespace-nowrap">Account:</label>
                        <select id="account-filter" x-model="selectedAccount" class="mt-1 block rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 min-w-[200px]">
                            <option value="">All accounts</option>
                            @foreach($accountEmails ?? [] as $email)
                                <option value="{{ $email }}">{{ $email }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <a href="{{ url('/admin/linked-accounts') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Linked Accounts
                </a>
            </div>
        </div>
    </div>

    @if(session('success') || request('success'))
        <div class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') ?? request('success') }}
        </div>
    @endif

    {{-- Overdue billing (shown first — most urgent) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5 sm:mb-6">
        <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-red-50/80 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Overdue billing</h2>
                <p class="text-xs text-gray-500 mt-0.5">Starlinks past billing date (based on start date). Select and mark as paid.</p>
            </div>
            <div class="flex items-center gap-3">
                <p class="text-sm text-red-700 font-medium">{{ count($starlinksOverdue ?? []) }} device(s)</p>
                @if(!empty($starlinksOverdue) && count($starlinksOverdue) > 0)
                    <form action="{{ url('/admin/billing/mark-paid') }}" method="POST" class="inline" id="form-mark-overdue" @submit="if (document.querySelectorAll('input[form=&quot;form-mark-overdue&quot;][name=&quot;ids[]&quot;]:checked').length === 0) { $event.preventDefault(); showSelectModal = true }">
                        @csrf
                        <input type="hidden" name="type" value="overdue" />
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Mark selected as paid
                        </button>
                        <button type="submit" formaction="{{ url('/admin/billing/advance-payment') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            Advance payment
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="divide-y divide-gray-200">
            @if(!empty($groupedOverdue) && count($groupedOverdue) > 0)
                @foreach($groupedOverdue as $accountEmail => $group)
                    @if($group['starlinks']->isNotEmpty())
                        <div class="p-4 sm:px-5" x-show="selectedAccount === '' || selectedAccount === @js($accountEmail)" x-transition>
                            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 text-sm">@</span>
                                {{ $accountEmail }}
                            </h3>
                            @if($group['starlinks']->isNotEmpty())
                                <div class="mb-4">
                                    <p class="text-xs font-medium text-red-700 uppercase tracking-wider mb-2">Starlink — overdue (past due from last paid date, not yet paid)</p>
                                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left">
                                                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onclick="var c=this.checked;this.closest('table').querySelectorAll('input[name=&quot;ids[]&quot;]').forEach(function(el){el.checked=c})" />
                                                    </th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Plan</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Was due</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($group['starlinks'] as $starlink)
                                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                                        <td class="px-4 sm:px-5 py-2">
                                                            <input type="checkbox" name="ids[]" value="{{ $starlink->id }}" form="form-mark-overdue" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                                        </td>
                                                        <td class="px-4 sm:px-5 py-2">
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <p class="font-medium text-gray-900 truncate max-w-[200px] sm:max-w-none">{{ $starlink->starlink_id ?: $starlink->serial_number ?: '—' }}</p>
                                                                @if(($starlink->late_payment_count ?? 0) > 0)
                                                                    <span class="inline-flex flex-shrink-0 items-center justify-center min-w-[1.5rem] h-6 px-1.5 rounded-full text-xs font-semibold bg-red-100 text-red-800" title="Late payments since last paid date ({{ $starlink->last_paid_date?->format('M j, Y') ?? '—' }})">{{ $starlink->late_payment_count }}</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="px-4 sm:px-5 py-2 text-sm text-gray-600 hidden sm:table-cell">{{ $starlink->subscriptionPlanType?->name ?? $starlink->plan ?? '—' }}</td>
                                                        <td class="px-4 sm:px-5 py-2 whitespace-nowrap text-sm text-red-700">{{ $starlink->overdue_date?->format('M j, Y') ?? '—' }}</td>
                                                        <td class="px-4 sm:px-5 py-2 text-right whitespace-nowrap">
                                                            <a href="{{ route('admin.starlinks.edit', $starlink) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">Edit</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            @else
                <div class="p-4 sm:px-5">
                    <p class="text-sm text-gray-500 py-4">No overdue devices.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Ongoing billing --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5 sm:mb-6">
        <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-green-50/80 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Ongoing billing</h2>
                <p class="text-xs text-gray-500 mt-0.5">Starlinks to be billed next month (based on last paid date; excludes devices already paid or advanced). Omada devices with active licenses. Select and mark as paid.</p>
            </div>
            <div class="flex items-center gap-3">
                <p class="text-sm text-gray-600">{{ count($starlinksOngoing ?? []) + count($omadaOngoing ?? []) }} device(s)</p>
                @if(!empty($starlinksOngoing) && count($starlinksOngoing) > 0)
                    <form action="{{ url('/admin/billing/mark-paid') }}" method="POST" class="inline" id="form-mark-ongoing" @submit="if (document.querySelectorAll('input[form=&quot;form-mark-ongoing&quot;][name=&quot;ids[]&quot;]:checked').length === 0) { $event.preventDefault(); showSelectModal = true }">
                        @csrf
                        <input type="hidden" name="type" value="ongoing" />
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Mark selected as paid
                        </button>
                        <button type="submit" formaction="{{ url('/admin/billing/advance-payment') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            Advance payment
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="divide-y divide-gray-200">
            @if(!empty($groupedOngoing) && count($groupedOngoing) > 0)
                @foreach($groupedOngoing as $accountEmail => $group)
                    @if($group['starlinks']->isNotEmpty() || $group['omadas']->isNotEmpty())
                        <div class="p-4 sm:px-5" x-show="selectedAccount === '' || selectedAccount === @js($accountEmail)" x-transition>
                            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 text-sm">@</span>
                                {{ $accountEmail }}
                            </h3>
                            @if($group['starlinks']->isNotEmpty())
                                <div class="mb-4">
                                    <p class="text-xs font-medium text-amber-700 uppercase tracking-wider mb-2">Starlink — to be billed next month</p>
                                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left">
                                                        <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onclick="var c=this.checked;this.closest('table').querySelectorAll('input[name=&quot;ids[]&quot;]').forEach(function(el){el.checked=c})" />
                                                    </th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Plan</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next billing</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($group['starlinks'] as $starlink)
                                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                                        <td class="px-4 sm:px-5 py-2">
                                                            <input type="checkbox" name="ids[]" value="{{ $starlink->id }}" form="form-mark-ongoing" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                                        </td>
                                                        <td class="px-4 sm:px-5 py-2">
                                                            <p class="font-medium text-gray-900 truncate max-w-[200px] sm:max-w-none">{{ $starlink->starlink_id ?: $starlink->serial_number ?: '—' }}</p>
                                                        </td>
                                                        <td class="px-4 sm:px-5 py-2 text-sm text-gray-600 hidden sm:table-cell">{{ $starlink->subscriptionPlanType?->name ?? $starlink->plan ?? '—' }}</td>
                                                        <td class="px-4 sm:px-5 py-2 whitespace-nowrap">
                                                            @php $interval = $starlink->billing_interval ?? 'monthly'; @endphp
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $interval === 'yearly' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">{{ $interval === 'yearly' ? 'Yearly' : 'Monthly' }}</span>
                                                        </td>
                                                        <td class="px-4 sm:px-5 py-2 whitespace-nowrap text-sm text-gray-700">{{ $starlink->next_billing_date?->format('M j, Y') ?? '—' }}</td>
                                                        <td class="px-4 sm:px-5 py-2 text-right whitespace-nowrap">
                                                            <a href="{{ route('admin.starlinks.edit', $starlink) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">Edit</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                            @if($group['omadas']->isNotEmpty())
                                <div>
                                    <p class="text-xs font-medium text-violet-700 uppercase tracking-wider mb-2">Omada — active licenses</p>
                                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">License</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License expires</th>
                                                    <th scope="col" class="px-4 sm:px-5 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($group['omadas'] as $omada)
                                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                                        <td class="px-4 sm:px-5 py-2">
                                                            <p class="font-medium text-gray-900 truncate max-w-[200px] sm:max-w-none">{{ $omada->serial_number ?: $omada->license ?: '—' }}</p>
                                                        </td>
                                                        <td class="px-4 sm:px-5 py-2 text-sm text-gray-600 hidden sm:table-cell">{{ $omada->license ?? '—' }}</td>
                                                        <td class="px-4 sm:px-5 py-2 whitespace-nowrap text-sm text-gray-700">{{ $omada->license_expiration?->format('M j, Y') ?? '—' }}</td>
                                                        <td class="px-4 sm:px-5 py-2 text-right whitespace-nowrap">
                                                            <a href="{{ route('admin.omadas.edit', $omada) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-violet-700 bg-violet-50 hover:bg-violet-100 transition-colors">Edit</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            @else
                <div class="p-4 sm:px-5">
                    <p class="text-sm text-gray-500 py-4">No devices due for billing next month.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Past billing — statements (mark as paid records) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-5 sm:mt-6">
        <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-slate-50/80">
            <h2 class="text-sm font-semibold text-gray-900">Past billing</h2>
            <p class="text-xs text-gray-500 mt-0.5">Billing statements from mark as paid and advance payment actions.</p>
        </div>
        <div class="divide-y divide-gray-200">
            @if(!empty($billingStatements) && $billingStatements->isNotEmpty())
                <div class="p-4 sm:px-5">
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Devices</th>
                                    <th scope="col" class="px-4 sm:px-5 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Marked by</th>
                                    <th scope="col" class="px-4 sm:px-5 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($billingStatements as $statement)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-4 sm:px-5 py-2 whitespace-nowrap text-sm text-gray-700">{{ $statement->created_at->format('M j, Y g:i A') }}</td>
                                        <td class="px-4 sm:px-5 py-2 whitespace-nowrap">
                                            @php
                                                $typeLabel = $statement->type === 'advance' ? 'Advance payment' : ucfirst($statement->type) . ' billing';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statement->type === 'advance' ? 'bg-indigo-100 text-indigo-800' : ($statement->type === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }}">{{ $typeLabel }}</span>
                                        </td>
                                        <td class="px-4 sm:px-5 py-2 whitespace-nowrap text-sm text-gray-700">{{ count($statement->starlink_ids ?? []) }} device(s)</td>
                                        <td class="px-4 sm:px-5 py-2 text-sm text-gray-600 hidden sm:table-cell">{{ $statement->markedByUser?->name ?? $statement->markedByUser?->email ?? '—' }}</td>
                                        <td class="px-4 sm:px-5 py-2 text-right whitespace-nowrap">
                                            <a href="{{ url('/admin/billing/statement/' . $statement->id) }}" target="_blank" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">View statement</a>
                                            <a href="{{ url('/admin/billing/statement/' . $statement->id . '/pdf') }}" target="_blank" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors ml-1">PDF</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="p-4 sm:px-5">
                    <p class="text-sm text-gray-500 py-4">No billing statements yet. Mark devices as paid or record an advance payment to see them here.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Select at least one device modal --}}
    <div x-show="showSelectModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true" role="dialog">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="showSelectModal = false" aria-hidden="true"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Select at least one device</h3>
                </div>
                <p class="text-sm text-gray-600 mb-6">Please select one or more devices before marking as paid or recording an advance payment.</p>
                <div class="flex justify-end">
                    <button type="button" @click="showSelectModal = false" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
