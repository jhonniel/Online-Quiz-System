<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NetworkGraphEdge;
use App\Models\NetworkGraphNode;
use App\Services\NetworkGraph\NetworkGraphBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NetworkGraphController extends Controller
{
    public function index()
    {
        return view('admin.system.network-graph', [
            'stats' => [
                'nodes' => NetworkGraphNode::query()->count(),
                'edges' => NetworkGraphEdge::query()->count(),
                'users' => NetworkGraphNode::query()->where('node_group', 'user')->count(),
                'displayed_nodes' => 0,
                'displayed_edges' => 0,
                'total_hits' => (int) NetworkGraphNode::query()->sum('hit_count'),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function graphData(NetworkGraphBuilder $builder): JsonResponse
    {
        $totalNodes = NetworkGraphNode::query()->count();
        $totalEdges = NetworkGraphEdge::query()->count();
        $totalUsers = NetworkGraphNode::query()->where('node_group', 'user')->count();

        // Cap payload size so the browser can render reliably on large datasets.
        $maxNodes = max(1, min($totalNodes, 500));
        $maxEdges = max(1, min($totalEdges, 1200));
        $maxUserNodes = max(1, min($totalUsers, 120));

        return response()->json($builder->toVisNetworkPayload(
            $maxNodes,
            $maxEdges,
            $maxUserNodes,
        ));
    }

    public function sync(Request $request, NetworkGraphBuilder $builder): RedirectResponse
    {
        $days = (int) $request->input('days', 14);
        $days = max(1, min($days, 90));

        $imported = $builder->syncHistorical($days);

        return back()->with('success', "Network graph synced from the last {$days} day(s) ({$imported} source records processed).");
    }

    public function clear(): RedirectResponse
    {
        NetworkGraphEdge::query()->delete();
        NetworkGraphNode::query()->delete();

        return back()->with('success', 'Network graph data cleared. New traffic will rebuild the graph automatically.');
    }
}
