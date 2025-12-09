<?php

namespace App\Models;

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
        'supporting_document_path',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

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
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'vacation_leave' => 'Vacation Leave',
            'sick_leave' => 'Sick Leave',
            'work_from_home' => 'Work From Home',
            'absent' => 'Absent',
            'overtime' => 'Overtime',
            'offset' => 'Offset',
            'additional_time' => 'Additional Time',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
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
     * Get the number of days.
     */
    public function getDaysAttribute(): int
    {
        if (!$this->end_date) {
            return 1;
        }

        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
