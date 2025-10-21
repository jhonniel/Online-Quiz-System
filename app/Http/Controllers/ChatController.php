<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatTicket;
use App\Models\TypingIndicator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $ticketNumber = $request->get('ticket_number');

        if ($ticketNumber) {
            // Get messages for specific ticket
            $messages = ChatMessage::with(['user', 'admin', 'ticket'])
                ->where('user_id', auth()->id())
                ->where('ticket_number', $ticketNumber)
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            // Get all messages for user
            $messages = ChatMessage::with(['user', 'admin', 'ticket'])
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'ticket_number' => 'nullable|string',
            'subject' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $ticketNumber = $request->ticket_number;

        // If no ticket number provided, create a new ticket
        if (!$ticketNumber) {
            $ticket = ChatTicket::create([
                'user_id' => $user->id,
                'subject' => $request->subject ?? 'Support Request',
                'description' => $request->message,
                'status' => 'open',
            ]);
            $ticketNumber = $ticket->ticket_number;
        } else {
            // Check if ticket exists and is open
            $ticket = ChatTicket::where('ticket_number', $ticketNumber)
                ->where('user_id', $user->id)
                ->first();

            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found or access denied.',
                ], 404);
            }

            if ($ticket->isClosed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This ticket is closed. Please request to reopen it.',
                ], 403);
            }
        }

        $chatMessage = ChatMessage::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $user->id,
            'message' => $request->message,
            'sender_type' => 'user',
            'status' => 'open',
            'is_read' => false,
        ]);

        $chatMessage->load(['user', 'ticket']);

        return response()->json([
            'success' => true,
            'message' => $chatMessage,
            'ticket_number' => $ticketNumber,
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'integer|exists:chat_messages,id',
        ]);

        ChatMessage::whereIn('id', $request->message_ids)
            ->where('user_id', auth()->id())
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCount(): JsonResponse
    {
        $count = ChatMessage::where('user_id', auth()->id())
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->where('status', 'open')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getTickets()
    {
        $tickets = ChatTicket::where('user_id', auth()->id())
            ->withCount(['messages as unread_count' => function($query) {
                $query->where('sender_type', 'admin')
                      ->where('is_read', false);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($tickets);
    }

    public function createTicket(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'nullable|string|max:255',
        ]);

        $ticket = ChatTicket::create([
            'ticket_number' => ChatTicket::generateTicketNumber(),
            'user_id' => auth()->id(),
            'subject' => $request->subject ?: 'Support Request',
            'status' => 'open',
            'priority' => 'medium'
        ]);

        // Create initial message
        ChatMessage::create([
            'ticket_number' => $ticket->ticket_number,
            'user_id' => auth()->id(),
            'message' => 'Ticket created',
            'sender_type' => 'user',
            'status' => 'open',
            'is_read' => true,
        ]);

        return response()->json([
            'success' => true,
            'ticket_number' => $ticket->ticket_number,
            'message' => 'Ticket created successfully',
        ]);
    }

    public function getTicket($ticketNumber)
    {
        $ticket = ChatTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', auth()->id())
            ->with(['messages.user', 'messages.admin'])
            ->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        return response()->json($ticket);
    }

    public function requestReopen(Request $request, $ticketNumber): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $ticket = ChatTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', auth()->id())
            ->first();

        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        if (!$ticket->isClosed()) {
            return response()->json(['error' => 'Ticket is not closed'], 400);
        }

        // Update ticket status to reopen_requested
        $ticket->requestReopen(auth()->user(), $request->reason);

        // Create a reopen request message
        $message = ChatMessage::create([
            'ticket_number' => $ticketNumber,
            'user_id' => auth()->id(),
            'message' => "REOPEN REQUEST: " . $request->reason,
            'sender_type' => 'user',
            'status' => 'open',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reopen request submitted successfully. Admin will be notified.',
        ]);
    }

    public function startTyping(Request $request, $ticketNumber): JsonResponse
    {
        $request->validate([
            'typer_type' => 'required|in:user,admin',
        ]);

        $typerType = $request->typer_type;
        $userId = auth()->id();

        // Remove any existing typing indicators for this user
        TypingIndicator::where('ticket_number', $ticketNumber)
            ->where('typer_type', $typerType)
            ->where('user_id', $userId)
            ->delete();

        // Create new typing indicator
        TypingIndicator::create([
            'ticket_number' => $ticketNumber,
            'user_id' => $userId,
            'admin_id' => null,
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
            ->where('user_id', $userId)
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
