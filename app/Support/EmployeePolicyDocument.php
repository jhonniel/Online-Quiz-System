<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

final class EmployeePolicyDocument
{
    /** @var list<string> */
    public const POLICIES = [
        'Anti-Sexual Harassment and Safe Spaces Policy',
        'Lactation Policy',
        'Disaster Risk Reduction and Emergency Preparedness Policy',
        'Occupational Safety and Health (OSH) Policy',
        'Data Privacy Policy',
        'Information Technology and Information Security Policy',
        'Other Company Policies and Operational Guidelines issued by the Employer',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function viewData(User $user, ?\DateTimeInterface $signedAt = null, ?bool $includeSignatureAssets = null): array
    {
        $includeSignatureAssets ??= $signedAt !== null || $user->hasESignature();

        $user->loadMissing(['department:id,name', 'departmentPosition:id,name,department_id']);

        $data = [
            'employeeName' => $user->name,
            'employeeNameUpper' => strtoupper($user->name),
            'employeeAddress' => self::employeeAddress($user),
            'dateHired' => $user->date_hired?->format('F j, Y') ?? '',
            'companyName' => trim((string) Setting::get('system_name', config('app.name', 'the Company'))),
            'policies' => self::POLICIES,
            'employerName' => self::employerName(),
            'employerPosition' => self::employerPosition(),
            'signedAt' => $signedAt,
            'eSignatureDataUri' => $includeSignatureAssets ? EmployeeSampleDocument::eSignatureDataUri($user) : null,
            'digitalSignatureEnabled' => $includeSignatureAssets
                && $signedAt !== null
                && EmployeeDocumentPdfSigner::isConfiguredForUser($user),
        ];

        $data['bodyHtml'] = EmployeeDocumentTemplate::bodyHtml('policy', $data);

        return $data;
    }

    public static function renderPdfBinary(User $user, ?\DateTimeInterface $signedAt = null): string
    {
        return Pdf::loadView('user.employee-documents.policy-pdf', self::viewData($user, $signedAt))
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isRemoteEnabled', true)
            ->output();
    }

    public static function renderSignedPdfBinary(User $user, ?\DateTimeInterface $signedAt = null): string
    {
        $signedAt ??= now();
        $pdfBinary = self::renderPdfBinary($user, $signedAt);

        return EmployeeDocumentPdfSigner::sign($pdfBinary, $user, [
            'name' => $user->hasP12Certificate() ? $user->name : (string) Setting::get('system_name', config('app.name', 'System')),
            'reason' => 'Employee Policy signed by '.$user->name,
            'contact' => (string) Setting::get('contact_phone', ''),
            'location' => (string) Setting::get('contact_address', ''),
        ]);
    }

    private static function employeeAddress(User $user): string
    {
        return '';
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
