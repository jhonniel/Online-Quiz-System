@once
<style>
    .document-page-shell {
        position: relative;
        min-height: 10.5in;
        padding-bottom: 2.75rem;
        box-sizing: border-box;
    }

    .document-page-shell > .document-page-footer,
    .agreement-page > .agreement-page-footer {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .document-page-footer {
        text-align: left;
        font-size: 0.75rem;
        color: #374151;
        line-height: 1.35;
    }

    .document-print-page-number {
        display: none;
    }

    @media print {
        .document-page-shell,
        .agreement-page.document-page-shell {
            min-height: 10in;
            page-break-after: always;
            break-after: page;
        }

        .document-page-shell:last-child,
        .agreement-page.document-page-shell:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .document-page-shell > .document-page-footer,
        .agreement-page > .agreement-page-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .document-screen-page-number {
            display: none;
        }

        .document-print-page-number {
            display: inline;
        }

        .document-print-page-number::before {
            content: counter(page);
        }
    }
</style>
@endonce
