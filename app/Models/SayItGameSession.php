<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SayItGameSession extends Model
{
    public const MAX_PLAYERS = 4;

    public const STATUS_WAITING = 'waiting';

    public const STATUS_PLAYING = 'playing';

    public const STATUS_FINISHED = 'finished';

    public const TYPES = [
        'scrabble' => 'Scrabble',
        'snakes' => 'Snake & Ladder',
        'fourpics' => '4 Pics 1 Word',
        'duckrace' => 'Duck Race',
    ];

    protected $table = 'sayit_game_sessions';

    protected $fillable = [
        'code',
        'game_type',
        'status',
        'state',
        'players',
        'host_player_id',
        'require_code',
        'player_label',
        'last_played_at',
    ];

    protected $casts = [
        'state' => 'array',
        'players' => 'array',
        'require_code' => 'boolean',
        'last_played_at' => 'datetime',
    ];

    public function label(): string
    {
        return self::TYPES[$this->game_type] ?? $this->game_type;
    }

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isPlaying(): bool
    {
        return $this->status === self::STATUS_PLAYING;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function isOpen(): bool
    {
        return ! $this->require_code;
    }

    public function playerCount(): int
    {
        return count($this->players ?? []);
    }

    public function isFull(): bool
    {
        return $this->playerCount() >= self::MAX_PLAYERS;
    }

    public function canJoin(): bool
    {
        return $this->isWaiting() && ! $this->isFull();
    }

    public function hasPlayer(string $playerId): bool
    {
        foreach ($this->players ?? [] as $player) {
            if (($player['id'] ?? null) === $playerId) {
                return true;
            }
        }

        return false;
    }

    public function isHost(string $playerId): bool
    {
        return $this->host_player_id === $playerId;
    }

    public function playerName(string $playerId): ?string
    {
        foreach ($this->players ?? [] as $player) {
            if (($player['id'] ?? null) === $playerId) {
                return $player['name'] ?? null;
            }
        }

        return null;
    }

    /**
     * @return list<array{id: string, name: string, is_host: bool, joined_at: string}>
     */
    public function orderedPlayers(): array
    {
        return array_values($this->players ?? []);
    }

    public function addPlayer(string $playerId, string $name, bool $isHost = false): void
    {
        $players = $this->players ?? [];
        $players[] = [
            'id' => $playerId,
            'name' => $name,
            'is_host' => $isHost,
            'joined_at' => now()->toIso8601String(),
        ];
        $this->players = $players;
        if ($isHost) {
            $this->host_player_id = $playerId;
        }
    }

    public function removePlayer(string $playerId): void
    {
        $players = array_values(array_filter(
            $this->players ?? [],
            fn (array $p) => ($p['id'] ?? null) !== $playerId
        ));

        if ($this->host_player_id === $playerId && count($players) > 0) {
            $players[0]['is_host'] = true;
            $this->host_player_id = $players[0]['id'];
        } elseif (count($players) === 0) {
            $this->host_player_id = null;
        }

        $this->players = $players;
    }

    public function startPlaying(): void
    {
        $this->status = self::STATUS_PLAYING;
        $this->state = self::playingState($this->game_type, $this->orderedPlayers());
        $this->last_played_at = now();
    }

    public function toSyncPayload(): array
    {
        return [
            'code' => $this->code,
            'game_type' => $this->game_type,
            'status' => $this->status,
            'require_code' => (bool) $this->require_code,
            'players' => $this->orderedPlayers(),
            'host_player_id' => $this->host_player_id,
            'state' => $this->state ?? [],
            'player_count' => $this->playerCount(),
            'max_players' => self::MAX_PLAYERS,
        ];
    }

    public function scopeOpenWaiting(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_WAITING)
            ->where('require_code', false);
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    public static function generatePlayerId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * @param  list<array{id: string, name: string}>  $players
     */
    public static function playingState(string $gameType, array $players): array
    {
        $ids = array_column($players, 'id');

        return match ($gameType) {
            'scrabble' => [
                'bag_seed' => random_int(1, 999999),
                'turn_index' => 0,
                'scores' => array_fill_keys($ids, 0),
                'racks' => array_fill_keys($ids, []),
                'words' => [],
                'selected' => [],
                'message' => 'Make words of 3+ letters. Take turns!',
                'round' => 1,
            ],
            'snakes' => [
                'positions' => array_fill_keys($ids, 1),
                'turn_index' => 0,
                'last_roll' => null,
                'log' => ['Race to 100. '.($players[0]['name'] ?? 'Host').' goes first!'],
                'winner' => null,
            ],
            'fourpics' => [
                'level' => 0,
                'guess' => '',
                'solved' => [],
                'hints_used' => 0,
                'score' => 0,
                'slots' => [],
                'used' => [],
                'pool' => [],
                'poolWord' => '',
                'last_solver' => null,
                'message' => 'Anyone can guess — first correct advances!',
            ],
            'duckrace' => [
                'picks' => array_fill_keys($ids, null),
                'racing' => false,
                'finished' => false,
                'progress' => [],
                'results' => [],
                'wins' => array_fill_keys($ids, 0),
                'races' => 0,
                'history' => [],
                'message' => 'Everyone pick a duck, then start the race.',
            ],
            default => [],
        };
    }

    /** @deprecated Prefer playingState after lobby start */
    public static function initialState(string $gameType): array
    {
        return [];
    }
}
