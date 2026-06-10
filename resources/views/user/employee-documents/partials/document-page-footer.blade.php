@php
    use App\Support\EmployeeDocumentFooter;

    $documentType = $documentType ?? 'contract';
    $pageNumber = (int) ($pageNumber ?? 1);
    $companyName = $companyName ?? null;
@endphp

<div class="document-page-footer">
    {!! EmployeeDocumentFooter::renderScreenFooterHtml($documentType, $pageNumber, $companyName) !!}
</div>
