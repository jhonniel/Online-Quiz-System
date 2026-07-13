@extends('layouts.admin')

@push('styles')
<style>
    .chart-container { position: relative; height: 220px; width: 100%; }
    @media (min-width: 640px) { .chart-container { height: 240px; } }
    @media (min-width: 1024px) { .chart-container { height: 260px; } }
    .dashboard-panel-body { min-height: 220px; }
    @media (min-width: 640px) { .dashboard-panel-body { min-height: 240px; } }
    @media (min-width: 1024px) { .dashboard-panel-body { min-height: 260px; } }
</style>
@endpush

@section('title', 'Subscriptions – Dashboard')
@section('page-title', 'Subscriptions')

@section('content')
@php
    $hasAccountsChart = array_sum($weekData ?? []) > 0;
    $hasDeviceChart = array_sum($deviceBreakdownData ?? []) > 0;
    $hasOmadaChart = array_sum($omadaStatusData ?? []) > 0;
    $hasPlanChart = !empty($planTypeData) && array_sum($planTypeData) > 0;
@endphp
<div class="px-3 sm:px-4 lg:px-6 py-4 w-full min-w-0">
    {{-- Page header --}}
    <div class="mb-5 sm:mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Subscriptions</h1>
                <p class="mt-1 text-sm text-gray-500">Dashboard of linked accounts, Starlink and Omada devices, billing, and stats.</p>
            </div>
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 w-full lg:w-auto">
                <a href="{{ url('/admin/starlinks') }}" class="inline-flex items-center justify-center px-3 py-2.5 sm:px-4 border border-indigo-200 rounded-lg text-sm font-medium text-indigo-800 bg-indigo-50 hover:bg-indigo-100 transition-colors min-h-[44px] touch-manipulation">
                    <svg class="w-4 h-4 mr-2 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                    Starlinks
                </a>
                <a href="{{ url('/admin/omadas') }}" class="inline-flex items-center justify-center px-3 py-2.5 sm:px-4 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors min-h-[44px] touch-manipulation">
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2m14-8V6a2 2 0 00-2-2m-4 0a2 2 0 00-2 2v4a2 2 0 002 2m4 0h2"></path></svg>
                    Omada
                </a>
                @if(auth()->user()->canAccessBilling())
                <a href="{{ url('/admin/billing') }}" class="inline-flex items-center justify-center px-3 py-2.5 sm:px-4 border border-amber-200 rounded-lg text-sm font-medium text-amber-800 bg-amber-50 hover:bg-amber-100 transition-colors min-h-[44px] touch-manipulation">
                    <svg class="w-4 h-4 mr-2 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path></svg>
                    Billing
                </a>
                @endif
                @if(auth()->user()->canAccessSubscriptionFeature('plan_types'))
                <a href="{{ url('/admin/subscription-plan-types') }}" class="inline-flex items-center justify-center px-3 py-2.5 sm:px-4 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors min-h-[44px] touch-manipulation">
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    <span class="truncate">Plan Types</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-start sm:items-center">
            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="min-w-0 break-words">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 mb-5 sm:mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Linked accounts</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $total }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Starlink devices</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalStarlinks }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-violet-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2m14-8V6a2 2 0 00-2-2m-4 0a2 2 0 00-2 2v4a2 2 0 002 2m4 0h2"></path></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500">Omada devices</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalOmadas }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-green-700">Omada active</p>
                    <p class="text-2xl font-semibold text-green-700">{{ $omadaActiveCount }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $omadaExpiredCount }} expired</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ongoing billing (moved up – actionable) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5 sm:mb-6">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-200 bg-amber-50/80 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-sm sm:text-base font-semibold text-gray-900">Ongoing billing devices</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Starlinks due next month and Omada devices with active licenses.</p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <p class="text-sm font-medium text-gray-700">{{ count($starlinksToBillNextMonth ?? []) + count($omadaOngoingBilling ?? []) }} device(s)</p>
                @if(auth()->user()->canAccessBilling())
                <a href="{{ url('/admin/billing') }}" class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs sm:text-sm font-medium text-amber-800 bg-amber-100 hover:bg-amber-200 transition-colors min-h-[40px] touch-manipulation">View billing</a>
                @endif
            </div>
        </div>

        {{-- Starlink billing --}}
        <div class="border-b border-gray-200">
            <div class="px-4 sm:px-5 py-3 bg-gray-50/50">
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-amber-100 text-amber-700 text-[10px] font-bold">S</span>
                    Starlink — to be billed next month
                </h3>
            </div>

            @if(!empty($starlinksToBillNextMonth) && count($starlinksToBillNextMonth) > 0)
                <div class="md:hidden divide-y divide-gray-200">
                    @foreach($starlinksToBillNextMonth as $starlink)
                        <div class="p-4 space-y-2">
                            <p class="font-medium text-gray-900 break-words">{{ $starlink->starlink_id ?: $starlink->serial_number ?: '—' }}</p>
                            <p class="text-sm text-gray-500 break-words">{{ $starlink->account_linked_email ?? ($starlink->linkedAccount?->email ?? '—') }}</p>
                            <p class="text-sm text-gray-600">Client: {{ $starlink->municipality ?? '—' }}</p>
                            <dl class="grid grid-cols-2 gap-2 text-xs pt-1">
                                <div><dt class="text-gray-500">Plan</dt><dd class="font-medium text-gray-900">{{ $starlink->plan ?? '—' }}</dd></div>
                                <div>
                                    <dt class="text-gray-500">Billing</dt>
                                    <dd>
                                        @php $interval = $starlink->billing_interval ?? 'monthly'; @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $interval === 'yearly' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">{{ $interval === 'yearly' ? 'Yearly' : 'Monthly' }}</span>
                                    </dd>
                                </div>
                                <div class="col-span-2"><dt class="text-gray-500">Next billing</dt><dd class="font-medium text-gray-900">{{ $starlink->next_billing_date?->format('M j, Y') ?? '—' }}</dd></div>
                            </dl>
                            <a href="{{ route('admin.starlinks.edit', $starlink) }}" class="inline-flex items-center justify-center w-full px-3 py-2.5 rounded-lg text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors min-h-[44px] touch-manipulation">Edit / set advance payment</a>
                        </div>
                    @endforeach
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device / Account</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next billing</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($starlinksToBillNextMonth as $starlink)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-900">{{ $starlink->starlink_id ?: $starlink->serial_number ?: '—' }}</p>
                                        <p class="text-sm text-gray-500">{{ $starlink->account_linked_email ?? ($starlink->linkedAccount?->email ?? '—') }}</p>
                                        <p class="text-sm text-gray-600">Client: {{ $starlink->municipality ?? '—' }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-600">{{ $starlink->plan ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        @php $interval = $starlink->billing_interval ?? 'monthly'; @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $interval === 'yearly' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">{{ $interval === 'yearly' ? 'Yearly' : 'Monthly' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $starlink->next_billing_date?->format('M j, Y') ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('admin.starlinks.edit', $starlink) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-4 sm:px-5 py-6 text-sm text-gray-500">No Starlinks due for billing next month, or all have advance payment set.</p>
            @endif
        </div>

        {{-- Omada billing --}}
        <div>
            <div class="px-4 sm:px-5 py-3 bg-gray-50/50">
                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-violet-100 text-violet-700 text-[10px] font-bold">O</span>
                    Omada — active licenses
                </h3>
            </div>
            @if(!empty($omadaOngoingBilling) && count($omadaOngoingBilling) > 0)
                <div class="md:hidden divide-y divide-gray-200">
                    @foreach($omadaOngoingBilling as $omada)
                        <div class="p-4 space-y-2">
                            <p class="font-medium text-gray-900 break-words">{{ $omada->serial_number ?: $omada->license ?: '—' }}</p>
                            <p class="text-sm text-gray-500 break-words">{{ $omada->account_linked_email ?? '—' }}</p>
                            <dl class="grid grid-cols-2 gap-2 text-xs pt-1">
                                <div><dt class="text-gray-500">License</dt><dd class="font-medium text-gray-900">{{ $omada->license ?? '—' }}</dd></div>
                                <div><dt class="text-gray-500">Expires</dt><dd class="font-medium text-gray-900">{{ $omada->license_expiration?->format('M j, Y') ?? '—' }}</dd></div>
                            </dl>
                            <a href="{{ route('admin.omadas.edit', $omada) }}" class="inline-flex items-center justify-center w-full px-3 py-2.5 rounded-lg text-sm font-medium text-violet-700 bg-violet-50 hover:bg-violet-100 transition-colors min-h-[44px] touch-manipulation">Edit device</a>
                        </div>
                    @endforeach
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device / Account</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License expires</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($omadaOngoingBilling as $omada)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-900">{{ $omada->serial_number ?: $omada->license ?: '—' }}</p>
                                        <p class="text-sm text-gray-500">{{ $omada->account_linked_email ?? '—' }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-sm text-gray-600">{{ $omada->license ?? '—' }}</td>
                                    <td class="px-5 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $omada->license_expiration?->format('M j, Y') ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('admin.omadas.edit', $omada) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-violet-700 bg-violet-50 hover:bg-violet-100">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-4 sm:px-5 py-6 text-sm text-gray-500">No Omada devices with active licenses.</p>
            @endif
        </div>
    </div>

    {{-- Charts row 1 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-12 gap-4 sm:gap-5 mb-5 sm:mb-6 items-stretch">
        <div class="xl:col-span-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Accounts created (last 12 weeks)</h2>
                <p class="text-xs text-gray-500 mt-0.5">New linked accounts per week</p>
            </div>
            @if($hasAccountsChart)
                <div class="p-4 sm:p-5">
                    <div class="chart-container" id="wrapAccountsOverTime">
                        <canvas id="chartAccountsOverTime" aria-label="Accounts over time" role="img"></canvas>
                    </div>
                </div>
            @else
                @include('admin.linked-accounts.partials.panel-empty', [
                    'message' => 'No new accounts in the last 12 weeks',
                    'icon' => 'chart',
                ])
            @endif
        </div>
        <div class="md:col-span-1 xl:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Devices by type</h2>
                <p class="text-xs text-gray-500 mt-0.5">Starlink vs Omada</p>
            </div>
            @if($hasDeviceChart)
                <div class="p-4 sm:p-5">
                    <div class="chart-container" id="wrapDeviceBreakdown">
                        <canvas id="chartDeviceBreakdown" aria-label="Device breakdown" role="img"></canvas>
                    </div>
                </div>
            @else
                @include('admin.linked-accounts.partials.panel-empty', [
                    'message' => 'No devices yet',
                    'icon' => 'wifi',
                ])
            @endif
        </div>
        <div class="md:col-span-1 xl:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Recently linked</h2>
                <p class="text-xs text-gray-500 mt-0.5">Latest accounts</p>
            </div>
            @if($recentLinkedAccounts->isNotEmpty())
                <ul class="divide-y divide-gray-200 flex-1 max-h-[280px] overflow-y-auto dashboard-panel-body">
                    @foreach($recentLinkedAccounts as $acc)
                        <li class="px-4 sm:px-5 py-3 hover:bg-gray-50/50 transition-colors">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-900 truncate">{{ $acc->name ?: '—' }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ $acc->email }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5 sm:justify-end">
                                    @if($acc->starlinks_count > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">{{ $acc->starlinks_count }} Starlink</span>
                                    @endif
                                    @if(($acc->ongoing_billing_count ?? 0) > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">↑ {{ $acc->ongoing_billing_count }}</span>
                                    @endif
                                    @if(($acc->overdue_billing_count ?? 0) > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">↓ {{ $acc->overdue_billing_count }}</span>
                                    @endif
                                    <span class="text-xs text-gray-400 w-full sm:w-auto">{{ $acc->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                @include('admin.linked-accounts.partials.panel-empty', [
                    'message' => 'No linked accounts yet',
                    'hint' => 'Accounts appear when devices are linked by email.',
                    'icon' => 'users',
                ])
            @endif
        </div>
    </div>

    {{-- Charts row 2 --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5 mb-5 sm:mb-6 items-stretch">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Omada license status</h2>
                <p class="text-xs text-gray-500 mt-0.5">Active vs expired</p>
            </div>
            @if($hasOmadaChart)
                <div class="p-4 sm:p-5">
                    <div class="chart-container" id="wrapOmadaStatus">
                        <canvas id="chartOmadaStatus" aria-label="Omada status" role="img"></canvas>
                    </div>
                </div>
            @else
                @include('admin.linked-accounts.partials.panel-empty', [
                    'message' => 'No Omada licenses',
                    'icon' => 'check',
                ])
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Starlink plan type</h2>
                <p class="text-xs text-gray-500 mt-0.5">Devices by plan</p>
            </div>
            @if($hasPlanChart)
                <div class="p-4 sm:p-5">
                    <div class="chart-container" id="wrapPlanType">
                        <canvas id="chartPlanType" aria-label="Starlink plan type" role="img"></canvas>
                    </div>
                </div>
            @else
                @include('admin.linked-accounts.partials.panel-empty', [
                    'message' => 'No Starlink plans yet',
                    'icon' => 'chart',
                ])
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden md:col-span-2 xl:col-span-1 flex flex-col">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50 flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Client name counts</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Starlinks by client</p>
                </div>
                <span class="text-xs text-gray-500 flex-shrink-0">{{ $clientNameUniqueCount ?? 0 }} total</span>
            </div>
            @if(!empty($clientNameCountsTop) && $clientNameCountsTop->count() > 0)
                <ul class="divide-y divide-gray-200 max-h-[280px] overflow-y-auto dashboard-panel-body">
                    @foreach($clientNameCountsTop as $clientName => $count)
                        <li class="px-4 sm:px-5 py-3 hover:bg-gray-50/50 flex items-center justify-between gap-3">
                            <p class="font-medium text-gray-900 truncate min-w-0">{{ $clientName }}</p>
                            <span class="inline-flex flex-shrink-0 items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ $count }} Starlink</span>
                        </li>
                    @endforeach
                </ul>
            @else
                @include('admin.linked-accounts.partials.panel-empty', [
                    'message' => 'No client names yet',
                    'hint' => 'Set Client Name on Starlink devices to see counts.',
                    'icon' => 'chart',
                ])
            @endif
        </div>
    </div>

    {{-- All linked accounts --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-gray-200 bg-gray-50/50 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-sm sm:text-base font-semibold text-gray-900">All linked accounts</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Accounts linked to Starlink and Omada devices</p>
            </div>
            <p class="text-sm text-gray-500 flex-shrink-0">{{ $linkedAccounts->total() }} total</p>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($linkedAccounts as $account)
                <div class="p-4 space-y-3">
                    <div>
                        <p class="font-medium text-gray-900 break-words">{{ $account->name ?: '—' }}</p>
                        <p class="text-sm text-gray-600 break-all">{{ $account->email }}</p>
                    </div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-xs">
                        <div>
                            <dt class="text-gray-500">Provider</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $account->provider ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">User</dt>
                            <dd class="mt-0.5 font-medium text-gray-900 truncate">{{ $account->user ? $account->user->name : '—' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">Linked</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $account->created_at->format('M j, Y') }}</dd>
                        </div>
                    </dl>
                    <div class="flex flex-wrap gap-1.5">
                        @if(($account->starlinks_count ?? 0) > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ $account->starlinks_count }} Starlink</span>
                        @endif
                        @if(($account->omadas_count ?? 0) > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">{{ $account->omadas_count }} Omada</span>
                        @endif
                        @if(($account->ongoing_billing_count ?? 0) > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">↑ {{ $account->ongoing_billing_count }} ongoing</span>
                        @endif
                        @if(($account->overdue_billing_count ?? 0) > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">↓ {{ $account->overdue_billing_count }} overdue</span>
                        @endif
                        @if(($account->starlinks_count ?? 0) === 0 && ($account->omadas_count ?? 0) === 0)
                            <span class="text-gray-400 text-xs">No devices</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-14 text-center">
                    <div class="flex flex-col items-center max-w-sm mx-auto">
                        <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <p class="text-gray-900 font-medium">No linked accounts yet</p>
                        <p class="text-sm text-gray-500 mt-1">Add Starlink or Omada devices and link them to an account email.</p>
                        <a href="{{ url('/admin/starlinks/create') }}" class="mt-4 inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 min-h-[44px] touch-manipulation">
                            Add Starlink device
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">User</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Devices</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Linked</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($linkedAccounts as $account)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900">{{ $account->name ?: '—' }}</p>
                                <p class="text-sm text-gray-600">{{ $account->email }}</p>
                            </td>
                            <td class="px-5 py-3">
                                @if($account->provider)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $account->provider }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600 hidden lg:table-cell">{{ $account->user ? $account->user->name : '—' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @if(($account->starlinks_count ?? 0) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ $account->starlinks_count }} Starlink</span>
                                    @endif
                                    @if(($account->omadas_count ?? 0) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">{{ $account->omadas_count }} Omada</span>
                                    @endif
                                    @if(($account->ongoing_billing_count ?? 0) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" title="Ongoing billing">↑ {{ $account->ongoing_billing_count }}</span>
                                    @endif
                                    @if(($account->overdue_billing_count ?? 0) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="Overdue billing">↓ {{ $account->overdue_billing_count }}</span>
                                    @endif
                                    @if(($account->starlinks_count ?? 0) === 0 && ($account->omadas_count ?? 0) === 0)
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 whitespace-nowrap hidden xl:table-cell">{{ $account->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center text-sm text-gray-500">No linked accounts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($linkedAccounts->hasPages())
            <div class="px-4 sm:px-5 py-3 border-t border-gray-200 bg-gray-50/50 overflow-x-auto">
                {{ $linkedAccounts->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const weekLabels = @json($weekLabels ?? []);
    const weekData = @json($weekData ?? []);
    const deviceLabels = @json($deviceBreakdownLabels ?? []);
    const deviceData = @json($deviceBreakdownData ?? []);
    const omadaLabels = @json($omadaStatusLabels ?? []);
    const omadaData = @json($omadaStatusData ?? []);
    const planTypeLabels = @json($planTypeLabels ?? []);
    const planTypeData = @json($planTypeData ?? []);

    const defaultBarOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } };
    const defaultDoughnutOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };

    if (weekData.length && weekData.some(function(v) { return v > 0; })) {
        var chartAccounts = document.getElementById('chartAccountsOverTime');
        if (chartAccounts) {
            new Chart(chartAccounts, {
                type: 'bar',
                data: { labels: weekLabels, datasets: [{ label: 'Accounts', data: weekData, backgroundColor: 'rgba(99, 102, 241, 0.6)', borderColor: 'rgb(99, 102, 241)', borderWidth: 1 }] },
                options: defaultBarOptions
            });
        }
    }

    if (deviceLabels.length && deviceData.some(function(v) { return v > 0; })) {
        var chartDevice = document.getElementById('chartDeviceBreakdown');
        if (chartDevice) {
            new Chart(chartDevice, {
                type: 'doughnut',
                data: { labels: deviceLabels, datasets: [{ data: deviceData, backgroundColor: ['rgb(99, 102, 241)', 'rgb(139, 92, 246)'], borderWidth: 2 }] },
                options: defaultDoughnutOptions
            });
        }
    }

    if (omadaLabels.length && omadaData.some(function(v) { return v > 0; })) {
        var chartOmada = document.getElementById('chartOmadaStatus');
        if (chartOmada) {
            new Chart(chartOmada, {
                type: 'doughnut',
                data: { labels: omadaLabels, datasets: [{ data: omadaData, backgroundColor: ['rgb(34, 197, 94)', 'rgb(239, 68, 68)'], borderWidth: 2 }] },
                options: defaultDoughnutOptions
            });
        }
    }

    if (planTypeLabels.length && planTypeData.some(function(v) { return v > 0; })) {
        var chartPlan = document.getElementById('chartPlanType');
        if (chartPlan) {
            var planColors = ['rgb(99, 102, 241)', 'rgb(139, 92, 246)', 'rgb(34, 197, 94)', 'rgb(234, 179, 8)', 'rgb(239, 68, 68)', 'rgb(14, 165, 233)', 'rgb(168, 85, 247)', 'rgb(107, 114, 128)'];
            var bg = planTypeLabels.map(function(_, i) { return planColors[i % planColors.length]; });
            new Chart(chartPlan, {
                type: 'doughnut',
                data: { labels: planTypeLabels, datasets: [{ data: planTypeData, backgroundColor: bg, borderWidth: 2 }] },
                options: defaultDoughnutOptions
            });
        }
    }
});
</script>
@endpush
@endsection
