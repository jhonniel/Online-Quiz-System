@extends('layouts.admin')

@section('title', 'Starlinks')
@section('page-title', 'Starlinks')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 w-full" x-data="starlinkViewModal">
    {{-- Page header --}}
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Starlink Devices</h1>
                <p class="mt-1 text-sm text-gray-500">Manage Starlink devices, account links, and device details.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('/admin/linked-accounts') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Starlinks Accounts
                </a>
                <a href="{{ url('/admin/omadas') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2m14-8V6a2 2 0 00-2-2m-4 0a2 2 0 00-2 2v4a2 2 0 002 2m4 0h2"></path></svg>
                    Omada
                </a>
                <a href="{{ url('/admin/starlinks/create') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Starlink
                </a>
                <a href="{{ url('/admin/starlinks/import') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import CSV
                </a>
                <a href="{{ url('/admin/starlinks/import/template') }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors" download>
                    <svg class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Sample template
                </a>
                <a href="{{ url('/admin/starlinks/export/csv') . '?' . http_build_query(request()->only(['search', 'status_filter', 'account_email_filter', 'client_name_filter'])) }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-emerald-200 rounded-lg text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m0 0l-3-3m3 3l3-3M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1"></path></svg>
                    Export CSV
                </a>
                <a href="{{ url('/admin/starlinks/export/pdf') . '?' . http_build_query(request()->only(['search', 'status_filter', 'account_email_filter', 'client_name_filter'])) }}" class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2.5 border border-red-200 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-8 8h14a2 2 0 002-2V8l-6-6H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Export PDF
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">All devices</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $starlinks->total() }} device{{ $starlinks->total() !== 1 ? 's' : '' }}{!! (!empty($search) || !empty($statusFilter) || !empty($accountEmailFilter) || !empty($clientNameFilter)) ? ' <span class="text-gray-600">(filtered results)</span>' : '' !!}</p>
                </div>
                <form method="GET" action="{{ url('/admin/starlinks') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[320px_150px_220px_220px_auto_auto] gap-2 w-full lg:w-auto lg:ml-auto">
                    <div class="relative flex-1 sm:flex-initial">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" name="search" value="{{ old('search', $search ?? '') }}" placeholder="Search any field: account, ID, serial, location, plan, status…" class="block w-full h-10 pl-10 pr-3 rounded-lg border border-gray-300 text-sm placeholder-gray-400 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                    <select name="status_filter" class="block w-full h-10 px-3 rounded-lg border border-gray-300 text-sm text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Statuses</option>
                        <option value="Active" {{ (($statusFilter ?? '') === 'Active') ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ (($statusFilter ?? '') === 'Inactive') ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <select name="account_email_filter" class="block w-full h-10 px-3 rounded-lg border border-gray-300 text-sm text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Account / Email</option>
                        @foreach(($accountEmailOptions ?? []) as $emailOption)
                            <option value="{{ $emailOption }}" {{ (($accountEmailFilter ?? '') === $emailOption) ? 'selected' : '' }}>{{ $emailOption }}</option>
                        @endforeach
                    </select>
                    <select name="client_name_filter" class="block w-full h-10 px-3 rounded-lg border border-gray-300 text-sm text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Client Names</option>
                        @foreach(($clientNameOptions ?? []) as $clientNameOption)
                            <option value="{{ $clientNameOption }}" {{ (($clientNameFilter ?? '') === $clientNameOption) ? 'selected' : '' }}>{{ $clientNameOption }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors whitespace-nowrap">Search</button>
                    @if(!empty($search) || !empty($statusFilter) || !empty($accountEmailFilter) || !empty($clientNameFilter))
                        <a href="{{ url('/admin/starlinks') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors whitespace-nowrap">Clear</a>
                    @endif
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account / Email</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Starlink ID</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Kit No.</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Office / Location</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Client Name</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Start Date</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Plan</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($starlinks as $starlink)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 sm:px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 truncate max-w-[200px] sm:max-w-xs">{{ $starlink->account_linked_email ?: ($starlink->linkedAccount->email ?? '—') }}</p>
                                    @if($starlink->linkedAccount && $starlink->linkedAccount->name)
                                        <p class="text-xs text-gray-500 truncate max-w-[200px] sm:max-w-xs">{{ $starlink->linkedAccount->name }}</p>
                                    @endif
                                    <div class="mt-1 text-xs text-gray-500 sm:hidden">
                                        <span class="font-medium text-gray-600">Starlink ID:</span>
                                        <span class="font-mono">{{ $starlink->starlink_id ?: '—' }}</span>
                                        <span class="mx-1">|</span>
                                        <span class="font-medium text-gray-600">Kit No.:</span>
                                        <span class="font-mono">{{ $starlink->kit_number ?: '—' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm text-gray-600 font-mono">{{ $starlink->starlink_id ?: '—' }}</span>
                                    @php $overdueCount = $overdueCounts[$starlink->id] ?? 0; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $overdueCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500' }}" title="{{ $overdueCount > 0 ? $overdueCount . ' billing cycle(s) overdue (unpaid)' : 'No overdue cycles' }}">{{ $overdueCount }}</span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600 font-mono hidden md:table-cell">
                                {{ $starlink->kit_number ?: '—' }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600 hidden md:table-cell max-w-[160px] truncate" title="{{ $starlink->office_location ?? '' }}">
                                {{ $starlink->office_location ? Str::limit($starlink->office_location, 22) : '—' }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600 hidden md:table-cell max-w-[140px] truncate" title="{{ $starlink->municipality ?? '' }}">
                                {{ $starlink->municipality ? Str::limit($starlink->municipality, 18) : '—' }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600 hidden md:table-cell whitespace-nowrap">
                                {{ $starlink->start_date ? \Illuminate\Support\Carbon::parse($starlink->start_date)->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                @if($starlink->plan)
                                    <span class="text-sm text-gray-700">{{ $starlink->plan }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                @if($starlink->status)
                                    @php
                                        $statusLower = strtolower($starlink->status);
                                        $statusClass = $statusLower === 'active' ? 'bg-emerald-100 text-emerald-800' : ($statusLower === 'inactive' ? 'bg-gray-100 text-gray-700' : 'bg-amber-100 text-amber-800');
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ $starlink->status }}</span>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1 sm:gap-2">
                                    <button type="button" @click.prevent="openView({{ $starlink->id }})" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors" title="View">
                                        <svg class="w-4 h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    <a href="{{ url('/admin/starlinks/'.$starlink->id.'/edit') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors" title="Edit">
                                        <svg class="w-4 h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ url('/admin/starlinks/'.$starlink->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove this Starlink device?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors" title="Remove">
                                            <svg class="w-4 h-4 sm:mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 sm:px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                                    </div>
                                    <p class="text-gray-900 font-medium">No Starlink devices yet</p>
                                    <p class="text-sm text-gray-500 mt-1 max-w-sm">Add a device to link it to an account and store Starlink details.</p>
                                    <a href="{{ url('/admin/starlinks/create') }}" class="mt-4 inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Add Starlink
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($starlinks->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                {{ $starlinks->links() }}
            </div>
        @endif
    </div>

    {{-- View details modal (must be inside x-data scope) --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-modal="true" role="dialog">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="open = false" aria-hidden="true"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <div class="px-8 py-5 border-b border-gray-200 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900">Starlink device details</h3>
                        <button type="button" @click="open = false" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="p-8 overflow-y-auto flex-1">
                    <div x-show="loading" class="flex items-center justify-center py-16">
                        <svg class="animate-spin h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    <p x-show="errorMessage" x-text="errorMessage" class="text-base text-red-600 py-4"></p>
                    <dl x-show="!loading && !errorMessage && device" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Account / Email</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.account_display || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Starlink ID</dt><dd class="mt-1.5 text-base text-gray-900 font-mono flex items-center gap-2"><span x-text="device.starlink_id || '—'"></span><span x-show="device.overdue_billing_count > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800" x-text="'×' + (device.overdue_billing_count || 0) + ' overdue'"></span></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Serial number</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.serial_number || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Kit number</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.kit_number || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Router ID</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.router_id || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">SSID</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.ssid || '—'"></dd></div>
                        <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">WiFi password</dt><dd class="mt-1.5 text-base text-gray-900 font-mono" x-text="device.wifi_password ? '••••••••' : '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Office / location</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.office_location || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Client Name</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.municipality || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Start date</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.start_date_formatted || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">PO No.</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.po_no || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Contact email</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.contact_email || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Plan</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.plan || '—'"></dd></div>
                        <div><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Status</dt><dd class="mt-1.5"><span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" :class="(device.status || '').toLowerCase() === 'active' ? 'bg-emerald-100 text-emerald-800' : (device.status || '').toLowerCase() === 'inactive' ? 'bg-gray-100 text-gray-700' : 'bg-amber-100 text-amber-800'" x-text="device.status || '—'"></span></dd></div>
                        <div class="sm:col-span-2"><dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">End user email</dt><dd class="mt-1.5 text-base text-gray-900" x-text="device.end_user_email || '—'"></dd></div>
                    </dl>
                </div>
                <div class="px-8 py-5 border-t border-gray-200 bg-gray-50/50 flex-shrink-0 flex justify-end gap-3">
                    <template x-if="device">
                        <a :href="'/admin/starlinks/' + device.id + '/edit'" class="inline-flex items-center px-5 py-2.5 rounded-lg text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700">Edit device</a>
                    </template>
                    <button type="button" @click="open = false" class="inline-flex items-center px-5 py-2.5 rounded-lg text-base font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('alpine:init', function() {
    Alpine.data('starlinkViewModal', function() {
        return {
            open: false,
            loading: false,
            device: null,
            errorMessage: null,
            openView(id) {
                this.open = true;
                this.loading = true;
                this.device = null;
                this.errorMessage = null;
                var self = this;
                fetch('{{ url("/admin/starlinks") }}/' + id, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                    .then(function(r) {
                        if (!r.ok) throw new Error('Request failed');
                        return r.json();
                    })
                    .then(function(d) {
                        self.device = d;
                        self.loading = false;
                    })
                    .catch(function() {
                        self.loading = false;
                        self.errorMessage = 'Could not load device details.';
                    });
            }
        };
    });
});
</script>
@endsection
@endsection
