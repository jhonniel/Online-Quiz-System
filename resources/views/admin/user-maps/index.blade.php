@extends('layouts.admin')

@section('title', 'User Maps')

@section('content')
<style>
    #user-map {
        width: 100%;
        height: min(70vh, 720px);
        min-height: 420px;
        border-radius: 0.75rem;
    }

    .user-map-marker-wrap {
        position: relative;
        width: 14px;
        height: 14px;
        cursor: pointer;
    }

    .user-map-marker-wrap--online::before,
    .user-map-marker-wrap--online::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: rgba(34, 197, 94, 0.55);
        pointer-events: none;
        animation: user-map-ping 1.6s ease-out infinite;
    }

    .user-map-marker-wrap--online::after {
        animation-delay: 0.8s;
    }

    .user-map-marker {
        position: relative;
        z-index: 1;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.35);
    }

    .user-map-marker--online {
        background: #22c55e;
    }

    .user-map-marker--offline {
        background: #ef4444;
    }

    @keyframes user-map-ping {
        0% {
            transform: scale(1);
            opacity: 0.7;
        }
        100% {
            transform: scale(3);
            opacity: 0;
        }
    }

    .maplibregl-popup-content {
        font-family: inherit;
        border-radius: 0.5rem;
        padding: 0.75rem;
    }
</style>

<div class="space-y-6">
    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-sm p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V6.618a2 2 0 011.553-1.946L9 2m0 18l6-3m-6 3V2m6 15l5.447 2.724A2 2 0 0021 17.382V8.618a2 2 0 00-1.553-1.946L15 4m0 13V4m0 0L9 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-white">User Maps</h1>
                    <p class="text-indigo-100">Pin every IP address recorded in User Activity Logs on the TomTom map.</p>
                </div>
            </div>
            <a href="{{ url('/admin/settings?tab=general') }}"
               class="inline-flex items-center px-4 py-2 border border-white/30 rounded-md text-sm font-medium text-white bg-white/10 hover:bg-white/20">
                TomTom API settings
            </a>
        </div>
    </div>

    @unless($hasTomTomKey)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            TomTom API key is not configured.
            <a href="{{ url('/admin/settings?tab=general') }}" class="font-medium underline">Add your TomTom API key in Admin Settings</a>
            to load the map tiles.
        </div>
    @endunless

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50/80">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-3">
                <div class="xl:col-span-2">
                    <label for="user-map-search" class="block text-xs font-medium text-gray-600 mb-1">Search user</label>
                    <input type="text" id="user-map-search" placeholder="Name or email..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="user-map-ip" class="block text-xs font-medium text-gray-600 mb-1">IP address</label>
                    <input type="text" id="user-map-ip" placeholder="e.g. 203.177..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="user-map-activity-type" class="block text-xs font-medium text-gray-600 mb-1">Activity type</label>
                    <select id="user-map-activity-type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All types</option>
                    </select>
                </div>
                <div>
                    <label for="user-map-role" class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                    <select id="user-map-role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All roles</option>
                        <option value="admin">Admin</option>
                        <option value="employee">Employee</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="technician">Technician</option>
                        <option value="applicant">Applicant</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div>
                    <label for="user-map-department" class="block text-xs font-medium text-gray-600 mb-1">Department</label>
                    <select id="user-map-department" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All departments</option>
                    </select>
                </div>
                <div>
                    <label for="user-map-university" class="block text-xs font-medium text-gray-600 mb-1">University</label>
                    <select id="user-map-university" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All universities</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label for="user-map-date-from" class="block text-xs font-medium text-gray-600 mb-1">Activity from</label>
                    <input type="date" id="user-map-date-from"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="user-map-date-to" class="block text-xs font-medium text-gray-600 mb-1">Activity to</label>
                    <input type="date" id="user-map-date-to"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" id="user-map-online-only" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Online users only
                </label>
                <button type="button" id="user-map-refresh"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    Refresh map
                </button>
                <p id="user-map-status" class="text-sm text-gray-500">{{ $hasTomTomKey ? 'Loading map...' : 'Configure TomTom API key to load the map.' }}</p>
            </div>
        </div>

        <div class="p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-xs text-gray-500">Activity log IPs</p>
                    <p id="stat-total-ips" class="text-lg font-semibold text-gray-900">0</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-xs text-gray-500">On map</p>
                    <p id="stat-mapped" class="text-lg font-semibold text-gray-900">0</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-xs text-gray-500">Unmapped</p>
                    <p id="stat-unmapped" class="text-lg font-semibold text-gray-900">0</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-xs text-gray-500">Online</p>
                    <p id="stat-online" class="text-lg font-semibold text-gray-900">0</p>
                </div>
            </div>

            <div id="user-map" class="bg-slate-100"></div>
            <p class="mt-3 text-xs text-gray-500">
                Pins come from distinct IP addresses in
                <a href="{{ url('/admin/user-activity') }}" class="text-indigo-600 hover:text-indigo-800">User Activity Logs</a>
                (geocoded when public) and browser GPS shared at login. Local/private IPs (127.0.0.1) use GPS when available.
                In production, set <code class="text-xs bg-gray-100 px-1 rounded">TRUSTED_PROXIES=*</code> so real client IPs are logged.
            </p>
        </div>
    </div>
