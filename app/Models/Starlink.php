<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Starlink extends Model
{
    protected $fillable = [
        'linked_account_id',
        'account_linked_email',
        'starlink_id',
        'serial_number',
        'kit_number',
        'router_id',
        'ssid',
        'wifi_password',
        'office_location',
        'start_date',
        'po_no',
        'contact_email',
        'plan',
        'status',
        'end_user_email',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function linkedAccount(): BelongsTo
    {
        return $this->belongsTo(LinkedAccount::class);
    }

    /**
     * Compatibility: some Laravel versions call hasAnyGetMutator(); this model has no get mutators.
     */
    public function hasAnyGetMutator($key = null): bool
    {
        if ($key !== null && $key !== '') {
            return $this->hasGetMutator($key);
        }
        return false;
    }
}
