<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FriendshipController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Get friends from both directions (where user is user_id or friend_id)
        $friendsAsUser = $user->friends()->get();
        $friendsAsFriend = $user->acceptedFriends()->get();

        // Merge both collections and remove duplicates
        $allFriends = $friendsAsUser->merge($friendsAsFriend)->unique('id');

        $pendingRequests = $user->pendingFriendRequests()->with('user')->get();
        $sentRequests = $user->sentFriendRequests()->with('friend')->get();

        return view('friends.index', compact('allFriends', 'pendingRequests', 'sentRequests'));
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $currentUserId = auth()->id();

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', $currentUserId)
            ->where('is_active', true)
            ->where('is_approved', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->with('university')
            ->limit(10)
            ->get(['id', 'name', 'email', 'profile_picture', 'university_id', 'role']);

        // Add friendship status and additional data to each user
        $users->each(function ($user) use ($currentUserId) {
            $friendship = Friendship::where(function ($q) use ($currentUserId, $user) {
                $q->where('user_id', $currentUserId)->where('friend_id', $user->id);
            })->orWhere(function ($q) use ($currentUserId, $user) {
                $q->where('user_id', $user->id)->where('friend_id', $currentUserId);
            })->first();

            $user->friendship_status = $friendship ? $friendship->status : 'none';
            $user->friendship_id = $friendship ? $friendship->id : null;
            $user->profile_picture_url = $user->getProfilePictureUrl();
            $user->university = $user->university ? $user->university->name : null;
        });

        return response()->json($users);
    }

    public function sendRequest(Request $request): JsonResponse
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $currentUserId = auth()->id();
        $friendId = $request->friend_id;

        if ($currentUserId === $friendId) {
            return response()->json(['error' => 'Cannot send friend request to yourself'], 400);
        }

        // Check if friendship already exists
        $existingFriendship = Friendship::where(function ($q) use ($currentUserId, $friendId) {
            $q->where('user_id', $currentUserId)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($currentUserId, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $currentUserId);
        })->first();

        if ($existingFriendship) {
            return response()->json(['error' => 'Friendship already exists'], 400);
        }

        $friendship = Friendship::create([
            'user_id' => $currentUserId,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        // Create notification for the friend request
        $sender = auth()->user();
        \App\Models\Notification::createFriendRequestNotification(
            $friendId,
            $currentUserId,
            $sender->name
        );

        return response()->json([
            'success' => true,
            'message' => 'Friend request sent successfully',
            'friendship' => $friendship,
        ]);
    }

    public function acceptRequest(Request $request, $friendshipId): JsonResponse
    {
        $friendship = Friendship::where('id', $friendshipId)
            ->where('friend_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return response()->json(['error' => 'Friend request not found'], 404);
        }

        $friendship->accept();

        return response()->json([
            'success' => true,
            'message' => 'Friend request accepted',
        ]);
    }

    public function rejectRequest(Request $request, $friendshipId): JsonResponse
    {
        $friendship = Friendship::where('id', $friendshipId)
            ->where('friend_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return response()->json(['error' => 'Friend request not found'], 404);
        }

        $friendship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Friend request rejected',
        ]);
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancelRequest(Request $request, $friendshipId): JsonResponse
    {
        $friendship = Friendship::where('id', $friendshipId)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return response()->json(['error' => 'Friend request not found'], 404);
        }

        $friendship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Friend request cancelled',
        ]);
    }

    public function removeFriend(Request $request, $friendId): JsonResponse
    {
        $currentUserId = auth()->id();

        // Find the friendship where current user is either user_id or friend_id
        $friendship = Friendship::where('status', 'accepted')
            ->where(function ($q) use ($currentUserId, $friendId) {
                $q->where(function ($subQ) use ($currentUserId, $friendId) {
                    $subQ->where('user_id', $currentUserId)
                         ->where('friend_id', $friendId);
                })->orWhere(function ($subQ) use ($currentUserId, $friendId) {
                    $subQ->where('user_id', $friendId)
                         ->where('friend_id', $currentUserId);
                });
            })
            ->first();

        if (!$friendship) {
            return response()->json(['error' => 'Friendship not found'], 404);
        }

        $friendship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Friend removed successfully',
        ]);
    }

    public function blockUser(Request $request, $userId): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $currentUserId = auth()->id();

        if ($currentUserId === $userId) {
            return response()->json(['error' => 'Cannot block yourself'], 400);
        }

        // Find existing friendship or create new one
        $friendship = Friendship::where(function ($q) use ($currentUserId, $userId) {
            $q->where('user_id', $currentUserId)->where('friend_id', $userId);
        })->orWhere(function ($q) use ($currentUserId, $userId) {
            $q->where('user_id', $userId)->where('friend_id', $currentUserId);
        })->first();

        if ($friendship) {
            $friendship->block();
        } else {
            Friendship::create([
                'user_id' => $currentUserId,
                'friend_id' => $userId,
                'status' => 'blocked',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully',
        ]);
    }
}
