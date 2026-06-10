<div class="px-6 py-6 text-sm text-gray-900 leading-snug contract-document">
    @include('user.employee-documents.partials.document-page-shell-styles')
    <style>
        .contract-document .document-page-letterhead {
            margin-bottom: 1rem;
        }
        .contract-document .agreement-page { position: relative; margin-bottom: 2.5rem; padding-bottom: 3rem; border-bottom: 1px dashed #d1d5db; }
        .contract-document .agreement-page:last-child { border-bottom: 0; margin-bottom: 0; }
        @media print {
            .contract-document .agreement-page {
                page-break-after: always;
                break-after: page;
            }
            .contract-document .agreement-page:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }
        .contract-document .agreement-main-title { text-align: center; font-weight: bold; text-transform: uppercase; font-size: 1rem; margin-bottom: 0.15rem; }
        .contract-document .agreement-subtitle { text-align: center; font-weight: bold; text-transform: uppercase; font-size: 1rem; margin-bottom: 1.25rem; }
        .contract-document .section-heading { font-weight: bold; margin-top: 0.85rem; margin-bottom: 0.4rem; }
        .contract-document .body-text { margin-bottom: 0.75rem; text-align: justify; }
        .contract-document .body-text-center { margin-bottom: 0.75rem; text-align: center; }
        .contract-document .agreement-list { margin: 0 0 0.75rem 0; padding: 0; list-style: none; }
        .contract-document .agreement-list li { margin-bottom: 0.35rem; padding-left: 1rem; text-align: justify; position: relative; }
        .contract-document .agreement-list li::before { content: "\25CF"; position: absolute; left: 0; }
        .contract-document .field-line { display: inline-block; border-bottom: 1px solid #111827; }
        .contract-document .field-line--blank { min-width: 16rem; }
        .contract-document .agreement-page-footer { min-height: 2rem; }
        .contract-document .agreement-sign-block { margin-top: 1.25rem; }
        .contract-document .agreement-sign-heading { font-weight: bold; text-transform: uppercase; margin-bottom: 0.75rem; }
        .contract-document .agreement-sign-row { margin-bottom: 0.5rem; }
        .contract-document .agreement-sign-label { font-weight: bold; }
        .contract-document .agreement-sign-row-signature { min-height: 2.5rem; }
        .contract-document .agreement-signature-image { display: inline-block; max-height: 2.5rem; margin-left: 0.25rem; vertical-align: bottom; object-fit: contain; }
    </style>

    @include('user.employee-documents.partials.contract-body')
</div>
