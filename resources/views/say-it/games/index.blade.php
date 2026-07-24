@extends('layouts.say-it')

@section('title', 'Say it – Games')

@section('content')
<div class="max-w-3xl mx-auto w-full px-1 sm:px-0">
    <div class="rounded-3xl bg-gradient-to-br from-violet-600 via-fuchsia-600 to-orange-500 text-white p-6 sm:p-8 shadow-lg mb-6">
        <p class="text-sm font-semibold text-violet-100 uppercase tracking-wider">Mini games</p>
        <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight">Solo or lobby.</h1>
        <p class="mt-2 text-violet-100 text-sm sm:text-base max-w-xl">Play alone right away, or create a lobby and invite friends with a <strong class="text-white">6-digit code</strong> — or join an <strong class="text-white">open lobby</strong> before it starts.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-3">Join a lobby with a code</h2>
        <form method="POST" action="{{ route('say-it.games.join') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            @csrf
            <input type="text"
                   name="nickname"
                   maxlength="20"
                   placeholder="Your nickname"
                   value="{{ old('nickname') }}"
                   class="rounded-xl border border-gray-200 px-4 py-3 text-base focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                   required>
            <input type="text"
                   name="code"
                   inputmode="numeric"
                   pattern="[0-9]{6}"
                   maxlength="6"
                   placeholder="6-digit code"
                   value="{{ old('code') }}"
                   class="rounded-xl border border-gray-200 px-4 py-3 text-base tracking-[0.3em] text-center font-semibold focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none"
                   required>
            <button type="submit" class="px-5 py-3 rounded-xl bg-gray-900 text-white font-semibold hover:bg-gray-800 transition min-h-[48px]">
                Join lobby
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sm:p-5 mb-6">
        <div class="flex items-center justify-between gap-2 mb-3">
            <h2 class="text-base font-semibold text-gray-900">Open lobbies</h2>
            <span class="text-xs text-gray-400">No code needed · waiting only</span>
        </div>
        @if($openLobbies->isEmpty())
            <p class="text-sm text-gray-500">No open lobbies right now. Create a lobby below and leave it open for anyone to join.</p>
        @else
            <ul class="space-y-2">
                @foreach($openLobbies as $lobby)
                    <li class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 rounded-xl border border-gray-100 bg-gray-50 px-3 py-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900">{{ $lobby->label() }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $lobby->playerCount() }}/{{ \App\Models\SayItGameSession::MAX_PLAYERS }} players
                                · Host {{ collect($lobby->players)->firstWhere('is_host', true)['name'] ?? '—' }}
                            </div>
                        </div>
                        <form method="POST" action="{{ route('say-it.games.join-open') }}" class="flex gap-2 items-center">
                            @csrf
                            <input type="hidden" name="session_id" value="{{ $lobby->id }}">
                            <input type="text"
                                   name="nickname"
                                   maxlength="20"
                                   placeholder="Nickname"
                                   value="{{ old('nickname') }}"
                                   class="w-28 sm:w-36 rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                   required>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 whitespace-nowrap">
                                Join
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <h2 class="text-base font-semibold text-gray-900 mb-3">Pick a game</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($games as $game)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col game-card" data-game-card>
                <div class="h-28 bg-gradient-to-br {{ $game['color'] }} flex items-center justify-center text-white">
                    <i class="{{ $game['icon'] }} text-4xl drop-shadow"></i>
                </div>
                <div class="p-4 flex-1 flex flex-col">
                    <h3 class="text-lg font-bold text-gray-900">{{ $game['title'] }}</h3>
                    <p class="mt-1 text-sm text-gray-500 flex-1">{{ $game['blurb'] }}</p>

                    <div class="mt-4 grid grid-cols-2 gap-2 p-1 rounded-xl bg-gray-100">
                        <button type="button" data-mode-btn="solo" class="mode-btn rounded-lg py-2 text-sm font-semibold transition bg-white shadow text-gray-900">
                            Solo
                        </button>
                        <button type="button" data-mode-btn="lobby" class="mode-btn rounded-lg py-2 text-sm font-semibold transition text-gray-500">
                            Lobby
                        </button>
                    </div>

                    {{-- Solo: start immediately --}}
                    <form method="POST" action="{{ route('say-it.games.start') }}" class="mt-3 mode-panel" data-mode-panel="solo">
                        @csrf
                        <input type="hidden" name="game_type" value="{{ $game['type'] }}">
                        <input type="hidden" name="mode" value="solo">
                        <p class="text-xs text-gray-500 mb-2">Start immediately — just you.</p>
                        <button type="submit" class="w-full rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-semibold py-2.5 transition">
                            Play solo
                        </button>
                    </form>

                    {{-- Lobby: wait for friends --}}
                    <form method="POST" action="{{ route('say-it.games.start') }}" class="mt-3 mode-panel hidden space-y-2" data-mode-panel="lobby">
                        @csrf
                        <input type="hidden" name="game_type" value="{{ $game['type'] }}">
                        <input type="hidden" name="mode" value="lobby">
                        <input type="text"
                               name="nickname"
                               maxlength="20"
                               placeholder="Your nickname"
                               value="{{ old('nickname') }}"
                               class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm"
                               required>
                        <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                            <input type="checkbox" name="require_code" value="1" class="rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                            Private — invite code only
                        </label>
                        <button type="submit" class="w-full rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-semibold py-2.5 transition">
                            Create lobby
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-game-card]').forEach(function(card) {
    var buttons = card.querySelectorAll('[data-mode-btn]');
    var panels = card.querySelectorAll('[data-mode-panel]');
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var mode = btn.getAttribute('data-mode-btn');
            buttons.forEach(function(b) {
                var on = b.getAttribute('data-mode-btn') === mode;
                b.classList.toggle('bg-white', on);
                b.classList.toggle('shadow', on);
                b.classList.toggle('text-gray-900', on);
                b.classList.toggle('text-gray-500', !on);
            });
            panels.forEach(function(p) {
                p.classList.toggle('hidden', p.getAttribute('data-mode-panel') !== mode);
            });
        });
    });
});
</script>
@endpush
@endsection
