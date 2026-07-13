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
        'anonymous_chats' => 'Anonymous Chats',
        'students_review' => 'Students Review',
        'employee_records' => 'Employee Records',
    ];

    protected $fillable = [
        'user_id',
        'content_management',
        'allowed_content_features',
        'analytics_reports',
        'allowed_analytics_features',
        'employee_management',
        'allowed_employee_departments',
        'allowed_employee_features',
        'allowed_student_departments',
        'allowed_departments',
        'student_management',
        'allowed_student_features',
        'hiring_process',
        'allowed_positions',
        'allowed_hiring_features',
        'communication',
        'allowed_communication_features',
        'linked_accounts',
        'billing',
        'allowed_subscription_features',
        'files',
        'confession',
        'tasks',
        'allowed_task_features',
        'feedback',
        'user_management',
        'allowed_user_management_features',
        'system',
        'allowed_system_features',
        'qr_code',
    ];

    protected $casts = [
        'content_management' => 'boolean',
        'allowed_content_features' => 'array',
        'analytics_reports' => 'boolean',
        'allowed_analytics_features' => 'array',
        'employee_management' => 'boolean',
        'allowed_employee_departments' => 'array',
        'allowed_employee_features' => 'array',
        'allowed_student_departments' => 'array',
        'allowed_departments' => 'array',
        'student_management' => 'boolean',
        'allowed_student_features' => 'array',
        'hiring_process' => 'boolean',
        'allowed_positions' => 'array',
        'allowed_hiring_features' => 'array',
        'communication' => 'boolean',
        'allowed_communication_features' => 'array',
        'linked_accounts' => 'boolean',
        'billing' => 'boolean',
        'allowed_subscription_features' => 'array',
        'files' => 'boolean',
        'confession' => 'boolean',
        'tasks' => 'boolean',
        'allowed_task_features' => 'array',
        'feedback' => 'boolean',
        'user_management' => 'boolean',
        'allowed_user_management_features' => 'array',
        'system' => 'boolean',
        'allowed_system_features' => 'array',
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
