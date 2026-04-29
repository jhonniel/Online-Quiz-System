<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TicketClosedNotification;
use App\Models\TicketReport;
use App\Models\TicketReportLog;
use App\Models\TicketReportNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketReportController extends Controller
{
    public function dashboard()
    {
        $total = TicketReport::count();
        $open = TicketReport::whereIn('status', [
            TicketReport::STATUS_OPEN,
            TicketReport::STATUS_PROCESSING,
            TicketReport::STATUS_NEEDS_INVESTIGATION,
        ])->count();
        $closed = TicketReport::whereIn('status', [
            TicketReport::STATUS_RESOLVED,
            TicketReport::STATUS_CLOSED,
        ])->count();
        $recent = TicketReport::orderByDesc('created_at')->take(10)->get();

        return view('admin.tickets.dashboard', compact('total', 'open', 'closed', 'recent'));
    }

    public function open()
    {
        $tickets = TicketReport::whereIn('status', [
                TicketReport::STATUS_OPEN,
                TicketReport::STATUS_PROCESSING,
                TicketReport::STATUS_NEEDS_INVESTIGATION,
            ])
            ->with(['latestLog.user', 'assignedTo'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.tickets.index', ['tickets' => $tickets, 'filter' => 'open']);
    }

    public function closed()
    {
        $tickets = TicketReport::whereIn('status', [
                TicketReport::STATUS_RESOLVED,
                TicketReport::STATUS_CLOSED,
            ])
            ->with(['latestLog.user', 'assignedTo'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.tickets.index', ['tickets' => $tickets, 'filter' => 'closed']);
    }

    public function show(TicketReport $ticket_report)
    {
        $ticket_report->load([
            'notes.user',
            'logs.user',
            'assignedTo',
        ]);

        $assignees = User::query()
            ->where('role', 'technician')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.tickets.show', [
            'ticket' => $ticket_report,
            'assignees' => $assignees,
        ]);
    }

    public function update(Request $request, TicketReport $ticket_report)
    {
        $request->validate([
            'status' => 'sometimes|in:' . implode(',', TicketReport::adminStatuses()),
            'admin_notes' => 'nullable|string|max:10000',
            'payment_status' => 'sometimes|in:' . implode(',', TicketReport::paymentStatuses()),
            'amount_paid' => 'nullable|numeric|min:0|max:999999999.99',
            'assigned_to_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'technician');
                }),
            ],
        ]);

        $data = [];
        $statusChanged = false;
        $paymentChanged = false;
        if ($request->has('status') && $request->status !== $ticket_report->status) {
            $data['status'] = $request->status;
            $statusChanged = true;
        }
        if ($request->has('payment_status') && $request->payment_status !== $ticket_report->payment_status) {
            $data['payment_status'] = $request->payment_status;
            $paymentChanged = true;
        }
        if (array_key_exists('amount_paid', $request->all())) {
            $incomingAmount = $request->filled('amount_paid') ? (float) $request->input('amount_paid') : null;
            $currentAmount = $ticket_report->amount_paid !== null ? (float) $ticket_report->amount_paid : null;
            if ($incomingAmount !== $currentAmount) {
                $data['amount_paid'] = $incomingAmount;
                $paymentChanged = true;
            }
        }
        if (array_key_exists('admin_notes', $request->all())) {
            $data['admin_notes'] = $request->admin_notes;
        }
        if (array_key_exists('assigned_to_user_id', $request->all())) {
            $data['assigned_to_user_id'] = $request->filled('assigned_to_user_id') ? (int) $request->input('assigned_to_user_id') : null;
        }
        if (! empty($data)) {
            $fromStatus = $ticket_report->status;
            $beforeAssignedTo = $ticket_report->assigned_to_user_id;
            $ticket_report->update($data);

            if ($statusChanged) {
                $toStatus = $data['status'];
                $action = match ($toStatus) {
                    TicketReport::STATUS_PROCESSING => 'processed',
                    TicketReport::STATUS_NEEDS_INVESTIGATION => 'needs_investigation',
                    TicketReport::STATUS_RESOLVED, TicketReport::STATUS_CLOSED => 'resolved',
                    TicketReport::STATUS_OPEN => 'reopened',
                    default => 'status_updated',
                };

                TicketReportLog::create([
                    'ticket_report_id' => $ticket_report->id,
                    'user_id' => (int) Auth::id(),
                    'action' => $action,
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'meta' => null,
                ]);
            } elseif ($paymentChanged) {
                TicketReportLog::create([
                    'ticket_report_id' => $ticket_report->id,
                    'user_id' => (int) Auth::id(),
                    'action' => 'payment_updated',
                    'from_status' => $ticket_report->status,
                    'to_status' => $ticket_report->status,
                    'meta' => null,
                ]);
            } elseif (array_key_exists('admin_notes', $request->all())) {
                TicketReportLog::create([
                    'ticket_report_id' => $ticket_report->id,
                    'user_id' => (int) Auth::id(),
                    'action' => 'notes_updated',
                    'from_status' => $ticket_report->status,
                    'to_status' => $ticket_report->status,
                    'meta' => null,
                ]);
            }

            if (array_key_exists('assigned_to_user_id', $data) && (int) ($beforeAssignedTo ?? 0) !== (int) ($data['assigned_to_user_id'] ?? 0)) {
                TicketReportLog::create([
                    'ticket_report_id' => $ticket_report->id,
                    'user_id' => (int) Auth::id(),
                    'action' => 'assigned',
                    'from_status' => $ticket_report->status,
                    'to_status' => $ticket_report->status,
                    'meta' => 'assigned_to:' . ($data['assigned_to_user_id'] ?? 'none'),
                ]);
            }
        }

        $message = 'Ticket updated.';
        if ($statusChanged) {
            if (in_array($data['status'], [TicketReport::STATUS_RESOLVED, TicketReport::STATUS_CLOSED], true)) {
                $message = 'Ticket marked as resolved.';
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
            } elseif ($data['status'] === TicketReport::STATUS_NEEDS_INVESTIGATION) {
                $message = 'Ticket marked as needs further investigation.';
            } elseif ($data['status'] === TicketReport::STATUS_PROCESSING) {
                $message = 'Ticket marked as processing.';
            } else {
                $message = 'Ticket reopened.';
            }
        } elseif (array_key_exists('admin_notes', $request->all())) {
            $message = 'Notes saved.';
        } elseif ($paymentChanged) {
            $message = 'Payment details updated.';
        } elseif (array_key_exists('assigned_to_user_id', $request->all())) {
            $message = 'Assignee updated.';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function storeNote(Request $request, TicketReport $ticket_report)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $note = TicketReportNote::create([
            'ticket_report_id' => $ticket_report->id,
            'user_id' => (int) Auth::id(),
            'body' => $validated['body'],
        ]);

        TicketReportLog::create([
            'ticket_report_id' => $ticket_report->id,
            'user_id' => (int) Auth::id(),
            'action' => 'note_posted',
            'from_status' => $ticket_report->status,
            'to_status' => $ticket_report->status,
            'meta' => 'note_id:' . $note->id,
        ]);

        return redirect()->back()->with('success', 'Comment posted.');
    }

    public function destroyNote(Request $request, TicketReport $ticket_report, TicketReportNote $note)
    {
        if ($note->ticket_report_id !== $ticket_report->id) {
            abort(404);
        }
        if ((int) $note->user_id !== (int) Auth::id()) {
            abort(403, 'You can only delete your own comment.');
        }

        $noteId = $note->id;
        $note->delete();

        TicketReportLog::create([
            'ticket_report_id' => $ticket_report->id,
            'user_id' => (int) Auth::id(),
            'action' => 'note_deleted',
            'from_status' => $ticket_report->status,
            'to_status' => $ticket_report->status,
            'meta' => 'note_id:' . $noteId,
        ]);

        return redirect()->back()->with('success', 'Comment deleted.');
    }
}
