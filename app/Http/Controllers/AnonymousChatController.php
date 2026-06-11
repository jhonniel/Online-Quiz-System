<?php

namespace App\Http\Controllers;

use App\Events\Chat\AnonymousChatMessageSent;
use App\Models\AnonymousChatMessage;
use App\Models\AnonymousChatRoom;
use App\Models\User;
use App\Models\UserActivity;
use App\Support\AnonymousChatAliasService;
use App\Support\AnonymousChatEligibility;
use App\Support\AnonymousChatToken;
use App\Models\ChatMessageMedia;
use App\Support\ChatBroadcast;
use App\Support\ChatMediaService;
use App\Support\ChatUnread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnonymousChatController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $params = [];

        if ($request->filled('room')) {
            $params['anonymous_room'] = (int) $request->query('room');
        }

        if ($request->filled('peer_name') && $request->query('starter') === '1') {
            $params['peer_name'] = (string) $request->query('peer_name');
            $params['starter'] = 1;
        }

        return redirect()->route('user-chat.index', $params);
    }

    public function targets(): JsonResponse
    {
        $user = auth()->user();
        $existingPeerIds = AnonymousChatRoom::query()
            ->where('created_by', $user->id)
            ->get()
            ->map(fn (AnonymousChatRoom $room) => $room->peerUser($user)?->id)
            ->filter()
            ->all();

        $targets = $this->eligibleTargetsQuery($user)
            ->whereNotIn('id', $existingPeerIds)
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name'])
            ->map(fn (User $target) => [
                'token' => AnonymousChatToken::issue($user->id, $target->id),
                'alias' => AnonymousChatAliasService::browseLabel($user->id, $target->id),
            ])
            ->values();

        return response()->json(['targets' => $targets]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|max:2048',
            'know_peer' => 'sometimes|boolean',
        ]);

        $user = $request->user();
        $targetId = AnonymousChatToken::resolve($validated['token'], $user->id)
            ?? AnonymousChatToken::resolveLegacy($validated['token']);

        if ($targetId === null) {
            return response()->json(['error' => 'Invalid or expired anonymous chat selection.'], 422);
        }

        if ($targetId === $user->id) {
            return response()->json(['error' => 'Invalid anonymous chat selection.'], 422);
        }

        if (AnonymousChatEligibility::isBlockedBetween($user->id, $targetId)) {
            return response()->json(['error' => 'Invalid anonymous chat selection.'], 422);
        }

        $target = $this->eligibleTargetsQuery($user)->where('id', $targetId)->first();
        if (! $target instanceof User) {
            return response()->json(['error' => 'Invalid anonymous chat selection.'], 422);
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
        if ($request->boolean('know_peer')) {
            $redirectParams['starter'] = 1;
            if ($peer) {
                $redirectParams['peer_name'] = $peer->name;
            }
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
        $peer = $anonymousChatRoom->peerUser($user);

        $messages = ChatMediaService::attachMediaPayload(
            ChatMessageMedia::TYPE_ANONYMOUS,
            $anonymousChatRoom->messages()->orderBy('created_at')->get(),
            $user
        )->map(fn (AnonymousChatMessage $message) => [
            'id' => $message->id,
            'sender_alias' => $anonymousChatRoom->senderAlias((int) $message->sender_id),
            'message' => $message->message,
            'created_at' => $message->created_at,
            'is_own' => (int) $message->sender_id === $user->id,
            'media' => $message->media,
        ]);

        ChatUnread::markAnonymousRead($anonymousChatRoom, $user->id);

        $isCreator = $anonymousChatRoom->wasCreatedBy($user->id);

        return response()->json([
            'room' => [
                'id' => $anonymousChatRoom->id,
                'peer_alias' => $peer ? $anonymousChatRoom->aliasForUser($peer->id) : 'Anonymous',
                'my_alias' => $anonymousChatRoom->aliasForUser($user->id),
                'is_creator' => $isCreator,
                'peer_name' => $isCreator ? $peer?->name : null,
            ],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, AnonymousChatRoom $anonymousChatRoom): JsonResponse
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
        $peer = $anonymousChatRoom->peerUser($user);

        $message = AnonymousChatMessage::create([
            'anonymous_chat_room_id' => $anonymousChatRoom->id,
            'sender_id' => $user->id,
            'message' => (string) ($validated['message'] ?? ''),
        ]);

        if ($hasImage) {
            ChatMediaService::storeForMessage(
                ChatMessageMedia::TYPE_ANONYMOUS,
                $message->id,
                $user,
                $request->file('image'),
                (string) $request->input('image_mode')
            );
        }

        $media = ChatMediaService::serializeForViewer(
            ChatMediaService::findForMessage(ChatMessageMedia::TYPE_ANONYMOUS, $message->id),
            $user
        );

        $anonymousChatRoom->touch();

        $preview = $hasImage
            ? 'Sent an image'
            : (strlen((string) ($validated['message'] ?? '')) > 50
                ? substr((string) $validated['message'], 0, 50).'...'
                : (string) ($validated['message'] ?? ''));

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

        ChatBroadcast::dispatch(new AnonymousChatMessageSent($message, $anonymousChatRoom));

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_alias' => $anonymousChatRoom->senderAlias($user->id),
                'message' => $message->message,
                'created_at' => $message->created_at,
                'is_own' => true,
                'media' => $media,
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
