<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\StudentNda;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

final class EmployeeNdaDocument
{
    /**
     * @return array<string, mixed>
     */
    public static function viewData(User $user, ?\DateTimeInterface $signedAt = null, ?bool $includeSignatureAssets = null): array
    {
        $includeSignatureAssets ??= $signedAt !== null;

        $agreementDate = $signedAt
            ? Carbon::instance($signedAt)
            : now();

        $idNumber = trim((string) ($user->tin ?: $user->sss_number ?: ''));
        $validIdType = $user->tin
            ? 'TIN'
            : ($user->sss_number ? 'SSS' : 'VALID ID');

        $data = [
            'fullName' => $user->name,
            'fullNameUpper' => strtoupper($user->name),
            'idNumber' => $idNumber !== '' ? $idNumber : '—',
            'idNumberUpper' => $idNumber !== '' ? strtoupper($idNumber) : '—',
            'validIdType' => $validIdType,
            'validIdTypeUpper' => strtoupper($validIdType),
            'city' => self::defaultCity(),
            'agreementDateFormal' => StudentNda::formatFormalDate($agreementDate),
            'agreementDateUpper' => strtoupper($agreementDate->format('F d, Y')),
            'branding' => StudentNdaDocument::branding(),
            'signedAt' => $signedAt,
            'eSignatureDataUri' => $includeSignatureAssets ? EmployeeSampleDocument::eSignatureDataUri($user) : null,
            'digitalSignatureEnabled' => $includeSignatureAssets
                && $signedAt !== null
                && EmployeeDocumentPdfSigner::isConfiguredForUser($user),
        ];

        $data['bodyHtml'] = EmployeeDocumentTemplate::bodyHtml('nda', $data);

        return $data;
    }

    public static function renderPdfBinary(User $user, ?\DateTimeInterface $signedAt = null): string
    {
        return Pdf::loadView('user.employee-documents.nda-pdf', self::viewData($user, $signedAt))
            ->setPaper([0, 0, 612, 1008], 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isPhpEnabled', true)
            ->output();
    }

    public static function renderSignedPdfBinary(User $user, ?\DateTimeInterface $signedAt = null, ?string $p12Password = null): string
    {
        $signedAt ??= now();
        $pdfBinary = self::renderPdfBinary($user, $signedAt);

        return EmployeeDocumentPdfSigner::sign($pdfBinary, $user, [
            'name' => $user->hasP12Certificate() ? $user->name : (string) Setting::get('system_name', config('app.name', 'System')),
            'reason' => 'Employee NDA signed by '.$user->name,
            'contact' => (string) Setting::get('contact_phone', ''),
            'location' => (string) Setting::get('contact_address', ''),
            'visible_appearance' => false,
            'password' => $p12Password,
        ]);
    }

    private static function defaultCity(): string
    {
        $city = trim((string) Setting::get('nda_default_city', 'Davao'));

        return $city !== '' ? $city : 'Davao';
    }
}
