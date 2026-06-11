<?php

namespace App\Support;

use App\Models\Friendship;
use App\Models\User;
use App\Models\UserStory;
use App\Models\UserStoryView;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class StoryService
{
    public const MAX_BYTES = 5_242_880;

    /**
     * @return list<int>
     */
    public static function friendIdsFor(User $user): array
    {
        $asUser = $user->friends()->pluck('users.id');
        $asFriend = $user->acceptedFriends()->pluck('users.id');

        return $asUser->merge($asFriend)->unique()->values()->all();
    }

    public static function usersAreFriends(int $userIdA, int $userIdB): bool
    {
        if ($userIdA === $userIdB) {
            return true;
        }

        return Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($userIdA, $userIdB) {
                $query->where(function ($inner) use ($userIdA, $userIdB) {
                    $inner->where('user_id', $userIdA)->where('friend_id', $userIdB);
                })->orWhere(function ($inner) use ($userIdA, $userIdB) {
                    $inner->where('user_id', $userIdB)->where('friend_id', $userIdA);
                });
            })
            ->exists();
    }

    public static function store(User $user, UploadedFile $file, ?string $caption = null): UserStory
    {
        if ($file->getSize() > self::MAX_BYTES) {
            abort(422, 'Story images must be 5MB or smaller.');
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = $file->storeAs(
            'user-stories/'.now()->format('Y/m/d'),
            Str::uuid()->toString().'.'.$extension,
            'local'
        );

        return UserStory::create([
            'user_id' => $user->id,
            'path' => $path,
            'disk' => 'local',
            'mime_type' => (string) $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'caption' => $caption ? trim($caption) : null,
            'expires_at' => now()->addDay(),
        ]);
    }

  /**
     * @return array<int, array{has_story: bool, has_unviewed: bool}>
     */
    public static function ringMapFor(User $viewer, array $userIds): array
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if ($userIds === []) {
            return [];
        }

        $activeStoryUserIds = UserStory::query()
            ->active()
            ->whereIn('user_id', $userIds)
            ->distinct()
            ->pluck('user_id')
            ->all();

        $activeSet = array_fill_keys($activeStoryUserIds, true);
        $map = [];

        foreach ($userIds as $userId) {
            $hasStory = isset($activeSet[$userId]);
            $hasUnviewed = $hasStory
                && $userId !== $viewer->id
                && self::hasUnviewedStoriesFrom($viewer->id, $userId);

            $map[$userId] = [
                'has_story' => $hasStory,
                'has_unviewed' => $hasUnviewed,
            ];
        }

        return $map;
    }

    public static function hasUnviewedStoriesFrom(int $viewerId, int $authorId): bool
    {
        return UserStory::query()
            ->active()
            ->where('user_id', $authorId)
            ->whereDoesntHave('views', fn ($query) => $query->where('viewer_id', $viewerId))
            ->exists();
    }

    public static function hasActiveStory(int $userId): bool
    {
        return UserStory::query()->active()->where('user_id', $userId)->exists();
    }

    /**
     * @return Collection<int, array{user: User, has_unviewed: bool, is_self: bool, has_story: bool}>
     */
    public static function feedFor(User $viewer): Collection
    {
        $friendIds = self::friendIdsFor($viewer);

        $authorIds = UserStory::query()
            ->active()
            ->whereIn('user_id', $friendIds)
            ->distinct()
            ->pluck('user_id');

        $users = User::query()
            ->whereIn('id', $authorIds)
            ->get(['id', 'name', 'profile_picture'])
            ->keyBy('id');

        $selfEntry = [
            'user' => $viewer,
            'has_unviewed' => false,
            'is_self' => true,
            'has_story' => self::hasActiveStory($viewer->id),
        ];

        $friendEntries = $authorIds
            ->map(function (int $authorId) use ($viewer, $users) {
                $user = $users->get($authorId);
                if (! $user instanceof User) {
                    return null;
                }

                return [
                    'user' => $user,
                    'has_unviewed' => self::hasUnviewedStoriesFrom($viewer->id, $authorId),
                    'is_self' => false,
                    'has_story' => true,
                ];
            })
            ->filter()
            ->sortBy([
                fn ($item) => $item['has_unviewed'] ? 0 : 1,
                fn ($item) => $item['user']->name,
            ])
            ->values();

        return collect([$selfEntry])->merge($friendEntries)->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function storiesForViewer(User $viewer, User $author): array
    {
        if ($viewer->id !== $author->id && ! self::usersAreFriends($viewer->id, $author->id)) {
            abort(403);
        }

        return UserStory::query()
            ->active()
            ->where('user_id', $author->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (UserStory $story) => self::serializeStory($story, $viewer))
            ->all();
    }

    public static function markViewed(UserStory $story, User $viewer): void
    {
        if ($story->isExpired()) {
            abort(404);
        }

        if ($viewer->id !== $story->user_id && ! self::usersAreFriends($viewer->id, $story->user_id)) {
            abort(403);
        }

        if ($viewer->id === $story->user_id) {
            return;
        }

        UserStoryView::query()->firstOrCreate(
            [
                'user_story_id' => $story->id,
                'viewer_id' => $viewer->id,
            ],
            ['viewed_at' => now()]
        );
    }

    public static function stream(UserStory $story, User $viewer): StreamedResponse
    {
        if ($story->isExpired()) {
            abort(404);
        }

        if ($viewer->id !== $story->user_id && ! self::usersAreFriends($viewer->id, $story->user_id)) {
            abort(403);
        }

        if (! Storage::disk($story->disk)->exists($story->path)) {
            abort(404);
        }

        return Storage::disk($story->disk)->response($story->path, null, [
            'Content-Type' => $story->mime_type,
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public static function purgeExpired(): int
    {
        $purged = 0;

        UserStory::query()
            ->where('expires_at', '<', now())
            ->orderBy('id')
            ->chunkById(100, function ($stories) use (&$purged) {
                foreach ($stories as $story) {
                    if (Storage::disk($story->disk)->exists($story->path)) {
                        Storage::disk($story->disk)->delete($story->path);
                    }

                    $story->delete();
                    $purged++;
                }
            });

        return $purged;
    }

    /**
     * @return array<string, mixed>
     */
    public static function serializeStory(UserStory $story, User $viewer): array
    {
        return [
            'id' => $story->id,
            'caption' => $story->caption,
            'expires_at' => $story->expires_at?->toIso8601String(),
            'created_at' => $story->created_at?->toIso8601String(),
            'media_url' => route('stories.media', $story),
            'is_own' => $viewer->id === $story->user_id,
            'viewed' => $viewer->id === $story->user_id
                || $story->views()->where('viewer_id', $viewer->id)->exists(),
        ];
    }
}
