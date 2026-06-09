<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final class EmployeeSampleDocument
{
    /** @var list<string> */
    public const TYPES = ['nda', 'contract', 'policy'];

    public static function isValidType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }

    public static function label(string $type): string
    {
        return match ($type) {
            'nda' => 'NDA',
            'contract' => 'Contract',
            'policy' => 'Policy',
            default => strtoupper($type),
        };
    }

    public static function title(string $type): string
    {
        return match ($type) {
            'nda' => 'Non-Disclosure Agreement',
            'contract' => 'Employment Contract',
            'policy' => 'Company Policy Acknowledgment',
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
            'nda' => [
                "This Non-Disclosure Agreement (\"Agreement\") is entered into by {$employee}, employed as {$position} under {$department}, and {$company}.",
                'The Employee acknowledges access to confidential information including client data, source code, business processes, financial records, and internal communications.',
                'The Employee agrees not to disclose, copy, or use such information outside official duties or without written authorization from management.',
                'This obligation continues during employment and after separation, unless the information becomes public through no fault of the Employee.',
                'Unauthorized disclosure may result in disciplinary action, termination, and applicable legal remedies.',
            ],
            'contract' => [
                "This Employment Contract (\"Contract\") is between {$company} and {$employee}, for the position of {$position} in the {$department} department.",
                "The employment relationship is effective as of {$dateHired}, subject to company rules, performance standards, and applicable labor regulations.",
                'Compensation, benefits, and work schedule shall follow the offer accepted by the Employee and policies issued by the Company from time to time.',
                'The Employee agrees to perform assigned duties professionally, comply with attendance and reporting requirements, and follow lawful instructions from supervisors.',
                'Either party may end this employment relationship according to notice periods and procedures defined by company policy and applicable law.',
            ],
            'policy' => [
                "This Company Policy Acknowledgment confirms that {$employee}, {$position}, has reviewed and understood {$company}'s workplace policies.",
                'Policies include attendance and punctuality, leave procedures, acceptable use of company systems, data protection, and professional conduct.',
                'The Employee agrees to report safety concerns, conflicts of interest, and policy violations through proper channels.',
                'Failure to comply with company policies may result in corrective action up to and including termination of employment.',
                'The Employee confirms that policy updates communicated by management shall be treated as binding upon receipt.',
            ],
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
        ];
    }

    public static function renderPdfBinary(User $user, string $type, ?\DateTimeInterface $signedAt = null): string
    {
        $data = self::pdfViewData($user, $type, $signedAt);

        if ($signedAt === null) {
            $data['eSignatureDataUri'] = null;
        }

        return Pdf::loadView('user.employee-documents.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->output();
    }

    public static function renderSignedPdfBinary(User $user, string $type, ?\DateTimeInterface $signedAt = null): string
    {
        return self::renderPdfBinary($user, $type, $signedAt ?? now());
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
