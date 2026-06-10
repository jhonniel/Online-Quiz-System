<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

final class EmployeeContractDocument
{
    /**
     * @return array<string, mixed>
     */
    public static function viewData(User $user, ?\DateTimeInterface $signedAt = null, ?bool $includeSignatureAssets = null): array
    {
        $includeSignatureAssets ??= $signedAt !== null || $user->hasESignature();

        $user->loadMissing(['department:id,name', 'departmentPosition:id,name']);

        $position = trim((string) ($user->departmentPosition?->name ?? ''));
        if ($position === '') {
            $position = 'Software Developer';
        }

        $employeeName = trim($user->name);
        $employeeAddress = '';
        $dateHired = $user->date_hired?->format('F j, Y') ?? '';

        $data = [
            'documentTitle' => EmployeeSampleDocument::title('contract'),
            'footerLogoDataUri' => self::footerLogoDataUri(),
            'employeeName' => $employeeName,
            'employeeAddress' => $employeeAddress,
            'employeeNameLine' => $employeeName !== '' ? $employeeName : '___________________________________________',
            'employeeAddressLine' => $employeeAddress,
            'dateHiredLine' => $dateHired !== '' ? $dateHired : '__________________________________________',
            'position' => $position,
            'department' => strtoupper((string) ($user->department?->name ?? 'GENERAL')),
            'dateHired' => $dateHired,
            'companyName' => self::employerCompanyName(),
            'employerAddress' => self::employerAddress(),
            'employerName' => self::employerName(),
            'employerPosition' => self::employerPosition(),
            'signedAt' => $signedAt,
            'eSignatureDataUri' => $includeSignatureAssets ? EmployeeSampleDocument::eSignatureDataUri($user) : null,
            'digitalSignatureEnabled' => $includeSignatureAssets
                && $signedAt !== null
                && EmployeeDocumentPdfSigner::isConfiguredForUser($user),
        ];

        $data['bodyHtml'] = EmployeeDocumentTemplate::bodyHtml('contract', $data);

        return $data;
    }

    public static function renderPdfBinary(User $user, ?\DateTimeInterface $signedAt = null): string
    {
        return Pdf::loadView('user.employee-documents.contract-pdf', self::viewData($user, $signedAt))
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
            'reason' => 'Employee Agreement signed by '.$user->name,
            'contact' => (string) Setting::get('contact_phone', ''),
            'location' => (string) Setting::get('contact_address', ''),
        ]);
    }

    public static function footerLogoDataUri(): string
    {
        $path = public_path('images/employee-documents/miniclean-footer-logo.png');
        if (! is_file($path)) {
            return '';
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }

    private static function employerCompanyName(): string
    {
        $name = trim((string) Setting::get(
            'nda_company_name',
            Setting::get('system_name', 'Mini Clean Business Solutions')
        ));

        return $name !== '' ? $name : 'Mini Clean Business Solutions';
    }

    private static function employerAddress(): string
    {
        $address = trim((string) Setting::get(
            'contact_address',
            'MS Land Complex Building 2, KM 3 McArthur Highway, Matina Crossing, Talomo District, Davao City'
        ));

        return $address !== '' ? $address : 'MS Land Complex Building 2, KM 3 McArthur Highway, Matina Crossing, Talomo District, Davao City';
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
