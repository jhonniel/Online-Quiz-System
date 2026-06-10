<div class="document-page-shell">
@include('user.employee-documents.partials.document-page-letterhead')

<p class="policy-title">Company Policy Acknowledgment</p>

<p class="policy-field"><span class="policy-field-label">Employee Name:</span> {{ $employeeName }}</p>
<p class="policy-field"><span class="policy-field-label">Employee Address:</span> {{ $employeeAddress }}</p>
<p class="policy-field"><span class="policy-field-label">Date Hired:</span> {{ $dateHired }}</p>

<p class="policy-intro">
    I acknowledge that I have received, read, and understood the following Company policies and agree to comply with them during my employment:
</p>

@if(!empty($policiesHtml))
    {!! $policiesHtml !!}
@else
<ul class="policy-list">
    @foreach($policies as $policy)
        <li><span class="policy-check" aria-hidden="true">&#9745;</span> {{ $policy }}</li>
    @endforeach
</ul>
@endif

<p class="policy-paragraph">
    I understand that these policies may be amended, revised, or updated by the Company from time to time and agree to comply with such amendments upon receipt or publication, provided that they are consistent with my Employment Agreement and applicable laws and regulations of the Republic of the Philippines.
</p>

<p class="policy-paragraph">
    I acknowledge that these policies are intended to promote a safe, professional, secure, and respectful workplace and form part of my responsibilities as an employee.
</p>

<div class="policy-sign-block">
    <p class="policy-sign-heading">Employer</p>
    <p class="policy-sign-row"><span class="policy-sign-label">Name:</span> {{ $employerName }}</p>
    <p class="policy-sign-row"><span class="policy-sign-label">Position:</span> {{ $employerPosition }}</p>
    <p class="policy-sign-row"><span class="policy-sign-label">Signature:</span></p>
</div>

<div class="policy-sign-block">
    <p class="policy-sign-heading">Employee</p>
    <p class="policy-sign-row"><span class="policy-sign-label">Name:</span> {{ $employeeName }}</p>
    <p class="policy-sign-row policy-sign-row-signature"><span class="policy-sign-label">Signature:</span>@if(!empty($eSignatureDataUri)) <img src="{{ $eSignatureDataUri }}" alt="E-Signature" class="policy-signature-image">@elseif(!empty($signaturePlaceholder)) {{ $signaturePlaceholder }}@endif</p>
</div>

@include('user.employee-documents.partials.document-page-footer', ['documentType' => 'policy', 'pageNumber' => 1])
</div>
