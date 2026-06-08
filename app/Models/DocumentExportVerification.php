<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DocumentExportVerification extends Model
{
    protected $fillable = [
        'token',
        'reference_code',
        'document_type',
        'generated_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public static function createForExport(string $documentType, ?User $generatedBy, array $metadata = []): self
    {
        $token = Str::random(64);

        return self::create([
            'token' => $token,
            'reference_code' => self::makeReferenceCode(),
            'document_type' => $documentType,
            'generated_by' => $generatedBy?->id,
            'metadata' => $metadata,
        ]);
    }

    public function verificationUrl(): string
    {
        return route('document-export.verify', ['token' => $this->token]);
    }

    public function documentTypeLabel(): string
    {
        return match ($this->document_type) {
            'employee_leave_requests_approved' => 'Approved Employee Leave Requests',
            default => ucwords(str_replace('_', ' ', $this->document_type)),
        };
    }

    private static function makeReferenceCode(): string
    {
        do {
            $code = 'MCBS-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (self::where('reference_code', $code)->exists());

        return $code;
    }
}
