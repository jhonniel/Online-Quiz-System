<?php

namespace App\Models;

use App\Support\EmployeeDocumentRequestTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFileRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_FULFILLED = 'fulfilled';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'employee_file_template_id',
        'user_id',
        'generated_by',
        'title',
        'status',
        'request_type',
        'employee_notes',
        'admin_notes',
        'fulfilled_at',
        'original_filename',
        'mime_type',
        'field_values',
        'rendered_html',
        'pdf_path',
        'storage_disk',
    ];

    protected $casts = [
        'field_values' => 'array',
        'fulfilled_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmployeeFileTemplate::class, 'employee_file_template_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFulfilled(): bool
    {
        return $this->status === self::STATUS_FULFILLED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isEmployeeInitiated(): bool
    {
        return $this->request_type !== null && $this->request_type !== '';
    }

    public function requestTypeLabel(): string
    {
        return EmployeeDocumentRequestTypes::label($this->request_type);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_FULFILLED => 'Ready',
            self::STATUS_REJECTED => 'Declined',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800',
            self::STATUS_FULFILLED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForEmployee(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
