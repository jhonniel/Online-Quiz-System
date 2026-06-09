<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Applies a PKCS#12 (P12) digital signature to employee document PDFs using FPDI + TCPDF.
 */
final class EmployeeDocumentPdfSigner
{
    private const SIGNATURE_FOOTER_BOTTOM_MM = 15.0;

    private const DIGITAL_SIGNATURE_HEIGHT_MM = 22.0;

    private const DIGITAL_SIGNATURE_GAP_MM = 4.0;

    private const EMPLOYEE_ESIGNATURE_HEIGHT_MM = 14.0;

    private const SIGNATURE_LEFT_MM = 15.0;

    private const SIGNATURE_WIDTH_MM = 100.0;

    public static function isConfiguredForUser(?User $user): bool
    {
        if ($user?->hasP12Certificate()) {
            return self::readP12Contents((string) $user->p12_certificate_path) !== null;
        }

        return self::isSystemConfigured();
    }

    public static function isSystemConfigured(): bool
    {
        $path = Setting::get('employee_document_p12_path');

        return is_string($path)
            && trim($path) !== ''
            && self::resolveSystemP12Password() !== null
            && self::readP12Contents($path) !== null;
    }

    /**
     * @param  array{name?: string, reason?: string, location?: string, contact?: string}  $context
     */
    public static function sign(string $pdfBinary, ?User $user = null, array $context = []): string
    {
        $certificate = self::resolveCertificate($user, $context['password'] ?? null);

        if ($certificate === null) {
            return $pdfBinary;
        }

        $certs = [];
        if (! openssl_pkcs12_read($certificate['contents'], $certs, $certificate['password'])) {
            throw new \RuntimeException('Unable to read the P12 certificate. Check the certificate file and password.');
        }

        $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setMargins(0, 0, 0);
        $pdf->setAutoPageBreak(false, 0);

        $pageCount = $pdf->setSourceFile(StreamReader::createByString($pdfBinary));

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            $orientation = ($size['width'] ?? 0) > ($size['height'] ?? 0) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }

        $info = [
            'Name' => $context['name'] ?? $certificate['signer_name'],
            'Location' => $context['location'] ?? (string) Setting::get('contact_address', ''),
            'Reason' => $context['reason'] ?? 'Employee document digital signature',
            'ContactInfo' => $context['contact'] ?? (string) Setting::get('contact_phone', ''),
        ];

        $pdf->setSignature($certs['cert'], $certs['pkey'], '', '', 2, $info);

        $lastPage = $pageCount;
        $pageHeight = $pdf->getPageHeight($lastPage);
        $appearanceY = $pageHeight
            - self::SIGNATURE_FOOTER_BOTTOM_MM
            - self::EMPLOYEE_ESIGNATURE_HEIGHT_MM
            - self::DIGITAL_SIGNATURE_GAP_MM
            - self::DIGITAL_SIGNATURE_HEIGHT_MM;

        if ($context['visible_appearance'] ?? true) {
            $appearance = $context['appearance'] ?? null;

            $pdf->setSignatureAppearance(
                (float) ($appearance['x'] ?? self::SIGNATURE_LEFT_MM),
                (float) ($appearance['y'] ?? max(0, $appearanceY)),
                (float) ($appearance['width'] ?? self::SIGNATURE_WIDTH_MM),
                (float) ($appearance['height'] ?? self::DIGITAL_SIGNATURE_HEIGHT_MM),
                (int) ($appearance['page'] ?? $lastPage)
            );
        }

        return $pdf->Output('', 'S');
    }

    /**
     * @return array{contents: string, password: string, signer_name: string}|null
     */
    private static function resolveCertificate(?User $user, ?string $passwordOverride = null): ?array
    {
        if ($user && ! empty($user->p12_certificate_path)) {
            $contents = self::readP12Contents((string) $user->p12_certificate_path);
            $password = $passwordOverride ?? (string) $user->p12_certificate_password;

            if ($contents !== null && $password !== '') {
                return [
                    'contents' => $contents,
                    'password' => $password,
                    'signer_name' => (string) $user->name,
                ];
            }
        }

        $path = Setting::get('employee_document_p12_path');
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $contents = self::readP12Contents($path);
        $password = self::resolveSystemP12Password();

        if ($contents === null || $password === null) {
            return null;
        }

        return [
            'contents' => $contents,
            'password' => $password,
            'signer_name' => (string) Setting::get('system_name', config('app.name', 'System')),
        ];
    }

    private static function resolveSystemP12Password(): ?string
    {
        $password = env('EMPLOYEE_DOCUMENT_P12_PASSWORD');

        if (! is_string($password) || $password === '') {
            return null;
        }

        return $password;
    }

    private static function readP12Contents(string $path): ?string
    {
        foreach (['digitalocean', 'public', 'local', config('filesystems.default', 'local')] as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                if ($disk->exists($path)) {
                    $contents = $disk->get($path);

                    return is_string($contents) && $contents !== '' ? $contents : null;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
