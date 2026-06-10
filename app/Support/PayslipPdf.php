<?php

namespace App\Support;

use App\Models\EmployeePayslip;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

final class PayslipPdf
{
    public static function renderPdfBinary(
        EmployeePayslip $payslip,
        User $employee,
        ?\DateTimeInterface $signedAt = null,
    ): string {
        $employee->loadMissing(['department:id,name', 'departmentPosition:id,name,department_id']);
        $payslip->setRelation('employee', $employee);
        $payslip->syncProfileFieldsFromEmployee($employee);

        $data = self::pdfViewData($payslip, $employee, $signedAt);

        return Pdf::loadView('user.payslips.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('dpi', 120)
            ->output();
    }

    public static function renderSignedPdfBinary(
        EmployeePayslip $payslip,
        User $employee,
        \DateTimeInterface $signedAt,
        ?string $p12Password = null,
    ): string {
        $pdfBinary = self::renderPdfBinary($payslip, $employee, $signedAt);

        if (empty($employee->p12_certificate_path) || $p12Password === null || $p12Password === '') {
            return $pdfBinary;
        }

        return EmployeeDocumentPdfSigner::sign($pdfBinary, $employee, [
            'password' => $p12Password,
            'name' => $employee->name,
            'reason' => 'Payslip signed by '.$employee->name.' ('.$payslip->periodLabel().')',
            'contact' => (string) Setting::get('contact_phone', ''),
            'location' => (string) Setting::get('contact_address', ''),
            'visible_appearance' => false,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function pdfViewData(
        EmployeePayslip $payslip,
        User $employee,
        ?\DateTimeInterface $signedAt,
    ): array {
        return [
            'payslip' => $payslip,
            'signedAt' => $signedAt,
            'eSignatureDataUri' => EmployeeSampleDocument::eSignatureDataUri($employee),
        ];
    }
}
