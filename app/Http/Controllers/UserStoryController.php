<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserStory;
use App\Support\StoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserStoryController extends Controller
{
    public function feed(Request $request): JsonResponse
    {
        $feed = StoryService::feedFor($request->user())->map(function (array $entry) use ($request) {
            $user = $entry['user'];

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'profile_picture_url' => $user->getProfilePictureUrl(),
                'initials' => $user->getInitials(),
                'has_unviewed' => $entry['has_unviewed'],
                'is_self' => $entry['is_self'],
                'has_story' => $entry['has_story'],
            ];
        });

        return response()->json([
            'feed' => $feed,
            'self_has_story' => StoryService::hasActiveStory($request->user()->id),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'caption' => 'nullable|string|max:500',
        ]);

        $story = StoryService::store(
            $request->user(),
            $request->file('image'),
            $validated['caption'] ?? null
        );

        return response()->json([
            'success' => true,
            'story' => StoryService::serializeStory($story, $request->user()),
        ]);
    }

    public function userStories(Request $request, User $user): JsonResponse
    {
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'profile_picture_url' => $user->getProfilePictureUrl(),
                'initials' => $user->getInitials(),
            ],
            'stories' => StoryService::storiesForViewer($request->user(), $user),
        ]);
    }

    public function media(Request $request, UserStory $userStory)
    {
        return StoryService::stream($userStory, $request->user());
    }

    public function markViewed(Request $request, UserStory $userStory): JsonResponse
    {
        StoryService::markViewed($userStory, $request->user());

        return response()->json(['success' => true]);
    }
}
