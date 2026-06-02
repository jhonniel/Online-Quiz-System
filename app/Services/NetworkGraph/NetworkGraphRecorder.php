<?php

namespace App\Services\NetworkGraph;

use App\Models\NetworkGraphEdge;
use App\Models\NetworkGraphNode;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class NetworkGraphRecorder
{
    /** @var array<string, string> */
    private const GROUP_COLORS = [
        'hub' => '#6366f1',
        'role' => '#8b5cf6',
        'module' => '#0ea5e9',
        'api' => '#14b8a6',
        'error' => '#ef4444',
        'activity' => '#f59e0b',
        'status' => '#64748b',
        'user' => '#ec4899',
    ];

    /** @var array<string, string> */
    private const USER_ROLE_COLORS = [
        'student' => '#3b82f6',
        'employee' => '#10b981',
        'teacher' => '#a855f7',
        'admin' => '#f97316',
        'applicant' => '#06b6d4',
        'guest' => '#94a3b8',
        'other' => '#64748b',
    ];

    public function recordHttpRequest(Request $request, Response $response): void
    {
        if ($this->shouldSkipRequest($request)) {
            return;
        }

        $route = $request->route();
        if (! $route) {
            return;
        }

        $uri = trim((string) $route->uri(), '/');
        $methods = array_values(array_diff($route->methods() ?? [], ['HEAD', 'OPTIONS']));
        $method = strtoupper($methods[0] ?? $request->method());
        $statusCode = $response->getStatusCode();
        $role = $this->resolveRoleKey($request->user(), $request);
        $module = $this->resolveModuleKey($uri);
        $routeKey = $method.' '.$uri;
        $statusKey = 'status:'.$statusCode;

        $this->touchNode('hub:system', 'System Core', 'hub', self::GROUP_COLORS['hub']);
        $this->touchNode('role:'.$role, ucfirst($role).' traffic', 'role', self::GROUP_COLORS['role']);
        $this->touchNode('module:'.$module, $this->moduleLabel($module), 'module', self::GROUP_COLORS['module']);
        $this->touchNode('api:'.$this->slug($routeKey), $routeKey, 'api', self::GROUP_COLORS['api']);
        $this->touchNode($statusKey, 'HTTP '.$statusCode, 'status', $statusCode >= 500 ? self::GROUP_COLORS['error'] : self::GROUP_COLORS['status']);

        $this->touchEdge('hub:system', 'module:'.$module);
        $this->touchEdge('role:'.$role, 'module:'.$module);
        $this->touchEdge('module:'.$module, 'api:'.$this->slug($routeKey));
        $this->touchEdge('api:'.$this->slug($routeKey), $statusKey);
        $this->touchEdge('role:'.$role, 'api:'.$this->slug($routeKey));

        $errorKey = null;
        if ($statusCode >= 400) {
            $errorKey = 'error:'.$statusCode.':'.$this->slug($request->path());
            $this->touchNode($errorKey, 'Error '.$statusCode.' · '.$request->path(), 'error', self::GROUP_COLORS['error']);
            $this->touchEdge('api:'.$this->slug($routeKey), $errorKey, 'error');
            $this->touchEdge($statusKey, $errorKey, 'error');
        }

        $user = $request->user();
        if ($user instanceof User) {
            $this->linkUserToAccess($user, $role, $module, 'api:'.$this->slug($routeKey), $errorKey);
        } elseif ($role === 'applicant') {
            $this->linkGuestToAccess('guest:applicant', 'Applicants (guest)', $module, 'api:'.$this->slug($routeKey), $errorKey);
        }
    }

    /**
     * @param  array{status_code?: int, path?: string, method?: string, user_id?: int|null}  $data
     */
    public function recordError(array $data): void
    {
        $statusCode = (int) ($data['status_code'] ?? 500);
        $path = (string) ($data['path'] ?? '/');
        $method = strtoupper((string) ($data['method'] ?? 'GET'));
        $module = $this->resolveModuleKey(ltrim($path, '/'));
        $routeKey = $method.' '.ltrim($path, '/');
        $errorKey = 'error:'.$statusCode.':'.$this->slug($path);
        $apiKey = 'api:'.$this->slug($routeKey);

        $this->touchNode('hub:system', 'System Core', 'hub', self::GROUP_COLORS['hub']);
        $this->touchNode('module:'.$module, $this->moduleLabel($module), 'module', self::GROUP_COLORS['module']);
        $this->touchNode($errorKey, 'Error '.$statusCode.' · '.$path, 'error', self::GROUP_COLORS['error']);
        $this->touchNode($apiKey, $routeKey, 'api', self::GROUP_COLORS['api']);

        $this->touchEdge('hub:system', $errorKey, 'error');
        $this->touchEdge('module:'.$module, $errorKey, 'error');
        $this->touchEdge($apiKey, $errorKey, 'error');

        $userId = $data['user_id'] ?? null;
        if ($userId) {
            $user = User::query()->find($userId);
            if ($user) {
                $role = $this->resolveRoleKey($user);
                $this->linkUserToAccess($user, $role, $module, $apiKey, $errorKey);
            }
        }
    }

    public function recordUserActivity(UserActivity $activity): void
    {
        $type = (string) $activity->activity_type;
        $activityKey = 'activity:'.$this->slug($type);
        $role = 'guest';
        $module = 'public';

        if ($activity->user_id) {
            $activity->loadMissing('user');
            $role = $this->resolveRoleKey($activity->user);
        }

        $this->touchNode('hub:system', 'System Core', 'hub', self::GROUP_COLORS['hub']);
        $this->touchNode('role:'.$role, ucfirst($role).' traffic', 'role', self::GROUP_COLORS['role']);
        $this->touchNode($activityKey, 'Activity: '.$type, 'activity', self::GROUP_COLORS['activity']);

        $this->touchEdge('role:'.$role, $activityKey, 'activity');
        $this->touchEdge('hub:system', $activityKey, 'activity');

        if ($activity->page_url) {
            $path = parse_url((string) $activity->page_url, PHP_URL_PATH) ?: '';
            $module = $this->resolveModuleKey(ltrim($path, '/'));
            $this->touchNode('module:'.$module, $this->moduleLabel($module), 'module', self::GROUP_COLORS['module']);
            $this->touchEdge($activityKey, 'module:'.$module, 'activity');
        }

        if ($activity->user instanceof User) {
            $this->linkUserToAccess($activity->user, $role, $module, $activityKey);
        } elseif (! $activity->user_id) {
            $this->linkGuestToAccess('guest:visitor', 'Guests', $module, $activityKey);
        }
    }

    public function linkUserToAccess(
        User $user,
        string $role,
        string $module,
        string $targetKey,
        ?string $errorKey = null
    ): void {
        $userKey = 'user:'.$user->id;
        $roleLabel = ucfirst((string) $user->role);
        $label = trim((string) $user->name) !== '' ? $user->name.' ('.$roleLabel.')' : 'User #'.$user->id.' ('.$roleLabel.')';
        $color = self::USER_ROLE_COLORS[(string) $user->role] ?? self::USER_ROLE_COLORS['other'];

        $this->touchNode($userKey, $label, 'user', $color, [
            'user_id' => $user->id,
            'role' => (string) $user->role,
            'email' => (string) $user->email,
        ]);

        $this->touchEdge($userKey, 'role:'.$role, 'user');
        $this->touchEdge($userKey, 'module:'.$module, 'user_access');
        $this->touchEdge($userKey, $targetKey, 'user_access');

        if ($errorKey !== null) {
            $this->touchEdge($userKey, $errorKey, 'error');
        }
    }

    public function linkGuestToAccess(
        string $guestKey,
        string $label,
        string $module,
        string $targetKey,
        ?string $errorKey = null
    ): void {
        $this->touchNode($guestKey, $label, 'user', self::USER_ROLE_COLORS['guest'], [
            'role' => 'guest',
        ]);

        $guestRole = str_contains($guestKey, 'applicant') ? 'applicant' : 'guest';
        $this->touchNode('role:'.$guestRole, ucfirst($guestRole).' traffic', 'role', self::GROUP_COLORS['role']);
        $this->touchEdge($guestKey, 'role:'.$guestRole, 'user');
        $this->touchEdge($guestKey, 'module:'.$module, 'user_access');
        $this->touchEdge($guestKey, $targetKey, 'user_access');

        if ($errorKey !== null) {
            $this->touchEdge($guestKey, $errorKey, 'error');
        }
    }

    public function touchNode(string $nodeKey, string $label, string $group, ?string $color = null, array $meta = []): void
    {
        try {
            $node = NetworkGraphNode::query()->firstOrNew(['node_key' => $nodeKey]);
            $node->label = $label;
            $node->node_group = $group;
            $node->color = $color ?? (self::GROUP_COLORS[$group] ?? '#94a3b8');
            if (! $node->exists) {
                $node->hit_count = 1;
                $node->first_seen_at = now();
            } else {
                $node->hit_count = (int) $node->hit_count + 1;
            }
            if ($meta !== []) {
                $node->meta = array_merge($node->meta ?? [], $meta);
            }
            $node->last_seen_at = now();
            $node->save();
        } catch (\Throwable $e) {
            Log::warning('Network graph node touch failed', ['key' => $nodeKey, 'error' => $e->getMessage()]);
        }
    }

    public function touchEdge(string $sourceKey, string $targetKey, string $edgeType = 'traffic'): void
    {
        if ($sourceKey === $targetKey) {
            return;
        }

        try {
            $edge = NetworkGraphEdge::query()->firstOrNew([
                'source_node_key' => $sourceKey,
                'target_node_key' => $targetKey,
                'edge_type' => $edgeType,
            ]);
            $edge->weight = (int) $edge->weight + 1;
            $edge->last_seen_at = now();
            $edge->save();
        } catch (\Throwable $e) {
            Log::warning('Network graph edge touch failed', [
                'source' => $sourceKey,
                'target' => $targetKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function shouldSkipRequest(Request $request): bool
    {
        if ($request->headers->get('X-Network-Graph-Probe') === '1') {
            return true;
        }

        $path = ltrim($request->path(), '/');

        return str_starts_with($path, '_debugbar')
            || str_starts_with($path, '_ignition')
            || str_starts_with($path, 'livewire')
            || str_starts_with($path, 'build/')
            || str_starts_with($path, 'vendor/')
            || str_starts_with($path, 'admin/system/api-monitoring')
            || str_starts_with($path, 'admin/system/network-graph');
    }

    private function resolveRoleKey(?User $user, ?Request $request = null): string
    {
        if ($user) {
            $role = (string) $user->role;

            return match ($role) {
                'student', 'employee', 'teacher', 'admin' => $role,
                default => 'other',
            };
        }

        if ($request) {
            $path = strtolower(ltrim($request->path(), '/'));
            if (str_contains($path, 'hiring') || str_contains($path, 'applicant') || str_contains($path, 'apply')) {
                return 'applicant';
            }
        }

        return 'guest';
    }

    private function resolveModuleKey(string $uri): string
    {
        $segments = array_values(array_filter(explode('/', $uri)));

        if ($segments === []) {
            return 'public';
        }

        if ($segments[0] === 'admin') {
            return 'admin:'.($segments[1] ?? 'dashboard');
        }

        if (in_array($segments[0], ['api', 'sanctum'], true)) {
            return 'api';
        }

        if ($segments[0] === 'teacher') {
            return 'teacher';
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
}
