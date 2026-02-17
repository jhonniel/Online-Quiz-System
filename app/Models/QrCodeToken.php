<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class QrCodeToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'is_used',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or generate a permanent token for a user
     * Returns existing token if available, otherwise creates a new permanent one
     * This ensures the QR code stays static forever (never changes)
     */
    public static function getOrGenerateForUser(User $user): string
    {
        // First, try to find an existing token for this user (permanent, reusable)
        $existingToken = static::where('user_id', $user->id)
            ->orderBy('created_at', 'asc') // Get the first/oldest token
            ->first();

        // If we have an existing token, return it (keeps QR code static forever)
        if ($existingToken) {
            return $existingToken->token;
        }

        // Generate a new secure random token (permanent, never expires)
        $token = Str::random(64);

        // Create permanent token record (no expiration, reusable)
        static::create([
            'user_id' => $user->id,
            'token' => $token,
            'is_used' => false, // Keep as false so it can be reused
            'expires_at' => null, // Never expires
        ]);

        return $token;
    }

    /**
     * Generate a new token for a user (forces new token generation)
     * @deprecated Use getOrGenerateForUser() instead for static QR codes
     */
    public static function generateForUser(User $user): string
    {
        return static::getOrGenerateForUser($user);
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }

    /**
     * Check if token is valid (not expired)
     * Note: Tokens are now permanent and reusable, so is_used is not checked
     */
    public function isValid(): bool
    {
        // Only check expiration, tokens are now permanent and reusable
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
