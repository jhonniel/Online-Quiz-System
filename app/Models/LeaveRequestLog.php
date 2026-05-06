<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'action',
        'status_before',
        'status_after',
        'notes',
        'performed_by',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Get the leave request this log belongs to.
     */
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Get a human-readable action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'for_more_verification' => 'For More Verification',
            'resubmission_requested' => 'Resubmission Requested',
            'updated' => 'Updated',
            'created' => 'Created',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
