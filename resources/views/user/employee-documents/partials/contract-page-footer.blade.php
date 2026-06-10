<div class="agreement-page-footer">
    @include('user.employee-documents.partials.document-page-footer', [
        'documentType' => 'contract',
        'pageNumber' => $pageNumber ?? 1,
        'companyName' => $companyName ?? null,
    ])
</div>
