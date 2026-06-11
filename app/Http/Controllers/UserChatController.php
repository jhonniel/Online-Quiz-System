<?php

namespace App\Http\Controllers;

use App\Events\Chat\DirectChatMessageSent;
use App\Events\Chat\DirectChatMessagesRead;
use App\Models\ChatMessageMedia;
use App\Support\ChatBroadcast;
use App\Support\ChatMediaService;
use App\Support\ChatUnread;
use App\Support\StoryService;
use App\Models\AnonymousChatRoom;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserChatMessage;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Get friends from both directions (where user is user_id or friend_id)
        $friendsAsUser = $user->friends()->get();
        $friendsAsFriend = $user->acceptedFriends()->get();

        // Merge both collections and remove duplicates
        $friends = $friendsAsUser->merge($friendsAsFriend)->unique('id')
            ->map(function (User $friend) use ($user) {
                $unread = ChatUnread::friendUnreadCount($user, $friend->id);

                return [
                    'friend' => $friend,
                    'unread_count' => $unread,
                    'preview' => ChatUnread::friendLatestPreview($user, $friend->id),
                    'has_new' => $unread > 0,
                ];
            })
            ->sortByDesc('unread_count')
            ->values();

        $groupChats = $user->groupChats()
            ->with(['members:id,name', 'creator:id,name'])
            ->withCount('members')
            ->get()
            ->map(function ($groupChat) use ($user) {
                $unread = ChatUnread::groupUnreadCount($groupChat, $user->id);

                return [
                    'group' => $groupChat,
                    'unread_count' => $unread,
                    'preview' => ChatUnread::groupLatestPreview($groupChat),
                    'has_new' => $unread > 0,
                ];
            })
            ->sortByDesc('unread_count')
            ->values();

        $anonymousRooms = AnonymousChatRoom::query()
            ->with(['participants', 'userOne:id,name', 'userTwo:id,name'])
            ->where(function ($query) use ($user) {
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (AnonymousChatRoom $room) use ($user) {
                $peer = $room->peerUser($user);
                $isCreator = $room->wasCreatedBy($user->id);
                $unread = ChatUnread::anonymousUnreadCount($room, $user->id);

                return [
                    'room' => $room,
                    'peer_alias' => $peer ? (string) $room->aliasForUser($peer->id) : 'Anonymous',
                    'peer_name' => $isCreator ? $peer?->name : null,
                    'is_creator' => $isCreator,
                    'unread_count' => $unread,
                    'preview' => ChatUnread::anonymousLatestPreview($room, $user->id),
                    'has_new' => $unread > 0,
                ];
            })
            ->sortByDesc('unread_count')
            ->values();

        if ($request->filled('anonymous_room')) {
            $room = AnonymousChatRoom::query()->find((int) $request->query('anonymous_room'));
            if ($room instanceof AnonymousChatRoom && $room->includesUser($user->id)) {
                $peer = $room->peerUser($user);
                UserActivity::logActivity($user, 'anonymous_chat', 'room_opened', [
                    'description' => 'Opened anonymous chat with '.$peer?->name,
                    'room_id' => $room->id,
                    'viewer_id' => $user->id,
                    'viewer_name' => $user->name,
                    'peer_id' => $peer?->id,
                    'peer_name' => $peer?->name,
                    'peer_alias' => $peer ? $room->aliasForUser($peer->id) : null,
                ]);
            }
        }

        $storyFeed = StoryService::feedFor($user);
        $storyRingMap = StoryService::ringMapFor(
            $user,
            $friends->map(fn (array $entry) => $entry['friend']->id)->all()
        );

        return view('user-chat.index', compact('friends', 'groupChats', 'anonymousRooms', 'storyFeed', 'storyRingMap'));
    }

    public function getChat(Request $request, $friendId): JsonResponse
    {
        $currentUserId = auth()->id();

        // Verify friendship
        $friendship = Friendship::where(function ($q) use ($currentUserId, $friendId) {
            $q->where(function ($q) use ($currentUserId, $friendId) {
                $q->where('user_id', $currentUserId)->where('friend_id', $friendId);
            })->orWhere(function ($q) use ($currentUserId, $friendId) {
                $q->where('user_id', $friendId)->where('friend_id', $currentUserId);
            });
        })->where('status', 'accepted')->first();

        if (!$friendship) {
            return response()->json(['error' => 'You are not friends with this user'], 403);
        }

        $friend = User::findOrFail($friendId);
        $messages = ChatMediaService::attachMediaPayload(
            ChatMessageMedia::TYPE_DIRECT,
            UserChatMessage::betweenUsers($currentUserId, $friendId)
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->get(),
            auth()->user()
        );

        // Mark messages as read when the conversation is opened.
        $markedIds = UserChatMessage::query()
            ->where('sender_id', $friendId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->pluck('id')
            ->all();

        if ($markedIds !== []) {
            UserChatMessage::query()
                ->whereIn('id', $markedIds)
                ->update(['is_read' => true, 'read_at' => now()]);

            ChatBroadcast::dispatch(new DirectChatMessagesRead($currentUserId, (int) $friendId, $markedIds));
        }

        return response()->json([
            'friend' => $friend,
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $hasImage = $request->hasFile('image');
        if ($hasImage) {
            ChatMediaService::validateUploadRequest($request->all());
        } else {
            $request->validate([
                'message' => 'required|string|max:1000',
            ]);
        }

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $currentUserId = auth()->id();
        $receiverId = $request->receiver_id;

        // Verify friendship
        $friendship = Friendship::where(function ($q) use ($currentUserId, $receiverId) {
            $q->where(function ($q) use ($currentUserId, $receiverId) {
                $q->where('user_id', $currentUserId)->where('friend_id', $receiverId);
            })->orWhere(function ($q) use ($currentUserId, $receiverId) {
                $q->where('user_id', $receiverId)->where('friend_id', $currentUserId);
            });
        })->where('status', 'accepted')->first();

        if (!$friendship) {
            return response()->json(['error' => 'You are not friends with this user'], 403);
        }

        $message = UserChatMessage::create([
            'sender_id' => $currentUserId,
            'receiver_id' => $receiverId,
            'message' => (string) $request->input('message', ''),
        ]);

        if ($hasImage) {
            ChatMediaService::storeForMessage(
                ChatMessageMedia::TYPE_DIRECT,
                $message->id,
                $request->user(),
                $request->file('image'),
                (string) $request->input('image_mode')
            );
        }

        $message = ChatMediaService::attachMediaPayload(
            ChatMessageMedia::TYPE_DIRECT,
            collect([$message->load(['sender', 'receiver'])]),
            $request->user()
        )->first();

        // Create notification for the message
        $sender = auth()->user();
        $messagePreview = $hasImage
            ? 'Sent an image'
            : (strlen((string) $request->message) > 50 ? substr((string) $request->message, 0, 50).'...' : (string) $request->message);
        \App\Models\Notification::createMessageNotification(
            $receiverId,
            $currentUserId,
            $sender->name,
            $messagePreview
        );

        ChatBroadcast::dispatch(new DirectChatMessageSent($message));

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function getUnreadCount(): JsonResponse
    {
        return response()->json([
            'count' => ChatUnread::totalUnreadFor(auth()->user()),
        ]);
    }

    public function getSidebarUnread(): JsonResponse
    {
        return response()->json(ChatUnread::sidebarPayload(auth()->user()));
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
        ]);

        $markedIds = UserChatMessage::query()
            ->where('sender_id', $request->sender_id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->pluck('id')
            ->all();

        if ($markedIds !== []) {
            UserChatMessage::query()
                ->whereIn('id', $markedIds)
                ->update(['is_read' => true, 'read_at' => now()]);

            ChatBroadcast::dispatch(new DirectChatMessagesRead((int) auth()->id(), (int) $request->sender_id, $markedIds));
        }

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
