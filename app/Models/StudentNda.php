<?php

namespace App\Models;

use App\Support\StudentNdaStorage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentNda extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'id_number',
        'valid_id_type',
        'city',
        'agreement_date',
        'signed_document_path',
        'storage_disk',
        'signed_uploaded_at',
        'reupload_allowed',
        'approval_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'agreement_date' => 'date',
        'signed_uploaded_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'reupload_allowed' => 'boolean',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function hasSignedUpload(): bool
    {
        return ! empty($this->signed_document_path);
    }

    public function hasViewableSignedUpload(): bool
    {
        return $this->hasSignedUpload() && ! $this->isRejected();
    }

    public function isApprovedForAttendance(): bool
    {
        return $this->hasSignedUpload() && $this->approval_status === self::STATUS_APPROVED;
    }

    public function isPendingApproval(): bool
    {
        if (! $this->hasSignedUpload()) {
            return false;
        }

        return $this->approval_status === self::STATUS_PENDING || $this->approval_status === null;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === self::STATUS_REJECTED;
    }

    public function removeSignedDocument(): void
    {
        if ($this->signed_document_path) {
            StudentNdaStorage::delete(
                (string) $this->signed_document_path,
                (string) ($this->storage_disk ?? '')
            );
        }

        if ($this->hasSignedUpload() || $this->signed_uploaded_at !== null || $this->storage_disk !== null) {
            $this->forceFill([
                'signed_document_path' => null,
                'storage_disk' => null,
                'signed_uploaded_at' => null,
            ])->save();
        }
    }

    public function purgeRejectedSignedDocument(): void
    {
        if (! $this->isRejected() || ! $this->hasSignedUpload()) {
            return;
        }

        $this->removeSignedDocument();
    }

    public function approvalStatusLabel(): string
    {
        return match ($this->approval_status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PENDING => 'Pending Review',
            default => $this->hasSignedUpload() ? 'Pending Review' : 'Not Uploaded',
        };
    }

    public function approvalStatusBadgeClass(): string
    {
        return match ($this->approval_status) {
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::STATUS_PENDING => 'bg-amber-100 text-amber-800',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function canUploadSignedDocument(): bool
    {
        if ($this->isApprovedForAttendance()) {
            return false;
        }

        return ! $this->hasSignedUpload() || $this->reupload_allowed;
    }

    public function canEditNdaDetails(): bool
    {
        return ! $this->isApprovedForAttendance();
    }

    public function agreementDateFormal(): string
    {
        $date = $this->agreement_date instanceof Carbon
            ? $this->agreement_date->copy()
            : Carbon::parse($this->agreement_date);

        return self::formatFormalDate($date);
    }

    public function agreementDateUpper(): string
    {
        $date = $this->agreement_date instanceof Carbon
            ? $this->agreement_date->copy()
            : Carbon::parse($this->agreement_date);

        return strtoupper($date->format('F d, Y'));
    }

    public static function formatFormalDate(Carbon $date): string
    {
        $day = (int) $date->format('j');
        $suffix = match ($day % 10) {
            1 => $day % 100 === 11 ? 'th' : 'st',
            2 => $day % 100 === 12 ? 'th' : 'nd',
            3 => $day % 100 === 13 ? 'th' : 'rd',
            default => 'th',
        };

        return $day.$suffix.' day of '.$date->format('F Y');
    }
}
