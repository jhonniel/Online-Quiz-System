<?php

namespace App\Http\Controllers;

use App\Events\Chat\GroupChatMessageSent;
use App\Models\ChatMessageMedia;
use App\Support\ChatBroadcast;
use App\Support\ChatMediaService;
use App\Support\ChatUnread;
use App\Models\Friendship;
use App\Models\GroupChat;
use App\Models\GroupChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupChatController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'integer|exists:users,id|distinct',
        ]);

        $user = $request->user();
        $memberIds = collect($validated['member_ids'])
            ->map(fn ($id) => (int) $id)
            ->reject(fn (int $id) => $id === $user->id)
            ->unique()
            ->values();

        if ($memberIds->isEmpty()) {
            return response()->json(['error' => 'Select at least one friend for the group chat.'], 422);
        }

        if (! $this->userIsFriendsWithAll($user, $memberIds->all())) {
            return response()->json(['error' => 'You can only add friends to a group chat.'], 403);
        }

        $groupChat = DB::transaction(function () use ($user, $validated, $memberIds) {
            $group = GroupChat::create([
                'name' => trim($validated['name']),
                'created_by' => $user->id,
            ]);

            $memberPivotData = $memberIds
                ->prepend($user->id)
                ->unique()
                ->mapWithKeys(fn (int $id) => [$id => ['joined_at' => now()]])
                ->all();

            $group->members()->attach($memberPivotData);

            return $group->load(['members:id,name,email,profile_picture', 'creator:id,name']);
        });

        return response()->json([
            'success' => true,
            'group_chat' => $groupChat,
            'redirect_url' => url('/user-chat?group='.$groupChat->id),
        ]);
    }

    public function messages(Request $request, GroupChat $groupChat): JsonResponse
    {
        $user = $request->user();

        if (! $groupChat->hasMember($user->id)) {
            return response()->json(['error' => 'You are not a member of this group chat.'], 403);
        }

        ChatUnread::markGroupRead($groupChat, $user->id);

        $messages = ChatMediaService::attachMediaPayload(
            ChatMessageMedia::TYPE_GROUP,
            $groupChat->messages()
                ->with('sender:id,name')
                ->orderBy('created_at')
                ->get(),
            $user
        );

        return response()->json([
            'group_chat' => $groupChat->load(['members:id,name,email,profile_picture', 'creator:id,name']),
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, GroupChat $groupChat): JsonResponse
    {
        $hasImage = $request->hasFile('image');
        if ($hasImage) {
            $validated = ChatMediaService::validateUploadRequest($request->all());
        } else {
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
            ]);
        }

        $user = $request->user();

        if (! $groupChat->hasMember($user->id)) {
            return response()->json(['error' => 'You are not a member of this group chat.'], 403);
        }

        $message = GroupChatMessage::create([
            'group_chat_id' => $groupChat->id,
            'sender_id' => $user->id,
            'message' => (string) ($validated['message'] ?? ''),
        ]);

        if ($hasImage) {
            ChatMediaService::storeForMessage(
                ChatMessageMedia::TYPE_GROUP,
                $message->id,
                $user,
                $request->file('image'),
                (string) $request->input('image_mode')
            );
        }

        $message = ChatMediaService::attachMediaPayload(
            ChatMessageMedia::TYPE_GROUP,
            collect([$message->load('sender:id,name')]),
            $user
        )->first();

        $groupChat->touch();

        $preview = $hasImage
            ? 'Sent an image'
            : (strlen((string) ($validated['message'] ?? '')) > 50
                ? substr((string) $validated['message'], 0, 50).'...'
                : (string) ($validated['message'] ?? ''));

        $groupChat->members()
            ->where('users.id', '!=', $user->id)
            ->get()
            ->each(function (User $member) use ($user, $preview) {
                \App\Models\Notification::createMessageNotification(
                    $member->id,
                    $user->id,
                    $user->name.' (group)',
                    $preview
                );
            });

        ChatBroadcast::dispatch(new GroupChatMessageSent($message));

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * @param  list<int>  $userIds
     */
    private function userIsFriendsWithAll(User $user, array $userIds): bool
    {
        foreach ($userIds as $userId) {
            if (! $this->usersAreFriends($user->id, $userId)) {
                return false;
            }
        }

        return true;
    }

    private function usersAreFriends(int $currentUserId, int $otherUserId): bool
    {
        return Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($currentUserId, $otherUserId) {
                $query->where(function ($inner) use ($currentUserId, $otherUserId) {
                    $inner->where('user_id', $currentUserId)
                        ->where('friend_id', $otherUserId);
                })->orWhere(function ($inner) use ($currentUserId, $otherUserId) {
                    $inner->where('user_id', $otherUserId)
                        ->where('friend_id', $currentUserId);
                });
            })
            ->exists();
    }
}
