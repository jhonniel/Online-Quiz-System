<?php

namespace App\Services\NetworkGraph;

use App\Models\ApiEndpointMetric;
use App\Models\ErrorLog;
use App\Models\NetworkGraphEdge;
use App\Models\NetworkGraphNode;
use App\Models\User;
use App\Models\UserActivity;
class NetworkGraphBuilder
{
    public function __construct(
        private readonly NetworkGraphRecorder $recorder
    ) {}

    public function syncHistorical(int $days = 14): int
    {
        $since = now()->subDays($days);
        $imported = 0;

        foreach (ApiEndpointMetric::query()->where('updated_at', '>=', $since)->cursor() as $metric) {
            $routeKey = (string) $metric->route_key;
            $module = $this->moduleFromUri((string) $metric->uri);
            $apiKey = 'api:'.$this->slug($routeKey);

            $this->recorder->touchNode('hub:system', 'System Core', 'hub');
            $this->recorder->touchNode('module:'.$module, $this->moduleLabel($module), 'module');
            $this->recorder->touchNode($apiKey, $routeKey, 'api');

            $hits = max((int) $metric->request_count, 1);
            for ($i = 0; $i < min($hits, 50); $i++) {
                $this->recorder->touchEdge('hub:system', 'module:'.$module);
                $this->recorder->touchEdge('module:'.$module, $apiKey);
            }

            if ((int) $metric->failure_count > 0) {
                $errorKey = 'error:metric:'.$apiKey;
                $this->recorder->touchNode($errorKey, 'Failures · '.$routeKey, 'error');
                $this->recorder->touchEdge($apiKey, $errorKey, 'error');
            }

            $imported++;
        }

        foreach (ErrorLog::query()->where('created_at', '>=', $since)->orderByDesc('id')->limit(500)->cursor() as $error) {
            $this->recorder->recordError([
                'status_code' => (int) $error->status_code,
                'path' => (string) $error->path,
                'method' => (string) $error->method,
                'user_id' => $error->user_id,
            ]);
            $imported++;
        }

        foreach (UserActivity::query()->where('created_at', '>=', $since)->orderByDesc('id')->limit(1000)->cursor() as $activity) {
            $this->recorder->recordUserActivity($activity);
            $imported++;
        }

        return $imported;
    }

    /**
     * Smaller graph focused on users, hub, modules — fewer API/status nodes (better performance).
     *
     * @return array{nodes: list<array<string, mixed>>, edges: list<array<string, mixed>>, users: list<array<string, mixed>>, stats: array<string, int|float|string>}
     */
    public function toCompactPayload(): array
    {
        $users = NetworkGraphNode::query()
            ->where('node_group', 'user')
            ->orderByDesc('hit_count')
            ->limit(20)
            ->get();

        $pinned = NetworkGraphNode::query()
            ->where('node_key', 'hub:system')
            ->get();

        $roles = NetworkGraphNode::query()
            ->where('node_group', 'role')
            ->orderByDesc('hit_count')
            ->limit(8)
            ->get();

        $modules = NetworkGraphNode::query()
            ->where('node_group', 'module')
            ->orderByDesc('hit_count')
            ->limit(12)
            ->get();

        $apis = NetworkGraphNode::query()
            ->where('node_group', 'api')
            ->orderByDesc('hit_count')
            ->limit(10)
            ->get();

        $errors = NetworkGraphNode::query()
            ->where('node_group', 'error')
            ->orderByDesc('hit_count')
            ->limit(5)
            ->get();

        $nodes = $users
            ->concat($pinned)
            ->concat($roles)
            ->concat($modules)
            ->concat($apis)
            ->concat($errors)
            ->unique('node_key')
            ->values();

        $nodeIds = $nodes->pluck('node_key')->all();

        $edges = NetworkGraphEdge::query()
            ->whereIn('source_node_key', $nodeIds)
            ->whereIn('target_node_key', $nodeIds)
            ->orderByRaw("CASE WHEN edge_type = 'user_access' THEN 0 ELSE 1 END")
            ->orderByDesc('weight')
            ->limit(55)
            ->get();

        return $this->formatPayload($nodes, $edges);
    }

    /**
     * @return array{nodes: list<array<string, mixed>>, edges: list<array<string, mixed>>, users: list<array<string, mixed>>, activity_logs: list<array<string, mixed>>, stats: array<string, int|float|string>}
     */
    public function toVisNetworkPayload(int $maxNodes = 220, int $maxEdges = 450, int $maxUserNodes = 50): array
    {
        $userNodes = NetworkGraphNode::query()
            ->where('node_group', 'user')
            ->orderByDesc('hit_count')
            ->limit($maxUserNodes)
            ->get();

        $remaining = max(0, $maxNodes - $userNodes->count());
        $otherNodes = NetworkGraphNode::query()
            ->where('node_group', '!=', 'user')
            ->orderByDesc('hit_count')
            ->limit($remaining)
            ->get();

        $nodes = $userNodes->concat($otherNodes);

        $nodeIds = $nodes->pluck('node_key')->all();

        $edgesQuery = NetworkGraphEdge::query()
            ->whereIn('source_node_key', $nodeIds)
            ->whereIn('target_node_key', $nodeIds)
            ->orderByDesc('weight')
            ->limit($maxEdges);

        return $this->formatPayload($nodes, $edgesQuery->get());
    }

