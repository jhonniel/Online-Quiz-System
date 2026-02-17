<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $query = ForumThread::with('admin')->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search)
                        ->orWhere('admin_id', (int) $search);
                }

                $q->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('admin', function ($aq) use ($search) {
                        $aq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $threads = $query->paginate($perPage)->appends($request->query());

        return view('admin.forum.index', compact('threads', 'search', 'perPage'));
    }

    public function create()
    {
        return view('admin.forum.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_published' => 'boolean',
            'is_pinned' => 'boolean',
        ]);

        $data = [
            'admin_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'is_published' => $request->boolean('is_published', true),
            'is_pinned' => $request->boolean('is_pinned', false),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('forum-images', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        ForumThread::create($data);

        return redirect('/admin/forum')
            ->with('success', 'Forum thread created successfully!');
    }

    public function show(ForumThread $forum)
    {
        $forum->load([
            'admin',
            'topLevelComments.user',
            'topLevelComments.replies.user',
            'topLevelComments.likes',
            'likes.user'
        ]);
        return view('admin.forum.show', compact('forum'));
    }

    public function edit(ForumThread $forum)
    {
        return view('admin.forum.edit', compact('forum'));
    }

    public function update(Request $request, ForumThread $forum)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_published' => 'boolean',
            'is_pinned' => 'boolean',
        ]);

        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'is_published' => $request->boolean('is_published', true),
            'is_pinned' => $request->boolean('is_pinned', false),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($forum->image) {
                Storage::disk('public')->delete($forum->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('forum-images', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $forum->update($data);

        return redirect('/admin/forum')
            ->with('success', 'Forum thread updated successfully!');
    }

    public function destroy(ForumThread $forum)
    {
        // Delete image if exists
        if ($forum->image) {
            Storage::disk('public')->delete($forum->image);
        }

        $forum->delete();

        return redirect('/admin/forum')
            ->with('success', 'Forum thread deleted successfully!');
    }

    public function togglePublish(ForumThread $forum)
    {
        $forum->update(['is_published' => !$forum->is_published]);

        $status = $forum->is_published ? 'published' : 'unpublished';
        return response()->json([
            'success' => true,
            'message' => "Thread {$status} successfully!",
            'is_published' => $forum->is_published
        ]);
    }

    public function togglePin(ForumThread $forum)
    {
        $forum->update(['is_pinned' => !$forum->is_pinned]);

        $status = $forum->is_pinned ? 'pinned' : 'unpinned';
        return response()->json([
            'success' => true,
            'message' => "Thread {$status} successfully!",
            'is_pinned' => $forum->is_pinned
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
        $user = Auth::user();

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

        $comment = \App\Models\ForumComment::create($commentData);

        $thread->updateCounts();

        // If it's a reply, update parent comment's replies count and notify the parent commenter
        if ($request->parent_id) {
            $parentComment = \App\Models\ForumComment::findOrFail($request->parent_id);
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

        $comment = \App\Models\ForumComment::findOrFail($request->comment_id);
        $user = Auth::user();

        $like = \App\Models\ForumLike::where('user_id', $user->id)
            ->where('likeable_type', \App\Models\ForumComment::class)
            ->where('likeable_id', $comment->id)
            ->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            \App\Models\ForumLike::create([
                'user_id' => $user->id,
                'likeable_type' => \App\Models\ForumComment::class,
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
