<?php

namespace App\Support;

use App\Models\AnonymousChatMessage;
use App\Models\AnonymousChatRoom;
use App\Models\ChatMessageMedia;
use App\Models\ChatMessageMediaView;
use App\Models\GroupChat;
use App\Models\GroupChatMessage;
use App\Models\User;
use App\Models\UserChatMessage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ChatMediaService
{
    public const MAX_BYTES = 2_097_152;

    /**
     * @param  list<string>  $modes
     * @return array{mode: string}
     */
    public static function validateUploadRequest(array $input, array $modes = [
        ChatMessageMedia::MODE_VIEW_ONCE,
        ChatMessageMedia::MODE_STAY_24H,
    ]): array
    {
        return validator($input, [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'image_mode' => 'required|in:'.implode(',', $modes),
            'message' => 'nullable|string|max:1000',
        ], [
            'image.max' => 'Images must be 2MB or smaller.',
            'image.image' => 'Please upload a valid image file.',
        ])->validate();
    }

    public static function storeForMessage(
        string $chatType,
        int $messageId,
        User $uploader,
        UploadedFile $file,
        string $mode,
    ): ChatMessageMedia {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $file->storeAs(
            'chat-images/'.now()->format('Y/m/d'),
            Str::uuid()->toString().'.'.$extension,
            'local'
        );

        return ChatMessageMedia::create([
            'chat_type' => $chatType,
            'message_id' => $messageId,
            'uploaded_by' => $uploader->id,
            'path' => $path,
            'disk' => 'local',
            'mime_type' => (string) $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'mode' => $mode,
            'expires_at' => now()->addDay(),
        ]);
    }

    /**
     * @param  Collection<int, mixed>  $messages
     */
    public static function attachMediaPayloadForAdmin(string $chatType, Collection $messages): Collection
    {
        if ($messages->isEmpty()) {
            return $messages;
        }

        $mediaByMessageId = ChatMessageMedia::query()
            ->where('chat_type', $chatType)
            ->whereIn('message_id', $messages->pluck('id'))
            ->withCount('views')
            ->get()
            ->keyBy('message_id');

        return $messages->map(function ($message) use ($mediaByMessageId) {
            $media = $mediaByMessageId->get($message->id);
            $message->setAttribute('media', $media ? self::serializeForAdmin($media) : null);

            return $message;
        });
    }

    /**
     * @param  Collection<int, mixed>  $messages
     */
    public static function attachMediaPayload(string $chatType, Collection $messages, User $viewer): Collection
    {
        if ($messages->isEmpty()) {
            return $messages;
        }

        $mediaByMessageId = ChatMessageMedia::query()
            ->where('chat_type', $chatType)
            ->whereIn('message_id', $messages->pluck('id'))
            ->get()
            ->keyBy('message_id');

        return $messages->map(function ($message) use ($mediaByMessageId, $viewer) {
            $media = $mediaByMessageId->get($message->id);
            $message->setAttribute('media', $media ? self::serializeForViewer($media, $viewer) : null);

            return $message;
        });
    }

  /**
     * @return array<string, mixed>|null
     */
    public static function serializeForViewer(?ChatMessageMedia $media, User $viewer): ?array
    {
        if (! $media instanceof ChatMessageMedia || $media->isExpired()) {
            return null;
        }

        $isSender = (int) $media->uploaded_by === $viewer->id;
        $viewed = $media->viewedBy($viewer->id);
        $canViewImage = $isSender
            || ($media->mode === ChatMessageMedia::MODE_STAY_24H && self::viewerCanAccessChat($media, $viewer))
            || ($media->mode === ChatMessageMedia::MODE_VIEW_ONCE && ! $viewed && self::viewerCanAccessChat($media, $viewer));

        return [
            'id' => $media->id,
            'mode' => $media->mode,
            'expires_at' => $media->expires_at?->toIso8601String(),
            'is_sender' => $isSender,
            'viewed' => $viewed,
            'can_view' => $canViewImage,
            'url' => $canViewImage ? route('chat-media.show', $media) : null,
        ];
    }

    public static function viewerCanAccessChat(ChatMessageMedia $media, User $viewer): bool
    {
        return match ($media->chat_type) {
            ChatMessageMedia::TYPE_DIRECT => self::canAccessDirectMessage($media->message_id, $viewer->id),
            ChatMessageMedia::TYPE_GROUP => self::canAccessGroupMessage($media->message_id, $viewer->id),
            ChatMessageMedia::TYPE_ANONYMOUS => self::canAccessAnonymousMessage($media->message_id, $viewer->id),
            default => false,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function serializeForAdmin(?ChatMessageMedia $media): ?array
    {
        if (! $media instanceof ChatMessageMedia) {
            return null;
        }

        $exists = Storage::disk($media->disk)->exists($media->path);
        $expired = $media->isExpired();

        if (! $exists) {
            return [
                'id' => $media->id,
                'mode' => $media->mode,
                'expires_at' => $media->expires_at?->toIso8601String(),
                'expired' => $expired,
                'available' => false,
                'url' => null,
                'view_count' => (int) ($media->views_count ?? $media->views()->count()),
            ];
        }

        return [
            'id' => $media->id,
            'mode' => $media->mode,
            'expires_at' => $media->expires_at?->toIso8601String(),
            'expired' => $expired,
            'available' => true,
            'url' => route('admin.chat-media.show', $media),
            'view_count' => (int) ($media->views_count ?? $media->views()->count()),
        ];
    }

    public static function adminCanAccessMedia(ChatMessageMedia $media, User $admin): bool
    {
        if ($admin->isAdmin() && ! $admin->adminPermission) {
            return true;
        }

        return match ($media->chat_type) {
            ChatMessageMedia::TYPE_ANONYMOUS => $admin->canAccessAnalyticsFeature('anonymous_chats'),
            ChatMessageMedia::TYPE_DIRECT, ChatMessageMedia::TYPE_GROUP => $admin->canAccessAnalyticsFeature('anonymous_chats')
                || $admin->canAccessAnalyticsFeature('user_activity'),
            default => false,
        };
    }

    public static function streamForAdmin(ChatMessageMedia $media): StreamedResponse
    {
        if (! Storage::disk($media->disk)->exists($media->path)) {
            abort(404);
        }

        return Storage::disk($media->disk)->response($media->path, null, [
            'Content-Type' => $media->mime_type,
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public static function markViewed(ChatMessageMedia $media, User $viewer): void
    {
        if ($media->isExpired() || ! self::viewerCanAccessChat($media, $viewer)) {
            abort(403);
        }

        if ((int) $media->uploaded_by === $viewer->id) {
            return;
        }

        ChatMessageMediaView::query()->firstOrCreate(
            [
                'chat_message_media_id' => $media->id,
                'user_id' => $viewer->id,
            ],
            ['viewed_at' => now()]
        );
    }

    public static function streamForViewer(ChatMessageMedia $media, User $viewer): StreamedResponse
    {
        if ($media->isExpired() || ! self::viewerCanAccessChat($media, $viewer)) {
            abort(404);
        }

        $isSender = (int) $media->uploaded_by === $viewer->id;
        $viewed = $media->viewedBy($viewer->id);

        if (! $isSender) {
            if ($media->mode === ChatMessageMedia::MODE_VIEW_ONCE && $viewed) {
                abort(404);
            }

            if ($media->mode === ChatMessageMedia::MODE_VIEW_ONCE && ! $viewed) {
                self::markViewed($media, $viewer);
            }
        }

        if (! Storage::disk($media->disk)->exists($media->path)) {
            abort(404);
        }

        return Storage::disk($media->disk)->response($media->path, null, [
            'Content-Type' => $media->mime_type,
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function serializeForBroadcast(?ChatMessageMedia $media): ?array
    {
        if (! $media instanceof ChatMessageMedia || $media->isExpired()) {
            return null;
        }

        return [
            'id' => $media->id,
            'mode' => $media->mode,
            'expires_at' => $media->expires_at?->toIso8601String(),
        ];
    }

    public static function findForMessage(string $chatType, int $messageId): ?ChatMessageMedia
    {
        return ChatMessageMedia::query()
            ->where('chat_type', $chatType)
            ->where('message_id', $messageId)
            ->first();
    }

    public static function purgeExpired(): int
    {
        $purged = 0;

        ChatMessageMedia::query()
            ->where('expires_at', '<', now())
            ->orderBy('id')
            ->chunkById(100, function ($items) use (&$purged) {
                foreach ($items as $media) {
                    if (Storage::disk($media->disk)->exists($media->path)) {
                        Storage::disk($media->disk)->delete($media->path);
                    }

                    $media->delete();
                    $purged++;
                }
            });

        return $purged;
    }

    private static function canAccessDirectMessage(int $messageId, int $userId): bool
    {
        return UserChatMessage::query()
            ->where('id', $messageId)
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->exists();
    }

    private static function canAccessGroupMessage(int $messageId, int $userId): bool
    {
        $message = GroupChatMessage::query()->find($messageId);
        if (! $message instanceof GroupChatMessage) {
            return false;
        }

        return GroupChat::query()
            ->where('id', $message->group_chat_id)
            ->whereHas('members', fn ($query) => $query->where('users.id', $userId))
            ->exists();
    }

    private static function canAccessAnonymousMessage(int $messageId, int $userId): bool
    {
        $message = AnonymousChatMessage::query()->find($messageId);
        if (! $message instanceof AnonymousChatMessage) {
            return false;
        }

        $room = AnonymousChatRoom::query()->find($message->anonymous_chat_room_id);

        return $room instanceof AnonymousChatRoom && $room->includesUser($userId);
    }
}
