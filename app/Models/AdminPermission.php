<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminPermission extends Model
{
    /** Sub-areas under Analytics & Reports (sidebar + route gates). */
    public const ANALYTICS_FEATURES = [
        'analytics' => 'Student Performance Analytics',
        'error_logs' => 'Error Logs',
        'user_activity' => 'User Activity',
        'students_review' => 'Students Review',
    ];

    protected $fillable = [
        'user_id',
        'content_management',
        'analytics_reports',
        'allowed_analytics_features',
        'employee_management',
        'allowed_employee_departments',
        'allowed_student_departments',
        'allowed_departments',
        'student_management',
        'hiring_process',
        'allowed_positions',
        'communication',
        'linked_accounts',
        'billing',
        'files',
        'confession',
        'feedback',
        'user_management',
        'system',
        'qr_code',
    ];

    protected $casts = [
        'content_management' => 'boolean',
        'analytics_reports' => 'boolean',
        'allowed_analytics_features' => 'array',
        'employee_management' => 'boolean',
        'allowed_employee_departments' => 'array',
        'allowed_student_departments' => 'array',
        'allowed_departments' => 'array',
        'student_management' => 'boolean',
        'hiring_process' => 'boolean',
        'allowed_positions' => 'array',
        'communication' => 'boolean',
        'linked_accounts' => 'boolean',
        'billing' => 'boolean',
        'files' => 'boolean',
        'confession' => 'boolean',
        'feedback' => 'boolean',
        'user_management' => 'boolean',
        'system' => 'boolean',
        'qr_code' => 'boolean',
    ];

    /**
     * Get the user that owns the permission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
