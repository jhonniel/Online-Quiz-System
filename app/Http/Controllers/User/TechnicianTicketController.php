<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TicketReport;
use App\Models\TicketReportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianTicketController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'technician') {
            abort(403, 'Access denied.');
        }

        $tickets = TicketReport::where('assigned_to_user_id', $user->id)
            ->latest('updated_at')
            ->paginate(20);

        return view('user.technician-tickets.index', compact('tickets'));
    }

    public function update(Request $request, TicketReport $ticket)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'technician') {
            abort(403, 'Access denied.');
        }
        if ((int) $ticket->assigned_to_user_id !== (int) $user->id) {
            abort(403, 'You can only update tickets assigned to you.');
        }

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [
                TicketReport::STATUS_OPEN,
                TicketReport::STATUS_PROCESSING,
                TicketReport::STATUS_NEEDS_INVESTIGATION,
                TicketReport::STATUS_RESOLVED,
            ]),
            'admin_notes' => 'nullable|string|max:10000',
        ]);

        $fromStatus = $ticket->status;
        $statusChanged = $fromStatus !== $validated['status'];

        $ticket->status = $validated['status'];
        if (array_key_exists('admin_notes', $validated) && $validated['admin_notes'] !== null) {
            $ticket->admin_notes = $validated['admin_notes'];
        }
        $ticket->save();

        TicketReportLog::create([
            'ticket_report_id' => $ticket->id,
            'user_id' => (int) $user->id,
            'action' => $statusChanged ? 'technician_status_updated' : 'technician_notes_updated',
            'from_status' => $fromStatus,
            'to_status' => $ticket->status,
            'meta' => null,
        ]);

        return redirect('/technician/tickets')->with('success', 'Ticket updated successfully.');
    }
}

