<?php

namespace App\Http\Requests\Admin;

use App\Models\OtherSubscription;
use App\Models\OtherSubscriptionProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class OtherSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canAccessSubscriptionFeature('others');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'other_subscription_provider_id' => [
                'nullable',
                'integer',
                Rule::exists('other_subscription_providers', 'id'),
            ],
            'new_provider_name' => ['nullable', 'string', 'max:255'],
            'billing_type' => ['required', Rule::in(array_keys(OtherSubscription::BILLING_TYPES))],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'currency' => ['nullable', 'string', 'max:10'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'auto_renew' => ['nullable', 'boolean'],
            'notify_days_before' => ['required', 'integer', 'min:0', 'max:90'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $providerId = $this->input('other_subscription_provider_id');
            $newName = trim((string) $this->input('new_provider_name', ''));

            if (empty($providerId) && $newName === '') {
                $validator->errors()->add(
                    'other_subscription_provider_id',
                    'Select a provider or add a new one.'
                );
            }

            if ($newName !== '') {
                $exists = OtherSubscriptionProvider::query()
                    ->whereRaw('LOWER(name) = ?', [strtolower($newName)])
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'new_provider_name',
                        'That provider already exists. Pick it from the list.'
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $providerId = $this->input('other_subscription_provider_id');

        $this->merge([
            'auto_renew' => $this->boolean('auto_renew'),
            'currency' => strtoupper(trim((string) ($this->input('currency') ?: 'PHP'))),
            'other_subscription_provider_id' => ($providerId === '' || $providerId === null || $providerId === 'new')
                ? null
                : $providerId,
            'new_provider_name' => trim((string) $this->input('new_provider_name', '')) ?: null,
            'notes' => trim((string) $this->input('notes', '')) ?: null,
        ]);
    }
}
