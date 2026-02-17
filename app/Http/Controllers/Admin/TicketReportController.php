<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TicketClosedNotification;
use App\Models\TicketReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketReportController extends Controller
{
    public function dashboard()
    {
        $total = TicketReport::count();
        $open = TicketReport::where('status', TicketReport::STATUS_OPEN)->count();
        $closed = TicketReport::where('status', TicketReport::STATUS_CLOSED)->count();
        $recent = TicketReport::orderByDesc('created_at')->take(10)->get();

        return view('admin.tickets.dashboard', compact('total', 'open', 'closed', 'recent'));
    }

    public function open()
    {
        $tickets = TicketReport::where('status', TicketReport::STATUS_OPEN)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.tickets.index', ['tickets' => $tickets, 'filter' => 'open']);
    }

    public function closed()
    {
        $tickets = TicketReport::where('status', TicketReport::STATUS_CLOSED)
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.tickets.index', ['tickets' => $tickets, 'filter' => 'closed']);
    }

    public function show(TicketReport $ticket_report)
    {
        return view('admin.tickets.show', ['ticket' => $ticket_report]);
    }

    public function update(Request $request, TicketReport $ticket_report)
    {
        $request->validate([
            'status' => 'sometimes|in:open,closed',
            'admin_notes' => 'nullable|string|max:10000',
        ]);

        $data = [];
        $statusChanged = false;
        if ($request->has('status') && $request->status !== $ticket_report->status) {
            $data['status'] = $request->status;
            $statusChanged = true;
        }
        if (array_key_exists('admin_notes', $request->all())) {
            $data['admin_notes'] = $request->admin_notes;
        }
        if (! empty($data)) {
            $ticket_report->update($data);
        }

        $message = 'Ticket updated.';
        if ($statusChanged) {
            if ($data['status'] === TicketReport::STATUS_CLOSED) {
                $message = 'Ticket marked as closed.';
                try {
                    Mail::to($ticket_report->email)->send(new TicketClosedNotification($ticket_report->fresh()));
                    Log::info('Ticket closed notification sent', ['ticket' => $ticket_report->ticket_number, 'to' => $ticket_report->email]);
                } catch (\Throwable $e) {
                    Log::warning('Ticket closed notification failed: ' . $e->getMessage(), [
                        'ticket' => $ticket_report->ticket_number,
                        'to' => $ticket_report->email,
                        'exception' => $e,
                    ]);
                    report($e);
                    $message .= ' The notification email could not be sent. Check Admin → Settings → Mail and use "Send Test Email" to verify.';
                }
            } else {
                $message = 'Ticket reopened.';
            }
        } elseif (array_key_exists('admin_notes', $request->all())) {
            $message = 'Notes saved.';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }
}
