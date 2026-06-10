<div class="nda-page document-page-shell">
    @include('user.employee-documents.partials.document-page-letterhead')
    <p class="header-country">Republic of the Philippines</p>

    <p class="title">NON-DISCLOSURE AGREEMENT</p>

    <p class="body-text">
        This is to certify that I, <strong>{{ $fullNameUpper }}</strong>, of
        <strong>{{ strtoupper($branding['company_inline']) }}</strong> understand that I cannot give out or share any official record
        obtained or accessed from/thru and/or involving the development of the Systems, Websites and Social Media Content
        for the clients contracted to <strong>{{ strtoupper($branding['company_inline']) }}</strong> without proper authority or unless
        in connection with my official functions or in pursuance of official transactions and processes.
    </p>

    <p class="body-text">
        I understand that any unauthorized release or negligence in the handling of the abovementioned information is
        considered a breach of confidence and prejudicial to the best interest of the Republic of the Philippines.
    </p>

    <p class="body-text">
        I further understand that any such breach may give rise to grounds for administrative or criminal liabilities as
        provided under existing laws.
    </p>

    <p class="done-line">
        Done in the City of {{ $city }}, this {{ $agreementDateFormal }}.
    </p>

    <div class="signature-block">
        <div class="signature-sign-area">
            @if(!empty($eSignatureDataUri))
                <img src="{{ $eSignatureDataUri }}" alt="E-Signature" class="signature-image" style="object-fit: contain;">
            @elseif(!empty($signaturePlaceholder))
                {{ $signaturePlaceholder }}
            @endif
        </div>
        <div class="signature-line"></div>
        <div class="signature-identity">
            <p class="signature-meta">{{ $fullNameUpper }}</p>
            <p class="signature-meta">{{ strtoupper($branding['company_signature']) }}</p>
        </div>
        <div class="signature-id-group">
            <p class="signature-meta">{{ $idNumberUpper }}</p>
            <p class="signature-meta">{{ $validIdTypeUpper }}</p>
        </div>
    </div>

    <div class="ack-section">
        <p class="ack-title">PERSONALLY SIGNED BEFORE ME:</p>
        <p class="ack-name">{{ $branding['signatory_name'] }}</p>
        <p class="ack-meta">{{ $branding['signatory_title'] }}</p>
        <p class="ack-meta">{{ $branding['company_line1'] }}</p>
        @if(!empty($branding['company_line2']))
            <p class="ack-meta">{{ $branding['company_line2'] }}</p>
        @endif
        <p class="ack-meta">{{ $agreementDateUpper }}</p>
    </div>

    @include('user.employee-documents.partials.document-page-footer', ['documentType' => 'nda', 'pageNumber' => 1])
</div>
