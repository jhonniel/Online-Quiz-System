@extends('layouts.user')

@section('content')
<div class="h-full flex flex-col min-h-0">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 shadow-sm p-4 flex-shrink-0">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L6 20.75M14.25 7l3.75-3.75M7 7h.01M17 17h.01M7 17h.01M17 7h.01M12 12l0 0"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-white">Technician Dashboard</h1>
                <p class="text-indigo-100 text-sm">Welcome back, {{ auth()->user()->name }}. Here are your assigned reported tickets.</p>
            </div>
        </div>
    </div>

    <div class="p-4 sm:p-6 overflow-y-auto space-y-6">
        <div class="flex items-center justify-end">
            <a href="{{ url('/technician/tickets') }}"
               class="inline-flex items-center px-3 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                Manage Assigned Tickets
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <p class="text-sm text-gray-500">Total Assigned</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $assignedTotal }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <p class="text-sm text-gray-500">Open / Active</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $assignedOpen }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4 border border-gray-100">
                <p class="text-sm text-gray-500">Resolved / Closed</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ $assignedResolved }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Recent Assigned Tickets</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ticket #</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reporter</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Updated</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($recentAssignedTickets as $ticket)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $ticket->ticket_number }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $ticket->type_label }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $ticket->full_name }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ in_array($ticket->status, ['resolved', 'closed']) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ $ticket->updated_at?->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-sm text-gray-500 text-center">No assigned tickets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
