<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

final class EmployeeHandbookDocument
{
    /**
     * @return array<string, mixed>
     */
    public static function viewData(User $user, ?\DateTimeInterface $signedAt = null, ?bool $includeSignatureAssets = null): array
    {
        $includeSignatureAssets ??= $signedAt !== null;

        $user->loadMissing(['department:id,name', 'departmentPosition:id,name,department_id']);

        $data = [
            'employeeName' => $user->name,
            'employeeNameUpper' => strtoupper($user->name),
            'employeeAddress' => '',
            'dateHired' => $user->date_hired?->format('F j, Y') ?? '',
            'companyName' => trim((string) Setting::get('system_name', config('app.name', 'the Company'))),
            'employerName' => self::employerName(),
            'employerPosition' => self::employerPosition(),
            'signedAt' => $signedAt,
            'eSignatureDataUri' => $includeSignatureAssets ? EmployeeSampleDocument::eSignatureDataUri($user) : null,
            'digitalSignatureEnabled' => $includeSignatureAssets
                && $signedAt !== null
                && EmployeeDocumentPdfSigner::isConfiguredForUser($user),
        ];

        $data['bodyHtml'] = EmployeeDocumentTemplate::bodyHtml('handbook', $data);

        return $data;
    }

    public static function renderPdfBinary(User $user, ?\DateTimeInterface $signedAt = null): string
    {
        return Pdf::loadView('user.employee-documents.handbook-pdf', self::viewData($user, $signedAt))
            ->setPaper('a4', 'portrait')
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
            'reason' => 'Employee Handbook signed by '.$user->name,
            'contact' => (string) Setting::get('contact_phone', ''),
            'location' => (string) Setting::get('contact_address', ''),
            'password' => $p12Password,
        ]);
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
