<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $body = <<<'HTML'
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
HTML;

        $customFields = json_encode([
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
        ]);

        DB::table('employee_file_templates')
            ->where('slug', 'certificate-of-employment')
            ->update([
                'body' => $body,
                'custom_fields' => $customFields,
                'description' => 'Formal certificate of employment with company letterhead, matching the standard COE layout.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Previous simplified template restored only if needed manually.
    }
};
