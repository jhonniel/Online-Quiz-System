<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final class EmployeeSampleDocument
{
    /** @var list<string> */
    public const TYPES = ['nda', 'contract', 'policy', 'handbook'];

    public static function isValidType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }

    public static function label(string $type): string
    {
        return match ($type) {
            'nda' => 'NDA',
            'contract' => 'Agreement',
            'policy' => 'Policy',
            'handbook' => 'Hand Book',
            default => strtoupper($type),
        };
    }

    public static function title(string $type): string
    {
        return match ($type) {
            'nda' => 'Non-Disclosure Agreement',
            'contract' => 'Employment Agreement — Terms of Employment',
            'policy' => 'Company Policy Acknowledgment',
            'handbook' => 'Employee Handbook Acknowledgment',
            default => self::label($type),
        };
    }

    /**
     * @return list<string>
     */
    public static function paragraphs(User $user, string $type): array
    {
        $company = trim((string) Setting::get('system_name', config('app.name', 'the Company')));
        $employee = strtoupper($user->name);
        $position = 'EMPLOYEE';
        $department = strtoupper((string) ($user->department?->name ?? 'GENERAL'));
        $dateHired = $user->date_hired?->format('F j, Y') ?? '_______________';

        return match ($type) {
            'nda' => [],
            'contract' => [
                "This Employment Agreement (\"Agreement\") is between {$company} and {$employee}, for the position of {$position} in the {$department} department.",
                "The employment relationship is effective as of {$dateHired}, subject to company rules, performance standards, and applicable labor regulations.",
                'Compensation, benefits, and work schedule shall follow the offer accepted by the Employee and policies issued by the Company from time to time.',
                'The Employee agrees to perform assigned duties professionally, comply with attendance and reporting requirements, and follow lawful instructions from supervisors.',
                'Either party may end this employment relationship according to notice periods and procedures defined by company policy and applicable law.',
            ],
            'policy' => [],
            'handbook' => [],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function pdfViewData(User $user, string $type, ?\DateTimeInterface $signedAt = null): array
    {
        return [
            'documentType' => $type,
            'documentTitle' => self::title($type),
            'documentLabel' => self::label($type),
            'paragraphs' => self::paragraphs($user, $type),
            'employeeName' => strtoupper($user->name),
            'position' => 'EMPLOYEE',
            'department' => strtoupper((string) ($user->department?->name ?? 'GENERAL')),
            'dateHired' => $user->date_hired?->format('F j, Y') ?? '—',
            'companyName' => trim((string) Setting::get('system_name', config('app.name', 'the Company'))),
            'signedAt' => $signedAt,
            'eSignatureDataUri' => self::eSignatureDataUri($user),
            'digitalSignatureEnabled' => $signedAt !== null && EmployeeDocumentPdfSigner::isConfiguredForUser($user),
        ];
    }

    public static function renderPdfBinary(User $user, string $type, ?\DateTimeInterface $signedAt = null): string
    {
        if ($type === 'nda') {
            return EmployeeNdaDocument::renderPdfBinary($user, $signedAt);
        }

        if ($type === 'policy') {
            return EmployeePolicyDocument::renderPdfBinary($user, $signedAt);
        }

        if ($type === 'handbook') {
            return EmployeeHandbookDocument::renderPdfBinary($user, $signedAt);
        }

        if ($type === 'contract') {
            return EmployeeContractDocument::renderPdfBinary($user, $signedAt);
        }

        return Pdf::loadView('user.employee-documents.pdf', self::pdfViewData($user, $type, $signedAt))
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->output();
    }

    public static function renderSignedPdfBinary(User $user, string $type, ?\DateTimeInterface $signedAt = null): string
    {
        $signedAt ??= now();

        if ($type === 'nda') {
            return EmployeeNdaDocument::renderSignedPdfBinary($user, $signedAt);
        }

        if ($type === 'policy') {
            return EmployeePolicyDocument::renderSignedPdfBinary($user, $signedAt);
        }

        if ($type === 'handbook') {
            return EmployeeHandbookDocument::renderSignedPdfBinary($user, $signedAt);
        }

        if ($type === 'contract') {
            return EmployeeContractDocument::renderSignedPdfBinary($user, $signedAt);
        }

        $pdfBinary = self::renderPdfBinary($user, $type, $signedAt);

        return EmployeeDocumentPdfSigner::sign($pdfBinary, $user, [
            'name' => $user->hasP12Certificate() ? $user->name : (string) Setting::get('system_name', config('app.name', 'System')),
            'reason' => 'Employee '.self::label($type).' signed by '.$user->name,
            'contact' => (string) Setting::get('contact_phone', ''),
            'location' => (string) Setting::get('contact_address', ''),
        ]);
    }

    public static function pdfFilename(string $type, bool $signed = false): string
    {
        $slug = str($type)->slug('-');

        return 'employee-'.$slug.($signed ? '-signed' : '').'.pdf';
    }

    public static function eSignatureDataUri(User $user): ?string
    {
        $path = $user->e_signature_path;
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        foreach (['digitalocean', 'public', config('filesystems.default', 'local')] as $diskName) {
            try {
                $disk = Storage::disk($diskName);
                if (! $disk->exists($path)) {
                    continue;
                }

                $contents = $disk->get($path);

                return 'data:image/png;base64,'.base64_encode($contents);
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
