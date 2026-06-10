<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\StudentNda;
use App\Models\User;
use Illuminate\Support\Facades\View;

final class EmployeeDocumentTemplate
{
  /** @var array<string, string> */
    public const SETTING_KEYS = [
        'nda' => 'employee_nda_html',
        'contract' => 'employee_contract_html',
        'policy' => 'employee_policy_html',
        'handbook' => 'employee_handbook_acknowledgment_html',
    ];

    /** @var array<string, string> */
    public const DEFAULT_PARTIALS = [
        'nda' => 'user.employee-documents.partials.nda-body-default',
        'contract' => 'user.employee-documents.partials.contract-body-default',
        'policy' => 'user.employee-documents.partials.policy-body-default',
        'handbook' => 'user.employee-documents.partials.handbook-body-default',
    ];

    /** @var array<string, list<string>> */
    public const PLACEHOLDERS = [
        'nda' => [
            '{{full_name}}',
            '{{company_inline}}',
            '{{city}}',
            '{{agreement_date_formal}}',
            '{{agreement_date_upper}}',
            '{{id_number}}',
            '{{valid_id_type}}',
            '{{company_signature}}',
            '{{signatory_name}}',
            '{{signatory_title}}',
            '{{company_line1}}',
            '{{company_line2}}',
            '{{employee_signature}}',
        ],
        'contract' => [
            '{{company_name}}',
            '{{employer_address}}',
            '{{employee_name}}',
            '{{employee_address}}',
            '{{date_hired}}',
            '{{position}}',
            '{{employer_name}}',
            '{{employer_position}}',
            '{{employee_signature}}',
        ],
        'policy' => [
            '{{employee_name}}',
            '{{employee_address}}',
            '{{date_hired}}',
            '{{policies_list}}',
            '{{employer_name}}',
            '{{employer_position}}',
            '{{employee_signature}}',
        ],
        'handbook' => [
            '{{employee_name}}',
            '{{employee_address}}',
            '{{date_hired}}',
            '{{employer_name}}',
            '{{employer_position}}',
            '{{employee_signature}}',
        ],
    ];

    public static function supports(string $type): bool
    {
        return isset(self::SETTING_KEYS[$type]);
    }

    public static function settingKey(string $type): string
    {
        return self::SETTING_KEYS[$type] ?? '';
    }

