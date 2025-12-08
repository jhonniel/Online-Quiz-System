<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminPermission extends Model
{
    protected $fillable = [
        'user_id',
        'content_management',
        'analytics_reports',
        'employee_management',
        'student_management',
        'hiring_process',
        'communication',
        'user_management',
        'system',
    ];

    protected $casts = [
        'content_management' => 'boolean',
        'analytics_reports' => 'boolean',
        'employee_management' => 'boolean',
        'student_management' => 'boolean',
        'hiring_process' => 'boolean',
        'communication' => 'boolean',
        'user_management' => 'boolean',
        'system' => 'boolean',
    ];

    /**
     * Get the user that owns the permission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
