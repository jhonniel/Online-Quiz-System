<?php

namespace App\Http\Requests\Admin;

use App\Models\Starlink;
use App\Rules\UniqueDeviceIdentifier;
use Illuminate\Foundation\Http\FormRequest;

class StarlinkRequest extends FormRequest
{
    /**
     * Identifier fields that must be unique across all Starlink devices.
     */
    public const UNIQUE_FIELDS = [
        'starlink_id' => 'Starlink ID',
        'serial_number' => 'serial number',
        'kit_number' => 'kit number',
        'router_id' => 'router ID',
    ];

    public function authorize(): bool
    {
        return (bool) $this->user()?->canAccessLinkedAccounts();
    }

    public function rules(): array
    {
        $ignoreId = $this->route('starlink') instanceof Starlink ? $this->route('starlink')->id : null;
        $unique = fn (string $field) => new UniqueDeviceIdentifier('starlinks', $field, self::UNIQUE_FIELDS[$field], $ignoreId);

        return [
            'linked_account_id' => 'nullable|exists:linked_accounts,id',
            'account_linked_email' => 'nullable|string|max:255',
            'starlink_id' => ['nullable', 'string', 'max:255', $unique('starlink_id')],
            'serial_number' => ['required', 'string', 'max:255', $unique('serial_number')],
            'kit_number' => ['required', 'string', 'max:255', $unique('kit_number')],
            'router_id' => ['nullable', 'string', 'max:255', $unique('router_id')],
            'ssid' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'office_location' => 'nullable|string|max:255',
            'municipality_select' => 'nullable|string|max:255',
            'municipality_custom' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'advance_payment_until' => 'nullable|date',
            'last_paid_date' => 'nullable|date',
            'billing_interval' => 'nullable|string|in:monthly,yearly',
            'po_no' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'plan' => 'nullable|string|max:255',
            'subscription_plan_type_id' => 'nullable|exists:subscription_plan_types,id',
            'status' => 'nullable|string|max:50',
            'replaced_at' => 'nullable|date',
            'replacement_note' => 'nullable|string|max:500',
            'linked_starlink_ids' => 'nullable|array',
            'linked_starlink_ids.*' => 'integer|exists:starlinks,id',
            'end_user_email' => 'nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'serial_number.required' => 'Serial number is required.',
            'kit_number.required' => 'Kit number is required.',
        ];
    }
}
