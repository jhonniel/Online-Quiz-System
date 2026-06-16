<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Request #{{ $leaveRequest->id }}</title>
    <style>
        @page { margin: 38mm 24mm 16mm 24mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; margin: 0; padding: 0; line-height: 1.5; }
        .letter-content { padding: 0; }
        .letter-date { font-size: 10px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; margin: 0 0 12px 0; color: #374151; }
        .letter-body p { margin: 0 0 10px 0; }
        .emphasis { font-weight: 700; text-decoration: underline; }
        .signatory-block { margin: 0 0 12px 0; }
        .signature-slot { height: 36px; margin: 0 0 -28px 0; }
        .signature-image { display: block; height: 36px; max-width: 160px; width: auto; object-fit: contain; }
        .signatory-name { font-weight: 700; text-decoration: underline; margin: 0; }
        .signatory-role { font-size: 9px; color: #374151; margin: 2px 0 0 0; text-transform: uppercase; letter-spacing: 0.03em; }
        .remarks { font-size: 10px; font-weight: 700; text-align: right; margin: 0; }
    </style>
</head>
<body>
    @include('user.employee-documents.partials.document-letterhead-pdf-fixed')

    @php
        $remarksText = strtoupper(match ($leaveRequest->status) {
            'approved' => 'Approved',
            'rejected' => 'Disapproved',
            default => $leaveRequest->display_status,
        });
        $signatoryAssets = $signatoryAssets ?? [];
    @endphp

    @include('admin.leave-requests.partials.show-pdf-letter')
</body>
</html>
