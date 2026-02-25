<?php

namespace App\Http\Controllers;

use App\Models\ConfessionComment;
use App\Models\ConfessionCommentVote;
use App\Models\ConfessionHashtag;
use App\Models\ConfessionPost;
use App\Models\ConfessionPostVote;
use App\Models\ConfessionTopic;
use App\Services\ConfessionCodenameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SayItController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'recent');
        $topicSlug = $request->get('topic');
        $hashtagSlug = $request->get('hashtag');

        $query = ConfessionPost::withCount('allComments')->with(['latestComment', 'topic']);

        if ($topicSlug) {
            $topic = ConfessionTopic::where('slug', $topicSlug)->first();
            if ($topic) {
                $query->where('confession_topic_id', $topic->id);
                $sort = 'popular';
            }
        }
        if ($hashtagSlug) {
            $query->whereHas('hashtags', fn ($q) => $q->where('confession_hashtags.slug', $hashtagSlug));
            $sort = 'popular';
        }

        // Single most popular post for featured slot at top (score = net votes + comment count)
        $mostPopularPost = ConfessionPost::withCount('allComments')
            ->with(['latestComment', 'topic'])
            ->orderByRaw(
                '(confession_posts.upvotes_count - confession_posts.downvotes_count) + ' .
                '(SELECT COUNT(*) FROM confession_comments WHERE confession_comments.confession_post_id = confession_posts.id) DESC'
            )
            ->orderByDesc('created_at')
            ->first();

        if ($sort === 'popular') {
            $query->orderByRaw(
                '(confession_posts.upvotes_count - confession_posts.downvotes_count) + ' .
                '(SELECT COUNT(*) FROM confession_comments WHERE confession_comments.confession_post_id = confession_posts.id) DESC'
            )->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
            if ($mostPopularPost) {
                $query->where('confession_posts.id', '!=', $mostPopularPost->id);
            }
        }

        $posts = $query->paginate(15)->withQueryString();

        $recentPosts = ConfessionPost::withCount('allComments')
            ->with(['latestComment', 'topic'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $topics = ConfessionTopic::orderByDesc('posts_count')->orderBy('name')->get(['id', 'name', 'slug', 'posts_count']);
        $topTopics = $topics->take(10);
        $topHashtags = ConfessionHashtag::orderByDesc('posts_count')->limit(10)->get(['id', 'name', 'slug', 'posts_count']);

        if ($request->get('lazy') || $request->ajax()) {
            $sessionCodename = self::codenameForSession($request);
            $html = view('say-it.partials.post-cards', ['posts' => $posts, 'sessionCodename' => $sessionCodename])->render();
            return response()->json([
                'html' => $html,
                'next_page_url' => $posts->hasMorePages() ? $posts->nextPageUrl() : null,
                'has_more' => $posts->hasMorePages(),
            ]);
        }

        $sessionCodename = self::codenameForSession($request);
        return view('say-it.index', compact('posts', 'recentPosts', 'sort', 'topics', 'topTopics', 'topHashtags', 'topicSlug', 'hashtagSlug', 'sessionCodename', 'mostPopularPost'));
    }

    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'nullable|exists:confession_topics,id',
            'topic_name' => 'nullable|string|max:100',
            'content' => 'nullable|string|max:10000',
            'text_size' => 'nullable|in:normal,medium,large',
            'image' => [
                'nullable',
                'file',
                'max:5120', // 5MB
                'mimes:jpeg,jpg,png,gif,webp',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp',
            ],
        ], [
            'content.required_without' => 'Please write something or attach an image.',
            'image.image' => 'The photo must be an image (JPEG, PNG, GIF, or WebP).',
            'image.mimes' => 'The photo must be an image (JPEG, PNG, GIF, or WebP).',
            'image.mimetypes' => 'The photo must be an image (JPEG, PNG, GIF, or WebP).',
            'image.max' => 'The photo may not be larger than 5 MB.',
        ]);

        $topicId = $request->input('topic_id');
        $topicName = trim((string) $request->input('topic_name', ''));
        if (! $topicId && $topicName === '') {
            return back()->withInput()->withErrors(['topic' => 'Please select or enter a topic. BUGO KAYKA OYYY!']);
        }

        if ($topicName !== '') {
            $topic = ConfessionTopic::findOrCreateByName($topicName);
        } else {
            $topic = ConfessionTopic::findOrFail($topicId);
        }

        if (empty(trim($validated['content'] ?? '')) && !$request->hasFile('image')) {
            return back()->withInput()->withErrors(['content' => 'Please write something or attach an image.']);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (! config('filesystems.disks.digitalocean.key') || ! config('filesystems.disks.digitalocean.secret')) {
                return back()->withInput()->withErrors(['image' => 'Photo upload is not configured. Please contact the administrator.']);
            }
            try {
                $imagePath = $file->store('confessions', 'digitalocean');
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['image' => 'Photo could not be uploaded to storage. Please try again.']);
            }
        }

        $codename = self::codenameForSession($request);

        $post = ConfessionPost::create([
            'confession_topic_id' => $topic->id,
            'content' => $validated['content'] ?? '',
            'text_size' => $validated['text_size'] ?? 'normal',
            'image_path' => $imagePath,
            'codename' => $codename,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $topic->increment('posts_count');

        $content = $validated['content'] ?? '';
        $slugs = ConfessionHashtag::extractFromContent($content);
        $hashtagIds = [];
        foreach ($slugs as $slug) {
            $hashtag = ConfessionHashtag::findOrCreateBySlug($slug);
            $hashtagIds[$hashtag->id] = [];
            $hashtag->increment('posts_count');
        }
        $post->hashtags()->sync(array_keys($hashtagIds));

        return redirect(url('/Say-it'))->with('success', 'Your confession was posted. You are ' . $codename . '.');
    }

    public function show(ConfessionPost $post)
    {
        $post->load('topic');
        $allComments = $post->allComments()->orderBy('created_at')->get();
        $topTopics = ConfessionTopic::orderByDesc('posts_count')->limit(10)->get(['id', 'name', 'slug', 'posts_count']);
        $topHashtags = ConfessionHashtag::orderByDesc('posts_count')->limit(10)->get(['id', 'name', 'slug', 'posts_count']);

        return view('say-it.show', compact('post', 'allComments', 'topTopics', 'topHashtags'));
    }

    public function storeComment(Request $request)
    {
        $validated = $request->validate([
            'confession_post_id' => 'required|exists:confession_posts,id',
            'parent_id' => 'nullable|exists:confession_comments,id',
            'content' => 'required|string|max:5000',
        ]);

        $codename = self::codenameForSession($request);

        ConfessionComment::create([
            'confession_post_id' => $validated['confession_post_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'codename' => $codename,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->to(url('/Say-it/' . $validated['confession_post_id']) . '#comments')->with('success', 'Comment posted as ' . $codename . '.');
    }

    public function vote(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['post', 'comment'])],
            'id' => 'required|integer',
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        $voteValue = $validated['direction'] === 'up' ? 1 : -1;
        $ip = $request->ip();

        if ($validated['type'] === 'post') {
            $post = ConfessionPost::findOrFail($validated['id']);
            $existing = ConfessionPostVote::where('confession_post_id', $post->id)->where('ip_address', $ip)->first();
            if ($existing) {
                if ($existing->vote === $voteValue) {
                    $existing->delete();
                    $post->decrement($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
                } else {
                    $existing->update(['vote' => $voteValue]);
                    $post->increment($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
                    $post->decrement($voteValue === 1 ? 'downvotes_count' : 'upvotes_count');
                }
            } else {
                ConfessionPostVote::create([
                    'confession_post_id' => $post->id,
                    'ip_address' => $ip,
                    'vote' => $voteValue,
                ]);
                $post->increment($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
            }
            return response()->json(['score' => $post->fresh()->upvotes_count - $post->fresh()->downvotes_count]);
        }

        $comment = ConfessionComment::findOrFail($validated['id']);
        $existing = ConfessionCommentVote::where('confession_comment_id', $comment->id)->where('ip_address', $ip)->first();
        if ($existing) {
            if ($existing->vote === $voteValue) {
                $existing->delete();
                $comment->decrement($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
            } else {
                $existing->update(['vote' => $voteValue]);
                $comment->increment($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
                $comment->decrement($voteValue === 1 ? 'downvotes_count' : 'upvotes_count');
            }
        } else {
            ConfessionCommentVote::create([
                'confession_comment_id' => $comment->id,
                'ip_address' => $ip,
                'vote' => $voteValue,
            ]);
            $comment->increment($voteValue === 1 ? 'upvotes_count' : 'downvotes_count');
        }
        return response()->json(['score' => $comment->fresh()->upvotes_count - $comment->fresh()->downvotes_count]);
    }

    /**
     * Get the codename for the current session. Same session keeps the same codename;
     * when the session is no longer active, a new unique codename is generated.
     */
    public function destroyPost(Request $request, ConfessionPost $post)
    {
        // Check if post was created within 50 seconds
        $secondsSinceCreation = now()->diffInSeconds($post->created_at);
        if ($secondsSinceCreation > 50) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your post within 50 seconds of posting.'
            ], 403);
        }

        // Verify ownership: check codename and IP address
        $sessionCodename = $request->session()->get('sayit_codename');
        $requestIp = $request->ip();

        if ($post->codename !== $sessionCodename || $post->ip_address !== $requestIp) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own posts.'
            ], 403);
        }

        // Delete associated image if exists
        if ($post->image_path) {
            try {
                Storage::disk('digitalocean')->delete($post->image_path);
            } catch (\Exception $e) {
                // Log but don't fail deletion
                \Log::warning('Failed to delete confession post image: ' . $e->getMessage());
            }
        }

        // Decrement topic count
        if ($post->confession_topic_id) {
            $post->topic()->decrement('posts_count');
        }

        // Decrement hashtag counts
        $post->load('hashtags');
        foreach ($post->hashtags as $hashtag) {
            $hashtag->decrement('posts_count');
        }

        // Delete the post (cascade will handle related votes and comments)
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully.'
        ]);
    }

    protected static function codenameForSession(Request $request): string
    {
        if ($request->session()->has('sayit_codename')) {
            return $request->session()->get('sayit_codename');
        }
        $codename = ConfessionCodenameService::generate();
        $request->session()->put('sayit_codename', $codename);
        return $codename;
    }
}
