<?php

namespace App\Support;

use App\Models\Setting;

final class EmployeeDocumentFooter
{
    /** @var array<string, string> */
    public const SETTING_KEYS = [
        'nda' => 'employee_nda_footer_html',
        'contract' => 'employee_contract_footer_html',
        'policy' => 'employee_policy_footer_html',
        'handbook' => 'employee_handbook_footer_html',
    ];

    /** @var list<string> */
    public const PLACEHOLDERS = [
        '{{page_number}}',
        '{{company_name}}',
        '{{document_title}}',
    ];

    public static function supports(string $type): bool
    {
        return isset(self::SETTING_KEYS[$type]);
    }

    public static function settingKey(string $type): string
    {
        return self::SETTING_KEYS[$type] ?? '';
    }

    public static function defaultHtml(string $type): string
    {
        $companyName = self::defaultCompanyName();
        $documentTitle = EmployeeSampleDocument::title($type);

        return $companyName.' | '.$documentTitle.' | Confidential | Version 1.0 | Page {{page_number}}';
    }

    public static function storedFooterHtml(string $type): string
    {
        return trim((string) Setting::get(self::settingKey($type), ''));
    }

    public static function hasCustomFooter(string $type): bool
    {
        return self::storedFooterHtml($type) !== '';
    }

    public static function editorFooterHtml(string $type): string
    {
        $stored = self::storedFooterHtml($type);

        return $stored !== '' ? $stored : self::defaultHtml($type);
    }

    public static function saveFooterHtml(string $type, string $html): void
    {
        abort_unless(self::supports($type), 404);

        $labels = [
            'nda' => 'Employee NDA footer template',
            'contract' => 'Employee Agreement footer template',
            'policy' => 'Employee Policy footer template',
            'handbook' => 'Employee Handbook footer template',
        ];

        Setting::set(self::settingKey($type), trim($html), 'text', $labels[$type]);
        Setting::clearCache();
    }

    public static function renderHtml(string $type, int $pageNumber = 1, ?string $companyName = null): string
    {
        $html = EmployeeDocumentTemplateSanitizer::sanitize(self::editorFooterHtml($type));

        $replacements = [
            '{{page_number}}' => (string) max(1, $pageNumber),
            '{{company_name}}' => e($companyName ?? self::defaultCompanyName()),
            '{{document_title}}' => e(EmployeeSampleDocument::title($type)),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }

    public static function renderPdfFixedFooterHtml(string $type, ?string $companyName = null, ?string $documentTitle = null): string
    {
        $html = EmployeeDocumentTemplateSanitizer::sanitize(self::editorFooterHtml($type));

        $replacements = [
            '{{page_number}}' => '<span class="document-pdf-page-number"></span>',
            '{{company_name}}' => e($companyName ?? self::defaultCompanyName()),
            '{{document_title}}' => e($documentTitle ?? EmployeeSampleDocument::title($type)),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }

    public static function renderScreenFooterHtml(string $type, int $pageNumber = 1, ?string $companyName = null): string
    {
        $html = EmployeeDocumentTemplateSanitizer::sanitize(self::editorFooterHtml($type));

        $pageNumberMarkup = '<span class="document-screen-page-number">'.max(1, $pageNumber).'</span>'
            .'<span class="document-print-page-number"></span>';

        $replacements = [
            '{{page_number}}' => $pageNumberMarkup,
            '{{company_name}}' => e($companyName ?? self::defaultCompanyName()),
            '{{document_title}}' => e(EmployeeSampleDocument::title($type)),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }

    public static function defaultCompanyName(): string
    {
        $name = trim((string) Setting::get(
            'nda_company_name',
            Setting::get('system_name', 'Mini Clean Business Solutions')
        ));

        return $name !== '' ? $name : 'Mini Clean Business Solutions';
    }

    public static function injectIntoBodyHtml(string $type, string $html, int $pageNumber = 1, ?string $companyName = null): string
    {
        if (! self::supports($type)) {
            return $html;
        }

        $footerInner = self::renderScreenFooterHtml($type, $pageNumber, $companyName);
        $footerMarkup = '<div class="document-page-footer">'.$footerInner.'</div>';

        $replaced = preg_replace(
            '/<div class="document-page-footer">(?:\s|&nbsp;|&#160;)*<\/div>/i',
            $footerMarkup,
            $html,
            1
        );

        if (is_string($replaced) && $replaced !== $html) {
            return $replaced;
        }

        if (! str_contains($html, 'document-page-footer')) {
            if (preg_match('/<div class="[^"]*document-page-shell[^"]*">/i', $html)) {
                return preg_replace(
                    '/<\/div>\s*$/',
                    $footerMarkup.'</div>',
                    $html,
                    1
                ) ?? ($html.$footerMarkup);
            }

            return $html.$footerMarkup;
        }

        return $html;
    }

}