    /**
     * @param  \Illuminate\Support\Collection<int, NetworkGraphNode>  $nodes
     * @param  \Illuminate\Support\Collection<int, NetworkGraphEdge>  $edges
     * @return array{nodes: list<array<string, mixed>>, edges: list<array<string, mixed>>, users: list<array<string, mixed>>, activity_logs: list<array<string, mixed>>, stats: array<string, int|float|string>}
     */
    private function formatPayload($nodes, $edges): array
    {
        $userNodeIds = $nodes
            ->filter(fn (NetworkGraphNode $node) => $node->node_group === 'user')
            ->map(fn (NetworkGraphNode $node) => (int) ($node->meta['user_id'] ?? 0))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $profileByUserId = User::query()
            ->whereIn('id', $userNodeIds)
            ->get(['id', 'profile_picture'])
            ->mapWithKeys(fn (User $user) => [$user->id => $user->getProfilePictureUrl()])
            ->all();

        $visNodes = $nodes->map(function (NetworkGraphNode $node) use ($profileByUserId) {
            $isUser = $node->node_group === 'user';
            $userId = (int) ($node->meta['user_id'] ?? 0);
            $profileUrl = $isUser ? ($profileByUserId[$userId] ?? '') : '';
            $avatarFallback = $isUser ? $this->makeAvatarFallbackDataUri($node->label, $node->color ?? '#94a3b8') : '';

            return [
                'id' => $node->node_key,
                'label' => $this->shortLabel($node->label, $isUser ? 56 : 48),
                'group' => $node->node_group,
                'value' => $node->hit_count,
                'color' => $node->color ?? '#94a3b8',
                'shape' => $isUser ? 'circularImage' : 'dot',
                'image' => $isUser ? ($profileUrl !== '' ? $profileUrl : $avatarFallback) : null,
            ];
        })->values()->all();

        $visEdges = $edges->map(function (NetworkGraphEdge $edge) {
            $isUserAccess = $edge->edge_type === 'user_access'
                || str_starts_with($edge->source_node_key, 'user:')
                || str_starts_with($edge->source_node_key, 'guest:');

            return [
                'from' => $edge->source_node_key,
                'to' => $edge->target_node_key,
                'value' => max(1, (int) $edge->weight),
                'dashes' => $isUserAccess,
            ];
        })->values()->all();

        return [
            'nodes' => $visNodes,
            'edges' => $visEdges,
            'users' => $this->buildUsersList(30),
            'activity_logs' => $this->buildRecentActivityLogs(40),
            'stats' => [
                'nodes' => NetworkGraphNode::query()->count(),
                'edges' => NetworkGraphEdge::query()->count(),
                'users' => NetworkGraphNode::query()->where('node_group', 'user')->count(),
                'displayed_nodes' => count($visNodes),
                'displayed_edges' => count($visEdges),
                'total_hits' => (int) NetworkGraphNode::query()->sum('hit_count'),
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildUsersList(int $limit): array
    {
        $userNodes = NetworkGraphNode::query()
            ->where('node_group', 'user')
            ->orderByDesc('hit_count')
            ->limit($limit)
            ->get();

        if ($userNodes->isEmpty()) {
            return [];
        }

        $userKeys = $userNodes->pluck('node_key')->all();
        $accessCounts = NetworkGraphEdge::query()
            ->whereIn('source_node_key', $userKeys)
            ->where('edge_type', 'user_access')
            ->selectRaw('source_node_key, COUNT(DISTINCT target_node_key) as access_targets')
            ->groupBy('source_node_key')
            ->pluck('access_targets', 'source_node_key');

        return $userNodes->map(function (NetworkGraphNode $node) use ($accessCounts) {
            $meta = $node->meta ?? [];

            return [
                'node_key' => $node->node_key,
                'label' => $node->label,
                'role' => (string) ($meta['role'] ?? 'unknown'),
                'user_id' => $meta['user_id'] ?? null,
                'email' => (string) ($meta['email'] ?? ''),
                'hits' => (int) $node->hit_count,
                'access_targets' => (int) ($accessCounts[$node->node_key] ?? 0),
                'last_seen_at' => $node->last_seen_at?->toIso8601String(),
            ];
        })->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildRecentActivityLogs(int $limit): array
    {
        return UserActivity::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (UserActivity $activity) {
                $user = $activity->user;

                return [
                    'id' => (int) $activity->id,
                    'user_id' => $activity->user_id ? (int) $activity->user_id : null,
                    'user_name' => $user?->name ?: 'Guest',
                    'user_role' => $user?->role ?: 'guest',
                    'activity_type' => (string) $activity->activity_type,
                    'action' => (string) ($activity->action ?: ''),
                    'page_url' => (string) ($activity->page_url ?: ''),
                    'created_at' => $activity->created_at?->toIso8601String(),
                    'created_at_human' => $activity->created_at?->diffForHumans(),
                ];
            })
            ->values()
            ->all();
    }

    private function moduleFromUri(string $uri): string
    {
        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));

        if ($segments === []) {
            return 'public';
        }

        if ($segments[0] === 'admin') {
            return 'admin:'.($segments[1] ?? 'dashboard');
        }

        return $segments[0];
    }

    private function moduleLabel(string $module): string
    {
        return str($module)->replace(['admin:', '_', '-'], ['', ' ', ' '])->title()->toString();
    }

    private function slug(string $value): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '_', strtolower($value)) ?? 'node';

        return trim($slug, '_') !== '' ? trim($slug, '_') : 'node';
    }

    private function shortLabel(string $label, int $max): string
    {
        return strlen($label) > $max ? substr($label, 0, $max - 1).'…' : $label;
    }

    private function makeAvatarFallbackDataUri(string $name, string $color): string
    {
        $initials = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        if ($initials === '') {
            $initials = 'U';
        }

        $bg = preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#64748b';
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">
  <circle cx="64" cy="64" r="64" fill="{$bg}" />
  <text x="50%" y="53%" dominant-baseline="middle" text-anchor="middle"
        font-family="Arial, sans-serif" font-size="44" font-weight="700" fill="#ffffff">{$initials}</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
