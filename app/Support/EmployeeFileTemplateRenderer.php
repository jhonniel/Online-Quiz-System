<?php

namespace App\Support;

use App\Models\EmployeeFileTemplate;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

final class EmployeeFileTemplateRenderer
{
    /**
     * @param  array<string, mixed>  $fieldValues
     */
    public function render(EmployeeFileTemplate $template, User $employee, array $fieldValues = []): string
    {
        $replacements = $this->buildReplacements($template, $employee, $fieldValues);
        $body = (string) $template->body;

        return str_replace(array_keys($replacements), array_values($replacements), $body);
    }

    /**
     * @return list<string>
     */
    public function availablePlaceholders(?EmployeeFileTemplate $template = null): array
    {
        $placeholders = [
            '{{employee_name}}',
            '{{employee_name_upper}}',
            '{{employee_email}}',
            '{{employee_department}}',
            '{{employee_role}}',
            '{{date_today}}',
            '{{date_today_formal}}',
            '{{system_name}}',
            '{{system_logo_img}}',
            '{{contact_phone}}',
            '{{contact_address}}',
        ];

        if ($template?->isCertificateOfEmployment()) {
            $placeholders[] = '{{coe_letterhead_img}}';
            $placeholders[] = '{{coe_full_document_img}}';
        }

        return $placeholders;
    }

    /**
     * @param  array<string, mixed>  $fieldValues
     * @return array<string, string>
     */
    public function buildReplacements(EmployeeFileTemplate $template, User $employee, array $fieldValues = []): array
    {
        $employee->loadMissing(['department:id,name']);

        $systemName = (string) Setting::get('system_name', config('app.name', 'System'));
        $contactPhone = (string) Setting::get('contact_phone', '');
        $contactAddress = (string) Setting::get('contact_address', '');
        $today = Carbon::now();
        $isCoe = $template->isCertificateOfEmployment();

        $replacements = [
            '{{employee_name}}' => (string) ($employee->name ?? ''),
            '{{employee_name_upper}}' => strtoupper((string) ($employee->name ?? '')),
            '{{employee_email}}' => (string) ($employee->email ?? ''),
            '{{employee_department}}' => (string) ($employee->department?->name ?? 'N/A'),
            '{{employee_role}}' => ucfirst(str_replace('_', ' ', (string) ($employee->role ?? 'employee'))),
            '{{date_today}}' => $today->format('F d, Y'),
            '{{date_today_formal}}' => $today->format('jS') . ' day of ' . $today->format('F Y'),
            '{{system_name}}' => $systemName,
            '{{system_logo_img}}' => $this->systemLogoImgTag(),
            '{{coe_letterhead_img}}' => $isCoe ? $this->coeLetterheadImgTag() : '',
            '{{coe_full_document_img}}' => $isCoe ? $this->coeFullDocumentImgTag() : '',
            '{{contact_phone}}' => $contactPhone,
            '{{contact_address}}' => $contactAddress,
        ];

        foreach ($template->customFieldDefinitions() as $field) {
            $key = (string) $field['key'];
            $placeholder = '{{' . $key . '}}';
            $value = $this->resolveCustomFieldValue(
                $template,
                $field,
                $fieldValues[$key] ?? null,
                $systemName,
                $contactPhone,
                $contactAddress
            );

            if ($key === 'signatory_signature_img') {
                $replacements[$placeholder] = $this->signatureImgTag($value, $isCoe);
                continue;
            }

            if (in_array($key, ['company_name', 'job_position', 'employment_start'], true) && $value !== '') {
                $value = strtoupper((string) $value);
            }

            if ($key === 'employment_end' && $value !== '') {
                $value = strtolower((string) $value) === 'present'
                    ? 'present'
                    : strtoupper((string) $value);
            }

            $replacements[$placeholder] = (string) $value;
        }

        return $replacements;
    }

    /**
     * @param  array{key: string, label: string, default?: string}  $field
     */
    public function resolveCustomFieldValue(
        EmployeeFileTemplate $template,
        array $field,
        mixed $submitted,
        ?string $systemName = null,
        ?string $contactPhone = null,
        ?string $contactAddress = null
    ): string {
        $systemName ??= (string) Setting::get('system_name', config('app.name', 'System'));
        $contactPhone ??= (string) Setting::get('contact_phone', '');
        $contactAddress ??= (string) Setting::get('contact_address', '');

        $submitted = trim((string) ($submitted ?? ''));
        if ($submitted !== '') {
            return $submitted;
        }

        $fieldDefault = trim((string) ($field['default'] ?? ''));
        if ($fieldDefault !== '') {
            return $fieldDefault;
        }

        $fallbacks = $template->isCertificateOfEmployment()
            ? $this->certificateOfEmploymentDefaults($contactPhone, $contactAddress)
            : $this->genericDefaults($systemName, $contactPhone, $contactAddress);

        return (string) ($fallbacks[(string) $field['key']] ?? '');
    }

