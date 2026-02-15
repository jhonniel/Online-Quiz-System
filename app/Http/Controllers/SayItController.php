<?php

namespace App\Http\Controllers;

use App\Models\ConfessionComment;
use App\Models\ConfessionCommentVote;
use App\Models\ConfessionPost;
use App\Models\ConfessionPostVote;
use App\Services\ConfessionCodenameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SayItController extends Controller
{
    public function index()
    {
        $posts = ConfessionPost::withCount('allComments')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('say-it.index', compact('posts'));
    }

    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:10000',
            'image' => 'nullable|image|max:5120', // 5MB
        ], [
            'content.required_without' => 'Please write something or attach an image.',
        ]);

        if (empty(trim($validated['content'] ?? '')) && !$request->hasFile('image')) {
            return back()->withInput()->withErrors(['content' => 'Please write something or attach an image.']);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('confessions', 'digitalocean');
        }

        $codename = self::codenameForSession($request);

        ConfessionPost::create([
            'content' => $validated['content'] ?? '',
            'image_path' => $imagePath,
            'codename' => $codename,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect(url('/Say-it'))->with('success', 'Your confession was posted. You are ' . $codename . '.');
    }

    public function show(ConfessionPost $post)
    {
        $post->load(['comments.replies.replies' => function ($q) {
            $q->orderBy('created_at');
        }, 'comments.replies', 'comments' => function ($q) {
            $q->orderBy('created_at');
        }]);
        // Flatten for nested display: load all comments for this post ordered by created_at
        $allComments = $post->allComments()->orderBy('created_at')->get();

        return view('say-it.show', compact('post', 'allComments'));
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
