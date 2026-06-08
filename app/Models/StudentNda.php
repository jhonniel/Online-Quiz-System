<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'agreement_date' => 'date',
        'signed_uploaded_at' => 'datetime',
        'reupload_allowed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasSignedUpload(): bool
    {
        return ! empty($this->signed_document_path);
    }

    public function canUploadSignedDocument(): bool
    {
        return ! $this->hasSignedUpload() || $this->reupload_allowed;
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
