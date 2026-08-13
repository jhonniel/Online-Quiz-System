<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\MailConfigService;
use App\Support\AdminPermissionAreas;
use App\Support\UserThemeColor;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const DEFAULT_STUDENT_ABSENCE_ALLOWANCE = 3.0;

    /** @var array<string, string> */
    public const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
        'prefer_not_to_say' => 'Prefer not to say',
    ];

    /** Roles that participate in quizzes / leaderboard rankings. */
    public const LEARNER_ROLES = ['student', 'user', 'applicant', 'employee', 'teacher'];

    public function scopeLearners($query)
    {
        return $query->whereIn('role', self::LEARNER_ROLES);
    }

    public static function normalizedStudentAbsenceAllowance(mixed $raw): float
    {
        $value = is_numeric($raw) ? (float) $raw : 0.0;

        return $value > 0 ? $value : self::DEFAULT_STUDENT_ABSENCE_ALLOWANCE;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'contact_number',
        'password',
        'role',
        'is_active',
        'is_approved',
        'profile_verified',
        'university_id',
        'course',
        'department_id',
        'department_position_id',
        'date_hired',
        'auto_tenure_leave_credits_enabled',
        'auto_tenure_leave_credits_last_tier',
        'tin',
        'sss_number',
        'hdmf_number',
        'phic_number',
        'status',
        'last_activity',
        'last_seen',
        'teacher_announcements_seen_at',
        'evaluation_forced_at',
        'profile_picture',
        'cover_photo',
        'e_signature_path',
        'p12_certificate_path',
        'p12_certificate_password',
        'moa_document_path',
        'moa_uploaded_at',
        'moa_reupload_allowed',
        'bio',
        'gender',
        'theme_color_enabled',
        'theme_color',
        'overtime_months_credited',
        'required_training_hours',
        'ojt_target_end_date',
        'student_absence_allowance',
        'student_manual_merits',
        'qr_code_id',
        'student_rules_warning',
        'student_rules_warning_manual',
        'student_rules_marquee_enabled',
        'student_rules_marquee_manual',
        'student_rules_merit_automation_disabled',
        'student_rules_notice_message',
        'student_terminated',
        'ojt_requirement_met_at',
        'ojt_completion_congratulations_sent_at',
        'ojt_post_completion_grace_closed_at',
        'ojt_account_disabled_notice_sent_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'p12_certificate_password',
    ];

    /**
     * Laravel 10 reads `$casts`; a `casts()` method alone is ignored.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        'profile_verified' => 'boolean',
        'last_activity' => 'datetime',
        'last_seen' => 'datetime',
        'teacher_announcements_seen_at' => 'datetime',
        'evaluation_forced_at' => 'datetime',
        'moa_uploaded_at' => 'datetime',
        'moa_reupload_allowed' => 'boolean',
        'theme_color_enabled' => 'boolean',
        'student_rules_warning' => 'boolean',
        'student_rules_warning_manual' => 'boolean',
        'student_rules_marquee_enabled' => 'boolean',
        'student_rules_marquee_manual' => 'boolean',
        'student_rules_merit_automation_disabled' => 'boolean',
        'student_terminated' => 'boolean',
        'student_absence_allowance' => 'float',
        'student_manual_merits' => 'integer',
        'date_hired' => 'date',
        'auto_tenure_leave_credits_enabled' => 'boolean',
        'auto_tenure_leave_credits_last_tier' => 'integer',
        'p12_certificate_password' => 'encrypted',
        'ojt_target_end_date' => 'date',
        'ojt_requirement_met_at' => 'datetime',
        'ojt_completion_congratulations_sent_at' => 'datetime',
        'ojt_post_completion_grace_closed_at' => 'datetime',
        'ojt_account_disabled_notice_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->role === 'teacher' || ! in_array($user->role, ['employee', 'hr', 'student'], true)) {
                $user->department_id = null;
                $user->department_position_id = null;
            }

            if ($user->department_id === null) {
                $user->department_position_id = null;
            }

            if ($user->department_position_id !== null && $user->department_id !== null) {
                $belongsToDepartment = DepartmentPosition::query()
                    ->whereKey($user->department_position_id)
                    ->where('department_id', $user->department_id)
                    ->exists();

                if (! $belongsToDepartment) {
                    $user->department_position_id = null;
                }
            }
        });
    }

    public function announcementAcknowledgments()
    {
        return $this->hasMany(EmployeeAnnouncementAcknowledgment::class);
    }

    public function pendingSystemAnnouncement(): ?SystemAnnouncement
    {
        if (! $this->isStaffMember() || ! $this->is_active) {
            return null;
        }

        return SystemAnnouncement::query()
            ->published()
            ->whereDoesntHave('acknowledgments', function ($query) {
                $query->where('user_id', $this->id);
            })
            ->orderBy('published_at')
            ->orderBy('id')
            ->first();
    }

    /**
     * Employment length from date_hired through today, for active employees only.
     */
    public function activeEmploymentDurationLabel(): ?string
    {
        if (! $this->isStaffMember() || ! $this->is_active || $this->date_hired === null) {
            return null;
        }

        $hired = $this->date_hired->copy()->startOfDay();
        $today = now()->startOfDay();
        $years = (int) $hired->diffInYears($today);

        if ($years >= 1) {
            return $years.' '.($years === 1 ? 'year' : 'years');
        }

        $months = (int) $hired->diffInMonths($today);
        if ($months >= 1) {
            return $months.' '.($months === 1 ? 'month' : 'months');
        }

        return 'Less than 1 month';
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

    public function studentNda()
    {
        return $this->hasOne(StudentNda::class);
    }

    public function studentPerformanceRating()
    {
        return $this->hasOne(StudentPerformanceRating::class);
    }

    /** Whether a student may use Record Attendance (signed NDA approved by admin). */
    public function canStudentRecordAttendance(): bool
    {
        if (! $this->isStudent()) {
            return true;
        }

        $this->loadMissing('studentNda');

        return $this->studentNda?->isApprovedForAttendance() ?? false;
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function departmentPosition()
    {
        return $this->belongsTo(DepartmentPosition::class, 'department_position_id');
    }

    public function payslipPositionLabel(): ?string
    {
        $this->loadMissing('departmentPosition:id,name,department_id');

        $position = $this->departmentPosition;
        if ($position === null || $this->department_id === null) {
            return null;
        }

        if ((int) $position->department_id !== (int) $this->department_id) {
            return null;
        }

        $label = trim((string) $position->name);

        return $label !== '' ? $label : null;
    }

    public function employeeDocumentSignatures()
    {
        return $this->hasMany(EmployeeDocumentSignature::class);
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

    /**
     * Profile verified badge — shown only after admin grants it in Users.
     */
    public function hasVerifiedBadge(): bool
    {
        return (bool) $this->profile_verified;
    }

    /**
     * Compact identity payload for JS UIs (chat, search, Alpine lists).
     *
     * @return array{id: int, name: string, profile_verified: bool}
     */
    public function verifiedIdentityPayload(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'profile_verified' => $this->hasVerifiedBadge(),
        ];
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public static function genderOptions(): array
    {
        return self::GENDERS;
    }

    public static function genderLabel(?string $gender): string
    {
        if ($gender === null || $gender === '') {
            return 'Not specified';
        }

        return self::GENDERS[$gender] ?? 'Not specified';
    }

    public function getGenderLabelAttribute(): string
    {
        return self::genderLabel($this->gender);
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    public static function employeeGenderChartData(): array
    {
        $labels = [];
        $data = [];

        foreach (self::GENDERS as $key => $label) {
            $labels[] = $label;
            $data[] = self::query()->where('role', 'employee')->where('gender', $key)->count();
        }

        $labels[] = 'Not specified';
        $data[] = self::query()
            ->where('role', 'employee')
            ->where(function ($query) {
                $query->whereNull('gender')->orWhere('gender', '');
            })
            ->count();

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function isHr()
    {
        return $this->role === 'hr';
    }

    /** HR accounts use admin workflows only — no employee/user portal nav. */
    public function shouldShowUserFeaturesNav(): bool
    {
        return ! $this->isHr();
    }

    public function usesHrDashboard(): bool
    {
        return $this->isHr();
    }

    public function adminDashboardUrl(): string
    {
        if ($this->usesHrDashboard()) {
            return url('/admin/hr-dashboard');
        }

        if ($this->isSuperAdmin() || ($this->isAdmin() && $this->hasAnyAdminPermission())) {
            return url('/admin/dashboard');
        }

        return url('/dashboard');
    }

    /** Employee or HR — internal staff with department workflows. */
    public function isStaffMember(): bool
    {
        return in_array($this->role, ['employee', 'hr'], true);
    }

    public function isApplicant()
    {
        return $this->role === 'applicant';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function isTechnician()
    {
        return $this->role === 'technician';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function getRoleLabel()
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'student' => 'Student',
            'employee' => 'Employee',
            'hr' => 'HR',
            'teacher' => 'Teacher',
            'applicant' => 'Applicant',
            'technician' => 'Technician',
            'user' => 'User',
            default => ucfirst($this->role),
        };
    }

    public function getRoleBadgeClass()
    {
        return match ($this->role) {
            'admin' => 'bg-purple-100 text-purple-800',
            'student' => 'bg-blue-100 text-blue-800',
            'employee' => 'bg-green-100 text-green-800',
            'hr' => 'bg-teal-100 text-teal-800',
            'teacher' => 'bg-sky-100 text-sky-800',
            'applicant' => 'bg-yellow-100 text-yellow-800',
            'technician' => 'bg-cyan-100 text-cyan-800',
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
        if (! $this->last_activity) {
            return 'Never';
        }

        $diff = now()->diffInMinutes($this->last_activity);

        if ($diff < 1) {
            return 'Just now';
        } elseif ($diff < 60) {
            return $diff.' minutes ago';
        } elseif ($diff < 1440) {
            return floor($diff / 60).' hours ago';
        } else {
            return Carbon::parse($this->last_activity)->format('M j, Y g:i A');
        }
    }

    // Rank calculation methods
    public function getTotalScore(): int
    {
        // Cache the total score for 5 minutes to improve performance
        return cache()->remember("user_total_score_{$this->id}", 300, function () {
            return $this->quizAttempts()
                ->whereNotNull('completed_at')
                ->sum('points_earned');
        });
    }

    public function getRank(): ?int
    {
        // Cache the rank calculation for 5 minutes to improve performance
        return cache()->remember("user_rank_{$this->id}", 300, function () {
            $totalScore = $this->getTotalScore();

            // If user has 0 points, they are unranked
            if ($totalScore === 0) {
                return null;
            }

            // Use a more efficient query to count users with higher scores
            $usersWithHigherScore = User::query()
                ->learners()
                ->where('is_active', true)
                ->where('id', '!=', $this->id)
                ->whereHas('quizAttempts', function ($query) {
                    $query->whereNotNull('completed_at');
                })
                ->withSum('quizAttempts', 'points_earned')
                ->get()
                ->filter(function ($user) use ($totalScore) {
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

    public function getESignatureUrl(): string
    {
        return $this->buildStorageUrl($this->e_signature_path);
    }

    public function hasESignature(): bool
    {
        return ! empty($this->e_signature_path);
    }

    public function hasP12Certificate(): bool
    {
        return ! empty($this->p12_certificate_path) && ! empty($this->p12_certificate_password);
    }

    public function getMoaDocumentUrl(): string
    {
        return $this->buildStorageUrl($this->moa_document_path);
    }

    protected function buildStorageUrl(?string $path): string
    {
        if (! $path) {
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
        return ! empty($this->cover_photo);
    }

    public function getInitials(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            if (! empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        return substr($initials, 0, 2);
    }

    public function canCustomizeThemeColor(): bool
    {
        return (bool) ($this->theme_color_enabled ?? false);
    }

    public function resolvedThemeColor(): ?string
    {
        if (! $this->canCustomizeThemeColor()) {
            return null;
        }

        return UserThemeColor::normalize($this->theme_color) ?? UserThemeColor::DEFAULT;
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

    public function groupChats()
    {
        return $this->belongsToMany(GroupChat::class, 'group_chat_members', 'user_id', 'group_chat_id')
            ->withPivot(['joined_at', 'last_read_at'])
            ->withTimestamps()
            ->orderByDesc('group_chats.updated_at');
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
     * Internship hiring application that grants access to the assigned-quizzes portal.
     */
    public function internshipApplicationForQuizPortal(): ?HiringApplication
    {
        if (! $this->isApplicant()) {
            return null;
        }

        return HiringApplication::query()
            ->where('user_id', $this->id)
            ->whereIn('status', HiringApplication::internQuizPortalStatuses())
            ->whereHas('hiringPosition', function ($query) {
                $query->whereRaw('LOWER(employment_type) = ?', ['internship']);
            })
            ->with('hiringPosition')
            ->latest('reviewed_at')
            ->first();
    }

    public function hasInternshipQuizPortalAccess(): bool
    {
        return $this->internshipApplicationForQuizPortal() !== null;
    }

    /**
     * Whether the user may open the quizzes area (only admin-assigned quizzes are listed).
     */
    public function canViewAssignedQuizzes(): bool
    {
        if (in_array($this->role, ['technician', 'teacher'], true)) {
            return false;
        }

        if ($this->isApplicant()) {
            return $this->hasInternshipQuizPortalAccess();
        }

        return true;
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
        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        // If user is an admin, they have full access (super admin)
        if ($this->isAdmin() && ! $this->adminPermission) {
            return true;
        }

        // If user has an adminPermission record (regardless of role), check the specific permission
        if ($this->adminPermission) {
            return $this->adminPermission->$permission ?? false;
        }

        // If user is not an admin/employee and has no adminPermission record, they don't have access
        return false;
    }

    public function canAccessSubscriptions(): bool
    {
        return $this->hasAdminPermission('linked_accounts') || $this->hasAdminPermission('billing');
    }

    /**
     * Parent permission flag for an admin area (including virtual subscriptions).
     */
    public function hasAdminAreaParent(string $areaKey): bool
    {
        $area = AdminPermissionAreas::area($areaKey);
        if (! $area) {
            return false;
        }

        if (! empty($area['subscription_parent'])) {
            return $this->canAccessSubscriptions();
        }

        if ($areaKey === 'communication' && $this->hasAdminPermission('feedback')) {
            return true;
        }

        return $this->hasAdminPermission($area['parent_flag']);
    }

    /**
     * @param  string  $areaKey  Key from AdminPermissionAreas::areas()
     * @param  string  $feature  Sub-feature key within that area
     */
    public function canAccessAdminSubFeature(string $areaKey, string $feature): bool
    {
        $area = AdminPermissionAreas::area($areaKey);
        if (! $area || ! array_key_exists($feature, $area['features'])) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($areaKey === 'analytics_reports') {
            return $this->canAccessAnalyticsFeature($feature);
        }

        if ($areaKey === 'communication' && $feature === 'feedback') {
            if ($this->hasAdminPermission('feedback')) {
                return true;
            }
            if (! $this->hasAdminPermission('communication')) {
                return false;
            }
        } elseif (! $this->hasAdminAreaParent($areaKey)) {
            return false;
        }

        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;
        if (! $adminPermission) {
            return false;
        }

        $column = $area['column'];
        $allowed = $adminPermission->{$column};
        if (empty($allowed)) {
            return true;
        }

        return in_array($feature, $allowed, true);
    }

    public function canAccessAnyAdminSubFeature(string $areaKey): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $area = AdminPermissionAreas::area($areaKey);
        if (! $area) {
            return false;
        }

        if ($areaKey === 'analytics_reports') {
            return $this->canAccessAnyAnalyticsFeature();
        }

        if (! $this->hasAdminAreaParent($areaKey)) {
            return false;
        }

        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $allowed = $this->adminPermission?->{$area['column']};
        if (empty($allowed)) {
            return true;
        }

        return count(array_intersect($allowed, array_keys($area['features']))) > 0;
    }

    public function canAccessContentFeature(string $feature): bool
    {
        return $this->canAccessAdminSubFeature('content_management', $feature);
    }

    public function canAccessEmployeeFeature(string $feature): bool
    {
        return $this->canAccessAdminSubFeature('employee_management', $feature);
    }

    public function canAccessAnyEmployeeDocumentFeature(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->canAccessEmployeeManagement()) {
            return false;
        }

        foreach (AdminPermissionAreas::EMPLOYEE_DOCUMENT_FEATURES as $feature) {
            if ($this->canAccessEmployeeFeature($feature)) {
                return true;
            }
        }

        return $this->canAccessEmployeeFeature('employee_signatures');
    }

    public function canAccessTasks(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->hasAdminPermission('tasks')) {
            return true;
        }

        return $this->hasAnyAdminPermissionExcludingTasks();
    }

    public function canAccessTaskFeature(string $feature): bool
    {
        if (! array_key_exists($feature, AdminPermissionAreas::TASK_FEATURES)) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->canAccessTasks()) {
            return false;
        }

        if (! $this->hasAdminPermission('tasks') && $this->hasAnyAdminPermissionExcludingTasks()) {
            return true;
        }

        return $this->canAccessAdminSubFeature('task_management', $feature);
    }

    public function canAccessAnyTaskFeature(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->canAccessTasks()) {
            return false;
        }

        if (! $this->hasAdminPermission('tasks') && $this->hasAnyAdminPermissionExcludingTasks()) {
            return true;
        }

        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $allowed = $this->adminPermission?->allowed_task_features;
        if (empty($allowed)) {
            return true;
        }

        return count(array_intersect($allowed, array_keys(AdminPermissionAreas::TASK_FEATURES))) > 0;
    }

    /**
     * @return bool Whether the user has any delegated admin permission other than Task To Do.
     */
    public function hasAnyAdminPermissionExcludingTasks(): bool
    {
        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;
        if (! $adminPermission) {
            return false;
        }

        return $adminPermission->content_management
            || $adminPermission->analytics_reports
            || $adminPermission->employee_management
            || $adminPermission->student_management
            || $adminPermission->hiring_process
            || $adminPermission->communication
            || $adminPermission->linked_accounts
            || $adminPermission->billing
            || $adminPermission->files
            || $adminPermission->confession
            || $adminPermission->feedback
            || $adminPermission->user_management
            || $adminPermission->system
            || ($adminPermission->qr_code ?? false);
    }

    public static function employeeDocumentsNavEnabled(): bool
    {
        return (string) Setting::get('employee_documents_nav_enabled', 'enabled') !== 'disabled';
    }

    public function canAccessStudentFeature(string $feature): bool
    {
        return $this->canAccessAdminSubFeature('student_management', $feature);
    }

    public function canAccessHiringFeature(string $feature): bool
    {
        return $this->canAccessAdminSubFeature('hiring_process', $feature);
    }

    public function canAccessCommunicationFeature(string $feature): bool
    {
        return $this->canAccessAdminSubFeature('communication', $feature);
    }

    public function canAccessSubscriptionFeature(string $feature): bool
    {
        return $this->canAccessAdminSubFeature('subscriptions', $feature);
    }

    public function canAccessUserManagementFeature(string $feature): bool
    {
        return $this->canAccessAdminSubFeature('user_management', $feature);
    }

    public function canAccessSystemFeature(string $feature): bool
    {
        return $this->canAccessAdminSubFeature('system', $feature);
    }

    /**
     * Check if user has access to Content Management.
     */
    public function canAccessContentManagement(): bool
    {
        return $this->hasAdminPermission('content_management');
    }

    /**
     * Check if user has access to Analytics & Reports (parent permission).
     */
    public function canAccessAnalyticsReports(): bool
    {
        return $this->hasAdminPermission('analytics_reports');
    }

    /**
     * Whether the user can open any Analytics & Reports sub-area (for sidebar group visibility).
     */
    public function canAccessAnyAnalyticsFeature(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $this->canAccessAnalyticsReports()) {
            return $this->canAccessAnalyticsFeature('user_activity');
        }

        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;
        if (! $adminPermission) {
            return false;
        }

        $allowed = $adminPermission->allowed_analytics_features;
        if (empty($allowed)) {
            return true;
        }

        return count(array_intersect($allowed, array_keys(AdminPermission::ANALYTICS_FEATURES))) > 0;
    }

    /**
     * Check access to a specific Analytics & Reports sub-area.
     *
     * @param  string  $feature  analytics|error_logs|user_activity|students_review
     */
    public function canAccessAnalyticsFeature(string $feature): bool
    {
        if (! array_key_exists($feature, AdminPermission::ANALYTICS_FEATURES)) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($feature === 'user_activity' && $this->canAccessSystem()) {
            return true;
        }

        if (! $this->canAccessAnalyticsReports()) {
            return false;
        }

        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;
        if (! $adminPermission) {
            return false;
        }

        $allowed = $adminPermission->allowed_analytics_features;
        if (empty($allowed)) {
            return true;
        }

        return in_array($feature, $allowed, true);
    }

    /**
     * Allowed Analytics & Reports sub-area keys, or null for all when parent is enabled.
     *
     * @return array<string>|null
     */
    public function getAllowedAnalyticsFeatures(): ?array
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        if (! $this->canAccessAnalyticsReports()) {
            return [];
        }

        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $allowed = $this->adminPermission?->allowed_analytics_features;

        return empty($allowed) ? null : $allowed;
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
        if (! $this->canAccessEmployeeManagement()) {
            return false;
        }

        // Load the relationship if not already loaded
        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;

        // If no permission record exists, return false (shouldn't happen if canAccessEmployeeManagement is true)
        if (! $adminPermission) {
            return false;
        }

        $allowedDepartmentIds = $this->getAllowedDepartmentIds();
        if ($allowedDepartmentIds === null) {
            return true;
        }

        if ($departmentId === null) {
            return false;
        }

        return in_array((int) $departmentId, $allowedDepartmentIds, true);
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
        if (! $this->canAccessEmployeeManagement()) {
            return [];
        }

        // Load the relationship if not already loaded
        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;

        // If no permission record exists, return empty array
        if (! $adminPermission) {
            return [];
        }

        $employeeDepartments = $adminPermission->allowed_employee_departments;
        $legacyDepartments = $adminPermission->allowed_departments;

        if (! empty($employeeDepartments)) {
            $allowedDepartments = $employeeDepartments;
        } elseif (! empty($legacyDepartments)) {
            $allowedDepartments = $legacyDepartments;
        } else {
            return null;
        }

        return array_values(array_unique(array_map('intval', $allowedDepartments)));
    }

    /**
     * Get allowed department IDs for Student Management filtering.
     * Returns null if user can access all departments, empty array if no access.
     */
    public function getAllowedStudentDepartmentIds(): ?array
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        if (! $this->canAccessStudentManagement()) {
            return [];
        }

        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;
        if (! $adminPermission) {
            return [];
        }

        // Prefer dedicated student department restrictions. Fallback to legacy allowed_departments.
        $allowedDepartments = $adminPermission->allowed_student_departments ?? $adminPermission->allowed_departments;

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
        if (! $this->canAccessHiringProcess()) {
            return [];
        }

        // Load the relationship if not already loaded
        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;

        // If no permission record exists, return empty array
        if (! $adminPermission) {
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
     * Check if user has access to Billing.
     */
    public function canAccessBilling(): bool
    {
        return $this->hasAdminPermission('billing')
            && $this->canAccessSubscriptionFeature('billing');
    }

    /**
     * Check if user has access to Linked Accounts (dashboard, Starlinks, Omada, Plan Types).
     */
    public function canAccessLinkedAccounts(): bool
    {
        if (! $this->hasAdminPermission('linked_accounts')) {
            return false;
        }

        return $this->canAccessSubscriptionFeature('dashboard')
            || $this->canAccessSubscriptionFeature('starlinks')
            || $this->canAccessSubscriptionFeature('omadas')
            || $this->canAccessSubscriptionFeature('plan_types');
    }

    /**
     * Check if user has access to File Storage.
     */
    public function canAccessFiles(): bool
    {
        return $this->hasAdminPermission('files');
    }

    /**
     * Check if user has access to Confession (Say-it).
     */
    public function canAccessConfession(): bool
    {
        return $this->hasAdminPermission('confession');
    }

    /**
     * Check if user has access to Feedback Management.
     */
    public function canAccessFeedback(): bool
    {
        return $this->canAccessCommunicationFeature('feedback');
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
     * Whether the user's identification QR (/qr/{token}) may resolve when scanned.
     * Employees always have a scannable ID; other roles need the "QR code" admin permission.
     */
    public function canAccessQrCode(): bool
    {
        if ($this->isStaffMember()) {
            return true;
        }

        return $this->hasAdminPermission('qr_code');
    }

    /**
     * Check if user is a super admin/employee (no permission restrictions).
     */
    public function isSuperAdmin(): bool
    {
        // Only admins without permission records are super admins
        // Employees without permission records are NOT super admins
        return $this->isAdmin() && ! $this->adminPermission;
    }

    /**
     * Official school-letter excuse (no demerit) — admin role only, not delegated staff.
     * Super admins always qualify; other admins need student management access.
     */
    public function canAcceptOfficiallyExcusedLeave(): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        return $this->isSuperAdmin() || $this->hasAdminPermission('student_management');
    }

    /**
     * Check if user has any admin permission assigned.
     * Works for admins, employees, and any other role that has been granted permissions.
     */
    public function hasAnyAdminPermission(): bool
    {
        // Load the relationship if not already loaded
        if (! $this->relationLoaded('adminPermission')) {
            $this->load('adminPermission');
        }

        $adminPermission = $this->adminPermission;

        // If user is an admin without a permission record, they are a super admin with full access
        if ($this->isAdmin() && ! $adminPermission) {
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
                   $adminPermission->linked_accounts ||
                   $adminPermission->billing ||
                   $adminPermission->files ||
                   $adminPermission->confession ||
                   $adminPermission->tasks ||
                   $adminPermission->feedback ||
                   $adminPermission->user_management ||
                   $adminPermission->system ||
                   ($adminPermission->qr_code ?? false);
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
        Cache::forget('setting.qr_code_prefix');
        $prefix = Setting::get('qr_code_prefix', 'QR');

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
            $qrCodeId = $prefix.$number;
            $attempt++;
        } while (self::where('qr_code_id', $qrCodeId)->where('id', '!=', $this->id)->exists() && $attempt < $maxAttempts);

        if ($attempt >= $maxAttempts) {
            // Fallback: use timestamp if all attempts failed
            $qrCodeId = $prefix.time().$this->id;
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
            $token = QrCodeToken::getOrGenerateForUser($this);

            // Generate QR code URL - use url() helper as fallback if route() fails
            try {
                $qrCodeUrl = route('qr.scan', ['token' => $token]);
            } catch (\Exception $routeException) {
                // Fallback to url() if route helper fails
                $qrCodeUrl = url('/qr/'.$token);
            }

            // Use the QrCode facade with full namespace
            return QrCode::size($size)->generate($qrCodeUrl);
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('QR Code image generation failed: '.$e->getMessage().' | Trace: '.$e->getTraceAsString());

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
            $token = QrCodeToken::getOrGenerateForUser($this);

            // Generate QR code URL - use url() helper as fallback if route() fails
            try {
                $qrCodeUrl = route('qr.scan', ['token' => $token]);
            } catch (\Exception $routeException) {
                // Fallback to url() if route helper fails
                $qrCodeUrl = url('/qr/'.$token);
            }

            // Use the QrCode facade (SVG format, doesn't require imagick)
            return QrCode::size($size)->format('svg')->generate($qrCodeUrl);
        } catch (\Exception $e) {
            // Log error for debugging but don't expose it to user
            \Log::error('QR Code generation failed: '.$e->getMessage().' | Trace: '.$e->getTraceAsString());

            return '<svg width="'.$size.'" height="'.$size.'"><text x="50%" y="50%" text-anchor="middle" dy=".3em">QR Code Unavailable</text></svg>';
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
        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
