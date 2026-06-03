<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HiringApplication extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'hiring_position_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'address',
        'position_applied',
        'cover_letter',
        'cover_letter_path',
        'resume_path',
        'resume_link',
        'school',
        'university_id',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'acceptance_token',
        'token_expires_at',
        'user_id',
        'interview_date',
        'interview_format',
        'interview_meeting_link',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'reviewed_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'interview_date' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function hiringPosition()
    {
        return $this->belongsTo(HiringPosition::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function resolvedUniversityId(): ?int
    {
        if ($this->university_id) {
            return (int) $this->university_id;
        }

        return University::resolveIdFromSchoolLabel($this->school);
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isHired()
    {
        return $this->status === 'hired';
    }

    public function isInterviewScheduled()
    {
        return $this->status === 'interview_scheduled';
    }

    public function isInterviewOnline(): bool
    {
        return ($this->interview_format ?? 'on_site') === 'online';
    }

    public function isInterviewDone()
    {
        return $this->status === 'done_interview';
    }

    /**
     * Application statuses where an internship applicant may use the quiz portal.
     *
     * @return list<string>
     */
    public static function internQuizPortalStatuses(): array
    {
        return ['accepted', 'interview_scheduled', 'done_interview', 'hired'];
    }

    public function isInternshipPosition(): bool
    {
        return strcasecmp((string) ($this->hiringPosition?->employment_type ?? ''), 'Internship') === 0;
    }

    public function qualifiesForInternQuizPortal(): bool
    {
        if (! $this->user_id) {
            return false;
        }

        $this->loadMissing('hiringPosition');

        if (! $this->isInternshipPosition()) {
            return false;
        }

        return in_array($this->status, self::internQuizPortalStatuses(), true);
    }

    public function generateAcceptanceToken()
    {
        $this->acceptance_token = Str::random(64);
        $this->token_expires_at = now()->addDays(30); // Token valid for 30 days
        $this->save();
        return $this->acceptance_token;
    }

    public function isTokenValid()
    {
        return $this->acceptance_token &&
               $this->token_expires_at &&
               $this->token_expires_at->isFuture();
    }
}
