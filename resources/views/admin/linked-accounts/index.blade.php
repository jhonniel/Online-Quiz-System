@extends('layouts.admin')

@push('styles')
<style>
    .chart-container { position: relative; height: 220px; width: 100%; }
    @media (min-width: 640px) { .chart-container { height: 240px; } }
    @media (min-width: 1024px) { .chart-container { height: 260px; } }
    .chart-empty { display: flex; align-items: center; justify-content: center; height: 100%; min-height: 180px; color: #9ca3af; font-size: 0.875rem; text-align: center; padding: 1rem; }
</style>
@endpush

@section('title', 'Linked Accounts – Dashboard')
@section('page-title', 'Linked Accounts')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 w-full">
    {{-- Page header --}}
    <div class="mb-5 sm:mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Linked Accounts</h1>
                <p class="mt-1 text-sm text-gray-500">Dashboard of linked accounts, Starlink and Omada devices. View stats and manage devices.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('/admin/starlinks') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-indigo-200 rounded-lg text-sm font-medium text-indigo-800 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                    Starlinks
                </a>
                <a href="{{ url('/admin/omadas') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2m14-8V6a2 2 0 00-2-2m-4 0a2 2 0 00-2 2v4a2 2 0 002 2m4 0h2"></path></svg>
                    Omada
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats cards – compact --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5 sm:mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Linked accounts</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $total }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Starlink devices</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $totalStarlinks }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-violet-50 flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2m14-8V6a2 2 0 00-2-2m-4 0a2 2 0 00-2 2v4a2 2 0 002 2m4 0h2"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">Omada devices</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $totalOmadas }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 overflow-hidden col-span-2 lg:col-span-1">
            <div class="p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-green-50 flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-green-700 truncate">Omada active</p>
                        <p class="text-lg sm:text-2xl font-semibold text-green-700">{{ $omadaActiveCount }} <span class="text-xs font-normal text-gray-400">/ {{ $omadaExpiredCount }} expired</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts + Recently linked: single dense row --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 mb-5 sm:mb-6">
        <div class="lg:col-span-5 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Accounts created (last 12 weeks)</h2>
                <p class="text-xs text-gray-500 mt-0.5">New accounts per week</p>
            </div>
            <div class="p-4 sm:p-5">
                <div class="chart-container" id="wrapAccountsOverTime">
                    <canvas id="chartAccountsOverTime" aria-label="Accounts over time" role="img"></canvas>
                </div>
                @if(array_sum($weekData ?? []) === 0)
                    <div class="chart-empty" id="emptyAccountsOverTime">
                        <div>
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <p class="text-gray-500 text-sm">No new accounts in the last 12 weeks</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Devices by type</h2>
                <p class="text-xs text-gray-500 mt-0.5">Starlink vs Omada</p>
            </div>
            <div class="p-4 sm:p-5">
                <div class="chart-container" id="wrapDeviceBreakdown">
                    <canvas id="chartDeviceBreakdown" aria-label="Device breakdown" role="img"></canvas>
                </div>
                @if(array_sum($deviceBreakdownData ?? []) === 0)
                    <div class="chart-empty" id="emptyDeviceBreakdown">
                        <div>
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01"></path></svg>
                            <p class="text-gray-500 text-sm">No devices yet</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="lg:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Recently linked</h2>
                <p class="text-xs text-gray-500 mt-0.5">Latest linked accounts</p>
            </div>
            <ul class="divide-y divide-gray-200 max-h-[260px] overflow-y-auto">
                @forelse($recentLinkedAccounts as $acc)
                    <li class="px-4 sm:px-5 py-3 hover:bg-gray-50/50 transition-colors flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900 truncate">{{ $acc->name ?: '—' }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ $acc->email }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($acc->starlinks_count > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">{{ $acc->starlinks_count }} Starlink</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $acc->created_at->diffForHumans() }}</span>
                        </div>
                    </li>
                @empty
                    <li class="px-4 sm:px-5 py-10 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-700">No linked accounts yet</p>
                            <p class="text-xs text-gray-500 mt-1">Link accounts from your auth provider to get started.</p>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Second row: Omada status + Plan type charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 mb-5 sm:mb-6">
        <div class="lg:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Omada license status</h2>
                <p class="text-xs text-gray-500 mt-0.5">Active vs expired</p>
            </div>
            <div class="p-4 sm:p-5">
                <div class="chart-container" id="wrapOmadaStatus">
                    <canvas id="chartOmadaStatus" aria-label="Omada status" role="img"></canvas>
                </div>
                @if(array_sum($omadaStatusData ?? []) === 0)
                    <div class="chart-empty" id="emptyOmadaStatus">
                        <div>
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-gray-500 text-sm">No Omada licenses</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="lg:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Starlink plan type</h2>
                <p class="text-xs text-gray-500 mt-0.5">Devices by plan</p>
            </div>
            <div class="p-4 sm:p-5">
                <div class="chart-container" id="wrapPlanType">
                    <canvas id="chartPlanType" aria-label="Starlink plan type" role="img"></canvas>
                </div>
                @if(empty($planTypeData) || array_sum($planTypeData ?? []) === 0)
                    <div class="chart-empty" id="emptyPlanType">
                        <div>
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <p class="text-gray-500 text-sm">No Starlink plans yet</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="lg:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Quick actions</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Manage devices and accounts</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ url('/admin/starlinks/create') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Starlink
                    </a>
                    <a href="{{ url('/admin/omadas') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path></svg>
                        Manage Omada
                    </a>
                </div>
            </div>
            <div class="p-4 sm:p-5 flex-1 min-h-0 flex items-center">
                <p class="text-sm text-gray-500">Use the buttons above or the table below to manage linked accounts and devices.</p>
            </div>
        </div>
    </div>

    {{-- All linked accounts table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50/50 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">All linked accounts</h2>
                <p class="text-xs text-gray-500 mt-0.5">Accounts that can be linked to Starlink and Omada devices</p>
            </div>
            <p class="text-sm text-gray-500">{{ $linkedAccounts->total() }} total</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Provider</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">User</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Devices</th>
                        <th scope="col" class="px-4 sm:px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Linked</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($linkedAccounts as $account)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                <div>
                                    <p class="font-medium text-gray-900 truncate max-w-[180px] sm:max-w-none">{{ $account->name ?: '—' }}</p>
                                    <p class="text-sm text-gray-600 truncate max-w-[180px] sm:max-w-none">{{ $account->email }}</p>
                                </div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap hidden sm:table-cell">
                                @if($account->provider)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $account->provider }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap text-sm text-gray-600 hidden md:table-cell">{{ $account->user ? $account->user->name : '—' }}</td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1.5">
                                    @if(($account->starlinks_count ?? 0) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ $account->starlinks_count }} Starlink</span>
                                    @endif
                                    @if(($account->omadas_count ?? 0) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">{{ $account->omadas_count }} Omada</span>
                                    @endif
                                    @if(($account->starlinks_count ?? 0) === 0 && ($account->omadas_count ?? 0) === 0)
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">{{ $account->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-5 py-14 text-center">
                                <div class="flex flex-col items-center max-w-sm mx-auto">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                    </div>
                                    <p class="text-gray-900 font-medium">No linked accounts yet</p>
                                    <p class="text-sm text-gray-500 mt-1">Linked accounts appear here when users connect via your auth provider. Add Starlink or Omada devices and link them to an account email.</p>
                                    <a href="{{ url('/admin/starlinks/create') }}" class="mt-4 inline-flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01"></path></svg>
                                        Add Starlink device
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($linkedAccounts->hasPages())
            <div class="px-4 sm:px-5 py-3 border-t border-gray-200 bg-gray-50/50">
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

    // Bar chart: always render (with zeros if needed)
    var wrapAccounts = document.getElementById('wrapAccountsOverTime');
    if (wrapAccounts) {
        var hasAccountData = weekData.length && weekData.some(function(v) { return v > 0; });
        if (hasAccountData) {
            var emptyEl = document.getElementById('emptyAccountsOverTime');
            if (emptyEl) emptyEl.style.display = 'none';
            new Chart(document.getElementById('chartAccountsOverTime'), {
                type: 'bar',
                data: { labels: weekLabels, datasets: [{ label: 'Accounts', data: weekData, backgroundColor: 'rgba(99, 102, 241, 0.6)', borderColor: 'rgb(99, 102, 241)', borderWidth: 1 }] },
                options: defaultBarOptions
            });
        } else {
            document.getElementById('chartAccountsOverTime').style.display = 'none';
        }
    }

    // Device breakdown: render only if at least one value > 0; otherwise empty state shows
    var wrapDevice = document.getElementById('wrapDeviceBreakdown');
    if (wrapDevice && deviceLabels.length) {
        var hasDeviceData = deviceData.length && deviceData.some(function(v) { return v > 0; });
        if (hasDeviceData) {
            var emptyDevice = document.getElementById('emptyDeviceBreakdown');
            if (emptyDevice) emptyDevice.style.display = 'none';
            new Chart(document.getElementById('chartDeviceBreakdown'), {
                type: 'doughnut',
                data: { labels: deviceLabels, datasets: [{ data: deviceData, backgroundColor: ['rgb(99, 102, 241)', 'rgb(139, 92, 246)'], borderWidth: 2 }] },
                options: defaultDoughnutOptions
            });
        } else {
            document.getElementById('chartDeviceBreakdown').style.display = 'none';
        }
    }

    // Omada status: same
    var wrapOmada = document.getElementById('wrapOmadaStatus');
    if (wrapOmada && omadaLabels.length) {
        var hasOmadaData = omadaData.length && omadaData.some(function(v) { return v > 0; });
        if (hasOmadaData) {
            var emptyOmada = document.getElementById('emptyOmadaStatus');
            if (emptyOmada) emptyOmada.style.display = 'none';
            new Chart(document.getElementById('chartOmadaStatus'), {
                type: 'doughnut',
                data: { labels: omadaLabels, datasets: [{ data: omadaData, backgroundColor: ['rgb(34, 197, 94)', 'rgb(239, 68, 68)'], borderWidth: 2 }] },
                options: defaultDoughnutOptions
            });
        } else {
            document.getElementById('chartOmadaStatus').style.display = 'none';
        }
    }

    // Plan type (Starlink): doughnut
    var wrapPlan = document.getElementById('wrapPlanType');
    if (wrapPlan && planTypeLabels.length) {
        var hasPlanData = planTypeData.length && planTypeData.some(function(v) { return v > 0; });
        if (hasPlanData) {
            var emptyPlan = document.getElementById('emptyPlanType');
            if (emptyPlan) emptyPlan.style.display = 'none';
            var planColors = ['rgb(99, 102, 241)', 'rgb(139, 92, 246)', 'rgb(34, 197, 94)', 'rgb(234, 179, 8)', 'rgb(239, 68, 68)', 'rgb(14, 165, 233)', 'rgb(168, 85, 247)', 'rgb(107, 114, 128)'];
            var bg = planTypeLabels.map(function(_, i) { return planColors[i % planColors.length]; });
            new Chart(document.getElementById('chartPlanType'), {
                type: 'doughnut',
                data: { labels: planTypeLabels, datasets: [{ data: planTypeData, backgroundColor: bg, borderWidth: 2 }] },
                options: defaultDoughnutOptions
            });
        } else {
            document.getElementById('chartPlanType').style.display = 'none';
        }
    }
});
</script>
@endpush
@endsection
