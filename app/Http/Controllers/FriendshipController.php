<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use App\Support\AnonymousChatAliasService;
use App\Support\AnonymousChatEligibility;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class FriendshipController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Get friends from both directions (where user is user_id or friend_id)
        $friendsAsUser = $user->friends()->with('department:id,name')->get();
        $friendsAsFriend = $user->acceptedFriends()->with('department:id,name')->get();

        // Merge both collections and remove duplicates
        $allFriends = $friendsAsUser->merge($friendsAsFriend)->unique('id')->values();

        $pendingRequests = $user->pendingFriendRequests()->with('user.department:id,name')->get();
        $sentRequests = $user->sentFriendRequests()->with('friend.department:id,name')->get();

        return view('friends.index', compact('allFriends', 'pendingRequests', 'sentRequests'));
    }

    public function show(User $user): JsonResponse
    {
        $currentUserId = auth()->id();

        if ($currentUserId === $user->id) {
            return response()->json(['error' => 'Cannot view your own profile here.'], 400);
        }

        $isFriend = Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($currentUserId, $user) {
                $query->where(function ($inner) use ($currentUserId, $user) {
                    $inner->where('user_id', $currentUserId)
                        ->where('friend_id', $user->id);
                })->orWhere(function ($inner) use ($currentUserId, $user) {
                    $inner->where('user_id', $user->id)
                        ->where('friend_id', $currentUserId);
                });
            })
            ->exists();

        if (! $isFriend) {
            return response()->json(['error' => 'You are not friends with this user.'], 403);
        }

        $user->load('department:id,name');

        return response()->json([
            'friend' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'department' => $user->department?->name,
                'profile_picture_url' => $user->profile_picture ? $user->getProfilePictureUrl() : null,
                'initials' => $user->getInitials(),
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) ($request->get('q', $request->get('search', ''))));
        $currentUserId = auth()->id();

        if ($query === '') {
            return response()->json([]);
        }

        if (mb_strlen($query) < 1) {
            return response()->json([]);
        }

        $users = User::query()
            ->where('id', '!=', $currentUserId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_approved', true)
                    ->orWhereNull('is_approved');
            })
            ->where(function ($q) use ($query) {
                $this->applyFriendSearchFilters($q, $query);
            })
            ->with(['university:id,name', 'department:id,name'])
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'email', 'profile_picture', 'university_id', 'department_id', 'role']);

        $payload = $users->map(function (User $user) use ($currentUserId) {
            $friendship = Friendship::query()
                ->where(function ($q) use ($currentUserId, $user) {
                    $q->where('user_id', $currentUserId)->where('friend_id', $user->id);
                })
                ->orWhere(function ($q) use ($currentUserId, $user) {
                    $q->where('user_id', $user->id)->where('friend_id', $currentUserId);
                })
                ->first();

            $viewer = auth()->user();
            $canAnonymousChat = $viewer instanceof User && AnonymousChatEligibility::isEligibleTarget($viewer, $user);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'full_name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department?->name,
                'profile_picture' => $user->profile_picture,
                'profile_picture_url' => $user->getProfilePictureUrl(),
                'university' => $user->university?->name,
                'friendship_status' => $friendship ? $friendship->status : 'none',
                'friendship_id' => $friendship?->id,
                'can_anonymous_chat' => $canAnonymousChat,
                'anonymous_chat_token' => $canAnonymousChat ? Crypt::encryptString((string) $user->id) : null,
                'anonymous_chat_alias' => $canAnonymousChat && $viewer instanceof User
                    ? AnonymousChatAliasService::browseLabel($viewer->id, $user->id)
                    : null,
            ];
        })->values();

        return response()->json($payload);
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

        if (! $friendship) {
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

        if (! $friendship) {
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

        if (! $friendship) {
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

        if (! $friendship) {
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

    private function applyFriendSearchFilters($query, string $searchText): void
    {
        $normalized = mb_strtolower(trim($searchText));
        $likeTerm = '%'.$this->escapeLikeTerm($normalized).'%';
        $tokens = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $query->where(function ($outer) use ($likeTerm, $tokens) {
            $outer->where(function ($q) use ($likeTerm) {
                $this->applyFriendSearchFieldMatch($q, $likeTerm);
            });

            if (count($tokens) > 1) {
                $outer->orWhere(function ($q) use ($tokens) {
                    foreach ($tokens as $token) {
                        $tokenTerm = '%'.$this->escapeLikeTerm($token).'%';
                        $q->where(function ($sub) use ($tokenTerm) {
                            $this->applyFriendSearchFieldMatch($sub, $tokenTerm);
                        });
                    }
                });
            }
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<User>|\Illuminate\Database\Query\Builder  $query
     */
    private function applyFriendSearchFieldMatch($query, string $likeTerm): void
    {
        $query->where(function ($q) use ($likeTerm) {
            $q->whereRaw('LOWER(name) LIKE ?', [$likeTerm])
                ->orWhereRaw('LOWER(email) LIKE ?', [$likeTerm])
                ->orWhereRaw('LOWER(role) LIKE ?', [$likeTerm])
                ->orWhereHas('university', function ($uq) use ($likeTerm) {
                    $uq->whereRaw('LOWER(name) LIKE ?', [$likeTerm]);
                })
                ->orWhereHas('department', function ($dq) use ($likeTerm) {
                    $dq->whereRaw('LOWER(name) LIKE ?', [$likeTerm]);
                });
        });
    }

    private function escapeLikeTerm(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
}
