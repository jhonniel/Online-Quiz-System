<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfessionComment;
use App\Models\ConfessionPost;
use App\Models\ConfessionTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConfessionController extends Controller
{
    /**
     * Access controlled by admin.permission:confession middleware.
     */
    public function index(Request $request)
    {
        $posts = ConfessionPost::withCount('allComments')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.confession.index', compact('posts'));
    }

    public function dashboard()
    {
        // Auto-delete posts that are already due (0 likes, 0 comments, older than 7 days)
        $duePosts = ConfessionPost::eligibleForAutoDelete()->get();
        $autoDeletedCount = 0;
        foreach ($duePosts as $post) {
            if (!empty($post->image_path)) {
                try {
                    Storage::disk('digitalocean')->delete($post->image_path);
                } catch (\Throwable $e) {
                    // Ignore image cleanup issues; post record deletion should still proceed
                }
            }
            $post->delete();
            $autoDeletedCount++;
        }

        // Unique IPs that posted or commented
        $postIps = ConfessionPost::whereNotNull('ip_address')->distinct('ip_address')->pluck('ip_address');
        $commentIps = ConfessionComment::whereNotNull('ip_address')->distinct('ip_address')->pluck('ip_address');
        $allIps = $postIps->merge($commentIps)->unique()->values();
        $ipLogs = [];
        foreach ($allIps as $ip) {
            $postCount = ConfessionPost::where('ip_address', $ip)->count();
            $commentCount = ConfessionComment::where('ip_address', $ip)->count();
            $ipLogs[] = [
                'ip' => $ip,
                'posts' => $postCount,
                'comments' => $commentCount,
                'total' => $postCount + $commentCount,
            ];
        }
        usort($ipLogs, fn($a, $b) => $b['total'] <=> $a['total']);

        // Trending: most engagement (comments + likes)
        $trending = ConfessionPost::withCount('allComments')
            ->get()
            ->map(function ($post) {
                $post->engagement = ($post->all_comments_count ?? 0) + $post->upvotes_count + $post->downvotes_count;
                return $post;
            })
            ->sortByDesc('engagement')
            ->take(20)
            ->values();

        // Posts with no likes and no comments – will be auto-deleted 7 days after post date (ranking: oldest first)
        $scheduledForDeletion = ConfessionPost::scheduledForDeletion()
            ->orderBy('created_at')
            ->get();

        $stats = [
            'total_posts_all_time' => ConfessionPost::withTrashed()->count(),
            'active_posts' => ConfessionPost::count(),
            'total_comments' => ConfessionComment::count(),
            'unique_ips' => $allIps->count(),
        ];

        return view('admin.confession.dashboard', compact('ipLogs', 'trending', 'stats', 'scheduledForDeletion', 'autoDeletedCount'));
    }

    public function topics()
    {
        $topics = ConfessionTopic::orderByDesc('posts_count')->orderBy('name')->get();
        return view('admin.confession.topics', compact('topics'));
    }

    public function editTopic(ConfessionTopic $confession_topic)
    {
        return view('admin.confession.topics-edit', compact('confession_topic'));
    }

    public function updateTopic(Request $request, ConfessionTopic $confession_topic)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($request->name);
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Topic name cannot be empty.');
        }

        $slug = \Illuminate\Support\Str::slug($name);
        if (empty($slug)) {
            $slug = 'topic-' . \Illuminate\Support\Str::random(6);
        }

        // If slug changed, ensure it's unique
        if ($slug !== $confession_topic->slug && ConfessionTopic::where('slug', $slug)->exists()) {
            return redirect()->back()->withInput()->with('error', 'A topic with that name already exists.');
        }

        $confession_topic->update(['name' => $name, 'slug' => $slug]);

        return redirect()->to('/admin/confession/topics')->with('success', 'Topic updated.');
    }

    public function destroyTopic(ConfessionTopic $confession_topic)
    {
        $confession_topic->delete();

        return redirect()->to('/admin/confession/topics')->with('success', 'Topic deleted. Posts under this topic are now uncategorized.');
    }

    public function destroy(ConfessionPost $confession_post)
    {
        $confession_post->delete();

        return redirect()->back()->with('success', 'Post deleted.');
    }
}
