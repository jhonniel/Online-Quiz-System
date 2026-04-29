@extends('layouts.user')

@section('content')
<div class="p-4 sm:p-6 space-y-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">My Assigned Tickets</h1>
        <p class="text-sm text-gray-600">Update status and notes for tickets assigned to you.</p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ticket #</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reporter</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $ticket->ticket_number }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $ticket->type_label }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $ticket->full_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <form method="POST" action="{{ url('/technician/tickets/' . $ticket->id) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="w-full rounded-md border-gray-300 text-sm">
                                        <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="processing" {{ $ticket->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="needs_investigation" {{ $ticket->status === 'needs_investigation' ? 'selected' : '' }}>Needs Investigation</option>
                                        <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    </select>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                    <textarea name="admin_notes" rows="2" class="w-full rounded-md border-gray-300 text-sm" placeholder="Add update note...">{{ old('admin_notes', $ticket->admin_notes) }}</textarea>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-md bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No tickets assigned to you yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($tickets, 'links'))
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

