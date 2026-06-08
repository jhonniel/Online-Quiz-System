<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\StudentNda;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

final class StudentNdaDocument
{
    /**
     * @return array<string, string>
     */
    public static function branding(): array
    {
        $companyName = trim((string) Setting::get('nda_company_name', ''));
        if ($companyName === '') {
            $companyName = 'MINI CLEAN BUSINESS SOLUTIONS (INFOSOFT)';
        }

        $companyParts = self::companyDisplayParts($companyName);

        return [
            'company_name' => $companyName,
            'company_inline' => $companyParts['inline'],
            'company_signature' => $companyParts['signature'],
            'company_line1' => $companyParts['line1'],
            'company_line2' => $companyParts['line2'],
            'signatory_name' => strtoupper((string) Setting::get('nda_signatory_name', Setting::get('leave_cto', 'NITISH G. KHEMANI'))),
            'signatory_title' => strtoupper((string) Setting::get('nda_signatory_title', 'CEO & FOUNDER')),
        ];
    }

    /**
     * @return array{inline: string, signature: string, line1: string, line2: string}
     */
    public static function companyDisplayParts(string $companyName): array
    {
        $companyName = trim($companyName);
        if ($companyName === '') {
            $companyName = 'MINI CLEAN BUSINESS SOLUTIONS (INFOSOFT)';
        }

        if (preg_match('/^(.+?)\s*\(([^)]+)\)\s*$/', $companyName, $matches)) {
            $base = trim($matches[1]);
            $suffix = trim($matches[2]);

            return [
                'inline' => $base.'('.$suffix.')',
                'signature' => $base.' ('.$suffix.')',
                'line1' => strtoupper($base),
                'line2' => '('.strtoupper($suffix).')',
            ];
        }

        $upper = strtoupper($companyName);

        return [
            'inline' => $companyName,
            'signature' => $companyName,
            'line1' => $upper,
            'line2' => '',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromInput(array $data): StudentNda
    {
        $nda = new StudentNda([
            'full_name' => trim((string) ($data['full_name'] ?? '')),
            'id_number' => trim((string) ($data['id_number'] ?? '')),
            'valid_id_type' => trim((string) ($data['valid_id_type'] ?? '')),
            'city' => trim((string) ($data['city'] ?? 'Davao')),
            'agreement_date' => Carbon::parse((string) ($data['agreement_date'] ?? now()->toDateString())),
        ]);

        return $nda;
    }

    /**
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:100'],
            'valid_id_type' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'agreement_date' => ['required', 'date'],
        ];
    }

    public static function renderUnsignedPdfBinary(StudentNda $nda): string
    {
        return Pdf::loadView('user.student-nda-pdf', [
            'nda' => $nda,
            'branding' => self::branding(),
        ])
            ->setPaper([0, 0, 612, 1008], 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->output();
    }

    public static function validateSignedUpload(UploadedFile $file, StudentNda $nda): ?string
    {
        $path = $file->getRealPath();
        if (! is_string($path) || $path === '') {
            return 'The uploaded PDF could not be read. Please try again.';
        }

        $unsignedPdf = self::renderUnsignedPdfBinary($nda);
        $detector = new SignedPdfDetector();

        if ($detector->appearsSigned($path, $unsignedPdf)) {
            return null;
        }

        return 'The uploaded PDF does not appear to be signed. Please sign the NDA above the signature line before uploading.';
    }
}
