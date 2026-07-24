@extends('layouts.say-it')

@section('title', '4 Pics 1 Word – Say it Games')

@include('say-it.games._game-styles')

@section('content')
<div class="max-w-2xl mx-auto w-full">
    @include('say-it.games._chrome')

    <div class="rounded-3xl overflow-hidden shadow-xl border border-violet-200 bg-gradient-to-b from-indigo-950 via-violet-900 to-fuchsia-900 text-white">
        <div class="px-4 sm:px-6 py-4 flex items-center justify-between gap-3 border-b border-white/10">
            <div>
                <p class="text-[10px] uppercase tracking-[0.25em] text-violet-200/70 font-semibold">Puzzle · team play</p>
                <h1 class="text-2xl font-bold">4 Pics 1 Word</h1>
            </div>
            <div class="flex gap-2 text-center">
                <div class="rounded-xl bg-white/10 px-3 py-2 border border-white/10 min-w-[4rem]">
                    <div class="text-[9px] uppercase text-violet-200/70">Level</div>
                    <div class="text-xl font-black tabular-nums" id="fp-level">1</div>
                </div>
                <div class="rounded-xl bg-amber-400/20 px-3 py-2 border border-amber-300/30 min-w-[4rem]">
                    <div class="text-[9px] uppercase text-amber-200/80">Score</div>
                    <div class="text-xl font-black text-amber-200 tabular-nums" id="fp-score">0</div>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6 space-y-4">
            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
                <div id="fp-progress" class="h-full bg-gradient-to-r from-fuchsia-400 to-amber-300 transition-all duration-500" style="width:0%"></div>
            </div>

            <div id="fp-pics" class="grid grid-cols-2 gap-3"></div>

            <div>
                <div class="text-[10px] uppercase tracking-wider text-violet-200/60 font-semibold text-center mb-2">Answer</div>
                <div id="fp-slots" class="flex flex-wrap justify-center gap-2 min-h-[3rem]"></div>
            </div>

            <div class="sayit-letter-bank rounded-2xl p-3 border border-white/10">
                <div class="text-[10px] uppercase tracking-wider text-violet-700/70 font-semibold text-center mb-2">Letter bank</div>
                <div id="fp-letters" class="flex flex-wrap justify-center gap-2"></div>
            </div>

            <div class="flex flex-wrap gap-2 justify-center">
                <button type="button" id="fp-clear" class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/15 font-semibold text-sm hover:bg-white/15">Clear</button>
                <button type="button" id="fp-hint" class="px-4 py-2.5 rounded-xl bg-amber-400/20 border border-amber-300/40 text-amber-100 font-semibold text-sm hover:bg-amber-400/30">Hint (−2)</button>
                <button type="button" id="fp-submit" class="px-6 py-2.5 rounded-xl bg-fuchsia-500 hover:bg-fuchsia-400 text-white font-bold text-sm shadow-lg">Check</button>
            </div>

            <p id="fp-msg" class="text-center text-sm text-violet-100 min-h-[1.25rem]"></p>
            <p id="fp-solver" class="text-center text-xs text-violet-300/70"></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var LEVELS = [
        { word: 'FIRE', pics: [['🔥','Flame'],['🚒','Truck'],['🕯️','Candle'],['🌋','Volcano']] },
        { word: 'COLD', pics: [['🧊','Ice'],['❄️','Snow'],['🥶','Freeze'],['🍦','Ice cream']] },
        { word: 'MUSIC', pics: [['🎵','Notes'],['🎸','Guitar'],['🎧','Headphones'],['🎤','Mic']] },
        { word: 'WATER', pics: [['💧','Drop'],['🌊','Wave'],['🚿','Shower'],['🚰','Tap']] },
        { word: 'LIGHT', pics: [['💡','Bulb'],['☀️','Sun'],['🕯️','Candle'],['🔦','Torch']] },
        { word: 'SLEEP', pics: [['😴','Sleepy'],['🛏️','Bed'],['🌙','Moon'],['💤','Zzz']] },
        { word: 'HAPPY', pics: [['😊','Smile'],['🎉','Party'],['🎈','Balloon'],['🥳','Celebrate']] },
        { word: 'BOOK', pics: [['📚','Stack'],['📖','Open'],['✍️','Write'],['🏫','School']] },
        { word: 'TIME', pics: [['⏰','Alarm'],['⌚','Watch'],['⏳','Hourglass'],['📅','Calendar']] },
        { word: 'HEART', pics: [['❤️','Red'],['💗','Pink'],['🫀','Organ'],['💌','Letter']] },
        { word: 'APPLE', pics: [['🍎','Fruit'],['💻','Laptop'],['🥧','Pie'],['🌳','Tree']] },
        { word: 'GREEN', pics: [['🌿','Leaf'],['🐸','Frog'],['🥒','Cucumber'],['🟢','Dot']] },
        { word: 'PHONE', pics: [['📱','Mobile'],['☎️','Retro'],['📞','Call'],['💬','Chat']] },
        { word: 'TRAIN', pics: [['🚂','Engine'],['🚆','Metro'],['🛤️','Tracks'],['🎫','Ticket']] },
        { word: 'BREAD', pics: [['🍞','Loaf'],['🥖','Baguette'],['🥪','Sandwich'],['🧈','Butter']] },
        { word: 'CLOCK', pics: [['🕐','Face'],['⏰','Alarm'],['🕰️','Mantel'],['⏱️','Stopwatch']] },
        { word: 'CLOUD', pics: [['☁️','Puff'],['🌧️','Rain'],['⛅','Partly'],['💭','Think']] },
        { word: 'DANCE', pics: [['💃','Dancer'],['🕺','Groove'],['🩰','Ballet'],['🎉','Party']] },
        { word: 'PLANT', pics: [['🌱','Sprout'],['🪴','Potted'],['🌻','Sunflower'],['🍃','Leaf']] },
        { word: 'SPORT', pics: [['⚽','Soccer'],['🏀','Basket'],['🎾','Tennis'],['🏆','Trophy']] },
    ];

    var me = window.SayItGame.playerId;
    var state = Object.assign({
        level: 0, slots: [], used: [], pool: [], poolWord: '', hints_used: 0, score: 0, solved: [], last_solver: null, message: ''
    }, @json($session->state ?? []));
    var localBusy = false;

    function lv() { return LEVELS[state.level % LEVELS.length]; }

    function makePool(answer) {
        var extras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var pool = answer.split('');
        while (pool.length < 14) pool.push(extras[Math.floor(Math.random() * extras.length)]);
        for (var i = pool.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var t = pool[i]; pool[i] = pool[j]; pool[j] = t;
        }
        return pool;
    }

    function ensureLevel() {
        var word = lv().word;
        if (state.poolWord !== word || !state.pool || !state.pool.length) {
            state.pool = makePool(word);
            state.poolWord = word;
            state.slots = Array(word.length).fill(null);
            state.used = Array(word.length).fill(null);
        }
        if (!state.slots || state.slots.length !== word.length) {
            state.slots = Array(word.length).fill(null);
            state.used = Array(word.length).fill(null);
        }
    }

    function persist(status) {
        window.SayItGame.save({
            level: state.level,
            slots: state.slots,
            used: state.used,
            pool: state.pool,
            poolWord: state.poolWord,
            hints_used: state.hints_used,
            score: state.score,
            solved: state.solved,
            last_solver: state.last_solver,
            message: state.message,
        }, status || 'playing');
    }

    function guessWord() {
        return (state.slots || []).map(function(c){ return c || ''; }).join('');
    }

    function burst() {
        var root = document.getElementById('fp-pics');
        for (var i = 0; i < 12; i++) {
            var s = document.createElement('span');
            s.textContent = ['✨','⭐','💜','🎉'][i % 4];
            s.style.cssText = 'position:absolute;left:' + (20 + Math.random()*60) + '%;top:30%;font-size:1.2rem;pointer-events:none;animation:sayit-confetti 0.9s ease-out forwards;animation-delay:' + (i*0.04) + 's';
            root.style.position = 'relative';
            root.appendChild(s);
            setTimeout(function(el){ return function(){ el.remove(); }; }(s), 1000);
        }
    }

    function render() {
        ensureLevel();
        var level = lv();
        var lvlNum = Math.min(LEVELS.length, (state.level % LEVELS.length) + 1);
        var done = (state.solved || []).length >= LEVELS.length;
        document.getElementById('fp-level').textContent = done ? LEVELS.length : lvlNum;
        document.getElementById('fp-score').textContent = state.score;
        document.getElementById('fp-progress').style.width = (Math.min(100, ((state.solved || []).length / LEVELS.length) * 100)) + '%';
        document.getElementById('fp-msg').textContent = state.message || '';
        document.getElementById('fp-solver').textContent = state.last_solver
            ? ('Last solved by ' + window.SayItGame.playerName(state.last_solver))
            : 'Anyone can fill letters — first correct advances.';
        window.SayItGame.setTurnMessage(done ? 'All levels cleared — great team!' : 'Team puzzle — anyone can guess.');

        document.getElementById('fp-pics').innerHTML = level.pics.map(function(p, idx) {
            return '<div class="sayit-pic-frame rounded-2xl aspect-square flex flex-col items-center justify-center gap-2 relative overflow-hidden">' +
                '<div class="absolute inset-0 opacity-30 bg-[radial-gradient(circle_at_30%_20%,#a78bfa,transparent_55%)]"></div>' +
                '<span class="relative text-5xl drop-shadow-lg">' + p[0] + '</span>' +
                '<span class="relative text-[10px] uppercase tracking-[0.15em] text-violet-200/80 font-semibold">' + p[1] + '</span>' +
                '<span class="absolute top-2 left-2 text-[10px] font-bold text-white/40">' + (idx+1) + '</span></div>';
        }).join('');

        var slotsEl = document.getElementById('fp-slots');
        slotsEl.innerHTML = '';
        state.slots.forEach(function(ch, i) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-11 h-12 rounded-xl font-black text-xl transition ' +
                (ch ? 'bg-white text-violet-900 shadow-lg sayit-tile-pop' : 'bg-white/10 border-2 border-dashed border-white/30 text-transparent');
            btn.textContent = ch || '_';
            btn.onclick = function() {
                if (state.used[i] == null) return;
                state.used[i] = null;
                state.slots[i] = null;
                render();
                persist();
            };
            slotsEl.appendChild(btn);
        });

        var lettersEl = document.getElementById('fp-letters');
        lettersEl.innerHTML = '';
        state.pool.forEach(function(letter, idx) {
            var taken = state.used.indexOf(idx) !== -1;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.disabled = taken;
            btn.className = 'w-10 h-11 rounded-xl font-black text-lg shadow-sm transition ' +
                (taken ? 'bg-violet-200/40 text-violet-400' : 'bg-white text-violet-900 hover:-translate-y-0.5');
            btn.textContent = letter;
            btn.onclick = function() {
                var slot = state.slots.findIndex(function(c){ return c == null; });
                if (slot === -1) return;
                state.slots[slot] = letter;
                state.used[slot] = idx;
                render();
                persist();
            };
            lettersEl.appendChild(btn);
        });
    }

    document.getElementById('fp-clear').onclick = function() {
        ensureLevel();
        state.slots = Array(lv().word.length).fill(null);
        state.used = Array(lv().word.length).fill(null);
        state.message = '';
        render();
        persist();
    };

    document.getElementById('fp-hint').onclick = function() {
        ensureLevel();
        var word = lv().word;
        for (var i = 0; i < word.length; i++) {
            if (state.slots[i] !== word[i]) {
                var need = word[i];
                var poolIdx = state.pool.findIndex(function(L, pi) {
                    return L === need && state.used.indexOf(pi) === -1;
                });
                if (poolIdx === -1) break;
                state.slots[i] = need;
                state.used[i] = poolIdx;
                state.hints_used++;
                state.score = Math.max(0, state.score - 2);
                state.message = window.SayItGame.playerName(me) + ' revealed a letter (−2).';
                break;
            }
        }
        render();
        persist();
    };

    document.getElementById('fp-submit').onclick = function() {
        if (localBusy) return;
        ensureLevel();
        var word = lv().word;
        var guess = guessWord();
        if (guess.length !== word.length || state.slots.some(function(c){ return !c; })) {
            state.message = 'Fill every letter slot.';
            render();
            return;
        }
        if (guess === word) {
            localBusy = true;
            burst();
            state.score += 10;
            state.solved = state.solved || [];
            state.solved.push(word);
            state.last_solver = me;
            state.message = '✓ ' + window.SayItGame.playerName(me) + ' got ' + word + '! +10';
            state.level++;
            state.poolWord = '';
            ensureLevel();
            persist(state.level >= LEVELS.length ? 'finished' : 'playing');
            render();
            setTimeout(function(){ localBusy = false; }, 600);
        } else {
            state.message = 'Not quite — reshuffle and try again.';
            persist();
            render();
        }
    };

    window.SayItGame.onRemote = function(payload) {
        if (!payload.state) return;
        var remote = payload.state;
        if (remote.level !== state.level || remote.poolWord !== state.poolWord ||
            JSON.stringify(remote.slots) !== JSON.stringify(state.slots) ||
            remote.score !== state.score || remote.message !== state.message) {
            state = Object.assign(state, remote);
            render();
        }
    };

    ensureLevel();
    persist();
    render();
    window.SayItGame.startPolling(1500);
})();
</script>
@endpush
@endsection
