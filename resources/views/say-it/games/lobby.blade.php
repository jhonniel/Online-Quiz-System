@extends('layouts.say-it')

@section('title', 'Lobby – '.$session->label())

@section('content')
<div class="max-w-xl mx-auto w-full px-1 sm:px-0" id="lobby-root"
     data-code="{{ $session->code }}"
     data-sync="{{ route('say-it.games.sync', $session->code) }}"
     data-play="{{ route('say-it.games.play', $session->code) }}"
     data-player="{{ $playerId }}">
    <a href="{{ route('say-it.games.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-violet-600 mb-4">
        <i class="fas fa-arrow-left"></i> All games
    </a>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-violet-500">Waiting lobby</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ $session->label() }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    @if($session->isOpen())
                        Open room — anyone can join from the games hub.
                    @else
                        Private room — share the invite code.
                    @endif
                </p>
            </div>
            <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $session->isOpen() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
                {{ $session->isOpen() ? 'Open' : 'Private' }}
            </span>
        </div>

        <div class="rounded-2xl bg-violet-50 border border-violet-100 p-4 text-center mb-5">
            <div class="text-xs font-semibold text-violet-500 uppercase tracking-wide">Invite code</div>
            <div class="mt-1 font-mono text-3xl font-bold tracking-[0.3em] text-violet-900" id="lobby-code">{{ $session->code }}</div>
            <button type="button" onclick="navigator.clipboard.writeText('{{ $session->code }}')" class="mt-2 text-sm font-semibold text-violet-600 hover:text-violet-800">Copy code</button>
        </div>

        <h2 class="text-sm font-semibold text-gray-700 mb-2">Players <span class="text-gray-400 font-normal" id="lobby-count">({{ $session->playerCount() }}/{{ \App\Models\SayItGameSession::MAX_PLAYERS }})</span></h2>
        <ul id="lobby-players" class="space-y-2 mb-5">
            @foreach($session->orderedPlayers() as $p)
                <li class="flex items-center justify-between rounded-xl border border-gray-100 px-3 py-2.5 {{ ($p['id'] ?? '') === $playerId ? 'bg-violet-50 border-violet-200' : 'bg-gray-50' }}">
                    <span class="font-medium text-gray-900">{{ $p['name'] ?? 'Player' }}</span>
                    <span class="text-xs font-semibold text-gray-400">
                        @if(!empty($p['is_host'])) Host @endif
                        @if(($p['id'] ?? '') === $playerId) · You @endif
                    </span>
                </li>
            @endforeach
        </ul>

        <div class="flex flex-col sm:flex-row gap-2">
            @if($isHost)
                <form method="POST" action="{{ route('say-it.games.start-game', $session->code) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-semibold py-3 transition">
                        Start game
                    </button>
                </form>
            @else
                <p class="flex-1 text-sm text-gray-500 py-3 text-center sm:text-left">Waiting for the host to start…</p>
            @endif
            <form method="POST" action="{{ route('say-it.games.leave', $session->code) }}">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-5 py-3 rounded-xl border border-gray-200 font-semibold text-gray-700 hover:bg-gray-50">
                    Leave
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var root = document.getElementById('lobby-root');
    var syncUrl = root.dataset.sync;
    var playUrl = root.dataset.play;
    var playerId = root.dataset.player;
    var csrf = document.querySelector('meta[name="csrf-token"]').content;

    function renderPlayers(payload) {
        var list = document.getElementById('lobby-players');
        var players = payload.players || [];
        document.getElementById('lobby-count').textContent = '(' + players.length + '/' + (payload.max_players || 4) + ')';
        list.innerHTML = players.map(function(p) {
            var you = p.id === playerId;
            return '<li class="flex items-center justify-between rounded-xl border px-3 py-2.5 ' +
                (you ? 'bg-violet-50 border-violet-200' : 'bg-gray-50 border-gray-100') + '">' +
                '<span class="font-medium text-gray-900">' + (p.name || 'Player') + '</span>' +
                '<span class="text-xs font-semibold text-gray-400">' +
                (p.is_host ? 'Host' : '') + (you ? (p.is_host ? ' · You' : 'You') : '') +
                '</span></li>';
        }).join('');
    }

    function apply(payload) {
        if (!payload) return;
        if (payload.status === 'playing' || payload.status === 'finished') {
            window.location.href = playUrl;
            return;
        }
        renderPlayers(payload);
    }

    function poll() {
        fetch(syncUrl, {
            headers: {
                'Accept': 'application/json',
                'X-SayIt-Player': playerId,
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'same-origin',
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data && data.ok) apply(data.payload);
        }).catch(function() {});
    }

    setInterval(poll, 2000);
    poll();

    try {
        localStorage.setItem('sayit_game_player', playerId);
    } catch (e) {}
})();
</script>
@endpush
@endsection
