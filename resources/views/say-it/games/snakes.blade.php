@extends('layouts.say-it')

@section('title', 'Snake & Ladder – Say it Games')

@include('say-it.games._game-styles')

@section('content')
<div class="max-w-3xl mx-auto w-full">
    @include('say-it.games._chrome')

    <div class="bg-white rounded-3xl border border-emerald-200 shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-700 via-teal-600 to-emerald-800 px-4 sm:px-6 py-4 text-white flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-[10px] uppercase tracking-[0.2em] text-emerald-100/80 font-semibold">Board race · first to 100</p>
                <h1 class="text-2xl font-bold">Snake & Ladder</h1>
            </div>
            <button type="button" id="sl-roll" class="relative px-6 py-3 rounded-2xl bg-white text-emerald-800 font-black shadow-lg hover:scale-105 transition disabled:opacity-40 disabled:hover:scale-100">
                <span id="sl-roll-label">Roll dice</span>
            </button>
        </div>

        <div class="p-4 sm:p-6 space-y-4">
            <div id="sl-scores" class="grid grid-cols-2 sm:grid-cols-4 gap-2"></div>

            <div class="flex items-center justify-center gap-4">
                <div id="sl-dice" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white border-2 border-emerald-200 shadow-md flex items-center justify-center text-4xl sm:text-5xl font-black text-emerald-800 select-none">?</div>
                <div class="text-sm text-gray-600 max-w-[12rem]" id="sl-status">Roll to move.</div>
            </div>

            <div class="relative rounded-2xl overflow-hidden sayit-snakes-board p-1.5 sm:p-2 shadow-inner">
                <div id="sl-board" class="grid grid-cols-10 gap-0.5 sm:gap-1 relative z-10"></div>
                <svg id="sl-overlays" class="pointer-events-none absolute inset-0 w-full h-full z-0 opacity-70" viewBox="0 0 100 100" preserveAspectRatio="none"></svg>
            </div>

            <div class="rounded-2xl bg-emerald-50 border border-emerald-100 p-3">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-emerald-700 mb-1">Game log</div>
                <ul id="sl-log" class="text-xs text-emerald-900/80 space-y-1 max-h-28 overflow-y-auto font-medium"></ul>
            </div>

            <div class="grid grid-cols-2 gap-2 text-[11px] text-gray-500">
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-2"><span class="inline-block w-2 h-2 rounded-sm bg-emerald-400 mr-1"></span> Ladder — climb up</div>
                <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-2"><span class="inline-block w-2 h-2 rounded-sm bg-rose-400 mr-1"></span> Snake — slide down</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var SNAKES = {16:6,47:26,49:11,56:53,62:19,64:60,87:24,93:73,95:75,98:78};
    var LADDERS = {1:38,4:14,9:31,21:42,28:84,36:44,51:67,71:91,80:100};
    var TOKEN = [
        { bg: 'bg-emerald-500', ring: 'ring-emerald-300', text: 'text-emerald-700', chip: 'bg-emerald-50 border-emerald-200', face: '🟢' },
        { bg: 'bg-rose-500', ring: 'ring-rose-300', text: 'text-rose-700', chip: 'bg-rose-50 border-rose-200', face: '🔴' },
        { bg: 'bg-sky-500', ring: 'ring-sky-300', text: 'text-sky-700', chip: 'bg-sky-50 border-sky-200', face: '🔵' },
        { bg: 'bg-amber-500', ring: 'ring-amber-300', text: 'text-amber-700', chip: 'bg-amber-50 border-amber-200', face: '🟡' },
    ];
    var DICE_FACES = ['⚀','⚁','⚂','⚃','⚄','⚅'];

    var me = window.SayItGame.playerId;
    var state = Object.assign({}, @json($session->state ?? []));
    var rolling = false;
    var cellEls = {};

    function players() { return window.SayItGame.players || []; }

    function ensure() {
        state.positions = state.positions || {};
        state.log = state.log || [];
        state.turn_index = state.turn_index || 0;
        players().forEach(function(p) {
            if (state.positions[p.id] == null) state.positions[p.id] = 1;
        });
    }

    function applyPortals(pos) {
        if (LADDERS[pos]) return {pos: LADDERS[pos], msg: '🪜 Ladder! Climb to ' + LADDERS[pos], type: 'ladder'};
        if (SNAKES[pos]) return {pos: SNAKES[pos], msg: '🐍 Snake! Slide to ' + SNAKES[pos], type: 'snake'};
        return {pos: pos, msg: null, type: null};
    }

    function persist(status) {
        window.SayItGame.save(state, status || (state.winner ? 'finished' : 'playing'));
    }

    function cellCenterPct(n) {
        // Convert board number 1-100 to % center in snake-row layout
        var row = Math.floor((n - 1) / 10); // 0..9 from bottom
        var colInRow = (n - 1) % 10;
        var col = row % 2 === 0 ? colInRow : 9 - colInRow;
        var visualRow = 9 - row; // top is 100s
        return { x: (col + 0.5) * 10, y: (visualRow + 0.5) * 10 };
    }

    function drawOverlays() {
        var svg = document.getElementById('sl-overlays');
        var parts = [];
        Object.keys(LADDERS).forEach(function(from) {
            var a = cellCenterPct(+from), b = cellCenterPct(LADDERS[from]);
            parts.push('<line x1="'+a.x+'" y1="'+a.y+'" x2="'+b.x+'" y2="'+b.y+'" stroke="#059669" stroke-width="1.2" stroke-linecap="round" opacity="0.85"/>');
            parts.push('<circle cx="'+a.x+'" cy="'+a.y+'" r="1.2" fill="#059669"/>');
            parts.push('<circle cx="'+b.x+'" cy="'+b.y+'" r="1.2" fill="#10b981"/>');
        });
        Object.keys(SNAKES).forEach(function(from) {
            var a = cellCenterPct(+from), b = cellCenterPct(SNAKES[from]);
            var mx = (a.x + b.x) / 2 + 3, my = (a.y + b.y) / 2;
            parts.push('<path d="M '+a.x+' '+a.y+' Q '+mx+' '+my+' '+b.x+' '+b.y+'" fill="none" stroke="#e11d48" stroke-width="1.4" stroke-linecap="round" opacity="0.85"/>');
            parts.push('<circle cx="'+a.x+'" cy="'+a.y+'" r="1.3" fill="#e11d48"/>');
        });
        svg.innerHTML = parts.join('');
    }

    function renderBoard() {
        ensure();
        var list = players();
        var myTurn = !state.winner && window.SayItGame.isMyTurn(state);
        var turnId = window.SayItGame.turnPlayerId(state);

        document.getElementById('sl-scores').innerHTML = list.map(function(p, i) {
            var t = TOKEN[i % TOKEN.length];
            var active = p.id === turnId && !state.winner;
            return '<div class="rounded-2xl border p-3 ' + t.chip + (active ? ' sayit-turn-pulse ring-2 ' + t.ring : '') + '">' +
                '<div class="flex items-center gap-1.5 text-xs font-semibold ' + t.text + '">' +
                '<span>' + t.face + '</span><span class="truncate">' + p.name + (p.id === me ? ' (you)' : '') + '</span></div>' +
                '<div class="mt-1 text-2xl font-black tabular-nums ' + t.text + '">' + (state.positions[p.id] || 1) + '</div>' +
                '<div class="text-[10px] text-gray-400">square</div></div>';
        }).join('');

        var board = document.getElementById('sl-board');
        board.innerHTML = '';
        cellEls = {};
        for (var row = 9; row >= 0; row--) {
            for (var col = 0; col < 10; col++) {
                var n = row % 2 === 0 ? (row * 10 + col + 1) : (row * 10 + (9 - col) + 1);
                var cell = document.createElement('div');
                var bg = (row + col) % 2 === 0 ? 'bg-white' : 'bg-emerald-50/80';
                if (LADDERS[n]) bg = 'bg-emerald-200/90';
                if (SNAKES[n]) bg = 'bg-rose-200/90';
                if (n === 100) bg = 'bg-amber-300';
                if (n === 1) bg = 'bg-sky-200';
                cell.className = 'aspect-square rounded-md sm:rounded-lg text-[8px] sm:text-[10px] flex flex-col items-center justify-center border border-emerald-900/10 relative ' + bg;
                cell.dataset.n = n;
                var icon = '';
                if (LADDERS[n]) icon = '<span class="absolute top-0.5 right-0.5 text-[8px]">🪜</span>';
                if (SNAKES[n]) icon = '<span class="absolute top-0.5 right-0.5 text-[8px]">🐍</span>';
                if (n === 100) icon = '<span class="absolute top-0.5 right-0.5 text-[8px]">🏆</span>';
                var tokens = '';
                list.forEach(function(p, i) {
                    if ((state.positions[p.id] || 1) === n) {
                        var t = TOKEN[i % TOKEN.length];
                        tokens += '<span class="inline-block w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full ' + t.bg + ' ring-1 ring-white sayit-token-hop shadow" title="' + p.name + '"></span>';
                    }
                });
                cell.innerHTML = '<span class="text-emerald-900/40 font-semibold leading-none">' + n + '</span>' + icon +
                    (tokens ? '<div class="flex gap-0.5 mt-0.5 leading-none">' + tokens + '</div>' : '');
                board.appendChild(cell);
                cellEls[n] = cell;
            }
        }
        drawOverlays();

        var diceEl = document.getElementById('sl-dice');
        if (!rolling) {
            diceEl.textContent = state.last_roll != null ? DICE_FACES[state.last_roll - 1] : '?';
        }

        if (state.winner) {
            var wname = window.SayItGame.playerName(state.winner);
            document.getElementById('sl-status').innerHTML = '<strong class="text-emerald-700">' + wname + ' wins!</strong> Race finished.';
            window.SayItGame.setTurnMessage(state.winner === me ? '🏆 You won the race!' : wname + ' reached 100 first.');
            document.getElementById('sl-roll-label').textContent = 'Finished';
        } else {
            document.getElementById('sl-status').textContent = myTurn ? 'Your turn — roll the dice!' : window.SayItGame.playerName(turnId) + ' is rolling…';
            window.SayItGame.setTurnMessage(myTurn ? 'Your turn — tap Roll dice.' : 'Waiting for ' + window.SayItGame.playerName(turnId) + '…');
            document.getElementById('sl-roll-label').textContent = myTurn ? 'Roll dice' : 'Waiting…';
        }
        document.getElementById('sl-log').innerHTML = (state.log || []).slice(-12).reverse().map(function(l) {
            return '<li>' + l + '</li>';
        }).join('');
        document.getElementById('sl-roll').disabled = !!state.winner || !myTurn || rolling;
    }

    function move(who, roll) {
        var name = window.SayItGame.playerName(who);
        var from = state.positions[who] || 1;
        var next = from + roll;
        if (next > 100) {
            state.log.push(name + ' rolled ' + roll + ' — need exact landing on 100.');
            return;
        }
        var portal = applyPortals(next);
        state.positions[who] = portal.pos;
        var line = name + ' rolled ' + roll + ' → ' + next;
        if (portal.msg) line += ' · ' + portal.msg;
        state.log.push(line);
        if (state.positions[who] >= 100) {
            state.positions[who] = 100;
            state.winner = who;
            state.log.push('🏆 ' + name + ' reached 100 — victory!');
        }
    }

    function animateDice(finalRoll, done) {
        rolling = true;
        var diceEl = document.getElementById('sl-dice');
        diceEl.classList.remove('sayit-dice-rolling');
        void diceEl.offsetWidth;
        diceEl.classList.add('sayit-dice-rolling');
        var flashes = 0;
        var iv = setInterval(function() {
            diceEl.textContent = DICE_FACES[Math.floor(Math.random() * 6)];
            flashes++;
            if (flashes > 8) {
                clearInterval(iv);
                diceEl.textContent = DICE_FACES[finalRoll - 1];
                rolling = false;
                done();
            }
        }, 70);
    }

    document.getElementById('sl-roll').onclick = function() {
        if (state.winner || !window.SayItGame.isMyTurn(state) || rolling) return;
        var roll = 1 + Math.floor(Math.random() * 6);
        document.getElementById('sl-roll').disabled = true;
        animateDice(roll, function() {
            state.last_roll = roll;
            move(me, roll);
            if (!state.winner) {
                var n = players().length || 1;
                state.turn_index = ((state.turn_index || 0) + 1) % n;
            }
            persist(state.winner ? 'finished' : 'playing');
            renderBoard();
        });
    };

    window.SayItGame.onRemote = function(payload) {
        if (!payload.state) return;
        state = Object.assign(state, payload.state);
        renderBoard();
    };

    ensure();
    renderBoard();
    window.SayItGame.startPolling(1500);
})();
</script>
@endpush
@endsection
