<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfessionComment;
use App\Models\ConfessionPost;
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

        $stats = [
            'total_posts' => ConfessionPost::count(),
            'total_comments' => ConfessionComment::count(),
            'unique_ips' => $allIps->count(),
        ];

        return view('admin.confession.dashboard', compact('ipLogs', 'trending', 'stats'));
    }
}