</div>
@endsection

@if($hasTomTomKey && $tomtomMapStyleUrl)
@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css">
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>
<script>
    (function () {
        const dataUrl = @json(route('admin.user-maps.data'));
        const mapStyle = @json($tomtomMapStyleUrl);
        let map = null;
        let markers = [];
        let loadTimer = null;
        let mapReady = false;

        function setStatus(message) {
            document.getElementById('user-map-status').textContent = message;
        }

        function updateStats(stats) {
            document.getElementById('stat-total-ips').textContent = stats.total_ips ?? 0;
            document.getElementById('stat-mapped').textContent = stats.mapped ?? 0;
            document.getElementById('stat-unmapped').textContent = stats.unmapped ?? 0;
            document.getElementById('stat-online').textContent = stats.online ?? 0;
        }

        function fillSelect(id, items, labelKey = 'name', valueKey = 'id') {
            const select = document.getElementById(id);
            const current = select.value;
            select.querySelectorAll('option:not(:first-child)').forEach(option => option.remove());
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = typeof item === 'string' ? item : String(item[valueKey]);
                option.textContent = typeof item === 'string' ? item : item[labelKey];
                select.appendChild(option);
            });
            if (current) {
                select.value = current;
            }
        }

        function clearMarkers() {
            markers.forEach(marker => marker.remove());
            markers = [];
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function popupHtml(marker) {
            const onlineBadge = marker.is_online
                ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Online</span>'
                : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Offline</span>';

            const usersHtml = (marker.users || []).slice(0, 5).map(user => `
                <div class="text-xs text-gray-600">
                    ${escapeHtml(user.name)}${user.is_online ? ' <span class="text-green-600">(online)</span>' : ''}
                </div>
            `).join('');

            const moreUsers = (marker.users || []).length > 5
                ? `<p class="text-xs text-gray-400">+${marker.users.length - 5} more user(s)</p>`
                : '';

            let sourceLabel = '<span class="text-xs text-gray-400">IP-based estimate</span>';
            if (marker.location_source === 'browser_gps') {
                sourceLabel = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">GPS (accurate)</span>';
            } else if (marker.location_source === 'activity_log_gps') {
                sourceLabel = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Local IP + user GPS</span>';
            }

            return `
                <div class="text-sm space-y-1 min-w-[200px]">
                    <p class="font-semibold text-gray-900">${escapeHtml(marker.location_source === 'browser_gps' ? (marker.name || 'GPS location') : marker.ip_address)}</p>
                    <p class="text-xs text-gray-500">${escapeHtml(marker.location_label || '')}</p>
                    <div class="pt-1">${sourceLabel}</div>
                    <p class="text-xs text-gray-500">${marker.hit_count ?? 0} log hit(s) · Last seen ${escapeHtml(marker.last_seen_human || '')}</p>
                    <div class="pt-1">${onlineBadge}</div>
                    ${(marker.users || []).length ? `<div class="pt-2 space-y-1"><p class="text-xs font-medium text-gray-700">Users (${marker.user_count ?? 0})</p>${usersHtml}${moreUsers}</div>` : '<p class="text-xs text-gray-400 pt-1">No linked user account</p>'}
                    <div class="pt-2 flex flex-col gap-1">
                        <a href="${escapeHtml(marker.activity_log_url)}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">View activity logs</a>
                        ${marker.admin_url ? `<a href="${escapeHtml(marker.admin_url)}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">View primary user</a>` : ''}
                    </div>
                </div>
            `;
        }

        function renderMarkers(markerData) {
            if (!mapReady || !map) {
                return;
            }

            clearMarkers();

            markerData.forEach(marker => {
                const wrap = document.createElement('div');
                wrap.className = 'user-map-marker-wrap ' + (marker.is_online ? 'user-map-marker-wrap--online' : 'user-map-marker-wrap--offline');

                const dot = document.createElement('div');
                dot.className = 'user-map-marker ' + (marker.is_online ? 'user-map-marker--online' : 'user-map-marker--offline');
                wrap.appendChild(dot);

                const popup = new maplibregl.Popup({ offset: 18, maxWidth: '260px' }).setHTML(popupHtml(marker));
                const mapMarker = new maplibregl.Marker({ element: wrap })
                    .setLngLat([marker.lng, marker.lat])
                    .setPopup(popup)
                    .addTo(map);

                markers.push(mapMarker);
            });

            if (markerData.length === 1) {
                map.flyTo({ center: [markerData[0].lng, markerData[0].lat], zoom: 10 });
            } else if (markerData.length > 1) {
                const bounds = new maplibregl.LngLatBounds();
                markerData.forEach(marker => bounds.extend([marker.lng, marker.lat]));
                map.fitBounds(bounds, { padding: 60, maxZoom: 12 });
            }
        }

        async function loadMapData() {
            if (!mapReady) {
                return;
            }

            setStatus('Loading activity log IPs...');

            const params = new URLSearchParams();
            const search = document.getElementById('user-map-search').value.trim();
            const ipAddress = document.getElementById('user-map-ip').value.trim();
            const activityType = document.getElementById('user-map-activity-type').value;
            const role = document.getElementById('user-map-role').value;
            const departmentId = document.getElementById('user-map-department').value;
            const universityId = document.getElementById('user-map-university').value;
            const dateFrom = document.getElementById('user-map-date-from').value;
            const dateTo = document.getElementById('user-map-date-to').value;
            const onlineOnly = document.getElementById('user-map-online-only').checked;

            if (search) params.set('search', search);
            if (ipAddress) params.set('ip_address', ipAddress);
            if (activityType) params.set('activity_type', activityType);
            if (role) params.set('role', role);
            if (departmentId) params.set('department_id', departmentId);
            if (universityId) params.set('university_id', universityId);
            if (dateFrom) params.set('date_from', dateFrom);
            if (dateTo) params.set('date_to', dateTo);
            if (onlineOnly) params.set('online_only', '1');

            try {
                const response = await fetch(dataUrl + '?' + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Unable to load map data.');
                }

                fillSelect('user-map-department', data.filters?.departments || []);
                fillSelect('user-map-university', data.filters?.universities || []);
                fillSelect('user-map-activity-type', data.filters?.activity_types || [], null, null);
                updateStats(data.stats || {});
                renderMarkers(data.markers || []);

                const mapped = data.stats?.mapped ?? 0;
                const totalIps = data.stats?.total_ips ?? 0;
                const unmapped = data.stats?.unmapped ?? 0;
                const privateIps = data.stats?.private_ips ?? 0;

                let status;
                if (mapped > 0) {
                    status = `Showing ${mapped} pin(s) from ${totalIps} activity log IP(s).`;
                } else if (totalIps === 0) {
                    status = 'No IP addresses found in User Activity Logs for the current filters.';
                } else if (privateIps === totalIps) {
                    status = `${totalIps} IP(s) in activity logs are private/local (e.g. 127.0.0.1) and cannot be geocoded. Set TRUSTED_PROXIES=* in production, or have users share location at login.`;
                } else {
                    status = `${unmapped} of ${totalIps} activity log IP(s) could not be geocoded.`;
                }
                if (data.stats?.capped) {
                    status += ' Showing the most recent 500 IPs.';
                }
                setStatus(status);
            } catch (error) {
                setStatus(error.message || 'Unable to load map data.');
            }
        }

        function scheduleLoad() {
            clearTimeout(loadTimer);
            loadTimer = setTimeout(loadMapData, 350);
        }

        function initMap() {
            if (typeof maplibregl === 'undefined') {
                setStatus('Map library failed to load. Check your network connection.');
                return;
            }

            map = new maplibregl.Map({
                container: 'user-map',
                style: mapStyle,
                center: [121.0244, 14.5547],
                zoom: 5,
            });

            map.addControl(new maplibregl.NavigationControl(), 'top-right');

            map.on('load', function () {
                map.resize();
                mapReady = true;
                setStatus('Map loaded. Fetching user locations...');
                loadMapData();
            });

            map.on('error', function (event) {
                const message = event?.error?.message || 'TomTom map tiles could not be loaded.';
                setStatus(message + ' Verify your TomTom API key in Admin Settings.');
            });
        }

        document.getElementById('user-map-refresh').addEventListener('click', loadMapData);
        document.getElementById('user-map-search').addEventListener('input', scheduleLoad);
        document.getElementById('user-map-ip').addEventListener('input', scheduleLoad);
        document.getElementById('user-map-activity-type').addEventListener('change', loadMapData);
        document.getElementById('user-map-role').addEventListener('change', loadMapData);
        document.getElementById('user-map-department').addEventListener('change', loadMapData);
        document.getElementById('user-map-university').addEventListener('change', loadMapData);
        document.getElementById('user-map-date-from').addEventListener('change', loadMapData);
        document.getElementById('user-map-date-to').addEventListener('change', loadMapData);
        document.getElementById('user-map-online-only').addEventListener('change', loadMapData);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            initMap();
        }
    })();
</script>
@endsection
@endif
