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
        'admin_notes',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public static function generateTicketNumber(): string
    {
        $prefix = 'TR-' . now()->format('Ymd');
        $max = static::where('ticket_number', 'like', $prefix . '-%')->count();
        return $prefix . '-' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
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
