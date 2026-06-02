@extends('layouts.admin')

@section('title', 'Network Graph')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/vis-network@9.1.9/styles/vis-network.min.css">
<style>
    #network-graph-wrap .ng-burst-particle {
        position: absolute;
        width: 6px;
        height: 6px;
        border-radius: 9999px;
        pointer-events: none;
        z-index: 20;
        transform: translate(-50%, -50%);
        animation: ng-particle-burst 680ms cubic-bezier(0.22, 0.8, 0.2, 1) forwards;
        box-shadow: 0 0 8px currentColor;
    }

    @keyframes ng-particle-burst {
        0% {
            opacity: 0.95;
            transform: translate(-50%, -50%) scale(0.6);
        }
        72% {
            opacity: 0.85;
            transform: translate(calc(-50% + var(--dx) * 0.78), calc(-50% + var(--dy) * 0.78)) scale(1);
        }
        100% {
            opacity: 0;
            transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(0.2);
        }
    }
</style>
<div class="px-3 sm:px-4 lg:px-6 xl:px-8 space-y-6">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Network Graph</h1>
                <p class="text-sm text-gray-500">Live moving graph with all connections. Click any node to show only its links; click empty space to reset. Drag to pan, scroll to zoom.</p>
            </div>
            <div class="text-xs text-gray-500">
                <span id="ng-generated-at" class="text-gray-500">Waiting for graph data...</span>
            </div>
        </div>

        <div class="p-6 space-y-4">
            @if(session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <div class="rounded-xl border border-gray-200 p-3">
                    <p class="text-xs text-gray-500">Stored nodes</p>
                    <p id="ng-stat-nodes" class="text-lg font-semibold text-gray-900">{{ number_format($stats['nodes'] ?? 0) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-3">
                    <p class="text-xs text-gray-500">Users on graph</p>
                    <p id="ng-stat-users" class="text-lg font-semibold text-gray-900">{{ number_format($stats['users'] ?? 0) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-3">
                    <p class="text-xs text-gray-500">Stored edges</p>
                    <p id="ng-stat-edges" class="text-lg font-semibold text-gray-900">{{ number_format($stats['edges'] ?? 0) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-3">
                    <p class="text-xs text-gray-500">On screen</p>
                    <p id="ng-stat-displayed" class="text-lg font-semibold text-gray-900">
                        {{ number_format($stats['displayed_nodes'] ?? 0) }} / {{ number_format($stats['displayed_edges'] ?? 0) }}
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 p-3">
                    <p class="text-xs text-gray-500">Total hits</p>
                    <p id="ng-stat-hits" class="text-lg font-semibold text-gray-900">{{ number_format($stats['total_hits'] ?? 0) }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="ng-refresh-data" class="h-9 rounded-md bg-indigo-600 px-3 text-sm font-medium text-white hover:bg-indigo-700">Refresh graph</button>
                <form method="POST" action="{{ route('admin.system.network-graph.sync') }}" class="inline-flex items-center gap-2">
                    @csrf
                    <select name="days" class="h-9 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="7">Last 7 days</option>
                        <option value="14" selected>Last 14 days</option>
                        <option value="30">Last 30 days</option>
                    </select>
                    <button type="submit" class="h-9 rounded-md border border-indigo-300 bg-indigo-50 px-3 text-sm font-medium text-indigo-800 hover:bg-indigo-100">Sync historical data</button>
                </form>
                <form method="POST" action="{{ route('admin.system.network-graph.clear') }}" onsubmit="return confirm('Clear all network graph nodes and edges?');">
                    @csrf
                    <button type="submit" class="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Clear graph</button>
                </form>
                <button type="button" id="ng-reset-view" class="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset view</button>
            </div>

            <div class="flex flex-wrap gap-3 text-xs text-gray-600">
                <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rotate-45 bg-pink-500"></span> User</span>
                <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Hub</span>
                <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-violet-500"></span> Role</span>
                <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span> Module</span>
                <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded-full bg-teal-500"></span> API</span>
                <span class="inline-flex items-center gap-1"><span class="h-0.5 w-4 border-t-2 border-dashed border-purple-400"></span> User access</span>
            </div>

            <div class="space-y-4">
                <div id="network-graph-empty" class="hidden h-[min(76vh,780px)] w-full rounded-xl border border-dashed border-gray-300 bg-slate-50 flex items-center justify-center p-8 text-center">
                    <p class="text-sm text-gray-600">No graph data. Sync historical data, then click <strong>Refresh graph</strong>.</p>
                </div>
                <div id="network-graph-wrap" class="relative h-[min(76vh,780px)] w-full rounded-xl border border-gray-200 bg-slate-50 overflow-hidden">
                    <div id="network-graph-canvas" class="h-full w-full"></div>
                    <div id="network-graph-loading" class="absolute inset-0 hidden items-center justify-center bg-slate-50/90 text-sm text-gray-600 z-10">
                        Loading graph...
                    </div>
                    <div id="network-graph-error" class="absolute inset-0 hidden items-center justify-center bg-slate-50/95 text-sm text-rose-700 z-20 p-6 text-center"></div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-gray-200 bg-white flex flex-col max-h-80">
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/60">
                            <h2 class="text-sm font-semibold text-gray-900">Users & access</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Click any node in the graph or a user here to show only its connections.</p>
                        </div>
                        <div id="ng-users-list" class="flex-1 overflow-y-auto divide-y divide-gray-100 text-sm">
                            <p class="px-4 py-6 text-xs text-gray-400 text-center">Click <strong>Load graph</strong> first.</p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white flex flex-col max-h-80">
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/60">
                            <h3 class="text-sm font-semibold text-gray-900">User Activity Logs</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Recent actions from users and guests.</p>
                        </div>
                        <div id="ng-activity-list" class="flex-1 overflow-y-auto divide-y divide-gray-100 text-xs">
                            <p class="px-4 py-6 text-gray-400 text-center">Activity logs will appear here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/vis-network@9.1.9/standalone/umd/vis-network.min.js"></script>
<script>
(function () {
    const dataUrl = "{{ route('admin.system.network-graph.data') }}";
    let wrap = null;
    let canvas = null;
    let emptyState = null;
    let loadBtn = null;
    let loading = null;
    let errorPanel = null;

    let network = null;
    let nodesDs = null;
    let edgesDs = null;
    let selectedUserKey = null;
    let focusedConnectionNodeIds = null;
    let allActivityLogs = [];
    let selectedNodeId = null;
    let loadInProgress = false;
    let focusHideTimer = null;
    let focusScatterTimer = null;
    let focusAnimationFrame = null;
    let clearAnimationFrame = null;
    let baseNodeStyles = new Map();
    let baseEdgeStyles = new Map();
    let focusOriginalPositions = new Map();

    const roleBadgeClass = {
        student: 'bg-blue-100 text-blue-800',
        employee: 'bg-emerald-100 text-emerald-800',
        teacher: 'bg-purple-100 text-purple-800',
        admin: 'bg-orange-100 text-orange-800',
        applicant: 'bg-cyan-100 text-cyan-800',
        guest: 'bg-slate-100 text-slate-700',
    };

    function setLoading(show) {
        if (!loading) return;
        loading.classList.toggle('hidden', !show);
        loading.classList.toggle('flex', show);
        if (show && errorPanel) {
            errorPanel.classList.add('hidden');
            errorPanel.classList.remove('flex');
        }
    }

    function showGraphError(message) {
        if (!errorPanel) return;
        errorPanel.textContent = message;
        errorPanel.classList.remove('hidden');
        errorPanel.classList.add('flex');
        if (loading) {
            loading.classList.add('hidden');
            loading.classList.remove('flex');
        }
    }

    function hideGraphError() {
        if (!errorPanel) return;
        errorPanel.classList.add('hidden');
        errorPanel.classList.remove('flex');
        errorPanel.textContent = '';
    }

    function withAlpha(color, alpha) {
        if (!color || typeof color !== 'string') return color;
        if (color.startsWith('rgba(')) {
            return color.replace(/rgba\(([^)]+)\)/, (_, inner) => {
                const parts = inner.split(',').map((p) => p.trim());
                return `rgba(${parts[0]}, ${parts[1]}, ${parts[2]}, ${alpha})`;
            });
        }
        if (color.startsWith('rgb(')) {
            return color.replace(/rgb\(([^)]+)\)/, 'rgba($1, '+alpha+')');
        }
        if (color.startsWith('#')) {
            let hex = color.slice(1);
            if (hex.length === 3) {
                hex = hex.split('').map((c) => c + c).join('');
            }
            const r = parseInt(hex.slice(0, 2), 16);
            const g = parseInt(hex.slice(2, 4), 16);
            const b = parseInt(hex.slice(4, 6), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
        return color;
    }

    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    function cancelFocusAnimations() {
        if (focusHideTimer) {
            clearTimeout(focusHideTimer);
            focusHideTimer = null;
        }
        if (focusScatterTimer) {
            clearTimeout(focusScatterTimer);
            focusScatterTimer = null;
        }
        if (focusAnimationFrame) {
            cancelAnimationFrame(focusAnimationFrame);
            focusAnimationFrame = null;
        }
        if (clearAnimationFrame) {
            cancelAnimationFrame(clearAnimationFrame);
            clearAnimationFrame = null;
        }
    }

    function updateStats(stats) {
        if (!stats) return;
        const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
        set('ng-stat-nodes', Number(stats.nodes || 0).toLocaleString());
        set('ng-stat-users', Number(stats.users || 0).toLocaleString());
        set('ng-stat-edges', Number(stats.edges || 0).toLocaleString());
        set('ng-stat-displayed', `${Number(stats.displayed_nodes || 0).toLocaleString()} / ${Number(stats.displayed_edges || 0).toLocaleString()}`);
        set('ng-stat-hits', Number(stats.total_hits || 0).toLocaleString());
        set('ng-generated-at', stats.generated_at ? `Updated ${stats.generated_at}` : '');
    }

    function renderUsersList(users) {
        const list = document.getElementById('ng-users-list');
        if (!list) return;
        if (!users || !users.length) {
            list.innerHTML = '<p class="px-4 py-6 text-xs text-gray-400 text-center">No users in graph data.</p>';
            return;
        }
        list.innerHTML = users.map(u => {
            const role = (u.role || 'unknown').toLowerCase();
            const badge = roleBadgeClass[role] || 'bg-gray-100 text-gray-700';
            const active = selectedUserKey === u.node_key ? 'bg-indigo-50 ring-1 ring-indigo-200' : 'hover:bg-gray-50';
            return `<button type="button" data-user-key="${escapeHtml(u.node_key)}" class="ng-user-row w-full text-left px-4 py-3 ${active}">
                <div class="flex justify-between gap-2">
                    <span class="font-medium text-gray-900 text-sm">${escapeHtml(u.label)}</span>
                    <span class="text-[10px] uppercase font-semibold px-1.5 py-0.5 rounded ${badge}">${escapeHtml(role)}</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">${Number(u.hits || 0).toLocaleString()} hits</p>
            </button>`;
        }).join('');
        list.querySelectorAll('.ng-user-row').forEach(btn => {
            btn.addEventListener('click', () => focusUser(btn.getAttribute('data-user-key')));
        });
    }

    function renderActivityLogs(logs, contextLabel = null) {
        const list = document.getElementById('ng-activity-list');
        if (!list) return;

        if (!logs || !logs.length) {
            list.innerHTML = '<p class="px-4 py-6 text-xs text-gray-400 text-center">No activity logs found.</p>';
            return;
        }

        const contextHeader = contextLabel
            ? `<div class="px-3 py-2 bg-indigo-50 text-[11px] text-indigo-700 border-b border-indigo-100">Filtered by: <strong>${escapeHtml(contextLabel)}</strong></div>`
            : '';

        list.innerHTML = contextHeader + logs.map((log) => {
            const role = escapeHtml((log.user_role || 'guest').toString());
            const action = escapeHtml((log.action || '').toString());
            const type = escapeHtml((log.activity_type || '').toString());
            const userName = escapeHtml((log.user_name || 'Guest').toString());
            const page = escapeHtml((log.page_url || '').toString());
            const time = escapeHtml((log.created_at_human || '').toString());
            const shortPage = page.length > 60 ? `${page.slice(0, 57)}...` : page;

            return `<div class="px-3 py-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-medium text-gray-900">${userName}</span>
                    <span class="text-[10px] uppercase px-1.5 py-0.5 rounded bg-gray-100 text-gray-700">${role}</span>
                </div>
                <p class="mt-1 text-gray-700">${type}${action ? ` · ${action}` : ''}</p>
                ${shortPage ? `<p class="mt-0.5 text-gray-500">${shortPage}</p>` : ''}
                ${time ? `<p class="mt-0.5 text-[10px] text-gray-400">${time}</p>` : ''}
            </div>`;
        }).join('');
    }

    function slugify(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }

    function filterLogsByNode(nodeId, nodeLabel) {
        const logs = allActivityLogs || [];
        if (!nodeId || !logs.length) return logs;

        const lowerId = String(nodeId).toLowerCase();
        const lowerLabel = String(nodeLabel || '').toLowerCase();

        if (lowerId.startsWith('user:')) {
            const userId = Number(lowerId.split(':')[1]);
            return logs.filter(log => Number(log.user_id) === userId);
        }

        if (lowerId.startsWith('role:')) {
            const role = lowerId.split(':')[1];
            return logs.filter(log => String(log.user_role || '').toLowerCase() === role);
        }

        if (lowerId.startsWith('activity:')) {
            const activitySlug = lowerId.split(':')[1];
            return logs.filter(log => slugify(log.activity_type) === activitySlug);
        }

        if (lowerId.startsWith('module:')) {
            const moduleKey = lowerId.split(':')[1].replace(/^admin:/, 'admin/');
            return logs.filter(log => String(log.page_url || '').toLowerCase().includes(moduleKey));
        }

        if (lowerId.startsWith('api:')) {
            const apiText = lowerLabel.replace(/^[a-z]+\s+/i, '').trim();
            if (!apiText) return logs;
            return logs.filter(log => String(log.page_url || '').toLowerCase().includes(apiText.toLowerCase()));
        }

        if (lowerId.startsWith('error:') || lowerId.startsWith('status:')) {
            return logs.filter(log => {
                const type = String(log.activity_type || '').toLowerCase();
                const action = String(log.action || '').toLowerCase();
                return type.includes('error') || action.includes('error') || action.includes('fail');
            });
        }

        if (lowerId.startsWith('quiz:')) {
            return logs.filter(log => {
                const url = String(log.page_url || '').toLowerCase();
                const type = String(log.activity_type || '').toLowerCase();
                return url.includes('quiz') || type.includes('quiz');
            });
        }

        if (lowerId.startsWith('dtr:')) {
            return logs.filter(log => {
                const url = String(log.page_url || '').toLowerCase();
                return url.includes('dtr') || url.includes('time-request');
            });
        }

        if (lowerId.startsWith('leave_request:')) {
            return logs.filter(log => {
                const url = String(log.page_url || '').toLowerCase();
                return url.includes('leave');
            });
        }

        if (lowerId.startsWith('application:') || lowerId.startsWith('position:')) {
            return logs.filter(log => {
                const url = String(log.page_url || '').toLowerCase();
                return url.includes('hiring') || url.includes('apply') || url.includes('application');
            });
        }

        return logs.filter(log => {
            const url = String(log.page_url || '').toLowerCase();
            const type = String(log.activity_type || '').toLowerCase();
            return (lowerLabel && url.includes(lowerLabel)) || (lowerLabel && type.includes(lowerLabel));
        });
    }

    function escapeHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function focusNode(nodeKey) {
        if (!network || !nodeKey) return;
        selectedNodeId = nodeKey;
        const isUserNode = nodeKey.startsWith('user:') || nodeKey.startsWith('guest:');
        selectedUserKey = isUserNode ? nodeKey : null;
        spawnExplosionParticles(nodeKey);
        const connected = network.getConnectedNodes(nodeKey);
        applyNodeFocus(nodeKey, connected);
        network.selectNodes([nodeKey, ...connected]);
        network.focus(nodeKey, {
            scale: 1.2,
            animation: { duration: 350, easingFunction: 'easeInOutQuad' },
        });
        document.querySelectorAll('.ng-user-row').forEach(row => {
            const on = row.getAttribute('data-user-key') === nodeKey;
            row.classList.toggle('bg-indigo-50', on);
            row.classList.toggle('ring-1', on);
            row.classList.toggle('ring-indigo-200', on);
        });
    }

    function focusUser(nodeKey) {
        focusNode(nodeKey);
    }

    function spawnExplosionParticles(nodeKey) {
        if (!network || !wrap || !nodeKey) return;
        const nodePos = network.getPositions([nodeKey])[nodeKey];
        if (!nodePos) return;

        const domPos = network.canvasToDOM(nodePos);
        const count = 36;
        const colors = ['#a855f7', '#6366f1', '#c084fc', '#93c5fd', '#e879f9'];

        for (let i = 0; i < count; i++) {
            const particle = document.createElement('span');
            particle.className = 'ng-burst-particle';

            const angle = (Math.PI * 2 * i) / count + (Math.random() - 0.5) * 0.35;
            const radius = 85 + Math.random() * 220;
            const dx = Math.cos(angle) * radius;
            const dy = Math.sin(angle) * radius;
            const size = 3 + Math.random() * 4;
            const color = colors[i % colors.length];

            particle.style.left = `${domPos.x}px`;
            particle.style.top = `${domPos.y}px`;
            particle.style.setProperty('--dx', `${dx}px`);
            particle.style.setProperty('--dy', `${dy}px`);
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            particle.style.color = color;
            particle.style.background = color;

            wrap.appendChild(particle);
            setTimeout(() => particle.remove(), 760);
        }
    }

    function applyNodeFocus(nodeKey, connectedNodeIds) {
        if (!nodesDs || !edgesDs) return;
        cancelFocusAnimations();

        const connectedSet = new Set([nodeKey, ...(connectedNodeIds || [])]);
        focusedConnectionNodeIds = connectedSet;
        const currentPositions = network ? network.getPositions() : {};
        const anchor = currentPositions[nodeKey] || { x: 0, y: 0 };
        focusOriginalPositions = new Map();

        const nodeTargets = nodesDs.get().map((node, idx) => {
            const base = baseNodeStyles.get(node.id) || {};
            const connected = connectedSet.has(node.id);
            const pos = currentPositions[node.id] || { x: node.x || 0, y: node.y || 0 };
            focusOriginalPositions.set(node.id, { x: pos.x, y: pos.y });

            let target = {
                id: node.id,
                connected,
                fromX: pos.x,
                fromY: pos.y,
                toX: pos.x,
                toY: pos.y,
                fromValue: Number(base.value || node.value || 1),
                toValue: connected ? Number(base.value || node.value || 1) : Math.max(1, Number(base.value || node.value || 1) * 0.16),
                fromColor: base.color || node.color,
                toColor: connected ? (base.color || node.color) : withAlpha(base.color || node.color, 0.16),
            };

            if (!connected) {
                // Scatter non-connected nodes outward from focused node.
                const dx = pos.x - anchor.x;
                const dy = pos.y - anchor.y;
                const len = Math.hypot(dx, dy) || 1;
                const ux = dx / len;
                const uy = dy / len;
                const burst = 290 + (idx % 10) * 42;
                target = {
                    ...target,
                    toX: pos.x + ux * burst,
                    toY: pos.y + uy * burst,
                };
            }

            return target;
        });

        const edgeTargets = edgesDs.get().map((edge) => {
            const base = baseEdgeStyles.get(edge.id) || {};
            const connected = connectedSet.has(edge.from) && connectedSet.has(edge.to);
            return {
                id: edge.id,
                connected,
                fromWidth: Number(base.width || edge.width || 0.5),
                toWidth: connected ? Number(base.width || edge.width || 0.5) : Math.max(0.12, Number(base.width || edge.width || 0.5) * 0.16),
                fromColor: base.color || edge.color,
                toColor: connected ? (base.color || edge.color) : withAlpha(base.color || edge.color, 0.08),
            };
        });

        if (network) {
            network.setOptions({ physics: { enabled: false } });
        }

        const start = performance.now();
        const durationMs = 520;
        const animateScatter = (now) => {
            const t = Math.min(1, (now - start) / durationMs);
            const e = easeOutCubic(t);

            const nodeUpdates = nodeTargets.map((target) => ({
                id: target.id,
                hidden: false,
                x: target.fromX + (target.toX - target.fromX) * e,
                y: target.fromY + (target.toY - target.fromY) * e,
                value: target.fromValue + (target.toValue - target.fromValue) * e,
                color: e >= 0.92 ? target.toColor : target.fromColor,
            }));
            nodesDs.update(nodeUpdates);

            const edgeUpdates = edgeTargets.map((target) => ({
                id: target.id,
                hidden: false,
                width: target.fromWidth + (target.toWidth - target.fromWidth) * e,
                color: e >= 0.92 ? target.toColor : target.fromColor,
            }));
            edgesDs.update(edgeUpdates);

            if (network) {
                network.redraw();
            }

            if (t < 1) {
                focusAnimationFrame = requestAnimationFrame(animateScatter);
            } else {
                focusAnimationFrame = null;
                focusHideTimer = setTimeout(() => {
                    const hideNodeUpdates = nodesDs.get().map((node) => ({
                        id: node.id,
                        hidden: !connectedSet.has(node.id),
                    }));
                    nodesDs.update(hideNodeUpdates);

                    const hideEdgeUpdates = edgesDs.get().map((edge) => ({
                        id: edge.id,
                        hidden: !(connectedSet.has(edge.from) && connectedSet.has(edge.to)),
                    }));
                    edgesDs.update(hideEdgeUpdates);
                }, 120);
            }
        };
        focusAnimationFrame = requestAnimationFrame(animateScatter);
    }

    function clearNodeFocus() {
        if (!nodesDs || !edgesDs) return;
        cancelFocusAnimations();
        focusedConnectionNodeIds = null;
        selectedUserKey = null;
        const start = performance.now();
        const durationMs = 380;

        const nodeTargets = nodesDs.get().map((node) => {
            const base = baseNodeStyles.get(node.id) || {};
            const originalPos = focusOriginalPositions.get(node.id);
            return {
                id: node.id,
                fromX: node.x,
                fromY: node.y,
                toX: originalPos ? originalPos.x : node.x,
                toY: originalPos ? originalPos.y : node.y,
                fromValue: Number(node.value || 1),
                toValue: Number(base.value ?? node.value ?? 1),
                fromColor: node.color,
                toColor: base.color ?? node.color,
            };
        });
        const edgeTargets = edgesDs.get().map((edge) => {
            const base = baseEdgeStyles.get(edge.id) || {};
            return {
                id: edge.id,
                fromWidth: Number(edge.width || 0.5),
                toWidth: Number(base.width ?? edge.width ?? 0.5),
                fromColor: edge.color,
                toColor: base.color ?? edge.color,
            };
        });

        const animateClear = (now) => {
            const t = Math.min(1, (now - start) / durationMs);
            const e = easeOutCubic(t);

            nodesDs.update(nodeTargets.map((target) => ({
                id: target.id,
                hidden: false,
                x: target.fromX + (target.toX - target.fromX) * e,
                y: target.fromY + (target.toY - target.fromY) * e,
                value: target.fromValue + (target.toValue - target.fromValue) * e,
                color: e >= 0.92 ? target.toColor : target.fromColor,
            })));

            edgesDs.update(edgeTargets.map((target) => ({
                id: target.id,
                hidden: false,
                width: target.fromWidth + (target.toWidth - target.fromWidth) * e,
                color: e >= 0.92 ? target.toColor : target.fromColor,
            })));

            if (network) {
                network.redraw();
            }

            if (t < 1) {
                clearAnimationFrame = requestAnimationFrame(animateClear);
            } else {
                clearAnimationFrame = null;
                if (network) {
                    network.setOptions({ physics: { enabled: true } });
                    network.startSimulation();
                }
            }
        };
        clearAnimationFrame = requestAnimationFrame(animateClear);
    }

    function buildVisNodes(rawNodes) {
        const maxValue = rawNodes.reduce((m, n) => Math.max(m, Number(n.value || 1)), 1);

        return rawNodes.map((n) => {
            const shape = n.shape === 'circularImage' ? 'circularImage' : 'dot';
            const t = Math.min(1, Math.max(0, Number(n.value || 1) / maxValue));
            const hue = Math.round(200 - (200 * t));
            const sat = 80;
            const light = Math.round(58 - (20 * t));
            const image = shape === 'circularImage' ? (n.image || undefined) : undefined;

            return {
                id: n.id,
                label: n.label,
                group: n.group,
                value: Math.max(1, Number(n.value || 1)),
                title: `${n.label} (${Number(n.value || 0).toLocaleString()} hits)`,
                shape,
                image,
                brokenImage: image,
                color: shape === 'circularImage' ? (n.color || '#94a3b8') : `hsl(${hue} ${sat}% ${light}%)`,
                hidden: false,
            };
        });
    }

    function buildVisEdges(rawEdges) {
        return (rawEdges || []).map((e, index) => ({
            id: `${e.from}|${e.to}|${index}`,
            from: e.from,
            to: e.to,
            value: Math.max(1, Number(e.value || 1)),
            dashes: !!e.dashes,
            color: e.dashes ? '#c084fc' : '#94a3b8',
            width: Math.min(6, Math.max(1, Math.log((e.value || 1) + 1))),
            hidden: false,
        }));
    }

    function createNetworkOptions(nodeCount) {
        const useImprovedLayout = nodeCount <= 80;

        return {
            layout: { improvedLayout: useImprovedLayout, randomSeed: 2 },
            nodes: {
                borderWidth: 0.5,
                scaling: { min: 8, max: 42 },
                font: {
                    size: 13,
                    face: 'Inter, sans-serif',
                    color: '#0f172a',
                    strokeWidth: 4,
                    strokeColor: '#ffffff',
                },
            },
            edges: {
                smooth: false,
                color: {
                    color: 'rgba(148, 163, 184, 0.24)',
                    highlight: 'rgba(99, 102, 241, 0.45)',
                    hover: 'rgba(99, 102, 241, 0.45)',
                },
                width: 0.5,
            },
            interaction: {
                dragView: true,
                zoomView: true,
                hover: true,
                navigationButtons: true,
                tooltipDelay: 90,
            },
            physics: {
                enabled: true,
                solver: 'forceAtlas2Based',
                timestep: 0.35,
                stabilization: {
                    enabled: true,
                    iterations: nodeCount > 120 ? 80 : 120,
                    updateInterval: 25,
                },
                forceAtlas2Based: {
                    gravitationalConstant: -95,
                    centralGravity: 0.02,
                    springLength: 58,
                    springConstant: 0.04,
                    damping: 0.62,
                },
                maxVelocity: 28,
                minVelocity: 0.75,
            },
        };
    }

    function bindNetworkEvents() {
        if (!network) return;

        network.on('click', (params) => {
            if (params.nodes.length === 1) {
                const id = String(params.nodes[0]);
                const clickedNode = nodesDs.get(id);
                const filteredLogs = filterLogsByNode(id, clickedNode?.label || '');
                renderActivityLogs(filteredLogs, clickedNode?.label || id);
                focusNode(id);
            } else if (params.nodes.length === 0) {
                selectedNodeId = null;
                clearNodeFocus();
                network.unselectAll();
                renderActivityLogs(allActivityLogs);
            }
        });

        const fitGraphToView = () => {
            if (!network) return;
            network.fit({ animation: { duration: 280, easingFunction: 'easeInOutQuad' } });
        };
        network.on('stabilizationIterationsDone', fitGraphToView);
        network.on('stabilized', fitGraphToView);
        setTimeout(fitGraphToView, 900);
    }

    function setGraphData(payload) {
        hideGraphError();

        const rawNodes = payload.nodes || [];
        updateStats(payload.stats);
        renderUsersList(payload.users || []);
        allActivityLogs = payload.activity_logs || [];
        renderActivityLogs(allActivityLogs);

        if (!rawNodes.length) {
            if (wrap) wrap.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
            if (network) {
                network.destroy();
                network = null;
                nodesDs = null;
                edgesDs = null;
            }
            return;
        }

        if (wrap) wrap.classList.remove('hidden');
        if (emptyState) emptyState.classList.add('hidden');

        const nodeItems = buildVisNodes(rawNodes);
        const edgeItems = buildVisEdges(payload.edges || []);

        focusedConnectionNodeIds = null;
        selectedUserKey = null;

        try {
            if (!network) {
                nodesDs = new vis.DataSet(nodeItems);
                edgesDs = new vis.DataSet(edgeItems);
                baseNodeStyles = new Map(nodesDs.get().map((n) => [n.id, { value: n.value, color: n.color }]));
                baseEdgeStyles = new Map(edgesDs.get().map((e) => [e.id, { width: e.width, color: e.color }]));

                network = new vis.Network(canvas, { nodes: nodesDs, edges: edgesDs }, createNetworkOptions(nodeItems.length));
                bindNetworkEvents();
            } else {
                const nodeIds = new Set(nodeItems.map(n => n.id));
                const edgeIds = new Set(edgeItems.map(e => e.id));
                nodesDs.update(nodeItems);
                nodesDs.remove(nodesDs.getIds().filter(id => !nodeIds.has(id)));
                edgesDs.update(edgeItems);
                edgesDs.remove(edgesDs.getIds().filter(id => !edgeIds.has(id)));
                baseNodeStyles = new Map(nodesDs.get().map((n) => [n.id, { value: n.value, color: n.color }]));
                baseEdgeStyles = new Map(edgesDs.get().map((e) => [e.id, { width: e.width, color: e.color }]));
                network.setOptions(createNetworkOptions(nodeItems.length));
                network.startSimulation();
                setTimeout(() => {
                    if (network) {
                        network.fit({ animation: { duration: 280, easingFunction: 'easeInOutQuad' } });
                    }
                }, 900);
            }
        } catch (error) {
            console.error('Network graph render failed', error);
            showGraphError('Could not render the graph. Try Refresh graph or Sync historical data.');
            throw error;
        }
    }

    async function loadGraphData() {
        if (loadInProgress) return;
        loadInProgress = true;
            if (loadBtn) { loadBtn.disabled = true; loadBtn.textContent = 'Loading...'; }
        setLoading(true);

        const url = dataUrl;

        try {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Network-Graph-Probe': '1' },
                credentials: 'same-origin',
            });
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }
            const payload = await res.json();
            setGraphData(payload);
        } catch (e) {
            console.warn(e);
            const message = e && e.message ? e.message : 'Unknown error';
            showGraphError(`Could not load graph data (${message}). Try Refresh graph or Sync historical data.`);
        } finally {
            loadInProgress = false;
            setLoading(false);
            if (loadBtn) { loadBtn.disabled = false; loadBtn.textContent = 'Refresh graph'; }
        }
    }

    function boot() {
        wrap = document.getElementById('network-graph-wrap');
        canvas = document.getElementById('network-graph-canvas');
        emptyState = document.getElementById('network-graph-empty');
        loadBtn = document.getElementById('ng-refresh-data');
        loading = document.getElementById('network-graph-loading');
        errorPanel = document.getElementById('network-graph-error');

        if (!canvas) {
            showGraphError('Graph canvas is missing on this page.');
            return;
        }

        if (typeof vis === 'undefined' || !vis.Network || !vis.DataSet) {
            showGraphError('Graph library failed to load. Check your internet connection and refresh.');
            return;
        }

        loadBtn?.addEventListener('click', loadGraphData);
        document.getElementById('ng-reset-view')?.addEventListener('click', () => {
            clearNodeFocus();
            selectedNodeId = null;
            if (network) {
                network.unselectAll();
                network.fit({ animation: { duration: 350, easingFunction: 'easeInOutQuad' } });
                network.startSimulation();
            }
            renderActivityLogs(allActivityLogs);
            document.querySelectorAll('.ng-user-row').forEach(r => r.classList.remove('bg-indigo-50', 'ring-1', 'ring-indigo-200'));
        });

        setTimeout(() => { loadGraphData(); }, 120);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
@endpush
