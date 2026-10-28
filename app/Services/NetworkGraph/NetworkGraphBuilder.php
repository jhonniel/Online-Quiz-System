<?php

namespace App\Services\NetworkGraph;

use App\Models\ApiEndpointMetric;
use App\Models\ErrorLog;
use App\Models\HiringApplication;
use App\Models\NetworkGraphEdge;
use App\Models\NetworkGraphNode;
use App\Models\QuizAttempt;
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
                'error_log_id' => (int) $error->id,
            ]);
            $imported++;
        }

        foreach (UserActivity::query()->where('created_at', '>=', $since)->orderByDesc('id')->limit(1000)->cursor() as $activity) {
            $this->recorder->recordUserActivity($activity);
            $imported++;
        }

        foreach (QuizAttempt::query()->where('created_at', '>=', $since)->with(['quiz', 'user'])->orderByDesc('id')->limit(3000)->cursor() as $attempt) {
            $quizId = (int) ($attempt->quiz_id ?? 0);
            if ($quizId <= 0) {
                continue;
            }

            $quizTitle = trim((string) ($attempt->quiz?->title ?? 'Quiz #'.$quizId));
            $quizKey = 'quiz:'.$quizId;

            $this->recorder->touchNode($quizKey, $quizTitle, 'quiz', '#22c55e', [
                'quiz_id' => $quizId,
            ]);
            $this->recorder->touchNode('quiz:domain', 'Quizzes', 'quiz', '#22c55e');
            $this->recorder->touchEdge('quiz:domain', $quizKey, 'domain');

            if ($attempt->user) {
                $role = match ((string) $attempt->user->role) {
                    'student', 'employee', 'teacher', 'admin' => (string) $attempt->user->role,
                    default => 'other',
                };
                $this->recorder->linkUserToAccess($attempt->user, $role, 'quiz', $quizKey);
                $this->recorder->touchEdge('user:'.$attempt->user->id, $quizKey, 'quiz_attempt');
            }

            $imported++;
        }

        foreach (HiringApplication::query()->where('created_at', '>=', $since)->with(['hiringPosition', 'user'])->orderByDesc('id')->limit(2000)->cursor() as $application) {
            $positionName = trim((string) ($application->hiringPosition?->title ?: $application->position_applied ?: 'Unspecified Position'));
            $positionKey = 'position:'.$this->slug($positionName);
            $applicationKey = 'application:'.$application->id;

            $applicantLabel = trim($application->full_name) !== '' ? $application->full_name : ('Applicant #'.$application->id);
            $this->recorder->touchNode('application:domain', 'Applications', 'application', '#06b6d4');
            $this->recorder->touchNode($applicationKey, $applicantLabel, 'application', '#06b6d4', [
                'application_id' => (int) $application->id,
                'status' => (string) $application->status,
                'email' => (string) $application->email,
            ]);
            $this->recorder->touchNode($positionKey, $positionName, 'position', '#0f766e');

            $this->recorder->touchEdge('application:domain', $applicationKey, 'application');
            $this->recorder->touchEdge($applicationKey, $positionKey, 'applied_position');
            $this->recorder->touchEdge('application:domain', $positionKey, 'application');

            if ($application->user) {
                $this->recorder->linkUserToAccess($application->user, (string) ($application->user->role ?: 'applicant'), 'hiring', $applicationKey);
                $this->recorder->touchEdge('user:'.$application->user->id, $positionKey, 'applied_position');
            } else {
                $guestKey = 'guest:applicant:'.$application->id;
                $this->recorder->linkGuestToAccess($guestKey, $applicantLabel.' (Applicant)', 'hiring', $applicationKey);
                $this->recorder->touchEdge($guestKey, $positionKey, 'applied_position');
            }

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

        $nodes = $this->ensureAnchorNodes(
            $users
                ->concat($pinned)
                ->concat($roles)
                ->concat($modules)
                ->concat($apis)
                ->concat($errors)
                ->unique('node_key')
                ->values()
        );

        $nodeIds = $nodes->pluck('node_key')->all();

        $edges = $this->selectConnectedEdges($nodeIds, 55);

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

        $nodes = $this->ensureAnchorNodes(
            $userNodes->concat($this->collectSupportingGraphNodes($maxNodes - $userNodes->count()))
        );

        $nodeIds = $nodes->pluck('node_key')->all();

        $edges = $this->selectConnectedEdges($nodeIds, $maxEdges);

        return $this->formatPayload($nodes, $edges);
    }

    /**
     * Pull error, log, and infrastructure nodes so activity/error trails stay visible in the graph.
     *
     * @return \Illuminate\Support\Collection<int, NetworkGraphNode>
     */
    private function collectSupportingGraphNodes(int $budget)
    {
        if ($budget <= 0) {
            return collect();
        }

        $nodes = collect();
        $take = function (string $group, int $limit) use (&$nodes, &$budget) {
            if ($budget <= 0 || $limit <= 0) {
                return;
            }
            $limit = min($limit, $budget);
            $chunk = NetworkGraphNode::query()
                ->where('node_group', $group)
                ->orderByDesc('last_seen_at')
                ->orderByDesc('hit_count')
                ->limit($limit)
                ->get();
            $nodes = $nodes->concat($chunk);
            $budget -= $chunk->count();
        };

        $take('error', 25);
        $take('error_log', 35);
        $take('activity_log', 40);
        $take('activity', 12);
        $take('role', 8);
        $take('module', 20);
        $take('api', 25);
        $take('status', 10);

        if ($budget > 0) {
            $existingKeys = $nodes->pluck('node_key')->all();
            $fill = NetworkGraphNode::query()
                ->where('node_group', '!=', 'user')
                ->when($existingKeys !== [], fn ($q) => $q->whereNotIn('node_key', $existingKeys))
                ->orderByDesc('hit_count')
                ->limit($budget)
                ->get();
            $nodes = $nodes->concat($fill);
        }

        return $nodes->unique('node_key')->values();
    }

    /**
     * Keep System Core in the graph so paths can be traced through the hub.
     *
     * @param  \Illuminate\Support\Collection<int, NetworkGraphNode>  $nodes
     * @return \Illuminate\Support\Collection<int, NetworkGraphNode>
     */
    private function ensureAnchorNodes($nodes)
    {
        $keys = $nodes->pluck('node_key')->all();
        if (! in_array('hub:system', $keys, true)) {
            $hub = NetworkGraphNode::query()->where('node_key', 'hub:system')->first();
            if ($hub !== null) {
                $nodes = $nodes->push($hub);
                $keys[] = 'hub:system';
            }
        }

        foreach (['guest:visitor', 'guest:applicant'] as $guestKey) {
            if (! in_array($guestKey, $keys, true)) {
                $guest = NetworkGraphNode::query()->where('node_key', $guestKey)->first();
                if ($guest !== null) {
                    $nodes = $nodes->push($guest);
                }
            }
        }

        return $nodes->unique('node_key')->values();
    }

    /**
     * Prefer edges that preserve traceable paths (user → role/module → API) and merge isolated nodes.
     *
     * @param  list<string>  $nodeKeys
     * @return \Illuminate\Support\Collection<int, NetworkGraphEdge>
     */
    private function selectConnectedEdges(array $nodeKeys, int $maxEdges)
    {
        if ($nodeKeys === []) {
            return collect();
        }

        $maxEdges = max(1, $maxEdges);
        $priorityOrder = "CASE edge_type
            WHEN 'user_access' THEN 0
            WHEN 'user' THEN 1
            WHEN 'error' THEN 2
            WHEN 'error_log' THEN 3
            WHEN 'activity_log' THEN 4
            WHEN 'domain' THEN 5
            WHEN 'quiz_attempt' THEN 6
            WHEN 'application' THEN 7
            WHEN 'activity' THEN 8
            ELSE 9 END";

        $pool = NetworkGraphEdge::query()
            ->whereIn('source_node_key', $nodeKeys)
            ->whereIn('target_node_key', $nodeKeys)
            ->orderByRaw($priorityOrder)
            ->orderByDesc('weight')
            ->limit(min($maxEdges * 4, 5000))
            ->get();

        /** @var \Illuminate\Support\Collection<int, NetworkGraphEdge> $selected */
        $selected = collect();
        $seen = [];

        $push = function (NetworkGraphEdge $edge) use (&$selected, &$seen, $maxEdges): bool {
            if ($selected->count() >= $maxEdges) {
                return false;
            }
            $key = $edge->source_node_key.'|'.$edge->target_node_key.'|'.$edge->edge_type;
            if (isset($seen[$key])) {
                return false;
            }
            $seen[$key] = true;
            $selected->push($edge);

            return true;
        };

        $spineTypes = ['user_access', 'user', 'domain', 'quiz_attempt', 'application', 'applied_position'];
        foreach ($pool as $edge) {
            if (in_array($edge->edge_type, $spineTypes, true)) {
                $push($edge);
            }
        }

        foreach ($pool as $edge) {
            if ($edge->source_node_key === 'hub:system' || $edge->target_node_key === 'hub:system') {
                if (! in_array($edge->edge_type, ['activity_log', 'error_log'], true)) {
                    $push($edge);
                }
            }
        }

        foreach ($pool as $edge) {
            if ($edge->edge_type === 'error') {
                $push($edge);
            }
        }

        $logTrailCap = (int) max(40, floor($maxEdges * 0.35));
        $logTrailCount = 0;
        foreach ($pool as $edge) {
            if ($logTrailCount >= $logTrailCap) {
                break;
            }
            if (! in_array($edge->edge_type, ['activity_log', 'error_log'], true)) {
                continue;
            }
            if ($push($edge)) {
                $logTrailCount++;
            }
        }

        foreach ($pool as $edge) {
            $push($edge);
        }

        $this->mergeEdgeComponents($nodeKeys, $selected, $seen, $maxEdges, $push);
        $this->attachOrphanNodes($nodeKeys, $selected, $seen, $maxEdges, $push);

        return $selected->values();
    }

    /**
     * @param  list<string>  $nodeKeys
     * @param  \Illuminate\Support\Collection<int, NetworkGraphEdge>  $selected
     * @param  array<string, true>  $seen
     * @param  callable(NetworkGraphEdge): bool  $push
     */
    private function mergeEdgeComponents(array $nodeKeys, $selected, array &$seen, int $maxEdges, callable $push): void
    {
        $parent = array_combine($nodeKeys, $nodeKeys);
        if ($parent === false) {
            return;
        }

        $find = function (string $node) use (&$parent, &$find): string {
            if ($parent[$node] !== $node) {
                $parent[$node] = $find($parent[$node]);
            }

            return $parent[$node];
        };

        $union = function (string $a, string $b) use ($find): void {
            $rootA = $find($a);
            $rootB = $find($b);
            if ($rootA !== $rootB) {
                $parent[$rootB] = $rootA;
            }
        };

        foreach ($selected as $edge) {
            $union($edge->source_node_key, $edge->target_node_key);
        }

        while ($selected->count() < $maxEdges) {
            $groups = [];
            foreach ($nodeKeys as $key) {
                $groups[$find($key)][] = $key;
            }

            if (count($groups) <= 1) {
                break;
            }

            $roots = array_keys($groups);
            $merged = false;

            for ($i = 0; $i < count($roots); $i++) {
                for ($j = $i + 1; $j < count($roots); $j++) {
                    $groupA = $groups[$roots[$i]];
                    $groupB = $groups[$roots[$j]];

                    $bridge = NetworkGraphEdge::query()
                        ->whereIn('source_node_key', $groupA)
                        ->whereIn('target_node_key', $groupB)
                        ->orderByDesc('weight')
                        ->first();

                    if ($bridge === null) {
                        $bridge = NetworkGraphEdge::query()
                            ->whereIn('source_node_key', $groupB)
                            ->whereIn('target_node_key', $groupA)
                            ->orderByDesc('weight')
                            ->first();
                    }

                    if ($bridge !== null && $push($bridge)) {
                        $union($bridge->source_node_key, $bridge->target_node_key);
                        $merged = true;
                        break 2;
                    }
                }
            }

            if (! $merged) {
                break;
            }
        }
    }

    /**
     * @param  list<string>  $nodeKeys
     * @param  \Illuminate\Support\Collection<int, NetworkGraphEdge>  $selected
     * @param  array<string, true>  $seen
     * @param  callable(NetworkGraphEdge): bool  $push
     */
    private function attachOrphanNodes(array $nodeKeys, $selected, array &$seen, int $maxEdges, callable $push): void
    {
        $incident = [];
        foreach ($selected as $edge) {
            $incident[$edge->source_node_key] = true;
            $incident[$edge->target_node_key] = true;
        }

        foreach ($nodeKeys as $nodeKey) {
            if (isset($incident[$nodeKey]) || $selected->count() >= $maxEdges) {
                continue;
            }

            $bridge = NetworkGraphEdge::query()
                ->where(function ($query) use ($nodeKey) {
                    $query->where('source_node_key', $nodeKey)
                        ->orWhere('target_node_key', $nodeKey);
                })
                ->whereIn('source_node_key', $nodeKeys)
                ->whereIn('target_node_key', $nodeKeys)
                ->orderByRaw("CASE WHEN source_node_key = 'hub:system' OR target_node_key = 'hub:system' THEN 0 ELSE 1 END")
                ->orderByDesc('weight')
                ->first();

            if ($bridge !== null) {
                $push($bridge);
            }
        }
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
            $isLogTrail = in_array($edge->edge_type, ['activity_log', 'error_log'], true);

            return [
                'from' => $edge->source_node_key,
                'to' => $edge->target_node_key,
                'value' => max(1, (int) $edge->weight),
                'dashes' => $isUserAccess || $isLogTrail,
                'log_trail' => $isLogTrail,
            ];
        })->values()->all();

        $visEdges = $this->appendSyntheticHubBridges($visNodes, $visEdges);

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

    /**
     * @param  list<array<string, mixed>>  $visNodes
     * @param  list<array<string, mixed>>  $visEdges
     * @return list<array<string, mixed>>
     */
    private function appendSyntheticHubBridges(array $visNodes, array $visEdges): array
    {
        $nodeIds = array_column($visNodes, 'id');
        if (! in_array('hub:system', $nodeIds, true)) {
            return $visEdges;
        }

        $parent = array_combine($nodeIds, $nodeIds);
        if ($parent === false) {
            return $visEdges;
        }

        $find = function (string $node) use (&$parent, &$find): string {
            if ($parent[$node] !== $node) {
                $parent[$node] = $find($parent[$node]);
            }

            return $parent[$node];
        };

        $union = function (string $a, string $b) use ($find): void {
            $rootA = $find($a);
            $rootB = $find($b);
            if ($rootA !== $rootB) {
                $parent[$rootB] = $rootA;
            }
        };

        foreach ($visEdges as $edge) {
            $from = (string) $edge['from'];
            $to = (string) $edge['to'];
            if (isset($parent[$from], $parent[$to])) {
                $union($from, $to);
            }
        }

        $groups = [];
        foreach ($nodeIds as $nodeId) {
            $groups[$find($nodeId)][] = $nodeId;
        }

        if (count($groups) <= 1) {
            return $visEdges;
        }

        $hubRoot = $find('hub:system');
        foreach ($groups as $root => $members) {
            if ($root === $hubRoot) {
                continue;
            }
            $visEdges[] = [
                'from' => $members[0],
                'to' => 'hub:system',
                'value' => 1,
                'dashes' => true,
                'bridge' => true,
            ];
        }

        return $visEdges;
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
