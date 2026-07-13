@extends('layouts.admin')

@section('title', 'View Starlink')
@section('page-title', 'View Starlink')

@section('content')
<div class="px-3 sm:px-4 lg:px-6 py-4 max-w-4xl mx-auto w-full min-w-0">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{ url('/admin/starlinks') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors min-h-[44px] touch-manipulation">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Starlinks
        </a>
        <a href="{{ url('/admin/starlinks/'.$starlink->id.'/edit') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors w-full sm:w-auto min-h-[44px] touch-manipulation">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Edit device
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 sm:px-6 py-5 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-900">Starlink device details</h2>
            <p class="text-sm text-gray-500 mt-0.5">Full details for this device (read-only).</p>
        </div>
        <div class="p-4 sm:p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Account / Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->account_linked_email ?: ($starlink->linkedAccount?->email ?? '—') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Starlink ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $starlink->starlink_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Serial number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->serial_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Kit number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->kit_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Router ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->router_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">SSID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->ssid ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">WiFi password</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $starlink->wifi_password ? '••••••••' : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Office / Location</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->office_location ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->municipality ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Start date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->start_date ? $starlink->start_date->format('M j, Y') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">PO No.</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->po_no ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Contact email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->contact_email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->plan ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</dt>
                    <dd class="mt-1">
                        @if($starlink->status)
                            @php
                                $statusLower = strtolower($starlink->status);
                                $statusClass = $statusLower === 'active' ? 'bg-emerald-100 text-emerald-800' : ($statusLower === 'inactive' ? 'bg-gray-100 text-gray-700' : 'bg-amber-100 text-amber-800');
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ $starlink->status }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">End user email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->end_user_email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Replaced / retired</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->replaced_at ? $starlink->replaced_at->format('M j, Y') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Replacement note</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $starlink->replacement_note ?? '—' }}</dd>
                </div>
                @if(($linkedDevices ?? collect())->isNotEmpty())
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Linked replacement devices</dt>
                        <dd class="mt-2 flex flex-wrap gap-2">
                            @foreach($linkedDevices as $linked)
                                <a href="{{ url('/admin/starlinks/'.$linked->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium text-sky-800 bg-sky-50 hover:bg-sky-100 border border-sky-200 transition-colors">
                                    {{ $linked->starlink_id ?: ($linked->serial_number ?: '#'.$linked->id) }}
                                    @if($linked->status)
                                        <span class="ml-1.5 text-sky-600">({{ $linked->status }})</span>
                                    @endif
                                </a>
                            @endforeach
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Created</dt>
                    <dd class="mt-1 text-sm text-gray-500">{{ $starlink->created_at ? $starlink->created_at->format('M j, Y g:i A') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</dt>
                    <dd class="mt-1 text-sm text-gray-500">{{ $starlink->updated_at ? $starlink->updated_at->format('M j, Y g:i A') : '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50/50 flex flex-col-reverse sm:flex-row flex-wrap justify-end gap-2">
            <a href="{{ url('/admin/starlinks') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 w-full sm:w-auto min-h-[44px] touch-manipulation">Back to list</a>
            <a href="{{ url('/admin/starlinks/'.$starlink->id.'/edit') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 w-full sm:w-auto min-h-[44px] touch-manipulation">Edit device</a>
        </div>
    </div>
</div>
@endsection
