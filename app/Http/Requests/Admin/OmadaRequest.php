<?php

namespace App\Http\Requests\Admin;

use App\Models\Omada;
use App\Rules\UniqueDeviceIdentifier;
use Illuminate\Foundation\Http\FormRequest;

class OmadaRequest extends FormRequest
{
    /**
     * Identifier fields that must be unique across all Omada devices.
     */
    public const UNIQUE_FIELDS = [
        'serial_number' => 'serial number',
        'mac_address' => 'MAC address',
        'license' => 'license',
    ];

    public function authorize(): bool
    {
        return (bool) $this->user()?->canAccessLinkedAccounts();
    }

    public function rules(): array
    {
        $ignoreId = $this->route('omada') instanceof Omada ? $this->route('omada')->id : null;
        $unique = fn (string $field) => new UniqueDeviceIdentifier('omadas', $field, self::UNIQUE_FIELDS[$field], $ignoreId);

        return [
            'account_linked_email' => 'nullable|email|max:255',
            'site' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'serial_number' => ['required', 'string', 'max:255', $unique('serial_number')],
            'mac_address' => ['nullable', 'string', 'max:255', $unique('mac_address')],
            'license' => ['nullable', 'string', 'max:255', $unique('license')],
            'license_expiration' => 'nullable|date',
            'subscription_plan_type_id' => 'nullable|exists:subscription_plan_types,id',
        ];
    }

    public function messages(): array
    {
        return [
            'serial_number.required' => 'Serial number is required.',
        ];
    }
}
