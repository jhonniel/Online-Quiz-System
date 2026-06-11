<?php

namespace App\Http\Controllers;

use App\Models\AnonymousChatMessage;
use App\Models\AnonymousChatRoom;
use App\Models\Friendship;
use App\Models\User;
use App\Models\UserActivity;
use App\Support\AnonymousChatAliasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class AnonymousChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $rooms = $this->roomsForUser($user);

        if ($request->filled('room')) {
            $room = AnonymousChatRoom::query()->find((int) $request->query('room'));
            if ($room instanceof AnonymousChatRoom && $room->includesUser($user->id)) {
                $peer = $room->peerUser($user);
                $this->logActivity($user, 'room_opened', [
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

        return view('anonymous-chat.index', compact('rooms'));
    }

    public function targets(): JsonResponse
    {
        $user = auth()->user();
        $existingPeerIds = AnonymousChatRoom::query()
            ->where(function ($query) use ($user) {
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })
            ->get()
            ->map(fn (AnonymousChatRoom $room) => $room->peerUser($user)?->id)
            ->filter()
            ->all();

        $targets = $this->eligibleTargetsQuery($user)
            ->whereNotIn('id', $existingPeerIds)
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name'])
            ->map(fn (User $target) => [
                'token' => Crypt::encryptString((string) $target->id),
                'alias' => AnonymousChatAliasService::browseLabel($user->id, $target->id),
            ])
            ->values();

        return response()->json(['targets' => $targets]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->user();

        try {
            $targetId = (int) Crypt::decryptString($validated['token']);
        } catch (\Throwable) {
            return response()->json(['error' => 'Invalid anonymous chat selection.'], 422);
        }

        if ($targetId === $user->id) {
            return response()->json(['error' => 'You cannot start an anonymous chat with yourself.'], 422);
        }

        $target = $this->eligibleTargetsQuery($user)->where('id', $targetId)->first();
        if (! $target instanceof User) {
            return response()->json(['error' => 'This user is not available for anonymous chat.'], 403);
        }

        $room = AnonymousChatRoom::findOrCreateBetween($user, $target);
        $peer = $room->peerUser($user);

        $this->logActivity($user, 'room_started', [
            'description' => 'Started anonymous chat with '.$peer?->name,
            'room_id' => $room->id,
            'initiator_id' => $user->id,
            'initiator_name' => $user->name,
            'peer_id' => $peer?->id,
            'peer_name' => $peer?->name,
            'initiator_alias' => $room->aliasForUser($user->id),
            'peer_alias' => $peer ? $room->aliasForUser($peer->id) : null,
        ]);

        return response()->json([
            'success' => true,
            'room_id' => $room->id,
            'peer_alias' => $peer ? $room->aliasForUser($peer->id) : 'Anonymous',
            'redirect_url' => route('anonymous-chat.index', ['room' => $room->id]),
        ]);
    }

    public function messages(Request $request, AnonymousChatRoom $anonymousChatRoom): JsonResponse
    {
        $user = $request->user();

        if (! $anonymousChatRoom->includesUser($user->id)) {
            return response()->json(['error' => 'You are not part of this anonymous chat.'], 403);
        }

        $peer = $anonymousChatRoom->peerUser($user);

        $messages = $anonymousChatRoom->messages()
            ->with('sender:id')
            ->orderBy('created_at')
            ->get()
            ->map(fn (AnonymousChatMessage $message) => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_alias' => $anonymousChatRoom->senderAlias((int) $message->sender_id),
                'message' => $message->message,
                'created_at' => $message->created_at,
                'is_own' => (int) $message->sender_id === $user->id,
            ]);

        return response()->json([
            'room' => [
                'id' => $anonymousChatRoom->id,
                'peer_alias' => $peer ? $anonymousChatRoom->aliasForUser($peer->id) : 'Anonymous',
                'my_alias' => $anonymousChatRoom->aliasForUser($user->id),
            ],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, AnonymousChatRoom $anonymousChatRoom): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = $request->user();

        if (! $anonymousChatRoom->includesUser($user->id)) {
            return response()->json(['error' => 'You are not part of this anonymous chat.'], 403);
        }

        $peer = $anonymousChatRoom->peerUser($user);

        $message = AnonymousChatMessage::create([
            'anonymous_chat_room_id' => $anonymousChatRoom->id,
            'sender_id' => $user->id,
            'message' => $validated['message'],
        ]);

        $anonymousChatRoom->touch();

        $preview = strlen($validated['message']) > 50
            ? substr($validated['message'], 0, 50).'...'
            : $validated['message'];

        $this->logActivity($user, 'message_sent', [
            'description' => 'Sent anonymous chat message to '.$peer?->name,
            'room_id' => $anonymousChatRoom->id,
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'sender_alias' => $anonymousChatRoom->aliasForUser($user->id),
            'recipient_id' => $peer?->id,
            'recipient_name' => $peer?->name,
            'recipient_alias' => $peer ? $anonymousChatRoom->aliasForUser($peer->id) : null,
            'message_preview' => $preview,
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_alias' => $anonymousChatRoom->senderAlias($user->id),
                'message' => $message->message,
                'created_at' => $message->created_at,
                'is_own' => true,
            ],
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    private function eligibleTargetsQuery(User $user)
    {
        $blockedIds = Friendship::query()
            ->where('status', 'blocked')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('friend_id', $user->id);
            })
            ->get()
            ->flatMap(fn (Friendship $friendship) => [$friendship->user_id, $friendship->friend_id])
            ->unique()
            ->reject(fn (int $id) => $id === $user->id)
            ->values()
            ->all();

        return User::query()
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->whereNotIn('role', ['admin'])
            ->when($blockedIds !== [], fn ($query) => $query->whereNotIn('id', $blockedIds));
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{room: AnonymousChatRoom, peer_alias: string}>
     */
    private function roomsForUser(User $user)
    {
        return AnonymousChatRoom::query()
            ->with(['participants', 'userOne:id,name', 'userTwo:id,name'])
            ->where(function ($query) use ($user) {
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (AnonymousChatRoom $room) use ($user) {
                $peer = $room->peerUser($user);

                return [
                    'room' => $room,
                    'peer_alias' => $peer ? (string) $room->aliasForUser($peer->id) : 'Anonymous',
                ];
            });
    }

    private function logActivity(User $user, string $action, array $metadata): void
    {
        UserActivity::logActivity($user, 'anonymous_chat', $action, $metadata);
    }
}
