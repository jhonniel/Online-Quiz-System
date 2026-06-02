<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DtrTimeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'hours',
        'request_type',
        'submission_batch',
        'requested_total_hours',
        'remarks',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'requested_total_hours' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function isOvertime(): bool
    {
        return $this->request_type === 'overtime';
    }

    public function isRegular(): bool
    {
        return ($this->request_type ?? 'regular') !== 'overtime';
    }

    protected static function booted(): void
    {
        static::creating(function (DtrTimeRequest $request): void {
            if (! $request->request_type) {
                $request->request_type = 'regular';
            }
        });
    }

    /**
     * Get the user who created this time request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed this time request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function leaveRequest(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LeaveRequest::class);
    }

    /**
     * Hours stored as decimal, displayed as HH:MM (same as student submission).
     */
    public function getFormattedTimeAttribute(): string
    {
        return \App\Support\DtrTimeRequestHours::decimalToTimeString((float) $this->hours);
    }

    public function getRequestTypeLabelAttribute(): string
    {
        $forStudent = auth()->check() && auth()->user()->role === 'student';

        return \App\Support\DtrTimeRequestHours::requestTypeLabel($this->request_type, $forStudent);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
