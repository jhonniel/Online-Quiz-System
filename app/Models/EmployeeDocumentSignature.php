<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocumentSignature extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'signed_at',
        'signed_document_path',
        'storage_disk',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null && ! empty($this->signed_document_path);
    }
}
