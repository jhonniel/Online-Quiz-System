<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserChatMessage;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserChatController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Get friends from both directions (where user is user_id or friend_id)
        $friendsAsUser = $user->friends()->get();
        $friendsAsFriend = $user->acceptedFriends()->get();

        // Merge both collections and remove duplicates
        $friends = $friendsAsUser->merge($friendsAsFriend)->unique('id');

        $groupChats = $user->groupChats()
            ->with(['members:id,name', 'creator:id,name'])
            ->withCount('members')
            ->get();

        return view('user-chat.index', compact('friends', 'groupChats'));
    }

    public function getChat(Request $request, $friendId): JsonResponse
    {
        $currentUserId = auth()->id();

        // Verify friendship
        $friendship = Friendship::where(function ($q) use ($currentUserId, $friendId) {
            $q->where('user_id', $currentUserId)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($currentUserId, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $currentUserId);
        })->where('status', 'accepted')->first();

        if (!$friendship) {
            return response()->json(['error' => 'You are not friends with this user'], 403);
        }

        $friend = User::findOrFail($friendId);
        $messages = UserChatMessage::betweenUsers($currentUserId, $friendId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        UserChatMessage::where('sender_id', $friendId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'friend' => $friend,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $currentUserId = auth()->id();
        $receiverId = $request->receiver_id;

        // Verify friendship
        $friendship = Friendship::where(function ($q) use ($currentUserId, $receiverId) {
            $q->where('user_id', $currentUserId)->where('friend_id', $receiverId);
        })->orWhere(function ($q) use ($currentUserId, $receiverId) {
            $q->where('user_id', $receiverId)->where('friend_id', $currentUserId);
        })->where('status', 'accepted')->first();

        if (!$friendship) {
            return response()->json(['error' => 'You are not friends with this user'], 403);
        }

        $message = UserChatMessage::create([
            'sender_id' => $currentUserId,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);

        $message->load(['sender', 'receiver']);

        // Create notification for the message
        $sender = auth()->user();
        $messagePreview = strlen($request->message) > 50 ? substr($request->message, 0, 50) . '...' : $request->message;
        \App\Models\Notification::createMessageNotification(
            $receiverId,
            $currentUserId,
            $sender->name,
            $messagePreview
        );

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function getUnreadCount(): JsonResponse
    {
        $unreadCount = auth()->user()->unreadMessageCount();

        return response()->json(['count' => $unreadCount]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
        ]);

        UserChatMessage::where('sender_id', $request->sender_id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function getRecentChats(): JsonResponse
    {
        $user = auth()->user();

        // Get friends with recent messages
        $recentChats = User::whereIn('id', function ($query) use ($user) {
            $query->select('sender_id')
                ->from('user_chat_messages')
                ->where('receiver_id', $user->id)
                ->union(
                    $query->select('receiver_id')
                        ->from('user_chat_messages')
                        ->where('sender_id', $user->id)
                );
        })
        ->whereHas('friendships', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'accepted');
        })
        ->orWhereHas('friendRequests', function ($q) use ($user) {
            $q->where('friend_id', $user->id)->where('status', 'accepted');
        })
        ->withCount(['receivedMessages as unread_count' => function ($q) use ($user) {
            $q->where('receiver_id', $user->id)->where('is_read', false);
        }])
        ->get();

        return response()->json($recentChats);
    }
}
