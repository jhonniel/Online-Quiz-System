@extends('layouts.say-it')

@section('title', 'Scrabble – Say it Games')

@include('say-it.games._game-styles')

@section('content')
<div class="max-w-3xl mx-auto w-full" id="game-root">
    @include('say-it.games._chrome')

    <div class="sayit-scrabble-wood rounded-3xl shadow-xl overflow-hidden border border-amber-900/40">
        <div class="px-4 sm:px-6 pt-5 pb-3 flex flex-wrap items-end justify-between gap-3 border-b border-black/10 bg-black/10">
            <div>
                <p class="text-[10px] uppercase tracking-[0.25em] text-amber-100/80 font-semibold">Classic word duel</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-amber-50 tracking-tight">Scrabble</h1>
            </div>
            <div class="rounded-2xl bg-black/25 px-4 py-2 text-center border border-amber-200/20">
                <div class="text-[10px] uppercase tracking-wider text-amber-100/70">Your score</div>
                <div class="text-3xl font-black text-amber-200 tabular-nums" id="sc-score">0</div>
            </div>
        </div>

        <div class="p-4 sm:p-6 space-y-4">
            <div id="sc-scores" class="grid grid-cols-2 sm:grid-cols-4 gap-2"></div>

            <div class="rounded-2xl bg-[#0f3d2e] border-4 border-[#0a2a20] p-3 shadow-inner">
                <div class="flex items-center justify-between mb-2 px-1">
                    <span class="text-[10px] uppercase tracking-wider text-emerald-200/70 font-semibold">Played words</span>
                    <span class="text-[10px] text-emerald-300/60" id="sc-round">Round 1</span>
                </div>
                <div id="sc-board-words" class="min-h-[4.5rem] flex flex-wrap gap-1.5 content-start"></div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-100/80">Letter rack</span>
                    <span class="text-[10px] text-amber-100/50" id="sc-rack-hint">Tap letters to build a word</span>
                </div>
                <div id="sc-rack" class="flex flex-wrap gap-2 min-h-[3.5rem] p-3 rounded-2xl bg-black/20 border border-amber-900/30 justify-center"></div>
            </div>

            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-amber-100/80 mb-2">Word tray</div>
                <div id="sc-word" class="flex flex-wrap gap-2 min-h-[3.5rem] p-3 rounded-2xl bg-[#f5e6c8]/15 border-2 border-dashed border-amber-200/30 justify-center items-center"></div>
                <p class="mt-2 text-center text-sm text-amber-100/90 font-medium" id="sc-preview"></p>
            </div>

            <div class="flex flex-wrap gap-2 justify-center">
                <button type="button" id="sc-submit" class="px-5 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-amber-950 font-bold shadow-lg disabled:opacity-40 transition">Play word</button>
                <button type="button" id="sc-pass" class="px-4 py-3 rounded-xl bg-black/30 text-amber-50 font-semibold border border-amber-200/20 hover:bg-black/40 disabled:opacity-40">Pass turn</button>
                <button type="button" id="sc-clear" class="px-4 py-3 rounded-xl bg-black/20 text-amber-100 font-semibold border border-amber-200/15">Clear</button>
                <button type="button" id="sc-shuffle" class="px-4 py-3 rounded-xl bg-black/20 text-amber-100 font-semibold border border-amber-200/15 disabled:opacity-40">Shuffle</button>
            </div>

            <p id="sc-msg" class="text-center text-sm text-amber-100/80 min-h-[1.25rem]"></p>

            <details class="rounded-xl bg-black/15 border border-amber-200/10 open:pb-2">
                <summary class="cursor-pointer px-3 py-2 text-xs font-semibold text-amber-100/70 uppercase tracking-wider">Score history</summary>
                <ul id="sc-words" class="px-3 text-sm text-amber-50/90 space-y-1 max-h-36 overflow-y-auto"></ul>
            </details>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var LETTERS = 'AAAAAAAAABBCCDDDDEEEEEEEEEEEEFFGGGHHIIIIIIIIIJKLLLLMMNNNNNNOOOOOOOOPPQRRRRRRSSSSTTTTTTUUUUVVWWXYYZ'.split('');
    var VALUES = {A:1,B:3,C:3,D:2,E:1,F:4,G:2,H:4,I:1,J:8,K:5,L:1,M:3,N:1,O:1,P:3,Q:10,R:1,S:1,T:1,U:1,V:4,W:4,X:8,Y:4,Z:10};
    var DICT = new Set(('ACE ACT ADD AGE AIR ALL AND ANT ANY APE ARC ARE ARM ART ASK BAD BAG BAN BAR BAT BAY BED BEE BET BIG BIT BOW BOX BOY BUS BUT BUY CAB CAN CAP CAR CAT COP COW CRY CUP CUT DAD DAY DIE DIG DOG DRY DUE DUG EAR EAT EEL EGG ELF END ERA EVE EYE FAN FAR FAT FEE FEW FIG FIN FIR FIT FIX FLY FOG FOR FOX FUN FUR GAP GAS GET GIN GOD GOT GUM GUN GUT GUY HAD HAM HAS HAT HAY HEN HER HID HIM HIP HIS HIT HOP HOT HOW HUB HUE HUG HUM HUT ICE ILL INK INN JAM JAR JAW JET JOB JOG JOY KEY KID KIT LAB LAD LAP LAW LAY LED LEG LET LID LIE LIP LIT LOG LOT LOW MAD MAN MAP MAT MAY MEN MET MIX MOB MUD MUG NAG NAP NET NEW NIP NOD NOR NOT NOW NUN NUT OAK OAR OAT ODD OFF OIL OLD ONE OUR OUT OWE OWL OWN PAD PAN PAT PAW PAY PEN PET PIE PIG PIN PIT POD POP POT PRO PUB PUT RAG RAM RAN RAP RAT RAW RED RID RIG RIP ROB ROD ROT ROW RUB RUG RUN SAD SAG SAP SAW SAY SEA SET SEW SHE SHY SIN SIP SIR SIT SIX SKY SOB SOD SON SOP SOW SOY SPA SPY SUB SUM SUN TAB TAG TAN TAP TAR TAX TEA TEN THE THY TIE TIN TIP TOE TON TOP TOW TOY TRY TUB TUG TWO URN USE VAN VAT VET VOW WAR WAS WAX WAY WEB WED WET WHO WHY WIN WIT WON WOO WOW YAK YAM YAP YEA YES YET YOU ZAP ZEN ZIP ZOO LOVE TIME GAME PLAY WORD CODE DUCK RACE LADDER SNAKE SAYIT QUIZ BOOK HOPE FIRE WATER EARTH MUSIC DREAM LIGHT NIGHT HAPPY SMILE STAR MOON RAIN SNOW WIND TREE BIRD FISH BLUE GOLD SILVER PEACE POWER HEART BRAIN HOUSE CHAIR TABLE PHONE TRAIN BREAD GREEN APPLE PARTY DANCE SING JUMP WALK RUN FAST SLOW HIGH LONG SHORT SMALL LARGE QUIET LOUD CLEAN FRESH SWEET SPICY BRAVE SMART FUNNY KIND WARM COLD FREE OPEN CLOSE BEGIN START FINISH READY BOARD TILE SCORE POINT BONUS BINGO RACK DEAL DRAW PASS TURN ROUND WINNER CHAMPION VICTORY').toLowerCase().split(/\s+/));

    var me = window.SayItGame.playerId;
    var state = Object.assign({}, @json($session->state ?? []));
    var localSelected = [];

    function mulberry32(a) {
        return function() {
            var t = a += 0x6D2B79F5;
            t = Math.imul(t ^ t >>> 15, t | 1);
            t ^= t + Math.imul(t ^ t >>> 7, t | 61);
            return ((t ^ t >>> 14) >>> 0) / 4294967296;
        };
    }
    var rng = mulberry32(state.bag_seed || 1);

    function draw(n) {
        var out = [];
        for (var i = 0; i < n; i++) out.push(LETTERS[Math.floor(rng() * LETTERS.length)]);
        return out;
    }

    function ensureRacks() {
        state.scores = state.scores || {};
        state.racks = state.racks || {};
        state.words = state.words || [];
        state.turn_index = state.turn_index || 0;
        (window.SayItGame.players || []).forEach(function(p) {
            if (state.scores[p.id] == null) state.scores[p.id] = 0;
            if (!state.racks[p.id] || !state.racks[p.id].length) state.racks[p.id] = draw(7);
        });
    }

    function myRack() { return state.racks[me] || []; }

    function wordPoints(word) {
        var s = 0;
        for (var i = 0; i < word.length; i++) s += VALUES[word[i]] || 1;
        if (word.length >= 7) s += 50;
        else if (word.length >= 5) s += 5;
        return s;
    }

    function tileHtml(letter, extraClass) {
        return '<span class="sayit-tile sayit-tile-pop inline-flex items-center justify-center w-11 h-12 text-lg ' + (extraClass || '') + '">' +
            letter + '<span class="pts">' + (VALUES[letter] || 1) + '</span></span>';
    }

    function advanceTurn() {
        var n = (window.SayItGame.players || []).length || 1;
        state.turn_index = ((state.turn_index || 0) + 1) % n;
        localSelected = [];
    }

    function persist() {
        window.SayItGame.save({
            bag_seed: state.bag_seed,
            turn_index: state.turn_index,
            scores: state.scores,
            racks: state.racks,
            words: state.words,
            message: state.message,
            round: state.round || 1,
        }, 'playing');
    }

    function currentWord() {
        var rack = myRack();
        return localSelected.map(function(i) { return rack[i]; }).join('');
    }

    function render() {
        ensureRacks();
        var myTurn = window.SayItGame.isMyTurn(state);
        var turnId = window.SayItGame.turnPlayerId(state);
        window.SayItGame.setTurnMessage(myTurn ? 'Your turn — build a word from your rack.' : 'Waiting for ' + window.SayItGame.playerName(turnId) + '…');

        document.getElementById('sc-score').textContent = state.scores[me] || 0;
        document.getElementById('sc-msg').textContent = state.message || '';
        document.getElementById('sc-round').textContent = 'Round ' + (state.round || 1);
        document.getElementById('sc-rack-hint').textContent = myTurn ? 'Your move' : 'Wait for your turn';

        document.getElementById('sc-scores').innerHTML = (window.SayItGame.players || []).map(function(p) {
            var active = p.id === turnId && !myTurn || (p.id === me && myTurn);
            return '<div class="rounded-xl px-3 py-2 border ' + (p.id === me ? 'bg-amber-200/90 border-amber-100 text-amber-950' : 'bg-black/25 border-amber-200/10 text-amber-50') + (active ? ' sayit-turn-pulse' : '') + '">' +
                '<div class="text-[10px] uppercase opacity-70 font-semibold truncate">' + p.name + (p.id === me ? ' · you' : '') + '</div>' +
                '<div class="text-xl font-black tabular-nums">' + (state.scores[p.id] || 0) + '</div></div>';
        }).join('');

        document.getElementById('sc-board-words').innerHTML = (state.words || []).slice(-12).map(function(w) {
            return '<div class="inline-flex items-center gap-1 rounded-lg bg-emerald-950/50 border border-emerald-400/20 px-2 py-1">' +
                w.word.split('').map(function(ch) { return tileHtml(ch, '!w-7 !h-8 !text-sm'); }).join('') +
                '<span class="ml-1 text-[10px] text-emerald-200/80 font-semibold">+' + w.points + '</span></div>';
        }).join('') || '<p class="text-emerald-200/50 text-sm w-full text-center py-3">Words will appear on the board here</p>';

        var rack = myRack();
        var rackEl = document.getElementById('sc-rack');
        rackEl.innerHTML = '';
        rack.forEach(function(letter, idx) {
            if (localSelected.indexOf(idx) !== -1) return;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.disabled = !myTurn;
            btn.className = 'sayit-tile sayit-tile-pop w-12 h-14 text-xl disabled:opacity-35 hover:-translate-y-0.5 transition';
            btn.innerHTML = letter + '<span class="pts">' + (VALUES[letter] || 1) + '</span>';
            btn.onclick = function() { if (!myTurn) return; localSelected.push(idx); render(); };
            rackEl.appendChild(btn);
        });

        var wordEl = document.getElementById('sc-word');
        wordEl.innerHTML = '';
        if (!localSelected.length) {
            wordEl.innerHTML = '<span class="text-amber-100/40 text-sm">Place tiles here</span>';
        }
        localSelected.forEach(function(idx, si) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'sayit-tile sayit-tile-selected w-12 h-14 text-xl';
            btn.innerHTML = rack[idx] + '<span class="pts">' + (VALUES[rack[idx]] || 1) + '</span>';
            btn.onclick = function() { localSelected.splice(si, 1); render(); };
            wordEl.appendChild(btn);
        });

        var word = currentWord();
        var preview = document.getElementById('sc-preview');
        if (word.length >= 3) {
            var pts = wordPoints(word);
            var ok = DICT.has(word.toLowerCase());
            preview.innerHTML = '<span class="font-mono tracking-widest font-bold">' + word + '</span> · ' +
                (ok ? ('<span class="text-emerald-300">+' + pts + ' pts' + (word.length >= 7 ? ' (bingo!)' : '') + '</span>') : '<span class="text-rose-300">not in dictionary</span>');
        } else {
            preview.textContent = word ? 'Need at least 3 letters' : '';
        }

        document.getElementById('sc-words').innerHTML = (state.words || []).slice().reverse().map(function(w) {
            return '<li class="flex justify-between gap-2 border-b border-amber-200/10 py-1"><span><strong>' + w.word + '</strong> · ' + (w.by || '') + '</span><span class="text-amber-200">+' + w.points + '</span></li>';
        }).join('') || '<li class="text-amber-100/40">No plays yet</li>';

        document.getElementById('sc-submit').disabled = !myTurn;
        document.getElementById('sc-pass').disabled = !myTurn;
        document.getElementById('sc-shuffle').disabled = !myTurn;
    }

    document.getElementById('sc-clear').onclick = function() { localSelected = []; render(); };
    document.getElementById('sc-shuffle').onclick = function() {
        if (!window.SayItGame.isMyTurn(state)) return;
        var rack = myRack().slice();
        for (var i = rack.length - 1; i > 0; i--) {
            var j = Math.floor(rng() * (i + 1));
            var t = rack[i]; rack[i] = rack[j]; rack[j] = t;
        }
        state.racks[me] = rack;
        localSelected = [];
        persist();
        render();
    };
    document.getElementById('sc-pass').onclick = function() {
        if (!window.SayItGame.isMyTurn(state)) return;
        state.message = window.SayItGame.playerName(me) + ' passed.';
        advanceTurn();
        persist();
        render();
    };
    document.getElementById('sc-submit').onclick = function() {
        if (!window.SayItGame.isMyTurn(state)) return;
        var rack = myRack();
        var word = currentWord();
        if (word.length < 3) { state.message = 'Need at least 3 letters.'; render(); return; }
        if (!DICT.has(word.toLowerCase())) { state.message = '"' + word + '" is not in the dictionary.'; render(); return; }
        if ((state.words || []).some(function(w) { return w.word === word; })) { state.message = 'That word was already played.'; render(); return; }
        var pts = wordPoints(word);
        state.scores[me] = (state.scores[me] || 0) + pts;
        state.words.push({ word: word, points: pts, by: window.SayItGame.playerName(me) });
        var used = localSelected.slice().sort(function(a,b){ return b-a; });
        used.forEach(function(i) { rack.splice(i, 1); });
        while (rack.length < 7) rack.push(draw(1)[0]);
        state.racks[me] = rack;
        state.round = (state.round || 1) + 1;
        state.message = window.SayItGame.playerName(me) + ' played ' + word + ' for ' + pts + ' points' + (word.length >= 7 ? ' — BINGO!' : '!');
        advanceTurn();
        persist();
        render();
    };

    window.SayItGame.onRemote = function(payload) {
        if (!payload.state) return;
        state = Object.assign(state, payload.state);
        rng = mulberry32(state.bag_seed || 1);
        if (!window.SayItGame.isMyTurn(state)) localSelected = [];
        render();
    };

    ensureRacks();
    persist();
    render();
    window.SayItGame.startPolling(2000);
})();
</script>
@endpush
@endsection
