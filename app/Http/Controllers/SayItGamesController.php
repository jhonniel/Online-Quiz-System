<?php

namespace App\Http\Controllers;

use App\Events\SayItGameUpdated;
use App\Models\ConfessionHashtag;
use App\Models\ConfessionPost;
use App\Models\ConfessionTopic;
use App\Models\SayItGameSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SayItGamesController extends Controller
{
    private const PLAYER_COOKIE = 'sayit_game_player';

    public function index(Request $request)
    {
        $games = [
            [
                'type' => 'scrabble',
                'title' => 'Scrabble',
                'blurb' => 'Solo practice or lobby turns with friends.',
                'icon' => 'fas fa-font',
                'color' => 'from-amber-500 to-orange-600',
            ],
            [
                'type' => 'snakes',
                'title' => 'Snake & Ladder',
                'blurb' => 'Race alone or invite friends to the board.',
                'icon' => 'fas fa-dice',
                'color' => 'from-emerald-500 to-teal-600',
            ],
            [
                'type' => 'fourpics',
                'title' => '4 Pics 1 Word',
                'blurb' => 'Solve alone, or team up in a lobby.',
                'icon' => 'fas fa-th-large',
                'color' => 'from-violet-500 to-fuchsia-600',
            ],
            [
                'type' => 'duckrace',
                'title' => 'Duck Race',
                'blurb' => 'Solo heats or a group race with friends.',
                'icon' => 'fas fa-water',
                'color' => 'from-sky-500 to-blue-600',
            ],
        ];

        $openLobbies = SayItGameSession::query()
            ->openWaiting()
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->filter(fn (SayItGameSession $s) => ! $s->isFull())
            ->values();

        return view('say-it.games.index', array_merge($this->shellData(), [
            'games' => $games,
            'openLobbies' => $openLobbies,
            'playerId' => $this->playerIdFromRequest($request),
        ]));
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'game_type' => ['required', Rule::in(array_keys(SayItGameSession::TYPES))],
            'mode' => ['required', Rule::in(['solo', 'lobby'])],
            'nickname' => ['nullable', 'string', 'min:2', 'max:20'],
            'require_code' => ['nullable', 'boolean'],
        ]);

        $mode = $validated['mode'];
        $playerId = $this->ensurePlayerId($request);
        $name = trim((string) ($validated['nickname'] ?? ''));
        if ($name === '') {
            $name = $mode === 'solo' ? 'You' : 'Player';
        }

        if ($mode === 'lobby' && strlen($name) < 2) {
            throw ValidationException::withMessages([
                'nickname' => 'Enter a nickname for the lobby.',
            ]);
        }

        $session = new SayItGameSession([
            'code' => SayItGameSession::generateUniqueCode(),
            'game_type' => $validated['game_type'],
            'status' => SayItGameSession::STATUS_WAITING,
            'state' => [],
            'players' => [],
            'require_code' => $mode === 'solo' ? true : $request->boolean('require_code'),
            'last_played_at' => now(),
        ]);
        $session->addPlayer($playerId, $name, true);

        if ($mode === 'solo') {
            $session->startPlaying();
            $session->save();

            return redirect()
                ->route('say-it.games.play', $session->code)
                ->withCookie($this->playerCookie($playerId));
        }

        $session->save();

        return redirect()
            ->route('say-it.games.lobby', $session->code)
            ->withCookie($this->playerCookie($playerId));
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'nickname' => ['required', 'string', 'min:2', 'max:20'],
        ]);

        $session = SayItGameSession::query()
            ->where('code', $validated['code'])
            ->first();

        if (! $session) {
            return redirect()
                ->route('say-it.games.index')
                ->withErrors(['code' => 'No game found for that code.'])
                ->withInput();
        }

        return $this->seatPlayer($request, $session, trim($validated['nickname']));
    }

    public function joinOpen(Request $request)
    {
        $validated = $request->validate([
            'session_id' => ['required', 'integer'],
            'nickname' => ['required', 'string', 'min:2', 'max:20'],
        ]);

        $session = SayItGameSession::query()->find($validated['session_id']);

        if (! $session || ! $session->isOpen() || ! $session->isWaiting()) {
            return redirect()
                ->route('say-it.games.index')
                ->withErrors(['open' => 'That open lobby is no longer available.'])
                ->withInput();
        }

        return $this->seatPlayer($request, $session, trim($validated['nickname']));
    }

    public function resume(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'nickname' => ['nullable', 'string', 'min:2', 'max:20'],
        ]);

        $session = SayItGameSession::query()
            ->where('code', $validated['code'])
            ->first();

        if (! $session) {
            return redirect()
                ->route('say-it.games.index')
                ->withErrors(['code' => 'No game found for that code.'])
                ->withInput();
        }

        $playerId = $this->playerIdFromRequest($request);

        if ($session->isWaiting()) {
            if ($playerId && $session->hasPlayer($playerId)) {
                return redirect()
                    ->route('say-it.games.lobby', $session->code)
                    ->withCookie($this->playerCookie($playerId));
            }

            if (empty($validated['nickname'])) {
                return redirect()
                    ->route('say-it.games.index')
                    ->withErrors(['nickname' => 'Enter a nickname to join this lobby.'])
                    ->withInput();
            }

            return $this->seatPlayer($request, $session, trim($validated['nickname']));
        }

        if ($playerId && $session->hasPlayer($playerId)) {
            $session->update(['last_played_at' => now()]);

            return redirect()
                ->route('say-it.games.play', $session->code)
                ->withCookie($this->playerCookie($playerId));
        }

        return redirect()
            ->route('say-it.games.index')
            ->withErrors(['code' => 'This game already started. Only seated players can rejoin with their browser.'])
            ->withInput();
    }

    public function lobby(Request $request, string $code)
    {
        $session = $this->findSession($code);
        $playerId = $this->playerIdFromRequest($request);

        if (! $playerId || ! $session->hasPlayer($playerId)) {
            return redirect()
                ->route('say-it.games.index')
                ->withErrors(['code' => 'Join this lobby first with your nickname.']);
        }

        if (! $session->isWaiting()) {
            return redirect()->route('say-it.games.play', $session->code);
        }

        $session->update(['last_played_at' => now()]);

        return view('say-it.games.lobby', array_merge($this->shellData(), [
            'session' => $session,
            'playerId' => $playerId,
            'isHost' => $session->isHost($playerId),
        ]));
    }

    public function startGame(Request $request, string $code)
    {
        $session = $this->findSession($code);
        $playerId = $this->requireSeatedPlayer($request, $session);

        if (! $session->isHost($playerId)) {
            throw ValidationException::withMessages([
                'host' => 'Only the host can start the game.',
            ]);
        }

        if (! $session->isWaiting()) {
            return redirect()->route('say-it.games.play', $session->code);
        }

        if ($session->playerCount() < 1) {
            throw ValidationException::withMessages([
                'players' => 'Need at least one player to start.',
            ]);
        }

        $session->startPlaying();
        $session->save();
        $this->broadcast($session);

        return redirect()->route('say-it.games.play', $session->code);
    }

    public function leave(Request $request, string $code)
    {
        $session = $this->findSession($code);
        $playerId = $this->playerIdFromRequest($request);

        if ($playerId && $session->hasPlayer($playerId) && $session->isWaiting()) {
            $session->removePlayer($playerId);
            if ($session->playerCount() === 0) {
                $session->delete();

                return redirect()->route('say-it.games.index');
            }
            $session->save();
            $this->broadcast($session);
        }

        return redirect()->route('say-it.games.index');
    }

    public function play(Request $request, string $code)
    {
        $session = $this->findSession($code);
        $playerId = $this->playerIdFromRequest($request);

        if ($session->isWaiting()) {
            if ($playerId && $session->hasPlayer($playerId)) {
                return redirect()->route('say-it.games.lobby', $session->code);
            }

            return redirect()
                ->route('say-it.games.index')
                ->withErrors(['code' => 'Join the lobby before the host starts.']);
        }

        // Legacy solo sessions (no lobby players) — seat visitor as host.
        if ($session->playerCount() === 0) {
            $playerId = $this->ensurePlayerId($request);
            $session->addPlayer($playerId, 'Player', true);
            if (empty($session->state)) {
                $session->state = SayItGameSession::playingState($session->game_type, $session->orderedPlayers());
            }
            $session->last_played_at = now();
            $session->save();

            return redirect()
                ->route('say-it.games.play', $session->code)
                ->withCookie($this->playerCookie($playerId));
        }

        if (! $playerId || ! $session->hasPlayer($playerId)) {
            return redirect()
                ->route('say-it.games.index')
                ->withErrors(['code' => 'You are not seated in this game.']);
        }

        $session->update(['last_played_at' => now()]);

        $view = match ($session->game_type) {
            'scrabble' => 'say-it.games.scrabble',
            'snakes' => 'say-it.games.snakes',
            'fourpics' => 'say-it.games.fourpics',
            'duckrace' => 'say-it.games.duckrace',
            default => abort(404),
        };

        return view($view, array_merge($this->shellData(), [
            'session' => $session,
            'playerId' => $playerId,
            'showResumeCode' => false,
        ]));
    }

    public function saveState(Request $request, string $code)
    {
        $session = $this->findSession($code);
        $this->requireSeatedPlayer($request, $session);

        if ($session->isWaiting()) {
            return response()->json(['ok' => false, 'message' => 'Game not started.'], 422);
        }

        $validated = $request->validate([
            'state' => ['required', 'array'],
            'status' => ['nullable', Rule::in([
                SayItGameSession::STATUS_PLAYING,
                SayItGameSession::STATUS_FINISHED,
            ])],
        ]);

        $session->update([
            'state' => $validated['state'],
            'status' => $validated['status'] ?? $session->status,
            'last_played_at' => now(),
        ]);

        $this->broadcast($session);

        return response()->json([
            'ok' => true,
            'payload' => $session->toSyncPayload(),
        ]);
    }

    public function sync(Request $request, string $code)
    {
        $session = $this->findSession($code);
        $playerId = $this->playerIdFromRequest($request);

        if (! $playerId || ! $session->hasPlayer($playerId)) {
            return response()->json(['ok' => false], 403);
        }

        return response()->json([
            'ok' => true,
            'payload' => $session->toSyncPayload(),
        ]);
    }

    private function seatPlayer(Request $request, SayItGameSession $session, string $name)
    {
        if (! $session->canJoin()) {
            $msg = $session->isWaiting()
                ? 'This lobby is full.'
                : 'This game already started — you cannot join.';

            return redirect()
                ->route('say-it.games.index')
                ->withErrors(['code' => $msg])
                ->withInput();
        }

        $playerId = $this->ensurePlayerId($request);

        if ($session->hasPlayer($playerId)) {
            return redirect()
                ->route('say-it.games.lobby', $session->code)
                ->withCookie($this->playerCookie($playerId));
        }

        $session->addPlayer($playerId, $name, false);
        $session->last_played_at = now();
        $session->save();
        $this->broadcast($session);

        return redirect()
            ->route('say-it.games.lobby', $session->code)
            ->withCookie($this->playerCookie($playerId));
    }

    private function findSession(string $code): SayItGameSession
    {
        return SayItGameSession::query()->where('code', $code)->firstOrFail();
    }

    private function requireSeatedPlayer(Request $request, SayItGameSession $session): string
    {
        $playerId = $this->playerIdFromRequest($request);
        if (! $playerId || ! $session->hasPlayer($playerId)) {
            throw ValidationException::withMessages([
                'player' => 'You are not seated in this game.',
            ]);
        }

        return $playerId;
    }

    private function playerIdFromRequest(Request $request): ?string
    {
        $fromCookie = $request->cookie(self::PLAYER_COOKIE);
        $fromHeader = $request->header('X-SayIt-Player');
        $id = $fromHeader ?: $fromCookie;

        return is_string($id) && strlen($id) >= 8 ? $id : null;
    }

    private function ensurePlayerId(Request $request): string
    {
        return $this->playerIdFromRequest($request) ?: SayItGameSession::generatePlayerId();
    }

    private function playerCookie(string $playerId)
    {
        return Cookie::make(self::PLAYER_COOKIE, $playerId, 60 * 24 * 30, '/', null, false, false, false, 'lax');
    }

    private function broadcast(SayItGameSession $session): void
    {
        try {
            event(new SayItGameUpdated($session->fresh()));
        } catch (\Throwable) {
            // Polling fallback covers missing Reverb.
        }
    }

    /**
     * @return array{topTopics: mixed, topHashtags: mixed, recentPosts: mixed}
     */
    private function shellData(): array
    {
        return [
            'topTopics' => ConfessionTopic::orderByDesc('posts_count')->limit(10)->get(['id', 'name', 'slug', 'posts_count']),
            'topHashtags' => ConfessionHashtag::orderByDesc('posts_count')->limit(10)->get(['id', 'name', 'slug', 'posts_count']),
            'recentPosts' => ConfessionPost::withCount('allComments')->with(['latestComment', 'topic'])->orderByDesc('created_at')->limit(10)->get(),
        ];
    }
}
