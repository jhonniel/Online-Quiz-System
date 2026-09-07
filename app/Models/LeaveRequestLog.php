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
        return match ($this->action) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'for_more_verification' => 'For More Verification',
            'resubmission_requested' => 'Resubmission Requested',
            'additional_time_reverted' => 'Additional time adjusted',
            'leave_time_reverted' => 'Leave time adjusted',
            'travel_time_reverted' => 'Travel time adjusted',
            'filed_by_admin' => 'Filed by administrator',
            'filed_by_teacher' => 'Filed by teacher',
            'requester_resubmitted' => 'Submission updated by requester',
            'updated' => 'Updated',
            'type_changed' => 'Request type changed',
            'dates_changed' => 'Request dates changed',
            'overtime_hours_adjusted' => 'Overtime hours adjusted',
            'admin_officially_excused' => 'Accepted as officially excused',
            'created' => 'Created',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
