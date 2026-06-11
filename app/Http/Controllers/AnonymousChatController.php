<?php

namespace App\Http\Controllers;

use App\Models\AnonymousChatMessage;
use App\Models\AnonymousChatRoom;
use App\Models\User;
use App\Models\UserActivity;
use App\Support\AnonymousChatAliasService;
use App\Support\AnonymousChatEligibility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AnonymousChatController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $params = [];

        if ($request->filled('room')) {
            $params['anonymous_room'] = (int) $request->query('room');
        }

        if ($request->filled('peer_name')) {
            $params['peer_name'] = (string) $request->query('peer_name');
        }

        return redirect()->route('user-chat.index', $params);
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
            'know_peer' => 'sometimes|boolean',
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

        $redirectParams = ['anonymous_room' => $room->id];
        if ($request->boolean('know_peer') && $peer) {
            $redirectParams['peer_name'] = $peer->name;
        }

        return response()->json([
            'success' => true,
            'room_id' => $room->id,
            'peer_alias' => $peer ? $room->aliasForUser($peer->id) : 'Anonymous',
            'redirect_url' => route('user-chat.index', $redirectParams),
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
        $blockedIds = AnonymousChatEligibility::blockedUserIdsFor($user);

        return User::query()
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->whereNotIn('role', ['admin'])
            ->when($blockedIds !== [], fn ($query) => $query->whereNotIn('id', $blockedIds));
    }

    private function logActivity(User $user, string $action, array $metadata): void
    {
        UserActivity::logActivity($user, 'anonymous_chat', $action, $metadata);
    }
}