    /**
     * @return array<string, string>
     */
    private function certificateOfEmploymentDefaults(string $contactPhone, string $contactAddress): array
    {
        return [
            'company_name' => 'MINI CLEAN BUSINESS SOLUTIONS',
            'office_contact' => $contactPhone !== '' ? $contactPhone : '09954450819',
            'mailing_address' => $contactAddress !== '' ? $contactAddress : 'Dr 8 Unit 2 MS Land Complex, Mc Arthur Highway, Matina Crossing, Talomo Dist. Davao City',
            'issue_city' => 'Davao City',
            'employment_end' => 'present',
            'signatory_name' => 'Nitish G. Khemani',
            'signatory_title' => 'Founder/CEO',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function genericDefaults(string $systemName, string $contactPhone, string $contactAddress): array
    {
        return [
            'company_name' => strtoupper($systemName),
            'office_contact' => $contactPhone,
            'mailing_address' => $contactAddress,
            'issue_city' => 'Davao City',
            'employment_end' => 'present',
            'signatory_title' => 'Authorized Signatory',
        ];
    }

    private function coeLetterheadImgTag(): string
    {
        return $this->imageTagFromPublicPath(
            'images/employee-file-templates/coe-letterhead.png',
            'Certificate of Employment letterhead',
            'width:100%;max-width:860px;height:auto;display:block;margin:0 auto 8px;'
        );
    }

    private function coeFullDocumentImgTag(): string
    {
        return $this->imageDataUriFromPublicPath('images/employee-file-templates/coe-mc-monde-with-logo.png');
    }

    private function imageDataUriFromPublicPath(string $relativePath): string
    {
        $path = public_path($relativePath);
        if (!is_file($path)) {
            return '';
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
    }

    private function systemLogoImgTag(): string
    {
        $path = Setting::get('system_logo');
        if (!$path) {
            return '';
        }

        return $this->imageTagFromStoragePath((string) $path, 'Company Logo', 'height:72px;width:auto;');
    }

    private function signatureImgTag(string $value, bool $isCertificateOfEmployment): string
    {
        $value = trim($value);

        if ($value === '') {
            if ($isCertificateOfEmployment) {
                return $this->imageTagFromPublicPath(
                    'images/employee-file-templates/coe-default-signature.png',
                    'Signature',
                    'height:48px;width:auto;display:block;margin:0 auto 4px;'
                );
            }

            return '<div style="height:52px;line-height:52px;">&nbsp;</div>';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:')) {
            return '<img src="' . e($value) . '" alt="Signature" style="height:52px;width:auto;display:block;margin:0 auto;">';
        }

        $storageTag = $this->imageTagFromStoragePath($value, 'Signature', 'height:52px;width:auto;');
        if ($storageTag !== '') {
            return $storageTag;
        }

        if ($isCertificateOfEmployment) {
            return $this->imageTagFromPublicPath(
                'images/employee-file-templates/coe-default-signature.png',
                'Signature',
                'height:48px;width:auto;display:block;margin:0 auto 4px;'
            );
        }

        return '<div style="height:52px;line-height:52px;">&nbsp;</div>';
    }

    private function imageTagFromPublicPath(string $relativePath, string $alt, string $style): string
    {
        $path = public_path($relativePath);
        if (!is_file($path)) {
            return '';
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        $encoded = base64_encode((string) file_get_contents($path));

        return '<img src="data:' . $mime . ';base64,' . $encoded . '" alt="' . e($alt) . '" style="' . e($style) . '">';
    }

    private function imageTagFromStoragePath(string $path, string $alt, string $style): string
    {
        try {
            $disk = Storage::disk('digitalocean');
            if (!$disk->exists($path)) {
                return '';
            }

            $contents = $disk->get($path);
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($extension === 'svg') {
                return '';
            }

            $mime = match ($extension) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'image/jpeg',
            };

            $encoded = base64_encode($contents);

            return '<img src="data:' . $mime . ';base64,' . $encoded . '" alt="' . e($alt) . '" style="' . e($style) . ' display:block;margin:0 auto;">';
        } catch (\Throwable) {
            return '';
        }
    }
}
