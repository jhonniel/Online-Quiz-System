<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'travel_hours',
        'supporting_document_path',
        'supporting_document_paths',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reviewed_at' => 'datetime',
        'travel_hours' => 'float',
        'supporting_document_paths' => 'array',
    ];

    /**
     * Normalized list of supporting document paths (new multi-upload + legacy single path).
     *
     * @return list<string>
     */
    public function getAllSupportingDocumentPathsAttribute(): array
    {
        $paths = collect($this->supporting_document_paths ?? [])
            ->map(fn ($path) => trim((string) $path))
            ->filter()
            ->values();

        if ($paths->isNotEmpty()) {
            return $paths->all();
        }

        $legacy = trim((string) ($this->supporting_document_path ?? ''));

        return $legacy !== '' ? [$legacy] : [];
    }

    /**
     * Get the user who created this leave request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed this leave request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get all logs for this leave request.
     */
    public function logs()
    {
        return $this->hasMany(LeaveRequestLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the log entry for approval (most recent).
     */
    public function approvedBy()
    {
        return $this->hasOne(LeaveRequestLog::class)
            ->where('action', 'approved')
            ->latest();
    }

    /**
     * Get the log entry for rejection (most recent).
     */
    public function rejectedBy()
    {
        return $this->hasOne(LeaveRequestLog::class)
            ->where('action', 'rejected')
            ->latest();
    }

    /**
     * Get the log entry for resubmission request (most recent).
     */
    public function resubmissionRequestedBy()
    {
        return $this->hasOne(LeaveRequestLog::class)
            ->where('action', 'resubmission_requested')
            ->latest();
    }

    /**
     * Leave types an admin may assign when correcting a request (by requester role).
     *
     * @return list<string>
     */
    public static function adminSelectableTypesForRole(string $role): array
    {
        if ($role === 'student') {
            return ['additional_time', 'absent', 'other'];
        }

        return [
            'leave',
            'vacation_leave',
            'sick_leave',
            'work_from_home',
            'absent',
            'overtime',
            'offset',
            'additional_time',
            'travel',
            'other',
        ];
    }

    /**
     * Human-readable label for a stored type value.
     */
    public static function labelForType(?string $type): string
    {
        if ($type === null || $type === '') {
            return 'Unknown';
        }

        return self::typeLabelMap()[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::labelForType($this->type);
    }

    /**
     * @return array<string, string>
     */
    private static function typeLabelMap(): array
    {
        return [
            'leave' => 'Leave',
            'vacation_leave' => 'Vacation Leave',
            'sick_leave' => 'Sick Leave',
            'work_from_home' => 'Work From Home',
            'absent' => 'Absent',
            'overtime' => 'Overtime',
            'offset' => 'Offset',
            'additional_time' => 'Additional Time',
            'travel' => 'Travel',
            'other' => 'Other',
        ];
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'for_more_verification' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get a human-readable status label.
     *
     * If the request is pending but has already been reviewed (admin requested
     * changes via resubmission), show \"Resubmission\" as the status label.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'pending' && $this->reviewed_at) {
            return 'Resubmission';
        }
        if ($this->status === 'for_more_verification') {
            return 'For More Verification';
        }

        return ucfirst($this->status);
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Pending requests where the administrator asked the user to fix and resubmit.
     */
    public function scopeAwaitingUserResubmission(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->whereNotNull('reviewed_at')
            ->whereHas('logs', function ($q): void {
                $q->where('action', 'resubmission_requested');
            });
    }

    /**
     * Get the number of days.
     */
    public function getDaysAttribute(): int
    {
        if (! $this->end_date) {
            return 1;
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Minutes from "Hours to Deduct: H:MM" in stored reason (offset), or null if missing/invalid.
     */
    public function parseOffsetHoursToDeductMinutes(): ?int
    {
        $raw = (string) ($this->reason ?? '');
        if (! preg_match('/Hours to Deduct:\s*(\d{1,4}):(\d{2})/', $raw, $m)) {
            return null;
        }
        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($min > 59) {
            return null;
        }
        $total = $h * 60 + $min;

        return $total > 0 ? $total : null;
    }

    /**
     * Human-readable duration for details UI.
     * For offset: if hours to deduct differs from (calendar days × 8h), show that HH:MM; otherwise show day count + date range.
     */
    public function getDurationDisplayLabelAttribute(): string
    {
        if ($this->type !== 'offset') {
            $d = $this->days;

            return $d.' '.($d === 1 ? 'day' : 'days');
        }

        $deductMins = $this->parseOffsetHoursToDeductMinutes();
        $days = $this->days;
        $defaultMins = $days * 8 * 60;
        $end = $this->end_date ?? $this->start_date;

        if ($deductMins !== null && $deductMins !== $defaultMins) {
            $h = intdiv($deductMins, 60);
            $m = $deductMins % 60;

            return sprintf('%d:%02d', $h, $m).' (hours to deduct)';
        }

        if ($deductMins === null) {
            return $days.' '.($days === 1 ? 'day' : 'days')
                .' — '.$this->start_date->format('M j, Y').' to '.$end->format('M j, Y');
        }

        return $days.' '.($days === 1 ? 'day' : 'days')
            .' — '.$this->start_date->format('M j, Y').' to '.$end->format('M j, Y');
    }

    /**
     * Total offset hours to deduct (matches DTR / balance logic): explicit HH:MM from reason, else days × 8.
     */
    public function getOffsetHoursNeededAttribute(): float
    {
        if ($this->type !== 'offset') {
            return 0.0;
        }
        $mins = $this->parseOffsetHoursToDeductMinutes();
        if ($mins !== null) {
            return $mins / 60.0;
        }

        return (float) ($this->days * 8);
    }
}
