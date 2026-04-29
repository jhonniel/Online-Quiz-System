@extends('layouts.user')

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-[0.12em] text-indigo-600 uppercase">Technician Workspace</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">Assigned Tickets</h1>
                <p class="mt-1 text-sm text-gray-600">Review your assigned reports and keep statuses updated.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:w-auto">
                <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 min-w-[130px]">
                    <p class="text-xs text-indigo-700 font-medium uppercase tracking-wide">Total</p>
                    <p class="mt-1 text-xl font-bold text-indigo-900">{{ method_exists($tickets, 'total') ? $tickets->total() : $tickets->count() }}</p>
                </div>
                <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 min-w-[130px]">
                    <p class="text-xs text-green-700 font-medium uppercase tracking-wide">Showing</p>
                    <p class="mt-1 text-xl font-bold text-green-900">{{ $tickets->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
            <p class="font-semibold mb-1">Please fix the following:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-900">Ticket Queue</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update status and notes directly from this list.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ticket #</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reporter</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-4 align-top">
                                <span class="inline-flex items-center rounded-lg bg-indigo-50 text-indigo-700 px-2.5 py-1 text-xs font-semibold">
                                    {{ $ticket->ticket_number }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 align-top">{{ $ticket->type_label }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 align-top">{{ $ticket->full_name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 align-top">
                                @php
                                    $statusClasses = match($ticket->status) {
                                        'open' => 'bg-amber-100 text-amber-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'needs_investigation' => 'bg-purple-100 text-purple-800',
                                        'resolved' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ strtoupper(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                                <form method="POST" action="{{ url('/technician/tickets/' . $ticket->id) }}" class="space-y-2 mt-3">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="processing" {{ $ticket->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="needs_investigation" {{ $ticket->status === 'needs_investigation' ? 'selected' : '' }}>Needs Investigation</option>
                                        <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    </select>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 align-top">
                                    <textarea name="admin_notes" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Add update note...">{{ old('admin_notes', $ticket->admin_notes) }}</textarea>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 align-top">
                                    <button type="submit" class="inline-flex items-center justify-center px-3.5 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shadow-sm transition-colors">
                                        Save Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-gray-700">No tickets assigned yet.</p>
                                <p class="text-xs text-gray-500 mt-1">Once an admin assigns tickets, they will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($tickets, 'links'))
            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/60">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

