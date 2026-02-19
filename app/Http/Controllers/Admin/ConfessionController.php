<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfessionComment;
use App\Models\ConfessionPost;
use App\Models\ConfessionTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfessionController extends Controller
{
    private function ensureFullAccess(): void
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Full admin access required to view Confession.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureFullAccess();

        $posts = ConfessionPost::withCount('allComments')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.confession.index', compact('posts'));
    }

    public function dashboard()
    {
        $this->ensureFullAccess();

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
            'total_posts' => ConfessionPost::count(),
            'total_comments' => ConfessionComment::count(),
            'unique_ips' => $allIps->count(),
        ];

        return view('admin.confession.dashboard', compact('ipLogs', 'trending', 'stats', 'scheduledForDeletion'));
    }

    public function topics()
    {
        $this->ensureFullAccess();
        $topics = ConfessionTopic::orderByDesc('posts_count')->orderBy('name')->get();
        return view('admin.confession.topics', compact('topics'));
    }

    public function editTopic(ConfessionTopic $confession_topic)
    {
        $this->ensureFullAccess();
        return view('admin.confession.topics-edit', compact('confession_topic'));
    }

    public function updateTopic(Request $request, ConfessionTopic $confession_topic)
    {
        $this->ensureFullAccess();

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
        $this->ensureFullAccess();

        $confession_topic->delete();

        return redirect()->to('/admin/confession/topics')->with('success', 'Topic deleted. Posts under this topic are now uncategorized.');
    }

    public function destroy(ConfessionPost $confession_post)
    {
        $this->ensureFullAccess();

        $confession_post->delete();

        return redirect()->back()->with('success', 'Post deleted.');
    }
}
