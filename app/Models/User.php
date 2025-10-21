<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'is_approved',
        'university_id',
        'status',
        'last_activity',
        'last_seen',
        'profile_picture',
        'cover_photo',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_approved' => 'boolean',
            'last_activity' => 'datetime',
            'last_seen' => 'datetime',
        ];
    }

    // Relationships
    public function createdQuizzes()
    {
        return $this->hasMany(Quiz::class, 'created_by');
    }

    public function quizAssignments()
    {
        return $this->hasMany(QuizAssignment::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function quizAttemptHistory()
    {
        return $this->hasMany(QuizAttemptHistory::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function adminChatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'admin_id');
    }

    public function chatTickets()
    {
        return $this->hasMany(ChatTicket::class);
    }

    public function assignedQuizzes()
    {
        return $this->belongsToMany(Quiz::class, 'quiz_assignments')
                    ->withPivot(['assigned_at', 'due_date', 'is_completed'])
                    ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->unread();
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function assignedFeedbacks()
    {
        return $this->hasMany(Feedback::class, 'assigned_to');
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isActive()
    {
        return $this->is_active;
    }

    // Status methods
    public function isAway()
    {
        return $this->status === 'away';
    }

    public function isIdle()
    {
        return $this->status === 'idle';
    }

    public function isOffline()
    {
        return $this->status === 'offline';
    }

    public function updateStatus(string $status)
    {
        $this->update([
            'status' => $status,
            'last_activity' => now(),
            'last_seen' => now(),
        ]);
    }

    public function markAsOnline()
    {
        $this->updateStatus('online');
    }

    public function markAsAway()
    {
        $this->updateStatus('away');
    }

    public function markAsIdle()
    {
        $this->updateStatus('idle');
    }

    public function markAsOffline()
    {
        $this->updateStatus('offline');
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'online' => 'bg-green-100 text-green-800',
            'away' => 'bg-yellow-100 text-yellow-800',
            'idle' => 'bg-orange-100 text-orange-800',
            'offline' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusIcon(): string
    {
        return match ($this->status) {
            'online' => '🟢',
            'away' => '🟡',
            'idle' => '🟠',
            'offline' => '⚫',
            default => '⚫',
        };
    }

    public function getLastActivityText(): string
    {
        if (!$this->last_activity) {
            return 'Never';
        }

        $diff = now()->diffInMinutes($this->last_activity);

        if ($diff < 1) {
            return 'Just now';
        } elseif ($diff < 60) {
            return $diff . ' minutes ago';
        } elseif ($diff < 1440) {
            return floor($diff / 60) . ' hours ago';
        } else {
            return $this->last_activity->format('M j, Y g:i A');
        }
    }

    // Rank calculation methods
    public function getTotalScore(): int
    {
        // Cache the total score for 5 minutes to improve performance
        return cache()->remember("user_total_score_{$this->id}", 300, function() {
            return $this->quizAttempts()
                ->whereNotNull('completed_at')
                ->sum('points_earned');
        });
    }

    public function getRank(): int|null
    {
        // Cache the rank calculation for 5 minutes to improve performance
        return cache()->remember("user_rank_{$this->id}", 300, function() {
            $totalScore = $this->getTotalScore();

            // If user has 0 points, they are unranked
            if ($totalScore === 0) {
                return null;
            }

            // Use a more efficient query to count users with higher scores
            $usersWithHigherScore = User::where('role', 'user')
                ->where('is_active', true)
                ->where('id', '!=', $this->id)
                ->whereHas('quizAttempts', function($query) {
                    $query->whereNotNull('completed_at');
                })
                ->withSum('quizAttempts', 'points_earned')
                ->get()
                ->filter(function($user) use ($totalScore) {
                    return ($user->quiz_attempts_sum_points_earned ?? 0) > $totalScore;
                })
                ->count();

            return $usersWithHigherScore + 1;
        });
    }

    public function getRankBadgeClass(): string
    {
        $rank = $this->getRank();

        // If user is unranked (0 points)
        if ($rank === null) {
            return 'bg-gray-100 text-gray-500 border-gray-200'; // Unranked
        }

        if ($rank === 1) {
            return 'bg-yellow-100 text-yellow-800 border-yellow-200'; // Gold
        } elseif ($rank === 2) {
            return 'bg-gray-100 text-gray-800 border-gray-200'; // Silver
        } elseif ($rank === 3) {
            return 'bg-orange-100 text-orange-800 border-orange-200'; // Bronze
        } elseif ($rank <= 10) {
            return 'bg-blue-100 text-blue-800 border-blue-200'; // Top 10
        } elseif ($rank <= 50) {
            return 'bg-green-100 text-green-800 border-green-200'; // Top 50
        } else {
            return 'bg-gray-100 text-gray-600 border-gray-200'; // Default
        }
    }

    public function getRankIcon(): string
    {
        $rank = $this->getRank();

        // If user is unranked (0 points)
        if ($rank === null) {
            return '❓'; // Question mark for unranked
        }

        if ($rank === 1) {
            return '🥇'; // Gold medal
        } elseif ($rank === 2) {
            return '🥈'; // Silver medal
        } elseif ($rank === 3) {
            return '🥉'; // Bronze medal
        } elseif ($rank <= 10) {
            return '⭐'; // Star for top 10
        } else {
            return '🏆'; // Trophy for others
        }
    }

    public function getRankText(): string
    {
        $rank = $this->getRank();

        // If user is unranked (0 points)
        if ($rank === null) {
            return 'Unranked';
        }

        if ($rank === 1) {
            return '1st Place';
        } elseif ($rank === 2) {
            return '2nd Place';
        } elseif ($rank === 3) {
            return '3rd Place';
        } else {
            return "#{$rank}";
        }
    }

    public function clearRankCache(): void
    {
        cache()->forget("user_rank_{$this->id}");
        cache()->forget("user_total_score_{$this->id}");
    }

    public function getProfilePictureUrl(): string
    {
        if ($this->profile_picture) {
            return \Storage::url($this->profile_picture);
        }
        return '';
    }

    public function getCoverPhotoUrl(): string
    {
        if ($this->cover_photo) {
            return \Storage::url($this->cover_photo);
        }
        return '';
    }

    public function hasCoverPhoto(): bool
    {
        return !empty($this->cover_photo);
    }

    public function getInitials(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        return substr($initials, 0, 2);
    }

    // Friendship relationships
    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    public function friendRequests()
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
                    ->wherePivot('status', 'accepted')
                    ->withTimestamps();
    }

    public function acceptedFriends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'friend_id', 'user_id')
                    ->wherePivot('status', 'accepted')
                    ->withTimestamps();
    }

    public function pendingFriendRequests()
    {
        return $this->friendRequests()->where('status', 'pending');
    }

    public function sentFriendRequests()
    {
        return $this->friendships()->where('status', 'pending');
    }

    // User chat relationships
    public function sentMessages()
    {
        return $this->hasMany(UserChatMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(UserChatMessage::class, 'receiver_id');
    }

    public function unreadMessages()
    {
        return $this->receivedMessages()->where('is_read', false);
    }

    public function unreadMessageCount()
    {
        return $this->unreadMessages()->count();
    }

    // User activity relationships
    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function currentSession()
    {
        return $this->hasOne(UserSession::class)->where('status', 'active');
    }

    public function isOnline()
    {
        return $this->sessions()
            ->where('status', 'active')
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->exists();
    }

    public function getLastActivity()
    {
        return $this->activities()->latest()->first();
    }

    // Forum relationships
    public function forumThreads()
    {
        return $this->hasMany(ForumThread::class, 'admin_id');
    }

    public function forumComments()
    {
        return $this->hasMany(ForumComment::class);
    }

    public function forumLikes()
    {
        return $this->hasMany(ForumLike::class);
    }

    public function forumSaves()
    {
        return $this->hasMany(ForumSave::class);
    }

    public function forumShares()
    {
        return $this->hasMany(ForumShare::class);
    }
}
