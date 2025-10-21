<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\ForumComment;
use App\Models\ForumLike;
use App\Models\ForumSave;
use App\Models\ForumShare;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index()
    {
        $threads = ForumThread::published()
            ->with(['admin', 'likes', 'saves', 'shares'])
            ->latest()
            ->paginate(10);

        // Get user's saved and liked threads
        $user = auth()->user();
        $savedThreadIds = $user->forumSaves()->pluck('thread_id')->toArray();
        $likedThreadIds = $user->forumLikes()
            ->where('likeable_type', ForumThread::class)
            ->pluck('likeable_id')
            ->toArray();

        return view('user.forum.index', compact('threads', 'savedThreadIds', 'likedThreadIds'));
    }

    public function show(ForumThread $forum)
    {
        // Only allow viewing published threads
        if (!$forum->is_published) {
            abort(404, 'Thread not found or not published.');
        }

        // Increment view count
        $forum->incrementViews();

        $forum->load([
            'admin',
            'topLevelComments.user',
            'topLevelComments.replies.user',
            'topLevelComments.likes',
            'likes.user'
        ]);

        // Get user's interaction status
        $user = auth()->user();
        $isLiked = $forum->isLikedBy($user);
        $isSaved = $forum->isSavedBy($user);

        return view('user.forum.show', compact('forum', 'isLiked', 'isSaved'));
    }

    public function like(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|exists:forum_threads,id',
        ]);

        $thread = ForumThread::findOrFail($request->thread_id);

        // Only allow liking published threads
        if (!$thread->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot like unpublished thread.'
            ], 403);
        }

        $user = auth()->user();

        $like = ForumLike::where('user_id', $user->id)
            ->where('likeable_type', ForumThread::class)
            ->where('likeable_id', $thread->id)
            ->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            ForumLike::create([
                'user_id' => $user->id,
                'likeable_type' => ForumThread::class,
                'likeable_id' => $thread->id,
            ]);
            $isLiked = true;
        }

        $thread->updateCounts();

        return response()->json([
            'success' => true,
            'is_liked' => $isLiked,
            'likes_count' => $thread->fresh()->likes_count
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|exists:forum_threads,id',
        ]);

        $thread = ForumThread::findOrFail($request->thread_id);

        // Only allow saving published threads
        if (!$thread->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot save unpublished thread.'
            ], 403);
        }

        $user = auth()->user();

        $save = ForumSave::where('user_id', $user->id)
            ->where('thread_id', $thread->id)
            ->first();

        if ($save) {
            $save->delete();
            $isSaved = false;
        } else {
            ForumSave::create([
                'user_id' => $user->id,
                'thread_id' => $thread->id,
            ]);
            $isSaved = true;
        }

        $thread->updateCounts();

        return response()->json([
            'success' => true,
            'is_saved' => $isSaved,
            'saves_count' => $thread->fresh()->saves_count
        ]);
    }

    public function share(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|exists:forum_threads,id',
            'message' => 'nullable|string|max:500',
        ]);

        $thread = ForumThread::findOrFail($request->thread_id);

        // Only allow sharing published threads
        if (!$thread->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot share unpublished thread.'
            ], 403);
        }

        $user = auth()->user();

        ForumShare::create([
            'user_id' => $user->id,
            'thread_id' => $thread->id,
            'message' => $request->message,
        ]);

        $thread->updateCounts();

        return response()->json([
            'success' => true,
            'message' => 'Thread shared successfully!',
            'shares_count' => $thread->fresh()->shares_count
        ]);
    }

    public function comment(Request $request)
    {
        $request->validate([
            'thread_id' => 'required|exists:forum_threads,id',
            'content' => 'required|string|min:1|max:1000',
            'parent_id' => 'nullable|exists:forum_comments,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        $thread = ForumThread::findOrFail($request->thread_id);

        // Only allow commenting on published threads
        if (!$thread->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot comment on unpublished thread.'
            ], 403);
        }

        $user = auth()->user();

        $commentData = [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '_' . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('comment-images', $imageName, 'public');
            $commentData['image'] = $imagePath;
        }

        $comment = ForumComment::create($commentData);

        $thread->updateCounts();

        // If it's a reply, update parent comment's replies count and notify the parent commenter
        if ($request->parent_id) {
            $parentComment = ForumComment::findOrFail($request->parent_id);
            $parentComment->updateCounts();

            // Send notification to the parent commenter (if not the same user)
            if ($parentComment->user_id !== $user->id) {
                \App\Models\Notification::createForumReplyNotification(
                    $parentComment->user_id,
                    $user->id,
                    $user->name,
                    $thread->id,
                    $thread->title,
                    $request->content
                );
            }
        }

        // Handle mentions in the comment content
        $this->handleMentions($request->content, $user, $thread, $comment);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully!',
            'comment' => $comment->load('user'),
            'comments_count' => $thread->fresh()->comments_count
        ]);
    }

    public function likeComment(Request $request)
    {
        $request->validate([
            'comment_id' => 'required|exists:forum_comments,id',
        ]);

        $comment = ForumComment::findOrFail($request->comment_id);
        $user = auth()->user();

        $like = ForumLike::where('user_id', $user->id)
            ->where('likeable_type', ForumComment::class)
            ->where('likeable_id', $comment->id)
            ->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            ForumLike::create([
                'user_id' => $user->id,
                'likeable_type' => ForumComment::class,
                'likeable_id' => $comment->id,
            ]);
            $isLiked = true;
        }

        $comment->updateCounts();

        return response()->json([
            'success' => true,
            'is_liked' => $isLiked,
            'likes_count' => $comment->fresh()->likes_count
        ]);
    }

    public function saved()
    {
        $savedThreads = auth()->user()->forumSaves()
            ->whereHas('thread', function($query) {
                $query->where('is_published', true);
            })
            ->with(['thread.admin', 'thread.likes', 'thread.saves', 'thread.shares'])
            ->latest()
            ->paginate(10);

        return view('user.forum.saved', compact('savedThreads'));
    }

    /**
     * Handle mentions in comment content and send notifications
     */
    private function handleMentions($content, $sender, $thread, $comment)
    {
        // Extract mentions from content using regex
        preg_match_all('/@(\w+)/', $content, $matches);

        if (empty($matches[1])) {
            return;
        }

        $mentionedUsernames = array_unique($matches[1]);

        foreach ($mentionedUsernames as $username) {
            // Find user by name (case insensitive)
            $mentionedUser = \App\Models\User::whereRaw('LOWER(name) = ?', [strtolower($username)])->first();

            if ($mentionedUser && $mentionedUser->id !== $sender->id) {
                // Send mention notification
                \App\Models\Notification::createForumMentionNotification(
                    $mentionedUser->id,
                    $sender->id,
                    $sender->name,
                    $thread->id,
                    $thread->title,
                    $content
                );
            }
        }
    }
}
