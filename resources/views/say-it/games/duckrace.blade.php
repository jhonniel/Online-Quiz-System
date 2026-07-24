@extends('layouts.say-it')

@section('title', 'Duck Race – Say it Games')

@include('say-it.games._game-styles')

@section('content')
<div class="max-w-3xl mx-auto w-full">
    @include('say-it.games._chrome')

    <div class="rounded-3xl overflow-hidden shadow-xl border border-sky-200 bg-white">
        <div class="bg-gradient-to-r from-sky-600 via-cyan-500 to-blue-700 px-4 sm:px-6 py-4 text-white flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-[10px] uppercase tracking-[0.25em] text-sky-100/80 font-semibold">Paddling championship</p>
                <h1 class="text-2xl font-bold">Duck Race</h1>
            </div>
            <div class="flex gap-3 text-sm">
                <div class="rounded-xl bg-white/15 px-3 py-2 border border-white/20 text-center">
                    <div class="text-[9px] uppercase text-sky-100/70">Your wins</div>
                    <div class="text-xl font-black tabular-nums" id="dr-wins">0</div>
                </div>
                <div class="rounded-xl bg-white/10 px-3 py-2 border border-white/15 text-center">
                    <div class="text-[9px] uppercase text-sky-100/70">Races</div>
                    <div class="text-xl font-black tabular-nums" id="dr-races">0</div>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 space-y-4">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-bold text-sky-900">Choose your duck</h2>
                    <span class="text-xs text-gray-400" id="dr-picks-status"></span>
                </div>
                <div id="dr-picks" class="grid grid-cols-2 sm:grid-cols-4 gap-2"></div>
            </div>

            <div id="dr-countdown" class="hidden text-center py-2">
                <div class="text-5xl font-black text-sky-600 tabular-nums" id="dr-count-num">3</div>
                <div class="text-xs uppercase tracking-wider text-sky-500 font-semibold">Get ready</div>
            </div>

            <div id="dr-track" class="relative rounded-3xl sayit-duck-water border-4 border-sky-300/60 h-72 sm:h-80 overflow-hidden shadow-inner">
                <div class="absolute left-2 top-2 bottom-2 w-1.5 bg-white/50 rounded-full" title="Start"></div>
                <div class="absolute right-3 top-2 bottom-2 w-2 bg-gradient-to-b from-amber-300 via-amber-400 to-amber-500 rounded shadow-lg" title="Finish">
                    <div class="absolute -top-1 left-1/2 -translate-x-1/2 text-lg">🏁</div>
                </div>
                <div class="absolute inset-x-0 top-0 h-8 bg-gradient-to-b from-sky-200/50 to-transparent pointer-events-none"></div>
                <div id="dr-lanes" class="absolute inset-0 p-4 flex flex-col justify-around"></div>
                <div id="dr-splash" class="pointer-events-none absolute inset-0"></div>
            </div>

            <div class="flex flex-wrap gap-2 justify-center">
                <button type="button" id="dr-race" class="px-6 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold shadow-lg disabled:opacity-40 transition">Start race</button>
                <button type="button" id="dr-reset" class="px-5 py-3 rounded-2xl border border-sky-200 font-semibold text-sky-800 hover:bg-sky-50">New race</button>
            </div>

            <p id="dr-msg" class="text-center text-sm font-medium text-sky-900 min-h-[1.25rem]"></p>

            <div class="rounded-2xl bg-sky-50 border border-sky-100 p-3">
                <div class="text-[10px] uppercase tracking-wider font-semibold text-sky-600 mb-1">Race results</div>
                <ol id="dr-results" class="text-sm text-sky-900 space-y-1 empty:hidden"></ol>
                <ul id="dr-history" class="mt-2 text-xs text-sky-700/70 space-y-1 border-t border-sky-100 pt-2"></ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var DUCKS = [
        { id: 'yellow', emoji: '🦆', name: 'Sunny', color: '#fbbf24', lane: 'from-amber-200/40' },
        { id: 'blue', emoji: '🐤', name: 'Splash', color: '#38bdf8', lane: 'from-sky-200/40' },
        { id: 'green', emoji: '🐥', name: 'Moss', color: '#34d399', lane: 'from-emerald-200/40' },
        { id: 'pink', emoji: '🦢', name: 'Blush', color: '#f472b6', lane: 'from-pink-200/40' },
    ];

    var me = window.SayItGame.playerId;
    var state = Object.assign({
        picks: {}, racing: false, finished: false, progress: {}, results: [], wins: {}, races: 0, history: [], message: '', race_seed: null
    }, @json($session->state ?? []));

    var animating = false;
    var tickTimer = null;
    var countTimer = null;

    function players() { return window.SayItGame.players || []; }

    function ensure() {
        state.picks = state.picks || {};
        state.wins = state.wins || {};
        state.progress = state.progress || {};
        state.history = state.history || [];
        players().forEach(function(p) {
            if (state.picks[p.id] === undefined) state.picks[p.id] = null;
            if (state.wins[p.id] == null) state.wins[p.id] = 0;
        });
    }

    function allPicked() {
        var list = players();
        if (!list.length) return false;
        return list.every(function(p) { return !!state.picks[p.id]; });
    }

    function persist(status) {
        window.SayItGame.save({
            picks: state.picks,
            racing: state.racing,
            finished: state.finished,
            progress: state.progress,
            results: state.results,
            wins: state.wins,
            races: state.races,
            history: state.history,
            message: state.message,
            race_seed: state.race_seed,
        }, status || 'playing');
    }

    function mulberry32(a) {
        return function() {
            var t = a += 0x6D2B79F5;
            t = Math.imul(t ^ t >>> 15, t | 1);
            t ^= t + Math.imul(t ^ t >>> 7, t | 61);
            return ((t ^ t >>> 14) >>> 0) / 4294967296;
        };
    }

    function duckById(id) {
        return DUCKS.find(function(d){ return d.id === id; });
    }

    function renderPicks() {
        ensure();
        var taken = {};
        Object.keys(state.picks || {}).forEach(function(pid) {
            var d = state.picks[pid];
            if (d) taken[d] = window.SayItGame.playerName(pid);
        });

        document.getElementById('dr-picks-status').textContent =
            players().filter(function(p){ return state.picks[p.id]; }).length + '/' + players().length + ' ready';

        var el = document.getElementById('dr-picks');
        el.innerHTML = DUCKS.map(function(d) {
            var mine = state.picks[me] === d.id;
            var claimedBy = taken[d.id];
            var locked = state.racing || (claimedBy && !mine);
            return '<button type="button" data-id="' + d.id + '" class="rounded-2xl border-2 p-3 text-center transition relative overflow-hidden ' +
                (mine ? 'border-sky-500 bg-sky-50 ring-2 ring-sky-200 scale-[1.02]' : 'border-gray-200 hover:border-sky-300 bg-white') +
                '"' + (locked ? ' disabled' : '') + '>' +
                '<div class="text-4xl sayit-bob" style="animation-delay:' + (d.id.charCodeAt(0)%4)*0.1 + 's">' + d.emoji + '</div>' +
                '<div class="mt-1 text-sm font-bold text-gray-900">' + d.name + '</div>' +
                (claimedBy ? '<div class="mt-0.5 text-[10px] font-semibold text-sky-600">' + claimedBy + '</div>' : '<div class="mt-0.5 text-[10px] text-gray-400">Available</div>') +
                '</button>';
        }).join('');
        el.querySelectorAll('button').forEach(function(btn) {
            btn.onclick = function() {
                if (state.racing) return;
                var id = btn.dataset.id;
                state.picks[me] = id;
                state.finished = false;
                state.results = [];
                state.message = window.SayItGame.playerName(me) + ' chose ' + duckById(id).name + '!';
                renderPicks();
                renderLanes(state.progress || {});
                persist();
            };
        });

        document.getElementById('dr-wins').textContent = state.wins[me] || 0;
        document.getElementById('dr-races').textContent = state.races || 0;
        document.getElementById('dr-race').disabled = !allPicked() || state.racing;
        document.getElementById('dr-msg').textContent = state.message || '';
        document.getElementById('dr-history').innerHTML = (state.history || []).slice(0, 8).map(function(h) {
            return '<li>' + h + '</li>';
        }).join('');

        var resultsEl = document.getElementById('dr-results');
        if (state.finished && state.results && state.results.length) {
            resultsEl.innerHTML = state.results.map(function(duckId, i) {
                var d = duckById(duckId);
                var owner = null;
                Object.keys(state.picks || {}).forEach(function(pid) {
                    if (state.picks[pid] === duckId) owner = window.SayItGame.playerName(pid);
                });
                var medal = ['🥇','🥈','🥉','4️⃣'][i] || (i+1);
                return '<li class="flex items-center gap-2"><span>' + medal + '</span><span class="text-lg">' + d.emoji + '</span>' +
                    '<span class="font-semibold">' + d.name + '</span>' +
                    (owner ? '<span class="text-sky-600 text-xs">· ' + owner + '</span>' : '') + '</li>';
            }).join('');
        } else {
            resultsEl.innerHTML = '';
        }

        window.SayItGame.setTurnMessage(state.racing ? 'Race in progress…' : (allPicked() ? 'All ducks claimed — start when ready!' : 'Each player picks one duck.'));
    }

    function renderLanes(progressMap) {
        progressMap = progressMap || {};
        var lanes = document.getElementById('dr-lanes');
        lanes.innerHTML = DUCKS.map(function(d, i) {
            var pct = Math.min(90, progressMap[d.id] || 0);
            var label = '';
            Object.keys(state.picks || {}).forEach(function(pid) {
                if (state.picks[pid] === d.id) label = window.SayItGame.playerName(pid);
            });
            return '<div class="relative h-12 sm:h-14 rounded-xl bg-gradient-to-r ' + d.lane + ' to-transparent">' +
                '<div class="absolute left-1 top-1 text-[9px] font-bold text-sky-900/50 uppercase">Lane ' + (i+1) + '</div>' +
                (label ? '<div class="absolute left-1 bottom-0.5 text-[10px] font-bold text-sky-950/70 truncate max-w-[40%]">' + label + '</div>' : '') +
                '<div class="absolute top-1/2 -translate-y-1/2 transition-all duration-100 ease-linear sayit-bob" style="left:' + pct + '%">' +
                '<span class="text-3xl drop-shadow-md filter drop-shadow">' + d.emoji + '</span></div></div>';
        }).join('');
    }

    function splash() {
        var box = document.getElementById('dr-splash');
        for (var i = 0; i < 8; i++) {
            var d = document.createElement('span');
            d.textContent = '💧';
            d.style.cssText = 'position:absolute;left:'+(10+Math.random()*80)+'%;top:'+(20+Math.random()*60)+'%;opacity:0.8;animation:sayit-confetti 0.8s ease-out forwards';
            box.appendChild(d);
            setTimeout(function(el){ return function(){ el.remove(); }; }(d), 900);
        }
    }

    function runRaceAnimation() {
        if (animating) return;
        animating = true;
        var rng = mulberry32(state.race_seed || 1);
        var progress = {};
        DUCKS.forEach(function(d){ progress[d.id] = 0; });
        var speeds = {};
        DUCKS.forEach(function(d) {
            speeds[d.id] = 0.75 + rng() * 1.55;
        });

        clearInterval(tickTimer);
        tickTimer = setInterval(function() {
            DUCKS.forEach(function(d) {
                if (progress[d.id] >= 90) return;
                progress[d.id] += speeds[d.id] * (0.65 + rng() * 1.1);
                if (progress[d.id] >= 90) progress[d.id] = 90;
            });
            state.progress = progress;
            renderLanes(progress);
            var doneCount = DUCKS.filter(function(d){ return progress[d.id] >= 90; }).length;
            if (doneCount === DUCKS.length) {
                clearInterval(tickTimer);
                splash();
                finishRace(progress);
            }
        }, 70);

        setTimeout(function() {
            clearInterval(tickTimer);
            if (state.racing) {
                splash();
                finishRace(progress);
            }
        }, 9000);
    }

    function finishRace(progress) {
        if (state.finished && !state.racing) {
            animating = false;
            renderLanes(progress || state.progress || {});
            return;
        }
        if (!state.racing) {
            animating = false;
            return;
        }
        var order = DUCKS.slice().sort(function(a,b){ return (progress[b.id]||0) - (progress[a.id]||0); });
        var wasRacing = state.racing;
        state.results = order.map(function(d){ return d.id; });
        state.racing = false;
        state.finished = true;
        state.progress = progress;
        if (wasRacing) {
            state.races = (state.races || 0) + 1;
            var winnerDuck = order[0];
            var winnerPlayer = null;
            Object.keys(state.picks || {}).forEach(function(pid) {
                if (state.picks[pid] === winnerDuck.id) winnerPlayer = pid;
            });
            if (winnerPlayer) {
                state.wins[winnerPlayer] = (state.wins[winnerPlayer] || 0) + 1;
                state.message = '🏁 ' + window.SayItGame.playerName(winnerPlayer) + ' wins with ' + winnerDuck.name + '!';
            } else {
                state.message = '🏁 ' + winnerDuck.name + ' crossed first!';
            }
            state.history.unshift(state.message);
            persist();
        }
        animating = false;
        document.getElementById('dr-countdown').classList.add('hidden');
        renderPicks();
        renderLanes(progress);
    }

    function startWithCountdown() {
        if (!allPicked() || state.racing) return;
        var box = document.getElementById('dr-countdown');
        var num = document.getElementById('dr-count-num');
        box.classList.remove('hidden');
        var n = 3;
        num.textContent = n;
        clearInterval(countTimer);
        countTimer = setInterval(function() {
            n--;
            if (n <= 0) {
                clearInterval(countTimer);
                num.textContent = 'GO!';
                setTimeout(function() {
                    box.classList.add('hidden');
                    state.racing = true;
                    state.finished = false;
                    state.results = [];
                    state.progress = {};
                    state.race_seed = Math.floor(Math.random() * 999999) + 1;
                    state.message = 'They are off!';
                    persist();
                    renderPicks();
                    runRaceAnimation();
                }, 350);
            } else {
                num.textContent = n;
            }
        }, 600);
    }

    document.getElementById('dr-race').onclick = startWithCountdown;

    document.getElementById('dr-reset').onclick = function() {
        if (state.racing) return;
        clearInterval(tickTimer);
        clearInterval(countTimer);
        animating = false;
        document.getElementById('dr-countdown').classList.add('hidden');
        state.racing = false;
        state.finished = false;
        state.results = [];
        state.progress = {};
        state.race_seed = null;
        state.message = 'New heat — ducks keep their owners unless you re-pick.';
        persist();
        renderPicks();
        renderLanes({});
    };

    window.SayItGame.onRemote = function(payload) {
        if (!payload.state) return;
        var remote = payload.state;
        var wasRacing = state.racing;
        state = Object.assign(state, remote);
        renderPicks();
        if (state.racing && !animating) {
            document.getElementById('dr-countdown').classList.add('hidden');
            runRaceAnimation();
        } else if (!state.racing) {
            renderLanes(state.progress || {});
            if (wasRacing && state.finished) animating = false;
        }
    };

    ensure();
    renderPicks();
    renderLanes(state.progress || {});
    if (state.racing && !state.finished) runRaceAnimation();
    window.SayItGame.startPolling(1500);
})();
</script>
@endpush
@endsection
