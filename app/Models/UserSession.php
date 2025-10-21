<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'status',
        'last_activity_at',
        'login_at',
        'logout_at',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_activity_at' => 'datetime',
        'login_at' => 'datetime',
        'logout_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create or update user session
     */
    public static function createOrUpdateSession(User $user, string $sessionId, string $status = 'active'): self
    {
        return self::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status' => $status,
                'last_activity_at' => now(),
                'login_at' => now(),
                'metadata' => [
                    'current_page' => request()->fullUrl(),
                    'referrer' => request()->header('referer')
                ]
            ]
        );
    }

    /**
     * Update last activity
     */
    public function updateActivity(): void
    {
        $this->update([
            'last_activity_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], [
                'current_page' => request()->fullUrl(),
                'last_action' => now()->toISOString()
            ])
        ]);
    }

    /**
     * Mark session as offline
     */
    public function markOffline(): void
    {
        $this->update([
            'status' => 'offline',
            'logout_at' => now()
        ]);
    }

    /**
     * Get online users
     */
    public static function getOnlineUsers()
    {
        return self::with('user')
            ->where('status', 'active')
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }

    /**
     * Get idle users (no activity for 5-15 minutes)
     */
    public static function getIdleUsers()
    {
        return self::with('user')
            ->where('status', 'active')
            ->where('last_activity_at', '>=', now()->subMinutes(15))
            ->where('last_activity_at', '<', now()->subMinutes(5))
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }

    /**
     * Get offline users (no activity for more than 15 minutes)
     */
    public static function getOfflineUsers()
    {
        return self::with('user')
            ->where(function ($query) {
                $query->where('status', 'offline')
                    ->orWhere('last_activity_at', '<', now()->subMinutes(15));
            })
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }

    /**
     * Clean up old sessions
     */
    public static function cleanupOldSessions(): void
    {
        self::where('last_activity_at', '<', now()->subHours(24))
            ->delete();
    }

    /**
     * Get session statistics
     */
    public static function getSessionStats(): array
    {
        return [
            'online' => self::where('status', 'active')
                ->where('last_activity_at', '>=', now()->subMinutes(5))
                ->count(),
            'idle' => self::where('status', 'active')
                ->where('last_activity_at', '>=', now()->subMinutes(15))
                ->where('last_activity_at', '<', now()->subMinutes(5))
                ->count(),
            'offline' => self::where(function ($query) {
                $query->where('status', 'offline')
                    ->orWhere('last_activity_at', '<', now()->subMinutes(15));
            })->count(),
            'total_today' => self::whereDate('login_at', today())->count()
        ];
    }
}