    /**
     * @return list<string>
     */
    public static function placeholders(string $type): array
    {
        return self::PLACEHOLDERS[$type] ?? [];
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    public static function bodyHtml(string $type, array $viewData): string
    {
        abort_unless(self::supports($type), 404);

        $custom = self::storedTemplateHtml($type);

        if ($custom === '') {
            return View::make(self::DEFAULT_PARTIALS[$type], $viewData)->render();
        }

        $html = self::replacePlaceholders(
            $type,
            EmployeeDocumentTemplateSanitizer::sanitize($custom),
            $viewData
        );

        return self::normalizeCustomBodyHtml($type, $html);
    }

    public static function storedTemplateHtml(string $type): string
    {
        return trim((string) Setting::get(self::settingKey($type), ''));
    }

    public static function hasCustomTemplate(string $type): bool
    {
        return self::storedTemplateHtml($type) !== '';
    }

    public static function defaultTemplateHtmlForEditor(string $type): string
    {
        return View::make(
            self::DEFAULT_PARTIALS[$type],
            self::editorPlaceholderViewData($type)
        )->render();
    }

    public static function editorTemplateHtml(string $type): string
    {
        $stored = self::storedTemplateHtml($type);

        return $stored !== '' ? $stored : self::defaultTemplateHtmlForEditor($type);
    }

    /**
     * @return array<string, mixed>
     */
    public static function editorPlaceholderViewData(string $type): array
    {
        return match ($type) {
            'nda' => [
                'fullNameUpper' => '{{full_name}}',
                'branding' => [
                    'company_inline' => '{{company_inline}}',
                    'company_signature' => '{{company_signature}}',
                    'signatory_name' => '{{signatory_name}}',
                    'signatory_title' => '{{signatory_title}}',
                    'company_line1' => '{{company_line1}}',
                    'company_line2' => '{{company_line2}}',
                ],
                'city' => '{{city}}',
                'agreementDateFormal' => '{{agreement_date_formal}}',
                'agreementDateUpper' => '{{agreement_date_upper}}',
                'idNumberUpper' => '{{id_number}}',
                'validIdTypeUpper' => '{{valid_id_type}}',
                'signaturePlaceholder' => '{{employee_signature}}',
                'eSignatureDataUri' => null,
            ],
            'contract' => [
                'employeeName' => '{{employee_name}}',
                'employeeAddress' => '{{employee_address}}',
                'employeeNameLine' => '{{employee_name}}',
                'employeeAddressLine' => '{{employee_address}}',
                'dateHiredLine' => '{{date_hired}}',
                'position' => '{{position}}',
                'dateHired' => '{{date_hired}}',
                'companyName' => '{{company_name}}',
                'employerAddress' => '{{employer_address}}',
                'employerName' => '{{employer_name}}',
                'employerPosition' => '{{employer_position}}',
                'footerLogoDataUri' => '',
                'signaturePlaceholder' => '{{employee_signature}}',
                'eSignatureDataUri' => null,
                'signedAt' => null,
                'digitalSignatureEnabled' => false,
            ],
            'policy' => [
                'employeeName' => '{{employee_name}}',
                'employeeAddress' => '{{employee_address}}',
                'dateHired' => '{{date_hired}}',
                'policies' => EmployeePolicyDocument::POLICIES,
                'policiesHtml' => '{{policies_list}}',
                'employerName' => '{{employer_name}}',
                'employerPosition' => '{{employer_position}}',
                'signaturePlaceholder' => '{{employee_signature}}',
                'eSignatureDataUri' => null,
            ],
            'handbook' => [
                'employeeName' => '{{employee_name}}',
                'employeeAddress' => '{{employee_address}}',
                'dateHired' => '{{date_hired}}',
                'employerName' => '{{employer_name}}',
                'employerPosition' => '{{employer_position}}',
                'signaturePlaceholder' => '{{employee_signature}}',
                'eSignatureDataUri' => null,
            ],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function editorPreviewViewData(string $type): array
    {
        return match ($type) {
            'nda' => self::ndaPreviewViewData(),
            'contract' => self::contractPreviewViewData(),
            'policy' => self::policyPreviewViewData(),
            'handbook' => self::handbookPreviewViewData(),
            default => [],
        };
    }

    public static function previewHtmlFromTemplate(string $type, string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return self::bodyHtml($type, self::editorPreviewViewData($type));
        }

        $html = self::replacePlaceholders(
            $type,
            EmployeeDocumentTemplateSanitizer::sanitize($html),
            self::editorPreviewViewData($type)
        );

        return self::normalizeCustomBodyHtml($type, $html);
    }

    /**
     * @return array<string, string>
     */
    public static function previewPlaceholderMap(string $type): array
    {
        $preview = self::editorPreviewViewData($type);

        return match ($type) {
            'nda' => [
                '{{full_name}}' => (string) ($preview['fullNameUpper'] ?? ''),
                '{{company_inline}}' => strtoupper((string) ($preview['branding']['company_inline'] ?? '')),
                '{{city}}' => (string) ($preview['city'] ?? ''),
                '{{agreement_date_formal}}' => (string) ($preview['agreementDateFormal'] ?? ''),
                '{{agreement_date_upper}}' => (string) ($preview['agreementDateUpper'] ?? ''),
                '{{id_number}}' => (string) ($preview['idNumberUpper'] ?? ''),
                '{{valid_id_type}}' => (string) ($preview['validIdTypeUpper'] ?? ''),
                '{{company_signature}}' => strtoupper((string) ($preview['branding']['company_signature'] ?? '')),
                '{{signatory_name}}' => (string) ($preview['branding']['signatory_name'] ?? ''),
                '{{signatory_title}}' => (string) ($preview['branding']['signatory_title'] ?? ''),
                '{{company_line1}}' => (string) ($preview['branding']['company_line1'] ?? ''),
                '{{company_line2}}' => (string) ($preview['branding']['company_line2'] ?? ''),
                '{{employee_signature}}' => '',
            ],
            'contract' => [
                '{{company_name}}' => (string) ($preview['companyName'] ?? ''),
                '{{employer_address}}' => (string) ($preview['employerAddress'] ?? ''),
                '{{employee_name}}' => (string) ($preview['employeeNameLine'] ?? ''),
                '{{employee_address}}' => (string) ($preview['employeeAddressLine'] ?? ''),
                '{{date_hired}}' => (string) ($preview['dateHiredLine'] ?? ''),
                '{{position}}' => (string) ($preview['position'] ?? ''),
                '{{employer_name}}' => (string) ($preview['employerName'] ?? ''),
                '{{employer_position}}' => (string) ($preview['employerPosition'] ?? ''),
                '{{employee_signature}}' => '',
            ],
            'policy' => [
                '{{employee_name}}' => (string) ($preview['employeeName'] ?? ''),
                '{{employee_address}}' => (string) ($preview['employeeAddress'] ?? ''),
                '{{date_hired}}' => (string) ($preview['dateHired'] ?? ''),
                '{{policies_list}}' => self::renderPoliciesListHtml(EmployeePolicyDocument::POLICIES),
                '{{employer_name}}' => (string) ($preview['employerName'] ?? ''),
                '{{employer_position}}' => (string) ($preview['employerPosition'] ?? ''),
                '{{employee_signature}}' => '',
            ],
            'handbook' => [
                '{{employee_name}}' => (string) ($preview['employeeName'] ?? ''),
                '{{employee_address}}' => (string) ($preview['employeeAddress'] ?? ''),
                '{{date_hired}}' => (string) ($preview['dateHired'] ?? ''),
                '{{employer_name}}' => (string) ($preview['employerName'] ?? ''),
                '{{employer_position}}' => (string) ($preview['employerPosition'] ?? ''),
                '{{employee_signature}}' => '',
            ],
            default => [],
        };
    }

    public static function saveTemplateHtml(string $type, string $html): void
    {
        abort_unless(self::supports($type), 404);

        $labels = [
            'nda' => 'Employee NDA HTML template',
            'contract' => 'Employee Agreement HTML template',
            'policy' => 'Employee Policy acknowledgment HTML template',
            'handbook' => 'Employee Handbook acknowledgment HTML template',
        ];

        Setting::set(self::settingKey($type), trim($html), 'text', $labels[$type]);
        Setting::clearCache();
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    public static function replacePlaceholders(string $type, string $html, array $viewData): string
    {
        $signature = self::signatureHtml($type, $viewData);

        $replacements = match ($type) {
            'nda' => [
                '{{full_name}}' => e((string) ($viewData['fullNameUpper'] ?? '')),
                '{{company_inline}}' => e(strtoupper((string) ($viewData['branding']['company_inline'] ?? ''))),
                '{{city}}' => e((string) ($viewData['city'] ?? '')),
                '{{agreement_date_formal}}' => e((string) ($viewData['agreementDateFormal'] ?? '')),
                '{{agreement_date_upper}}' => e((string) ($viewData['agreementDateUpper'] ?? '')),
                '{{id_number}}' => e((string) ($viewData['idNumberUpper'] ?? '')),
                '{{valid_id_type}}' => e((string) ($viewData['validIdTypeUpper'] ?? '')),
                '{{company_signature}}' => e(strtoupper((string) ($viewData['branding']['company_signature'] ?? ''))),
                '{{signatory_name}}' => e((string) ($viewData['branding']['signatory_name'] ?? '')),
                '{{signatory_title}}' => e((string) ($viewData['branding']['signatory_title'] ?? '')),
                '{{company_line1}}' => e((string) ($viewData['branding']['company_line1'] ?? '')),
                '{{company_line2}}' => e((string) ($viewData['branding']['company_line2'] ?? '')),
                '{{employee_signature}}' => $signature,
            ],
            'contract' => [
                '{{company_name}}' => e((string) ($viewData['companyName'] ?? '')),
                '{{employer_address}}' => e((string) ($viewData['employerAddress'] ?? '')),
                '{{employee_name}}' => e((string) ($viewData['employeeNameLine'] ?? $viewData['employeeName'] ?? '')),
                '{{employee_address}}' => e((string) ($viewData['employeeAddressLine'] ?? $viewData['employeeAddress'] ?? '')),
                '{{date_hired}}' => e((string) ($viewData['dateHiredLine'] ?? $viewData['dateHired'] ?? '')),
                '{{position}}' => e((string) ($viewData['position'] ?? '')),
                '{{employer_name}}' => e((string) ($viewData['employerName'] ?? '')),
                '{{employer_position}}' => e((string) ($viewData['employerPosition'] ?? '')),
                '{{employee_signature}}' => $signature,
            ],
            'policy' => [
                '{{employee_name}}' => e((string) ($viewData['employeeName'] ?? '')),
                '{{employee_address}}' => e((string) ($viewData['employeeAddress'] ?? '')),
                '{{date_hired}}' => e((string) ($viewData['dateHired'] ?? '')),
                '{{policies_list}}' => self::renderPoliciesListHtml($viewData['policies'] ?? EmployeePolicyDocument::POLICIES),
                '{{employer_name}}' => e((string) ($viewData['employerName'] ?? '')),
                '{{employer_position}}' => e((string) ($viewData['employerPosition'] ?? '')),
                '{{employee_signature}}' => $signature,
            ],
            'handbook' => [
                '{{employee_name}}' => e((string) ($viewData['employeeName'] ?? '')),
                '{{employee_address}}' => e((string) ($viewData['employeeAddress'] ?? '')),
                '{{date_hired}}' => e((string) ($viewData['dateHired'] ?? '')),
                '{{employer_name}}' => e((string) ($viewData['employerName'] ?? '')),
                '{{employer_position}}' => e((string) ($viewData['employerPosition'] ?? '')),
                '{{employee_signature}}' => $signature,
            ],
            default => [],
        };

        return self::applyPlaceholderReplacements($html, $replacements);
    }

    public static function normalizeCustomBodyHtml(string $type, string $html): string
    {
        $html = DocumentLetterhead::repairBlockInHtml($html);

        if (EmployeeDocumentFooter::supports($type)) {
            $html = EmployeeDocumentFooter::injectIntoBodyHtml($type, $html);
        }

        return $html;
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private static function applyPlaceholderReplacements(string $html, array $replacements): string
    {
        foreach ($replacements as $placeholder => $value) {
            $html = str_ireplace($placeholder, $value, $html);
        }

        return $html;
    }

    /**
     * @param  list<string>  $policies
     */
    public static function renderPoliciesListHtml(array $policies): string
    {
        $items = array_map(
            fn (string $policy) => '<li><span class="policy-check" aria-hidden="true">&#9745;</span> '.e($policy).'</li>',
            $policies
        );

        return '<ul class="policy-list">'.implode('', $items).'</ul>';
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    private static function signatureHtml(string $type, array $viewData): string
    {
        if (! empty($viewData['eSignatureDataUri'])) {
            $class = $type === 'nda' ? 'signature-image' : 'policy-signature-image';

            return '<img src="'.e((string) $viewData['eSignatureDataUri']).'" alt="E-Signature" class="'.$class.'">';
        }

        return '';
    }

  /** @return array<string, mixed> */
    private static function ndaPreviewViewData(): array
    {
        $agreementDate = now();
        $branding = StudentNdaDocument::branding();

        return [
            'fullNameUpper' => 'JUAN DELA CRUZ',
            'branding' => $branding,
            'city' => trim((string) Setting::get('nda_default_city', 'Davao')) ?: 'Davao',
            'agreementDateFormal' => StudentNda::formatFormalDate($agreementDate),
            'agreementDateUpper' => strtoupper($agreementDate->format('F d, Y')),
            'idNumberUpper' => '123-456-789-000',
            'validIdTypeUpper' => 'TIN',
            'eSignatureDataUri' => null,
        ];
    }

  /** @return array<string, mixed> */
    private static function contractPreviewViewData(): array
    {
        return [
            'employeeName' => 'Juan Dela Cruz',
            'employeeAddress' => '',
            'employeeNameLine' => 'Juan Dela Cruz',
            'employeeAddressLine' => '',
            'dateHiredLine' => 'January 15, 2024',
            'position' => 'Software Developer',
            'dateHired' => 'January 15, 2024',
            'companyName' => trim((string) Setting::get('nda_company_name', Setting::get('system_name', 'Mini Clean Business Solutions'))) ?: 'Mini Clean Business Solutions',
            'employerAddress' => trim((string) Setting::get('contact_address', 'MS Land Complex Building 2, KM 3 McArthur Highway, Matina Crossing, Talomo District, Davao City')),
            'employerName' => self::employerName(),
            'employerPosition' => self::employerPosition(),
            'footerLogoDataUri' => EmployeeContractDocument::footerLogoDataUri(),
            'eSignatureDataUri' => null,
            'signedAt' => null,
            'digitalSignatureEnabled' => false,
        ];
    }

  /** @return array<string, mixed> */
    private static function policyPreviewViewData(): array
    {
        return [
            'employeeName' => 'Juan Dela Cruz',
            'employeeAddress' => '',
            'dateHired' => 'January 15, 2024',
            'policies' => EmployeePolicyDocument::POLICIES,
            'employerName' => self::employerName(),
            'employerPosition' => self::employerPosition(),
            'eSignatureDataUri' => null,
        ];
    }

  /** @return array<string, mixed> */
    private static function handbookPreviewViewData(): array
    {
        return [
            'employeeName' => 'Juan Dela Cruz',
            'employeeAddress' => '',
            'dateHired' => 'January 15, 2024',
            'employerName' => self::employerName(),
            'employerPosition' => self::employerPosition(),
            'eSignatureDataUri' => null,
        ];
    }

    private static function employerName(): string
    {
        return PayslipSignatorySettings::documentEmployerName();
    }

    private static function employerPosition(): string
    {
        return PayslipSignatorySettings::documentEmployerPosition();
    }
}
