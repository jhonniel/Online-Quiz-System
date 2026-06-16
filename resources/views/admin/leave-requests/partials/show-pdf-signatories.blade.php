@php
    $employeeSignatory = $signatoryAssets['employee'] ?? [
        'name' => $employee->name ?? '[YOUR NAME]',
        'role' => strtoupper($employee->role ?? 'Employee'),
        'e_signature_data_uri' => null,
    ];
    $supervisorSignatory = $signatoryAssets['immediate_supervisor'] ?? [
        'name' => $signatories['immediate_supervisor'] ?? '—',
        'role' => 'Immediate Supervisor',
        'e_signature_data_uri' => null,
    ];
    $hrSignatory = $signatoryAssets['hr_admin'] ?? [
        'name' => $signatories['hr_admin'] ?? '—',
        'role' => 'HR Admin',
        'e_signature_data_uri' => null,
    ];
    $ctoSignatory = $signatoryAssets['cto'] ?? [
        'name' => $signatories['cto'] ?? '—',
        'role' => 'Chief Technology Officer',
        'e_signature_data_uri' => null,
    ];
@endphp

<div style="margin-top: 16px;">
    @include('admin.leave-requests.partials.show-pdf-signatory-row', ['signatory' => $employeeSignatory])

    <div style="margin-top: 20px;">
        <p style="margin: 0 0 8px 0;">Noted:</p>
        @include('admin.leave-requests.partials.show-pdf-signatory-row', ['signatory' => $supervisorSignatory])
        @include('admin.leave-requests.partials.show-pdf-signatory-row', ['signatory' => $hrSignatory])

        <table style="width: 100%; margin-top: 12px; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: top; width: 70%;">
                    <p style="margin: 0 0 4px 0; font-size: 10px;">Approved:</p>
                    @include('admin.leave-requests.partials.show-pdf-signatory-row', ['signatory' => $ctoSignatory])
                </td>
                <td style="vertical-align: top; text-align: right;">
                    <p class="remarks">REMARKS: {{ $remarksText }}</p>
                </td>
            </tr>
        </table>
    </div>
</div>
