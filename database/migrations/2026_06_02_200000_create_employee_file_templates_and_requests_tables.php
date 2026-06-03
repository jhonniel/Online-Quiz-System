<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_file_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 50)->default('letter');
            $table->text('description')->nullable();
            $table->longText('body');
            $table->json('custom_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('employee_file_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_file_template_id')->constrained('employee_file_templates')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->json('field_values')->nullable();
            $table->longText('rendered_html')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        $now = now();

        $templates = [
            [
                'name' => 'Certificate of Employment',
                'slug' => 'certificate-of-employment',
                'category' => 'certificate',
                'description' => 'Formal certificate of employment with company letterhead, matching the standard COE layout.',
                'body' => <<<'HTML'
<div style="text-align:center;font-family:DejaVu Sans,sans-serif;color:#111827;">
    <div style="margin-bottom:8px;">{{system_logo_img}}</div>
    <p style="font-size:11px;margin:6px 0 2px;color:#374151;">Owned and operated by: {{company_operated_by}}</p>
    <p style="font-size:11px;margin:0 0 14px;color:#374151;line-height:1.5;">{{header_address}}</p>
    <div style="border-top:2px solid #7ec8e3;margin:0 0 22px;"></div>

    <h1 style="color:#154360;font-size:24px;font-weight:bold;letter-spacing:1.5px;margin:0 0 28px;text-transform:uppercase;">Certificate of Employment</h1>

    <div style="font-size:13px;line-height:2.1;margin-bottom:24px;">
        <span style="font-style:italic;">This is to certify that</span><br>
        <strong style="font-size:18px;text-decoration:underline;text-transform:uppercase;display:inline-block;margin:4px 0;">{{employee_name_upper}}</strong><br>
        <span style="font-style:italic;">Currently employed</span><br>
        <span style="font-style:italic;">in</span><br>
        <strong style="text-transform:uppercase;">{{company_name}}</strong><br>
        <span style="font-style:italic;">as</span><br>
        <strong style="text-transform:uppercase;">{{job_position}}</strong><br>
        <span style="font-style:italic;">From {{employment_start}} up to {{employment_end}}</span>
    </div>

    <p style="font-size:12px;line-height:1.8;margin:0 auto 22px;max-width:520px;">
        This certification is being issued upon request of aforementioned name for whatever lawful purpose it may serve his best.
    </p>

    <p style="font-size:12px;margin-bottom:36px;">
        Given this <strong>{{date_today_formal}}</strong> at <strong>{{issue_city}}</strong>
    </p>

    <div style="margin-top:12px;">
        {{signatory_signature_img}}
        <p style="font-weight:bold;font-size:13px;margin:10px 0 2px;">{{signatory_name}}</p>
        <p style="font-size:12px;margin:0;color:#374151;">{{signatory_title}}</p>
        <p style="font-size:12px;font-weight:bold;margin:6px 0 0;text-transform:uppercase;">{{company_name}}</p>
    </div>

    <div style="border-top:2px solid #7ec8e3;margin:48px 0 14px;"></div>
    <p style="font-size:11px;margin:4px 0;color:#374151;">Office Contact No.: {{office_contact}}</p>
    <p style="font-size:11px;margin:4px 0;color:#374151;">Mailing Address: {{mailing_address}}</p>
</div>
HTML,
                'custom_fields' => json_encode([
                    ['key' => 'company_operated_by', 'label' => 'Owned and Operated By', 'default' => ''],
                    ['key' => 'header_address', 'label' => 'Header Address', 'default' => ''],
                    ['key' => 'company_name', 'label' => 'Company Name (Legal)', 'default' => ''],
                    ['key' => 'job_position', 'label' => 'Job Position / Designation', 'default' => ''],
                    ['key' => 'employment_start', 'label' => 'Employment Start (e.g. JULY 2025)', 'default' => ''],
                    ['key' => 'employment_end', 'label' => 'Employment End (e.g. present)', 'default' => 'present'],
                    ['key' => 'issue_city', 'label' => 'Issue City', 'default' => 'Davao City'],
                    ['key' => 'signatory_name', 'label' => 'Signatory Name', 'default' => ''],
                    ['key' => 'signatory_title', 'label' => 'Signatory Title', 'default' => 'Founder/CEO'],
                    ['key' => 'signatory_signature_img', 'label' => 'Signatory Signature (image URL, optional)', 'default' => ''],
                    ['key' => 'office_contact', 'label' => 'Office Contact Number', 'default' => ''],
                    ['key' => 'mailing_address', 'label' => 'Mailing Address', 'default' => ''],
                ]),
                'sort_order' => 1,
            ],
            [
                'name' => 'Certificate of Compensation',
                'slug' => 'certificate-of-compensation',
                'category' => 'certificate',
                'description' => 'Summarizes an employee\'s basic compensation for official use.',
                'body' => <<<'HTML'
<div style="text-align:center;margin-bottom:24px;">
    <h2 style="margin:0;font-size:20px;letter-spacing:1px;">CERTIFICATE OF COMPENSATION</h2>
</div>
<p style="text-align:justify;line-height:1.7;">
    This is to certify that <strong>{{employee_name}}</strong> is presently employed with
    <strong>{{system_name}}</strong> as <strong>{{employee_role}}</strong> and receives a monthly basic compensation of
    <strong>{{monthly_salary}}</strong>, exclusive of allowances and other benefits.
</p>
<p style="text-align:justify;line-height:1.7;">
    This certificate is issued upon request of the employee for <strong>{{purpose}}</strong>.
</p>
<p style="margin-top:32px;">Given this {{date_today}} at {{system_name}}.</p>
<p style="margin-top:48px;">
    _________________________________<br>
    HR / Authorized Signatory
</p>
HTML,
                'custom_fields' => json_encode([
                    ['key' => 'monthly_salary', 'label' => 'Monthly Basic Salary', 'default' => ''],
                    ['key' => 'purpose', 'label' => 'Purpose', 'default' => 'whatever legal purpose it may serve'],
                ]),
                'sort_order' => 2,
            ],
            [
                'name' => 'Payslip',
                'slug' => 'payslip',
                'category' => 'payslip',
                'description' => 'Standard payslip layout for an employee pay period.',
                'body' => <<<'HTML'
<div style="text-align:center;margin-bottom:20px;">
    <h2 style="margin:0;font-size:18px;">PAYSLIP</h2>
    <p style="margin:4px 0;color:#555;">{{system_name}}</p>
    <p style="margin:0;font-size:12px;">Pay Period: <strong>{{pay_period}}</strong></p>
</div>
<table style="width:100%;border-collapse:collapse;margin-bottom:16px;font-size:12px;">
    <tr><td style="padding:4px 0;"><strong>Employee:</strong></td><td>{{employee_name}}</td></tr>
    <tr><td style="padding:4px 0;"><strong>Department:</strong></td><td>{{employee_department}}</td></tr>
    <tr><td style="padding:4px 0;"><strong>Email:</strong></td><td>{{employee_email}}</td></tr>
</table>
<table style="width:100%;border-collapse:collapse;font-size:12px;">
    <thead>
        <tr style="background:#f3f4f6;">
            <th style="border:1px solid #ddd;padding:6px;text-align:left;">Earnings</th>
            <th style="border:1px solid #ddd;padding:6px;text-align:right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr><td style="border:1px solid #ddd;padding:6px;">Basic Pay</td><td style="border:1px solid #ddd;padding:6px;text-align:right;">{{basic_pay}}</td></tr>
        <tr><td style="border:1px solid #ddd;padding:6px;">Allowances</td><td style="border:1px solid #ddd;padding:6px;text-align:right;">{{allowances}}</td></tr>
        <tr><td style="border:1px solid #ddd;padding:6px;">Overtime / Other</td><td style="border:1px solid #ddd;padding:6px;text-align:right;">{{overtime_pay}}</td></tr>
    </tbody>
    <thead>
        <tr style="background:#f3f4f6;">
            <th style="border:1px solid #ddd;padding:6px;text-align:left;">Deductions</th>
            <th style="border:1px solid #ddd;padding:6px;text-align:right;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr><td style="border:1px solid #ddd;padding:6px;">Tax / Withholding</td><td style="border:1px solid #ddd;padding:6px;text-align:right;">{{tax_deduction}}</td></tr>
        <tr><td style="border:1px solid #ddd;padding:6px;">Other Deductions</td><td style="border:1px solid #ddd;padding:6px;text-align:right;">{{other_deductions}}</td></tr>
    </tbody>
</table>
<p style="margin-top:16px;font-size:13px;"><strong>Net Pay:</strong> {{net_pay}}</p>
<p style="margin-top:24px;font-size:11px;color:#666;">Generated on {{date_today}}. This is a system-generated payslip.</p>
HTML,
                'custom_fields' => json_encode([
                    ['key' => 'pay_period', 'label' => 'Pay Period', 'default' => ''],
                    ['key' => 'basic_pay', 'label' => 'Basic Pay', 'default' => '0.00'],
                    ['key' => 'allowances', 'label' => 'Allowances', 'default' => '0.00'],
                    ['key' => 'overtime_pay', 'label' => 'Overtime / Other', 'default' => '0.00'],
                    ['key' => 'tax_deduction', 'label' => 'Tax / Withholding', 'default' => '0.00'],
                    ['key' => 'other_deductions', 'label' => 'Other Deductions', 'default' => '0.00'],
                    ['key' => 'net_pay', 'label' => 'Net Pay', 'default' => '0.00'],
                ]),
                'sort_order' => 3,
            ],
            [
                'name' => 'Endorsement Letter',
                'slug' => 'endorsement-letter',
                'category' => 'letter',
                'description' => 'General endorsement letter for employees.',
                'body' => <<<'HTML'
<p>{{date_today}}</p>
<p style="margin-top:24px;">To Whom It May Concern:</p>
<p style="text-align:justify;line-height:1.7;margin-top:16px;">
    This letter serves to endorse <strong>{{employee_name}}</strong> of the
    <strong>{{employee_department}}</strong> department for <strong>{{endorsement_subject}}</strong>.
</p>
<p style="text-align:justify;line-height:1.7;">
    {{endorsement_body}}
</p>
<p style="margin-top:24px;">For your reference and appropriate action.</p>
<p style="margin-top:32px;">Respectfully yours,</p>
<p style="margin-top:40px;">
    _________________________________<br>
    {{signatory_name}}<br>
    {{signatory_title}}<br>
    {{system_name}}
</p>
HTML,
                'custom_fields' => json_encode([
                    ['key' => 'endorsement_subject', 'label' => 'Subject / Purpose', 'default' => ''],
                    ['key' => 'endorsement_body', 'label' => 'Letter Body Details', 'default' => ''],
                    ['key' => 'signatory_name', 'label' => 'Signatory Name', 'default' => ''],
                    ['key' => 'signatory_title', 'label' => 'Signatory Title', 'default' => 'Authorized Signatory'],
                ]),
                'sort_order' => 4,
            ],
            [
                'name' => 'General Letter',
                'slug' => 'general-letter',
                'category' => 'letter',
                'description' => 'Flexible letter template with custom recipient and body.',
                'body' => <<<'HTML'
<p>{{date_today}}</p>
<p style="margin-top:24px;">{{recipient_line}}</p>
<p style="margin-top:16px;">Dear {{salutation}},</p>
<p style="text-align:justify;line-height:1.7;margin-top:16px;white-space:pre-line;">{{letter_body}}</p>
<p style="margin-top:24px;">Sincerely,</p>
<p style="margin-top:40px;">
    _________________________________<br>
    {{signatory_name}}<br>
    {{signatory_title}}<br>
    {{system_name}}
</p>
HTML,
                'custom_fields' => json_encode([
                    ['key' => 'recipient_line', 'label' => 'Recipient (To Whom It May Concern, name, etc.)', 'default' => 'To Whom It May Concern:'],
                    ['key' => 'salutation', 'label' => 'Salutation', 'default' => 'Sir/Madam'],
                    ['key' => 'letter_body', 'label' => 'Letter Body', 'default' => ''],
                    ['key' => 'signatory_name', 'label' => 'Signatory Name', 'default' => ''],
                    ['key' => 'signatory_title', 'label' => 'Signatory Title', 'default' => 'Authorized Signatory'],
                ]),
                'sort_order' => 5,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('employee_file_templates')->insert(array_merge($template, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_file_requests');
        Schema::dropIfExists('employee_file_templates');
    }
};
