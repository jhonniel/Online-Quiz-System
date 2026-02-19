<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\MailConfigService;
use Illuminate\Auth\Notifications\ResetPassword;
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
        'department_id',
        'status',
        'last_activity',
        'last_seen',
        'profile_picture',
        'cover_photo',
        'bio',
        'overtime_months_credited',
        'required_training_hours',
        'qr_code_id',
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function assignedFeedbacks()
    {
        return $this->hasMany(Feedback::class, 'assigned_to');
    }

    public function dtrs()
    {
        return $this->hasMany(Dtr::class);
    }

    public function dtrDeficits()
    {
        return $this->hasMany(DtrDeficit::class);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public function isApplicant()
    {
        return $this->role === 'applicant';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function getRoleLabel()
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'student' => 'Student',
            'employee' => 'Employee',
            'applicant' => 'Applicant',
            'user' => 'User',
            default => ucfirst($this->role),
        };
    }

    public function getRoleBadgeClass()
    {
        return match($this->role) {
            'admin' => 'bg-purple-100 text-purple-800',
            'student' => 'bg-blue-100 text-blue-800',
            'employee' => 'bg-green-100 text-green-800',
            'applicant' => 'bg-yellow-100 text-yellow-800',
            'user' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
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
            return \Carbon\Carbon::parse($this->last_activity)->format('M j, Y g:i A');
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
        return $this->buildStorageUrl($this->profile_picture);
    }

    public function getCoverPhotoUrl(): string
    {
        return $this->buildStorageUrl($this->cover_photo);
    }

    protected function buildStorageUrl(?string $path): string
    {
        if (!$path) {
            return '';
        }

        // Try digitalocean disk first
        try {
            return \Storage::disk('digitalocean')->url($path);
        } catch (\Throwable $e) {
            // ignore
        }

        // Fallback to public disk if exists
        try {
            if (\Storage::disk('public')->exists($path)) {
                return \Storage::disk('public')->url($path);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Last resort
        try {
            return \Storage::url($path);
        } catch (\Throwable $e) {
            return '';
        }
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

    // Leave Requests relationship
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function adminPermission()
    {
        return $this->hasOne(AdminPermission::class);
    }

    public function hiringApplications()
    {
        return $this->hasMany(HiringApplication::class);
    }

    /**
     * Check if user has access to a specific admin feature.
     * Super admins (admins without permission records) have access to all features.
     * Employees must have explicit permission records with the specific permission enabled.
     * Other roles (students, applicants, etc.) can have access if they have an adminPermission record.
     */
    public function hasAdminPermission(string $permission): bool
    {
        // Load the relationship if not already loaded
        if (!$this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        // If user is an admin, they have full access (super admin)
        if ($this->isAdmin() && !$this->adminPermission) {
            return true;
        }

        // If user has an adminPermission record (regardless of role), check the specific permission
        if ($this->adminPermission) {
            return $this->adminPermission->$permission ?? false;
        }

        // If user is not an admin/employee and has no adminPermission record, they don't have access
        return false;
    }

    /**
     * Check if user has access to Content Management.
     */
    public function canAccessContentManagement(): bool
    {
        return $this->hasAdminPermission('content_management');
    }

    /**
     * Check if user has access to Analytics & Reports.
     */
    public function canAccessAnalyticsReports(): bool
    {
        return $this->hasAdminPermission('analytics_reports');
    }

    /**
     * Check if user has access to Employee Management.
     */
    public function canAccessEmployeeManagement(): bool
    {
        return $this->hasAdminPermission('employee_management');
    }

    /**
     * Check if user can manage a specific department.
     * Returns true if:
     * - User is a super admin (admin without permission restrictions)
     * - User has Employee Management permission and no department restrictions (allowed_departments is null or empty)
     * - User has Employee Management permission and the department is in their allowed_departments list
     */
    public function canManageDepartment(?int $departmentId): bool
    {
        // Super admins can manage all departments
        if ($this->isSuperAdmin()) {
            return true;
        }

        // If user doesn't have Employee Management permission, they can't manage any department
        if (!$this->canAccessEmployeeManagement()) {
            return false;
        }

        // Load the relationship if not already loaded
        if (!$this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;

        // If no permission record exists, return false (shouldn't happen if canAccessEmployeeManagement is true)
        if (!$adminPermission) {
            return false;
        }

        // If allowed_departments is null or empty, user can manage all departments
        $allowedDepartments = $adminPermission->allowed_departments;
        if (empty($allowedDepartments)) {
            return true;
        }

        // Check if the department ID is in the allowed list
        return in_array($departmentId, $allowedDepartments);
    }

    /**
     * Get the list of department IDs the user can manage.
     * Returns null if user can manage all departments, or an array of department IDs.
     */
    public function getAllowedDepartmentIds(): ?array
    {
        // Super admins can manage all departments (return null means all)
        if ($this->isSuperAdmin()) {
            return null;
        }

        // If user doesn't have Employee Management permission, return empty array
        if (!$this->canAccessEmployeeManagement()) {
            return [];
        }

        // Load the relationship if not already loaded
        if (!$this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;

        // If no permission record exists, return empty array
        if (!$adminPermission) {
            return [];
        }

        // Return allowed_departments (null or empty means all departments)
        $allowedDepartments = $adminPermission->allowed_departments;
        return empty($allowedDepartments) ? null : $allowedDepartments;
    }

    /**
     * Get allowed position IDs for hiring process filtering.
     * Returns null if user can access all positions, empty array if no access.
     */
    public function getAllowedPositionIds(): ?array
    {
        // Super admins can manage all positions (return null means all)
        if ($this->isSuperAdmin()) {
            return null;
        }

        // If user doesn't have Hiring Process permission, return empty array
        if (!$this->canAccessHiringProcess()) {
            return [];
        }

        // Load the relationship if not already loaded
        if (!$this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;

        // If no permission record exists, return empty array
        if (!$adminPermission) {
            return [];
        }

        // Return allowed_positions (null or empty means all positions)
        $allowedPositions = $adminPermission->allowed_positions;
        return empty($allowedPositions) ? null : $allowedPositions;
    }

    /**
     * Check if user has access to Student Management.
     */
    public function canAccessStudentManagement(): bool
    {
        return $this->hasAdminPermission('student_management');
    }

    /**
     * Check if user has access to Hiring Process.
     */
    public function canAccessHiringProcess(): bool
    {
        return $this->hasAdminPermission('hiring_process');
    }

    /**
     * Check if user has access to Communication.
     */
    public function canAccessCommunication(): bool
    {
        return $this->hasAdminPermission('communication');
    }

    /**
     * Check if user has access to User Management.
     */
    public function canAccessUserManagement(): bool
    {
        return $this->hasAdminPermission('user_management');
    }

    /**
     * Check if user has access to System.
     */
    public function canAccessSystem(): bool
    {
        return $this->hasAdminPermission('system');
    }

    /**
     * Check if user is a super admin/employee (no permission restrictions).
     */
    public function isSuperAdmin(): bool
    {
        // Only admins without permission records are super admins
        // Employees without permission records are NOT super admins
        return $this->isAdmin() && !$this->adminPermission;
    }

    /**
     * Check if user has any admin permission assigned.
     * Works for admins, employees, and any other role that has been granted permissions.
     */
    public function hasAnyAdminPermission(): bool
    {
        // Load the relationship if not already loaded
        if (!$this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;

        // If user is an admin without a permission record, they are a super admin with full access
        if ($this->isAdmin() && !$adminPermission) {
            return true; // Super admin has all permissions
        }

        // If user has a permission record (regardless of role), check if they have at least one permission enabled
        if ($adminPermission) {
            return $adminPermission->content_management ||
                   $adminPermission->analytics_reports ||
                   $adminPermission->employee_management ||
                   $adminPermission->student_management ||
                   $adminPermission->hiring_process ||
                   $adminPermission->communication ||
                   $adminPermission->user_management ||
                   $adminPermission->system;
        }

        // If user doesn't have a permission record, they have NO access
        return false;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // Ensure mail configuration is up to date from settings before sending reset link
        MailConfigService::configure();

        // Use Laravel's built-in ResetPassword notification
        $this->notify(new ResetPassword($token));
    }

    /**
     * Generate or get QR code ID for the user
     * Uses the current QR Code Prefix from settings dynamically
     * If prefix changes, regenerates QR code ID to match new prefix
     */
    public function generateQrCodeId()
    {
        // Get current prefix from settings (always fresh, not cached)
        // Clear cache to ensure we get the latest prefix value
        \Illuminate\Support\Facades\Cache::forget("setting.qr_code_prefix");
        $prefix = \App\Models\Setting::get('qr_code_prefix', 'QR');
        
        // Ensure prefix is not empty
        if (empty(trim($prefix))) {
            $prefix = 'QR';
        }
        $prefix = trim($prefix);
        
        // Check if QR code exists and if it matches the current prefix
        if ($this->qr_code_id) {
            // Check if the current QR code starts with the current prefix
            if (str_starts_with($this->qr_code_id, $prefix)) {
                // QR code already matches current prefix, return it
                return $this->qr_code_id;
            } else {
                // Prefix has changed, regenerate QR code with new prefix
                // Clear the old QR code ID so it gets regenerated
                $this->qr_code_id = null;
            }
        }

        // Generate new QR code ID with current prefix
        $maxAttempts = 100;
        $attempt = 0;

        do {
            $number = str_pad($this->id, 6, '0', STR_PAD_LEFT);
            $qrCodeId = $prefix . $number;
            $attempt++;
        } while (self::where('qr_code_id', $qrCodeId)->where('id', '!=', $this->id)->exists() && $attempt < $maxAttempts);

        if ($attempt >= $maxAttempts) {
            // Fallback: use timestamp if all attempts failed
            $qrCodeId = $prefix . time() . $this->id;
        }

        $this->qr_code_id = $qrCodeId;
        $this->save();

        return $qrCodeId;
    }

    /**
     * Get QR code image as base64
     * Uses static token per user (same QR code until scanned)
     */
    public function getQrCodeImage($size = 200)
    {
        try {
            // Get or generate a token for this user (reuses existing unused token)
            $token = \App\Models\QrCodeToken::getOrGenerateForUser($this);
            
            // Generate QR code URL - use url() helper as fallback if route() fails
            try {
                $qrCodeUrl = route('qr.scan', ['token' => $token]);
            } catch (\Exception $routeException) {
                // Fallback to url() if route helper fails
                $qrCodeUrl = url('/qr/' . $token);
            }
            
            // Use the QrCode facade with full namespace
            return \SimpleSoftwareIO\QrCode\Facades\QrCode::size($size)->generate($qrCodeUrl);
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('QR Code image generation failed: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return '';
        }
    }

    /**
     * Get QR code SVG string for embedding in HTML (doesn't require imagick)
     * Uses static token per user (same QR code until scanned)
     */
    public function getQrCodeSvg($size = 200)
    {
        try {
            // Get or generate a token for this user (reuses existing unused token)
            $token = \App\Models\QrCodeToken::getOrGenerateForUser($this);
            
            // Generate QR code URL - use url() helper as fallback if route() fails
            try {
                $qrCodeUrl = route('qr.scan', ['token' => $token]);
            } catch (\Exception $routeException) {
                // Fallback to url() if route helper fails
                $qrCodeUrl = url('/qr/' . $token);
            }
            
            // Use the QrCode facade (SVG format, doesn't require imagick)
            return \SimpleSoftwareIO\QrCode\Facades\QrCode::size($size)->format('svg')->generate($qrCodeUrl);
        } catch (\Exception $e) {
            // Log error for debugging but don't expose it to user
            \Log::error('QR Code generation failed: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return '<svg width="' . $size . '" height="' . $size . '"><text x="50%" y="50%" text-anchor="middle" dy=".3em">QR Code Unavailable</text></svg>';
        }
    }

    /**
     * Get QR code data URI for embedding in HTML (using SVG format to avoid imagick requirement)
     * Uses one-time token for security
     */
    public function getQrCodeDataUri($size = 200)
    {
        // Use getQrCodeSvg which already generates tokens
        $svg = $this->getQrCodeSvg($size);
        // Convert SVG to data URI
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
