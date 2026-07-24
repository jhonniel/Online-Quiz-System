{{-- Shared chrome: invite banner + sync helper --}}
<div class="mb-4 flex flex-wrap items-center gap-2 justify-between">
    <a href="{{ route('say-it.games.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-violet-600">
        <i class="fas fa-arrow-left"></i> All games
    </a>
    <div class="inline-flex items-center gap-2 rounded-full bg-violet-50 border border-violet-100 px-3 py-1.5">
        <span class="text-xs font-semibold text-violet-500 uppercase tracking-wide">Invite</span>
        <span class="font-mono text-lg font-bold tracking-[0.2em] text-violet-800" id="sayit-game-code">{{ $session->code }}</span>
        <button type="button" onclick="navigator.clipboard.writeText('{{ $session->code }}')" class="text-xs font-semibold text-violet-600 hover:text-violet-800">Copy</button>
    </div>
</div>

<div id="sayit-players-bar" class="mb-4 flex flex-wrap gap-2"></div>
<p id="sayit-turn-bar" class="mb-3 text-sm font-medium text-violet-700 hidden"></p>

<script>
window.SayItGame = {
    code: @json($session->code),
    playerId: @json($playerId ?? null),
    players: @json($session->orderedPlayers()),
    stateUrl: @json(route('say-it.games.state', $session->code)),
    syncUrl: @json(route('say-it.games.sync', $session->code)),
    csrf: document.querySelector('meta[name="csrf-token"]').content,
    saveTimer: null,
    pollTimer: null,
    onRemote: null,
    saving: false,
    applyingRemote: false,

    headers() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrf,
            'X-SayIt-Player': this.playerId || '',
        };
    },

    save(state, status) {
        if (this.applyingRemote) return;
        var self = this;
        clearTimeout(this.saveTimer);
        this.saveTimer = setTimeout(function() {
            self.saving = true;
            fetch(self.stateUrl, {
                method: 'POST',
                headers: self.headers(),
                credentials: 'same-origin',
                body: JSON.stringify({ state: state, status: status || 'playing' }),
            }).finally(function() { self.saving = false; });
        }, 200);
    },

    startPolling(intervalMs) {
        var self = this;
        clearInterval(this.pollTimer);
        this.pollTimer = setInterval(function() { self.pull(); }, intervalMs || 2000);
        this.pull();
    },

    pull() {
        var self = this;
        if (this.saving) return;
        fetch(this.syncUrl, {
            headers: this.headers(),
            credentials: 'same-origin',
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (!data || !data.ok || !data.payload) return;
            self.players = data.payload.players || self.players;
            self.renderPlayersBar();
            if (typeof self.onRemote === 'function') {
                self.applyingRemote = true;
                try { self.onRemote(data.payload); } finally { self.applyingRemote = false; }
            }
        }).catch(function() {});
    },

    renderPlayersBar() {
        var el = document.getElementById('sayit-players-bar');
        if (!el) return;
        var players = this.players || [];
        var me = this.playerId;
        el.innerHTML = players.map(function(p) {
            var you = p.id === me;
            return '<span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold border ' +
                (you ? 'bg-violet-100 border-violet-200 text-violet-800' : 'bg-gray-50 border-gray-200 text-gray-700') + '">' +
                (p.name || 'Player') + (you ? ' (you)' : '') + (p.is_host ? ' ★' : '') + '</span>';
        }).join('');
    },

    setTurnMessage(msg) {
        var el = document.getElementById('sayit-turn-bar');
        if (!el) return;
        if (!msg) { el.classList.add('hidden'); el.textContent = ''; return; }
        el.classList.remove('hidden');
        el.textContent = msg;
    },

    playerName(id) {
        var p = (this.players || []).find(function(x) { return x.id === id; });
        return p ? p.name : 'Player';
    },

    turnPlayerId(state) {
        var list = this.players || [];
        if (!list.length) return null;
        var idx = (state && state.turn_index != null) ? state.turn_index : 0;
        return list[idx % list.length].id;
    },

    isMyTurn(state) {
        return this.turnPlayerId(state) === this.playerId;
    }
};

try {
    if (window.SayItGame.playerId) {
        localStorage.setItem('sayit_game_player', window.SayItGame.playerId);
    }
} catch (e) {}

window.SayItGame.renderPlayersBar();
</script>
