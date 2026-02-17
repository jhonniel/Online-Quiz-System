<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatTicket;
use App\Models\User;
use App\Models\PreloadedMessage;
use App\Models\TypingIndicator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LiveChatController extends Controller
{
    public function index()
    {
        // Get all chat tickets with their users and latest messages
        $tickets = ChatTicket::with(['user', 'messages' => function($query) {
                $query->latest()->take(1);
            }])
            ->withCount(['messages as unread_count' => function($query) {
                $query->where('sender_type', 'user')->where('is_read', false);
            }])
            ->orderBy('unread_count', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $stats = [
            'total' => ChatTicket::count(),
            'open' => ChatTicket::open()->count(),
            'closed' => ChatTicket::closed()->count(),
            'reopened' => ChatTicket::reopened()->count(),
        ];

        return view('admin.live-chat.index', compact('tickets', 'stats'));
    }

    public function show($ticketNumber)
    {
        $ticket = ChatTicket::where('ticket_number', $ticketNumber)
            ->with(['user', 'messages.user', 'messages.admin'])
            ->first();

        if (!$ticket) {
            abort(404, 'Ticket not found');
        }

        $messages = $ticket->messages()->orderBy('created_at', 'asc')->get();

        // Get pre-loaded messages
        $preloadedMessages = PreloadedMessage::active()
            ->ordered()
            ->get()
            ->groupBy('category');

        // Mark all user messages as read
        ChatMessage::where('ticket_number', $ticketNumber)
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return view('admin.live-chat.show', compact('ticket', 'messages', 'preloadedMessages'));
    }

    public function store(Request $request, $ticketNumber): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $ticket = ChatTicket::where('ticket_number', $ticketNumber)->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        if ($ticket->isClosed()) {
            return response()->json(['error' => 'Cannot send message to closed ticket'], 403);
        }

        $chatMessage = ChatMessage::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $ticket->user_id,
            'admin_id' => auth()->id(),
            'message' => $request->message,
            'sender_type' => 'admin',
            'status' => 'open',
            'is_read' => false,
        ]);

        $chatMessage->load('admin');

        return response()->json([
            'success' => true,
            'message' => $chatMessage,
        ]);
    }

    public function getMessages($ticketNumber): JsonResponse
    {
        $messages = ChatMessage::with(['user', 'admin'])
            ->where('ticket_number', $ticketNumber)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function getUnreadCount(): JsonResponse
    {
        $count = ChatMessage::where('sender_type', 'user')
            ->where('is_read', false)
            ->where('status', 'open')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function closeTicket(Request $request, $ticketNumber): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $ticket = ChatTicket::where('ticket_number', $ticketNumber)->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        $ticket->close(auth()->user(), $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Ticket closed successfully',
        ]);
    }

    public function reopenTicket(Request $request, $ticketNumber)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $ticket = ChatTicket::where('ticket_number', $ticketNumber)->first();

        if (!$ticket) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Ticket not found'], 404);
            }
            return redirect()->back()->with('error', 'Ticket not found');
        }

        if ($ticket->isOpen()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Ticket is already open'], 400);
            }
            return redirect()->back()->with('error', 'Ticket is already open');
        }

        // If ticket has a reopen request, approve it
        if ($ticket->isReopenRequested()) {
            $ticket->approveReopen(auth()->user(), $request->reason);
        } else if ($ticket->isClosed()) {
            // If ticket is closed without a request, reopen it directly
            $ticket->reopen(auth()->user(), $request->reason);
        } else if ($ticket->isReopened()) {
            // If already reopened, just update the reason
            $ticket->update([
                'reopen_reason' => $request->reason,
                'reopened_at' => now(),
                'reopened_by' => auth()->id(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket reopened successfully',
            ]);
        }

        return redirect()->back()->with('success', 'Ticket reopened successfully');
    }

    public function denyReopen(Request $request, $ticketNumber)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $ticket = ChatTicket::where('ticket_number', $ticketNumber)->first();

        if (!$ticket) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Ticket not found'], 404);
            }
            return redirect()->back()->with('error', 'Ticket not found');
        }

        if (!$ticket->isReopenRequested()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No reopen request found'], 400);
            }
            return redirect()->back()->with('error', 'No reopen request found');
        }

        // Deny the reopen request by updating the ticket status
        $ticket->update([
            'status' => 'closed',
            'reopen_request_reason' => null,
            'reopen_requested_at' => null,
            'denied_reason' => $request->reason,
            'denied_at' => now(),
            'denied_by' => auth()->id(),
        ]);

        // Add a system message about the denial
        ChatMessage::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $ticket->user_id,
            'admin_id' => auth()->id(),
            'message' => 'Reopen request denied' . ($request->reason ? ': ' . $request->reason : ''),
            'sender_type' => 'admin',
            'status' => 'system',
            'is_read' => false,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Reopen request denied successfully',
            ]);
        }

        return redirect()->back()->with('success', 'Reopen request denied successfully');
    }

    public function getPreloadedMessages(): JsonResponse
    {
        $messages = PreloadedMessage::active()
            ->ordered()
            ->get()
            ->groupBy('category');

        return response()->json($messages);
    }

    public function startTyping(Request $request, $ticketNumber): JsonResponse
    {
        $request->validate([
            'typer_type' => 'required|in:user,admin',
        ]);

        $typerType = $request->typer_type;
        $userId = auth()->id();
        $adminId = $typerType === 'admin' ? $userId : null;

        // Remove any existing typing indicators for this user/admin
        TypingIndicator::where('ticket_number', $ticketNumber)
            ->where('typer_type', $typerType)
            ->where($typerType === 'admin' ? 'admin_id' : 'user_id', $userId)
            ->delete();

        // Create new typing indicator
        TypingIndicator::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $typerType === 'user' ? $userId : null,
            'admin_id' => $adminId,
            'typer_type' => $typerType,
            'started_typing_at' => now(),
            'last_activity_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function stopTyping(Request $request, $ticketNumber): JsonResponse
    {
        $request->validate([
            'typer_type' => 'required|in:user,admin',
        ]);

        $typerType = $request->typer_type;
        $userId = auth()->id();

        TypingIndicator::where('ticket_number', $ticketNumber)
            ->where('typer_type', $typerType)
            ->where($typerType === 'admin' ? 'admin_id' : 'user_id', $userId)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function getTypingIndicators($ticketNumber): JsonResponse
    {
        $indicators = TypingIndicator::forTicket($ticketNumber)
            ->active()
            ->with(['user', 'admin'])
            ->get()
            ->map(function ($indicator) {
                return [
                    'typer_type' => $indicator->typer_type,
                    'name' => $indicator->typer_type === 'admin'
                        ? $indicator->admin->name
                        : $indicator->user->name,
                    'started_typing_at' => $indicator->started_typing_at,
                ];
            });

        return response()->json($indicators);
    }

    public function getNewMessages($ticketNumber, Request $request): JsonResponse
    {
        $lastMessageId = $request->get('last_message_id', 0);

        $messages = ChatMessage::with(['user', 'admin'])
            ->where('ticket_number', $ticketNumber)
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
}
