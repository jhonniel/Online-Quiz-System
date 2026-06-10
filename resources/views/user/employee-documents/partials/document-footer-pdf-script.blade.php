@php
    use App\Support\EmployeeDocumentFooter;

    $documentType = $documentType ?? 'policy';
    $companyName = $companyName ?? null;
@endphp

<style>
    .agreement-page-footer,
    .document-page-shell > .document-page-footer,
    .document-page-footer {
        display: none !important;
    }

    .document-pdf-fixed-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        text-align: left;
        font-size: 7.5pt;
        color: #111827;
        line-height: 1.3;
        font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
    }

    .document-pdf-page-number::before {
        content: counter(page);
    }
</style>

<div class="document-pdf-fixed-footer">
    {!! EmployeeDocumentFooter::renderPdfFixedFooterHtml($documentType, $companyName) !!}
</div>
