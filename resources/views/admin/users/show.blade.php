@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-700 rounded-2xl shadow-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center">
                    <span class="text-xl font-semibold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                    <p class="text-indigo-100 text-sm">{{ $user->email }}</p>
                    <div class="mt-2 flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/15 text-white border border-white/20">
                            {{ ucfirst($user->role) }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                     {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }} bg-white text-indigo-900">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ url('/admin/users') }}"
               class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/30 rounded-lg text-white hover:bg-white/20 transition duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Users
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main profile info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Overview card -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Profile Overview</h2>
                        <p class="text-sm text-gray-500">Key information about this account.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Full Name</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email Address</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 break-all">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</p>
                        <p class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                  bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ ucfirst($user->role) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Account Status</p>
                        <p class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold 
                                  {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                            <span class="h-2 w-2 mr-1 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Joined</p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Last Activity</p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $user->last_activity ? \Carbon\Carbon::parse($user->last_activity)->format('M d, Y g:i A') : 'No recent activity' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- QR Code Card -->
            <div class="bg-white rounded-2xl shadow border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">QR Code</h3>
                        <p class="text-xs text-gray-500">User's unique identification code</p>
                    </div>
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <div class="flex flex-col items-center">
                    @php
                        $user->generateQrCodeId();
                    @endphp
                    <div class="bg-gray-50 p-4 rounded-lg border-2 border-indigo-200 mb-4">
                        <div class="w-48 h-48 flex items-center justify-center">
                            {!! $user->getQrCodeSvg(200) !!}
                        </div>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-200 w-full text-center">
                        <p class="text-xs text-gray-500 mb-1">QR Code ID</p>
                        <p class="text-lg font-mono font-bold text-indigo-600">{{ $user->qr_code_id }}</p>
                    </div>
                </div>
            </div>

            <!-- Leave balances & overtime (employees only) -->
            @if(isset($balances) && $user->role === 'employee')
                <div class="bg-white rounded-2xl shadow border border-gray-200 p-6 space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Leave & Overtime Summary</h3>
                            <p class="text-xs text-gray-500">Current balances for {{ now()->year }} and overtime window.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <div class="border border-gray-100 rounded-lg px-3 py-2 bg-indigo-50/40">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Vacation Leave</p>
                            <p class="text-sm text-gray-900">
                                Remaining:
                                <span class="font-bold">{{ $balances['vacation']['remaining'] }}</span>
                                / {{ $balances['vacation']['allowance'] }} days
                            </p>
                            <p class="text-xs text-gray-500">Used: {{ $balances['vacation']['used'] }} days ({{ now()->year }})</p>
                        </div>
                        <div class="border border-gray-100 rounded-lg px-3 py-2 bg-blue-50/40">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sick Leave</p>
                            <p class="text-sm text-gray-900">
                                Remaining:
                                <span class="font-bold">{{ $balances['sick']['remaining'] }}</span>
                                / {{ $balances['sick']['allowance'] }} days
                            </p>
                            <p class="text-xs text-gray-500">Used: {{ $balances['sick']['used'] }} days ({{ now()->year }})</p>
                        </div>
                        <div class="border border-gray-100 rounded-lg px-3 py-2 {{ str_starts_with($overtimeFormatted ?? '00:00', '-') ? 'bg-red-50/40 border-red-200' : 'bg-emerald-50/40' }}">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                Overtime ({{ $overtimeWindowLabel ?? 'Current Year' }})
                            </p>
                            <p class="text-sm text-gray-900">
                                <span class="font-bold {{ str_starts_with($overtimeFormatted ?? '00:00', '-') ? 'text-red-600' : 'text-gray-900' }}">{{ $overtimeFormatted ?? '00:00' }}</span> hours
                                @if(str_starts_with($overtimeFormatted ?? '00:00', '-'))
                                    <span class="text-xs text-red-500 ml-2">(Negative Balance)</span>
                                @endif
                            </p>
                            @if(isset($totalDeficitFormatted) && $totalDeficitHours > 0)
                                <div class="mt-2 pt-2 border-t border-gray-200">
                                    <p class="text-xs text-gray-600">
                                        <span class="font-medium">Total Deficit Deducted:</span>
                                        <span class="text-red-600 font-semibold">{{ $totalDeficitFormatted }}</span>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Deficit hours from weekly totals below 40:00 have been deducted from overtime balance
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Manual leave balance adjustment -->
                    <div class="pt-4 border-t border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Adjust Leave Balances ({{ now()->year }})</h4>
                        <p class="text-xs text-gray-500 mb-3">
                            Update this employee's yearly leave allowances. Used days are based on approved leave requests.
                        </p>
                        <form action="{{ url('/admin/users/' . $user->id . '/leave-balance') }}" method="POST" class="grid grid-cols-1 gap-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="year" value="{{ now()->year }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label for="vacation_allowance" class="block text-xs font-medium text-gray-700 mb-1">
                                        Vacation Allowance (days)
                                    </label>
                                    <input type="number" min="0" max="365" step="0.5"
                                           name="vacation_allowance" id="vacation_allowance"
                                           value="{{ old('vacation_allowance', $balances['vacation']['allowance']) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label for="sick_allowance" class="block text-xs font-medium text-gray-700 mb-1">
                                        Sick Allowance (days)
                                    </label>
                                    <input type="number" min="0" max="365" step="0.5"
                                           name="sick_allowance" id="sick_allowance"
                                           value="{{ old('sick_allowance', $balances['sick']['allowance']) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Save Leave Balances
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


