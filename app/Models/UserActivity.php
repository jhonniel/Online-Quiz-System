<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'activity_type',
        'action',
        'page_url',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime'
    ];

    protected static function booted(): void
    {
        static::creating(function (self $activity) {
            if (!$activity->created_at) {
                $activity->created_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log user activity
     */
    public static function logActivity(?User $user, string $activityType, ?string $action = null, array $metadata = []): self
    {
        return self::create([
            'user_id' => $user?->id,
            'activity_type' => $activityType,
            'action' => $action,
            'page_url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get recent activities
     *
     * @param int $limit
     * @param bool $excludeAdmins Exclude activities from admin users
     */
    public static function getRecentActivities(int $limit = 50, bool $excludeAdmins = false)
    {
        $query = self::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit);
        if ($excludeAdmins) {
            $query->where(function ($q) {
                $q->whereNull('user_id')
                    ->orWhereHas('user', fn($uq) => $uq->where('role', '!=', 'admin'));
            });
        }
        return $query->get();
    }

    /**
     * Get activities by type
     */
    public static function getActivitiesByType(string $type, int $limit = 50)
    {
        return self::with('user')
            ->where('activity_type', $type)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get online users count
     */
    public static function getOnlineUsersCount(): int
    {
        return UserSession::where('status', 'active')
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->count();
    }

    /**
     * Get today's login count
     */
    public static function getTodayLoginCount(): int
    {
        return self::where('activity_type', 'login')
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Get today's logout count
     */
    public static function getTodayLogoutCount(): int
    {
        return self::where('activity_type', 'logout')
            ->whereDate('created_at', today())
            ->count();
    }
}
