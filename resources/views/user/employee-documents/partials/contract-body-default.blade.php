@php
    $hasEmployeeName = trim($employeeName ?? '') !== '';
    $hasEmployeeAddress = trim($employeeAddress ?? '') !== '';
    $hasDateHired = trim($dateHired ?? '') !== '' && ($dateHired ?? '') !== '—';
@endphp
<div class="contract-document-body">
    <div class="agreement-page document-page-shell">
        @include('user.employee-documents.partials.document-page-letterhead')
        <p class="agreement-main-title">Employment Agreement</p>
        <p class="agreement-subtitle">Terms of Employment</p>

        <p class="body-text">This Employment Agreement and Terms of Employment ("Agreement") is entered into by and between:</p>
        <p class="body-text"><strong>Employer:</strong> {{ $companyName }}</p>
        <p class="body-text"><strong>Address:</strong> {{ $employerAddress }}</p>
        <p class="body-text-center">and</p>
        <p class="body-text"><strong>Employee:</strong> @if($hasEmployeeName)<span class="field-line">{{ $employeeName }}</span>@else<span class="field-line field-line--blank">&nbsp;</span>@endif</p>
        <p class="body-text"><strong>Address:</strong> @if($hasEmployeeAddress)<span class="field-line">{{ $employeeAddress }}</span>@endif</p>
        <p class="body-text">(collectively referred to as the "Parties").</p>
        <p class="body-text"><strong>Date Hired:</strong> @if($hasDateHired)<span class="field-line">{{ $dateHired }}</span>@else<span class="field-line field-line--blank">&nbsp;</span>@endif</p>

        <p class="body-text">
            The Parties acknowledge that the Employee commenced employment with the Employer on the Date Hired indicated above and agree that the terms and conditions set forth in this Agreement shall govern the Employee's employment from such date and shall remain in full force and effect until amended or terminated in accordance with this Agreement and applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Position</p>
        <p class="body-text">The Employee is employed as a <strong>{{ $position }}</strong> and agrees to faithfully perform the duties and responsibilities assigned by the Employer.</p>

        @if(!empty($positionContentHtml))
            <div class="agreement-position-content">{!! $positionContentHtml !!}</div>
        @endif

        <p class="section-heading">Job Description</p>
        <p class="body-text">The Employee's duties and responsibilities include, but are not limited to:</p>
        @if(!empty($jobDescriptionDutiesPage1))
            <ul class="agreement-list">
                @foreach($jobDescriptionDutiesPage1 as $duty)
                    <li>{{ $duty }}</li>
                @endforeach
            </ul>
        @endif

        @include('user.employee-documents.partials.contract-page-footer', ['pageNumber' => 1])
    </div>

    <div class="agreement-page document-page-shell">
        @include('user.employee-documents.partials.document-page-letterhead')
        @if(!empty($jobDescriptionDutiesPage2))
            <ul class="agreement-list">
                @foreach($jobDescriptionDutiesPage2 as $duty)
                    <li>{{ $duty }}</li>
                @endforeach
            </ul>
        @endif
        <p class="body-text">
            The Employee agrees to perform all assigned responsibilities with professionalism, competence, diligence, integrity, and the level of skill reasonably expected of a {{ $position }}.
        </p>

        <p class="section-heading">Employment Status</p>
        <p class="body-text">The Employee shall initially be employed as a Probationary Employee.</p>
        <p class="body-text">
            The probationary period shall not exceed six (6) months, during which the Employee's performance, attendance, professionalism, work quality, and overall suitability for the position shall be evaluated.
        </p>
        <p class="body-text">
            Upon satisfactory completion of the probationary period and continued employment, the Employee shall become a Regular Employee in accordance with applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Place of Work</p>
        <p class="body-text">This is an office-based position.</p>
        <p class="body-text">
            The Employee shall report to the Employer's designated office during scheduled working days unless otherwise authorized by the Employer.
        </p>

        <p class="section-heading">Working Hours</p>
        <p class="body-text">The Employee shall render eight (8) working hours per day, exclusive of a one (1) hour unpaid meal break.</p>
        <p class="body-text">The regular work schedule shall be either:</p>
        <ul class="agreement-list">
            <li>9:00 AM – 6:00 PM; or</li>
            <li>10:00 AM – 7:00 PM,</li>
        </ul>
        <p class="body-text">as assigned by the Employer.</p>
        <p class="body-text">The regular workweek shall consist of five (5) working days.</p>
        <p class="body-text">
            Unless specifically authorized in writing by the Employer, overtime work is neither required nor deemed approved. The Employer may reasonably adjust the Employee's work schedule based on operational requirements, provided that such adjustment complies with applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Compensation</p>
        <p class="body-text">
            The Employee shall receive compensation and benefits as determined by the Employer and acknowledged by the Employee upon hiring or upon any subsequent adjustment. Such compensation shall be reflected in the Employer's official payroll records and shall be subject to applicable taxes and mandatory government deductions. The Employer's official payroll records
        </p>

        @include('user.employee-documents.partials.contract-page-footer', ['pageNumber' => 2])
    </div>

    <div class="agreement-page document-page-shell">
        @include('user.employee-documents.partials.document-page-letterhead')
        <p class="body-text">
            and payslips shall serve as the official record of the Employee's compensation and payment history.
        </p>

        <p class="section-heading">Salary Review</p>
        <p class="body-text">
            The Employer may review and adjust the Employee's compensation from time to time based on performance, responsibilities, qualifications, market conditions, and business requirements.
        </p>
        <p class="body-text">
            Nothing in this Agreement shall be construed as guaranteeing any salary increase, bonus, incentive, or adjustment unless expressly approved by the Employer.
        </p>

        <p class="section-heading">Government Benefits</p>
        <p class="body-text">
            The Employee shall receive mandatory government benefits, including SSS, PhilHealth, and Pag-IBIG, as required by applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">13th Month Pay</p>
        <p class="body-text">
            The Employee shall be entitled to a 13th Month Pay in accordance with applicable laws and regulations of the Republic of the Philippines.
        </p>
        <p class="body-text">
            The 13th Month Pay shall be released on or before the month of December of each calendar year unless an earlier release date is determined by the Employer.
        </p>
        <p class="body-text">
            All employees, regardless of employment status, who have rendered at least one (1) month of service during the calendar year shall be entitled to a prorated 13th Month Pay.
        </p>
        <p class="body-text">
            The amount shall be computed based on one-twelfth (1/12) of the Employee's total basic salary earned during the calendar year, excluding overtime pay, premium pay, allowances, and other non-basic salary benefits.
        </p>

        <p class="section-heading">Leave Benefits</p>
        <p class="body-text">The Employee shall be entitled to the following leave benefits:</p>

        <p class="section-heading">Service Incentive Leave</p>
        <p class="body-text">Five (5) days Service Incentive Leave (SIL) for every year of service.</p>

        <p class="section-heading">Company Sick Leave</p>
        <p class="body-text">
            Five (5) additional paid Sick Leave days per calendar year, which may, at the Employee's option, be used as additional paid personal leave.
        </p>
        <p class="body-text">
            Unused Company Sick Leave shall not be convertible to cash and shall not be carried over to the succeeding calendar year unless otherwise approved by the Employer.
        </p>

        @include('user.employee-documents.partials.contract-page-footer', ['pageNumber' => 3])
    </div>

    <div class="agreement-page document-page-shell">
        @include('user.employee-documents.partials.document-page-letterhead')
        <p class="section-heading">Maternity Benefits</p>
        <p class="body-text">Qualified female employees shall be entitled to maternity leave benefits in accordance with applicable laws and regulations of the Republic of the Philippines, including:</p>
        <ul class="agreement-list">
            <li>One hundred five (105) days of paid maternity leave for live childbirth, regardless of the method of delivery;</li>
            <li>An additional fifteen (15) days of paid leave for qualified solo mothers, where applicable; and</li>
            <li>Sixty (60) days of paid maternity leave for miscarriage or emergency termination of pregnancy.</li>
        </ul>
        <p class="body-text">
            Eligibility, entitlement, and payment of maternity benefits shall be subject to the applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Paternity Benefits</p>
        <p class="body-text">
            Qualified legally married male employees shall be entitled to seven (7) days of paid paternity leave for the first four deliveries of their lawful spouse, subject to applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Solo Parent Leave</p>
        <p class="body-text">
            Qualified employees who are certified as solo parents shall be entitled to seven (7) working days of paid parental leave per year, subject to applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Leave for Victims of Violence Against Women and Their Children (VAWC)</p>
        <p class="body-text">
            Qualified female employees shall be entitled to up to ten (10) days of paid leave, subject to applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Special Leave for Women</p>
        <p class="body-text">
            Qualified female employees shall be entitled to special leave benefits for surgery due to gynecological disorders, subject to applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Other Statutory Leave Benefits</p>
        <p class="body-text">
            The Employee shall likewise be entitled to any other mandatory leave benefits that are now existing or may hereafter be provided under applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Holiday Pay</p>
        <p class="body-text">
            Employees required by the Employer to work on Philippine holidays shall receive holiday compensation in accordance with applicable laws and regulations of the Republic of the Philippines, including:
        </p>
        <ul class="agreement-list">
            <li><strong>Regular Holidays:</strong> The Employee shall receive their regular daily wage plus an additional one hundred percent (100%) of their regular daily wage for work performed on a Regular Holiday.</li>
            <li><strong>Special Non-Working Holidays:</strong> The Employee shall receive an additional thirty percent (30%) of their regular daily wage for work performed on a Special Non-Working Holiday.</li>
        </ul>

        @include('user.employee-documents.partials.contract-page-footer', ['pageNumber' => 4])
    </div>

    <div class="agreement-page document-page-shell">
        @include('user.employee-documents.partials.document-page-letterhead')
        <p class="body-text">
            If applicable laws require higher compensation, the Employer shall provide such higher compensation. Employees shall likewise receive any holiday pay or premium pay required under applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">No Work, No Pay</p>
        <p class="body-text">Except as otherwise provided by law or this Agreement, compensation shall be based on actual work performed.</p>
        <p class="body-text">
            Unauthorized absences, unpaid leave, or days not worked shall be subject to the No Work, No Pay principle.
        </p>

        <p class="section-heading">Professional Standards</p>
        <p class="body-text">
            The Employee shall perform assigned duties using industry best practices and maintain a high standard of professionalism, quality, security, and reliability.
        </p>
        <p class="body-text">
            The Employer reserves the right to conduct code reviews, quality assurance reviews, testing, and technical evaluations to ensure compliance with company and client standards.
        </p>
        <p class="body-text">
            The Employee agrees to cooperate with reasonable revisions, improvements, and code refactoring when necessary. The Employee shall exercise due care and diligence in the performance of assigned duties and shall be accountable for work product submitted in the ordinary course of employment.
        </p>

        <p class="section-heading">Data Privacy and Cybersecurity</p>
        <p class="body-text">
            The Employee shall exercise reasonable care in protecting company information, client data, source code, credentials, databases, documentation, and other proprietary information from unauthorized access, disclosure, copying, modification, or destruction.
        </p>
        <p class="body-text">The Employee shall immediately report any suspected data breach or cybersecurity incident.</p>
        <p class="body-text">
            The Employee shall immediately notify the Employer of any loss, unauthorized disclosure, or suspected compromise of company information, credentials, or devices.
        </p>
        <p class="body-text">
            The Employee acknowledges that a separate Non-Disclosure Agreement (NDA) and Confidentiality Agreement shall be executed and shall form part of the Employee's obligations.
        </p>

        <p class="section-heading">Company Property and Intellectual Property</p>
        <p class="body-text">
            All software applications, source code, repositories, databases, APIs, documentation, designs, scripts, AI-generated code, technical specifications, inventions, improvements, and other work products created, developed, or modified during the Employee's employment shall remain the exclusive property of the Employer.
        </p>
        <p class="body-text">
            Such ownership shall apply regardless of whether the work was created using company-owned equipment or personally owned devices. All usernames, passwords, API keys, access credentials, repositories, and digital assets created or maintained for company business shall remain the exclusive property of the Employer and shall be surrendered upon request or separation from employment.
        </p>

        @include('user.employee-documents.partials.contract-page-footer', ['pageNumber' => 5])
    </div>

    <div class="agreement-page document-page-shell">
        @include('user.employee-documents.partials.document-page-letterhead')
        <p class="body-text">
            The Employee shall not claim ownership over any company work product and agrees to execute any documents necessary to confirm the Employer's ownership rights.
        </p>

        <p class="section-heading">Confidentiality and Legal Remedies</p>
        <p class="body-text">
            The Employee acknowledges that all source code, repositories, software applications, databases, APIs, technical documentation, client information, credentials, business information, and proprietary materials are confidential and are valuable assets of the Employer.
        </p>
        <p class="body-text">
            The Employee agrees not to copy, retain, reproduce, disclose, distribute, upload, transmit, sell, or otherwise make available any confidential information except as authorized by the Employer in the performance of official duties. The Employee shall not upload company source code, client data, or confidential information to personal repositories, personal cloud storage, or unauthorized third-party services without the Employer's prior written consent.
        </p>
        <p class="body-text">
            Any intentional or unauthorized disclosure, copying, theft, misuse, retention, or distribution of confidential information or company-owned source code shall constitute a material breach of this Agreement and may result in disciplinary action, termination of employment, civil claims for damages, injunctive relief, recovery of attorney's fees and litigation costs, and criminal complaints as may be permitted under applicable laws and regulations of the Republic of the Philippines.
        </p>
        <p class="body-text">
            These obligations shall survive resignation, termination, or separation from employment.
        </p>

        <p class="section-heading">Resignation and Exit Clearance</p>
        <p class="body-text">
            The Employee agrees to provide at least one (1) month or thirty (30) calendar days written notice prior to resignation unless otherwise agreed by the Employer.
        </p>
        <p class="body-text">
            Upon separation, the Employee shall complete the Employer's clearance process and immediately return all company property.
        </p>
        <p class="body-text">
            All source code, repositories, databases, documentation, project files, and work products created during employment shall remain the exclusive property of the Employer.
        </p>
        <p class="body-text">
            If the Employee has used a personally owned computer or device for company work, the Employee agrees, upon request of the Employer, to permanently remove and delete all company-owned projects, source code, databases, credentials, documentation, backups, and proprietary files from such personal device.
        </p>
        <p class="body-text">
            The Employer may require the Employee to certify in writing that no company data or source code remains in the Employee's possession or control.
        </p>

        <p class="section-heading">Governing Law</p>
        <p class="body-text">
            This Agreement shall be governed by and construed in accordance with the applicable laws and regulations of the Republic of the Philippines.
        </p>

        <p class="section-heading">Amendments</p>
        <p class="body-text">
            The Employer reserves the right to amend, revise, or update its policies, procedures, manuals, and operational guidelines from time to time as necessary for business operations or compliance with applicable laws and regulations of the Republic of the Philippines. The
        </p>

        @include('user.employee-documents.partials.contract-page-footer', ['pageNumber' => 6])
    </div>

    <div class="agreement-page document-page-shell">
        @include('user.employee-documents.partials.document-page-letterhead')
        <p class="body-text">
            Employee agrees to comply with such policies upon receipt or publication thereof, provided that no amendment shall diminish rights guaranteed under this Agreement or by applicable law.
        </p>

        <p class="section-heading">Entire Agreement</p>
        <p class="body-text">
            This Agreement constitutes the entire understanding between the Parties regarding the Employee's employment and supersedes all prior verbal or written agreements relating thereto.
        </p>
        <p class="body-text">
            The Employee further acknowledges that separate Company Policies, a Non-Disclosure Agreement (NDA), Information Technology Policies, Employee Handbook, and other operational guidelines may be issued by the Employer from time to time and shall form part of the Employee's obligations upon receipt and acknowledgment thereof.
        </p>

        <p class="section-heading">Employee Acknowledgment</p>
        <p class="body-text">
            The Employee acknowledges that they have carefully read this Agreement, fully understand its contents, have been given the opportunity to ask questions or seek independent advice, and voluntarily accept all of its terms and conditions.
        </p>
        <p class="body-text">
            The Employee further agrees to comply with all lawful policies, procedures, and directives issued by the Employer in connection with their employment.
        </p>

        <div class="agreement-sign-block">
            <p class="agreement-sign-heading">Employer</p>
            <p class="agreement-sign-row"><span class="agreement-sign-label">Name:</span> {{ $employerName }}</p>
            <p class="agreement-sign-row"><span class="agreement-sign-label">Position:</span> {{ $employerPosition }}</p>
            <p class="agreement-sign-row"><span class="agreement-sign-label">Signature:</span></p>
        </div>

        <div class="agreement-sign-block">
            <p class="agreement-sign-heading">Employee</p>
            <p class="agreement-sign-row"><span class="agreement-sign-label">Name:</span> @if($hasEmployeeName){{ $employeeName }}@endif</p>
            <p class="agreement-sign-row agreement-sign-row-signature"><span class="agreement-sign-label">Signature:</span>@if(!empty($eSignatureDataUri)) <img src="{{ $eSignatureDataUri }}" alt="E-Signature" class="agreement-signature-image">@elseif(!empty($signaturePlaceholder)) {{ $signaturePlaceholder }}@endif</p>
        </div>

        @include('user.employee-documents.partials.contract-page-footer', ['pageNumber' => 7])
    </div>
</div>
