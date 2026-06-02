@extends('layouts.admin')

@section('title', 'Network Graph')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/vis-network@9.1.9/styles/vis-network.min.css">
<div class="px-3 sm:px-4 lg:px-6 xl:px-8 space-y-6">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/70 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Network Graph</h1>
                <p class="text-sm text-gray-500">Live moving graph with all connections. Drag to pan, scroll to zoom, and click <strong>Load graph</strong> to fetch current traffic.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                <span id="ng-generated-at" class="text-gray-400"></span>
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

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">
                <div class="xl:col-span-3">
                    <div id="network-graph-empty" class="hidden h-[min(72vh,720px)] w-full rounded-xl border border-dashed border-gray-300 bg-slate-50 flex items-center justify-center p-8 text-center">
                        <p class="text-sm text-gray-600">No graph data. Sync historical data, then click <strong>Refresh graph</strong>.</p>
                    </div>
                    <div id="network-graph-wrap" class="relative h-[min(72vh,720px)] w-full rounded-xl border border-gray-200 bg-slate-50 overflow-hidden">
                        <div id="network-graph-canvas" class="h-full w-full"></div>
                        <div id="network-graph-loading" class="absolute inset-0 hidden items-center justify-center bg-slate-50/90 text-sm text-gray-600 z-10">
                            Loading graph...
                        </div>
                        <div id="network-graph-hint" class="pointer-events-none absolute bottom-2 left-2 rounded bg-white/90 px-2 py-1 text-[11px] text-gray-600 border border-gray-200 shadow-sm">
                            Drag = pan · Scroll = zoom · Hover nodes for details
                        </div>
                    </div>
                </div>
                <div class="xl:col-span-1 rounded-xl border border-gray-200 bg-white flex flex-col max-h-[min(72vh,720px)]">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Users & access</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Click a user to highlight links.</p>
                    </div>
                    <div id="ng-users-list" class="h-1/2 overflow-y-auto divide-y divide-gray-100 text-sm">
                        <p class="px-4 py-6 text-xs text-gray-400 text-center">Click <strong>Load graph</strong> first.</p>
                    </div>
                    <div class="px-4 py-3 border-y border-gray-100 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-900">User Activity Logs</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Recent actions from users and guests.</p>
                    </div>
                    <div id="ng-activity-list" class="h-1/2 overflow-y-auto divide-y divide-gray-100 text-xs">
                        <p class="px-4 py-6 text-gray-400 text-center">Activity logs will appear here.</p>
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
    const wrap = document.getElementById('network-graph-wrap');
    const canvas = document.getElementById('network-graph-canvas');
    const emptyState = document.getElementById('network-graph-empty');
    const loadBtn = document.getElementById('ng-refresh-data');
    const loading = document.getElementById('network-graph-loading');

    if (!canvas || typeof vis === 'undefined') return;

    let network = null;
    let nodesDs = null;
    let edgesDs = null;
    let selectedUserKey = null;
    let allActivityLogs = [];
    let selectedNodeId = null;
    let loadInProgress = false;

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

        return logs.filter(log => {
            const url = String(log.page_url || '').toLowerCase();
            const type = String(log.activity_type || '').toLowerCase();
            return (lowerLabel && url.includes(lowerLabel)) || (lowerLabel && type.includes(lowerLabel));
        });
    }

    function escapeHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function focusUser(nodeKey) {
        if (!network || !nodeKey) return;
        selectedUserKey = nodeKey;
        const connected = network.getConnectedNodes(nodeKey);
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

    function setGraphData(payload) {
        const rawNodes = payload.nodes || [];
        updateStats(payload.stats);
        renderUsersList(payload.users || []);
        allActivityLogs = payload.activity_logs || [];
        renderActivityLogs(allActivityLogs);

        if (!rawNodes.length) {
            wrap.classList.add('hidden');
            emptyState.classList.remove('hidden');
            if (network) {
                network.destroy();
                network = null;
                nodesDs = null;
                edgesDs = null;
            }
            return;
        }

        wrap.classList.remove('hidden');
        emptyState.classList.add('hidden');

        const nodeItems = rawNodes.map((n) => ({
            id: n.id,
            label: n.label,
            group: n.group,
            value: Math.max(1, Number(n.value || 1)),
            title: `${n.label} (${Number(n.value || 0).toLocaleString()} hits)`,
            shape: n.group === 'user' ? 'diamond' : 'dot',
            color: typeof n.color === 'string' ? n.color : undefined,
        }));

        const edgeItems = (payload.edges || []).map((e) => ({
            id: `${e.from}|${e.to}`,
            from: e.from,
            to: e.to,
            value: Math.max(1, Number(e.value || 1)),
            dashes: !!e.dashes,
            color: e.dashes ? '#c084fc' : '#94a3b8',
            width: Math.min(6, Math.max(1, Math.log((e.value || 1) + 1))),
        }));

        if (!network) {
            nodesDs = new vis.DataSet(nodeItems);
            edgesDs = new vis.DataSet(edgeItems);
            const maxValue = nodeItems.reduce((m, n) => Math.max(m, Number(n.value || 1)), 1);

            nodesDs.update(nodeItems.map((n) => {
                const t = Math.min(1, Math.max(0, Number(n.value || 1) / maxValue));
                const hue = Math.round(200 - (200 * t)); // blue -> red
                const sat = 80;
                const light = Math.round(58 - (20 * t));

                return {
                    ...n,
                    color: `hsl(${hue} ${sat}% ${light}%)`,
                };
            }));

            network = new vis.Network(canvas, { nodes: nodesDs, edges: edgesDs }, {
                layout: { improvedLayout: true, randomSeed: 2 },
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
                    timestep: 0.3,
                    stabilization: { enabled: false },
                    forceAtlas2Based: {
                        gravitationalConstant: -115,
                        centralGravity: 0.015,
                        springLength: 52,
                        springConstant: 0.045,
                        damping: 0.55,
                    },
                    maxVelocity: 32,
                    minVelocity: 0.35,
                },
            });

            network.on('click', (params) => {
                if (params.nodes.length === 1) {
                    const id = String(params.nodes[0]);
                    const clickedNode = nodesDs.get(id);
                    selectedNodeId = id;
                    const filteredLogs = filterLogsByNode(id, clickedNode?.label || '');
                    renderActivityLogs(filteredLogs, clickedNode?.label || id);

                    if (id.startsWith('user:') || id.startsWith('guest:')) {
                        focusUser(id);
                    }
                } else if (params.nodes.length === 0) {
                    selectedNodeId = null;
                    renderActivityLogs(allActivityLogs);
                }
            });
        } else {
            const maxValue = nodeItems.reduce((m, n) => Math.max(m, Number(n.value || 1)), 1);
            const recolored = nodeItems.map((n) => {
                const t = Math.min(1, Math.max(0, Number(n.value || 1) / maxValue));
                const hue = Math.round(200 - (200 * t));
                const sat = 80;
                const light = Math.round(58 - (20 * t));

                return {
                    ...n,
                    color: `hsl(${hue} ${sat}% ${light}%)`,
                };
            });

            const nodeIds = new Set(nodeItems.map(n => n.id));
            const edgeIds = new Set(edgeItems.map(e => e.id));
            nodesDs.update(recolored);
            nodesDs.remove(nodesDs.getIds().filter(id => !nodeIds.has(id)));
            edgesDs.update(edgeItems);
            edgesDs.remove(edgesDs.getIds().filter(id => !edgeIds.has(id)));
            network.startSimulation();
        }

        const hint = document.getElementById('network-graph-hint');
        if (hint) {
            hint.textContent = `Showing ${nodeItems.length} nodes, ${edgeItems.length} links · Drag = pan · Scroll = zoom · Labels enabled`;
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
            if (!res.ok) throw new Error('Failed to load');
            const payload = await res.json();
            setGraphData(payload);
        } catch (e) {
            console.warn(e);
            alert('Could not load graph data. Try again or sync historical data first.');
        } finally {
            loadInProgress = false;
            setLoading(false);
            if (loadBtn) { loadBtn.disabled = false; loadBtn.textContent = 'Refresh graph'; }
        }
    }

    document.getElementById('ng-reset-view')?.addEventListener('click', () => {
        selectedUserKey = null;
        selectedNodeId = null;
        if (network) {
            network.unselectAll();
            network.fit({ animation: { duration: 350, easingFunction: 'easeInOutQuad' } });
            network.startSimulation();
        }
        renderActivityLogs(allActivityLogs);
        document.querySelectorAll('.ng-user-row').forEach(r => r.classList.remove('bg-indigo-50', 'ring-1', 'ring-indigo-200'));
    });

    loadBtn?.addEventListener('click', loadGraphData);
    // Auto-load on page open
    setTimeout(() => { loadGraphData(); }, 120);
})();
</script>
@endpush
