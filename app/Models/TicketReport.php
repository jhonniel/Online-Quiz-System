<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketReport extends Model
{
    protected $fillable = [
        'ticket_number',
        'type',
        'description',
        'full_name',
        'contact_number',
        'email',
        'office',
        'address',
        'image_path',
        'status',
        'assigned_to_user_id',
        'admin_notes',
        'payment_status',
        'amount_paid',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_NEEDS_INVESTIGATION = 'needs_investigation';
    public const STATUS_RESOLVED = 'resolved';

    // Legacy value kept for backward compatibility
    public const STATUS_CLOSED = 'closed';

    public const PAYMENT_STATUS_PENDING = 'pending_for_payment';
    public const PAYMENT_STATUS_PAID = 'paid';

    public static function adminStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_PROCESSING,
            self::STATUS_NEEDS_INVESTIGATION,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
        ];
    }

    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING,
            self::PAYMENT_STATUS_PAID,
        ];
    }

    public static function generateTicketNumber(): string
    {
        $prefix = 'TR-' . now()->format('Ymd');
        $max = static::where('ticket_number', 'like', $prefix . '-%')->count();
        return $prefix . '-' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_PROCESSING, self::STATUS_NEEDS_INVESTIGATION], true);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_CLOSED], true);
    }

    public function notes()
    {
        return $this->hasMany(TicketReportNote::class, 'ticket_report_id')->orderBy('created_at');
    }

    public function logs()
    {
        return $this->hasMany(TicketReportLog::class, 'ticket_report_id')->orderByDesc('created_at');
    }

    public function latestLog()
    {
        return $this->hasOne(TicketReportLog::class, 'ticket_report_id')->latestOfMany();
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }
        if (Storage::disk('digitalocean')->exists($this->image_path)) {
            return Storage::disk('digitalocean')->url($this->image_path);
        }
        if (Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }
        return null;
    }

    public static function problemTypes(): array
    {
        return \App\Models\TicketProblemType::ordered()
            ->pluck('label', 'slug')
            ->all();
    }

    public function getTypeLabelAttribute(): string
    {
        $type = \App\Models\TicketProblemType::where('slug', $this->type)->first();
        return $type ? $type->label : $this->type;
    }
}
