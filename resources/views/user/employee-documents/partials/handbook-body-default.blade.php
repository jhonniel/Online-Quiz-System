<p class="policy-title">Employee Handbook<br>Acknowledgment</p>

<p class="policy-field"><span class="policy-field-label">Employee Name:</span> {{ $employeeName }}</p>
<p class="policy-field"><span class="policy-field-label">Employee Address:</span> {{ $employeeAddress }}</p>
<p class="policy-field"><span class="policy-field-label">Date Hired:</span> {{ $dateHired }}</p>

<p class="policy-paragraph">
    I acknowledge that I have received a copy of the Company's Employee Handbook.
</p>

<p class="policy-paragraph">
    I understand that the Employee Handbook contains important information regarding the Company's policies, procedures, standards of conduct, workplace expectations, attendance rules, disciplinary procedures, information technology policies, and other operational guidelines.
</p>

<p class="policy-paragraph">
    I agree to read, understand, and comply with the provisions of the Employee Handbook and any amendments or updates that may be issued by the Company from time to time.
</p>

<p class="policy-paragraph">
    I understand that the Employee Handbook is intended as a guide for employees and does not create or modify the terms of my Employment Agreement or any rights provided under applicable laws and regulations of the Republic of the Philippines.
</p>

<p class="policy-paragraph">
    I further acknowledge that failure to comply with the Employee Handbook may result in disciplinary action in accordance with Company policies and applicable laws.
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
