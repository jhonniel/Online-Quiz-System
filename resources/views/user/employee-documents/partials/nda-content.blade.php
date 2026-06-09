<div class="px-6 py-6 space-y-4 text-sm text-gray-800 leading-relaxed">
    <p class="text-center font-medium">Republic of the Philippines</p>
    <p class="text-center font-bold uppercase tracking-wide">Non-Disclosure Agreement</p>

    <p class="text-justify">
        This is to certify that I, <strong>{{ $fullNameUpper }}</strong>, of
        <strong>{{ strtoupper($branding['company_inline']) }}</strong> understand that I cannot give out or share any official record
        obtained or accessed from/thru and/or involving the development of the Systems, Websites and Social Media Content
        for the clients contracted to <strong>{{ strtoupper($branding['company_inline']) }}</strong> without proper authority or unless
        in connection with my official functions or in pursuance of official transactions and processes.
    </p>

    <p class="text-justify">
        I understand that any unauthorized release or negligence in the handling of the abovementioned information is
        considered a breach of confidence and prejudicial to the best interest of the Republic of the Philippines.
    </p>

    <p class="text-justify">
        I further understand that any such breach may give rise to grounds for administrative or criminal liabilities as
        provided under existing laws.
    </p>

    <p>
        Done in the City of {{ $city }}, this {{ $agreementDateFormal }}.
    </p>

    <div class="pt-4 text-center space-y-1">
        <p class="font-bold uppercase">{{ $fullNameUpper }}</p>
        <p class="font-bold uppercase">{{ strtoupper($branding['company_signature']) }}</p>
        <p class="font-bold uppercase pt-4">{{ $idNumberUpper }}</p>
        <p class="font-bold uppercase">{{ $validIdTypeUpper }}</p>
    </div>

    <div class="pt-8 text-center space-y-1">
        <p class="font-bold uppercase">Personally signed before me:</p>
        <p class="font-bold uppercase underline">{{ $branding['signatory_name'] }}</p>
        <p class="font-bold uppercase">{{ $branding['signatory_title'] }}</p>
        <p class="font-bold uppercase">{{ $branding['company_line1'] }}</p>
        @if(!empty($branding['company_line2']))
            <p class="font-bold uppercase">{{ $branding['company_line2'] }}</p>
        @endif
        <p class="font-bold uppercase">{{ $agreementDateUpper }}</p>
    </div>
</div>
