<div class="px-6 py-6 text-sm text-gray-900 leading-snug policy-document">
    @include('user.employee-documents.partials.document-page-shell-styles')
    <style>
        .policy-document .document-page-letterhead {
            margin-bottom: 1rem;
        }
        .policy-document .policy-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 1rem;
            margin-bottom: 1.25rem;
        }
        .policy-document .policy-field {
            margin-bottom: 0.5rem;
        }
        .policy-document .policy-field-label {
            font-weight: bold;
        }
        .policy-document .policy-intro {
            margin-top: 1rem;
            margin-bottom: 0.75rem;
        }
        .policy-document .policy-paragraph {
            margin-bottom: 0.75rem;
            text-align: justify;
        }
        .policy-document .policy-sign-block {
            margin-top: 1.25rem;
        }
        .policy-document .policy-sign-heading {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }
        .policy-document .policy-sign-row {
            margin-bottom: 0.5rem;
        }
        .policy-document .policy-sign-label {
            font-weight: bold;
        }
        .policy-document .policy-sign-row-signature {
            min-height: 2.5rem;
        }
        .policy-document .policy-signature-image {
            display: inline-block;
            max-height: 2.5rem;
            margin-left: 0.25rem;
            vertical-align: bottom;
            object-fit: contain;
        }
    </style>

    @include('user.employee-documents.partials.handbook-body')
</div>
