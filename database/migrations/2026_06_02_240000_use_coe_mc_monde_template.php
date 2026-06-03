<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $body = <<<'HTML'
<div style="max-width:900px;margin:0 auto;background:#ffffff;padding:20px;font-family:DejaVu Sans,Arial,Helvetica,sans-serif;color:#000000;">
    {{coe_letterhead_img}}

    <h1 style="text-align:center;font-family:DejaVu Serif,Georgia,serif;font-size:30px;font-weight:bold;color:#2F5564;margin:22px 0 28px;text-transform:uppercase;letter-spacing:0.5px;line-height:1.2;">Certificate of Employment</h1>

    <div style="text-align:center;margin-bottom:24px;">
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:13px;margin:0 0 10px;line-height:1.5;">This is to certify that</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:21px;font-weight:bold;text-transform:uppercase;text-decoration:underline;margin:0 0 10px;line-height:1.3;">{{employee_name_upper}}</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:13px;margin:0 0 4px;line-height:1.5;">Currently employed</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:13px;margin:0 0 4px;line-height:1.5;">in</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;text-transform:uppercase;margin:0 0 8px;line-height:1.4;">{{company_name}}</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:13px;margin:0 0 4px;line-height:1.5;">as</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;text-transform:uppercase;margin:0 0 8px;line-height:1.4;">{{job_position}}</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:13px;margin:0;line-height:1.5;">From {{employment_start}} up to {{employment_end}}</p>
    </div>

    <p style="text-align:center;font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:12px;line-height:1.65;margin:0 auto 20px;max-width:500px;">
        This certification is being issued upon request of aforementioned name for whatever lawful purpose it may serve his best.
    </p>

    <p style="text-align:center;font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:12px;font-weight:bold;margin:0 0 36px;line-height:1.5;">
        Given this {{date_today_formal}} at {{issue_city}}
    </p>

    <div style="text-align:center;margin-bottom:40px;">
        {{signatory_signature_img}}
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-weight:bold;font-size:13px;margin:6px 0 2px;line-height:1.4;">{{signatory_name}}</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:12px;margin:0;line-height:1.4;">{{signatory_title}}</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:12px;margin:4px 0 0;line-height:1.4;text-transform:uppercase;">{{company_name}}</p>
    </div>

    <div style="border-top:1px solid #A6D9EB;height:0;margin:0 0 10px;"></div>
    <p style="text-align:left;font-size:10px;margin:3px 0;line-height:1.4;">Office Contact No.: {{office_contact}}</p>
    <p style="text-align:left;font-size:10px;margin:3px 0;line-height:1.45;">Mailing Address: {{mailing_address}}</p>
</div>
HTML;

        $customFields = json_encode([
            ['key' => 'company_name', 'label' => 'Company Name (Legal)', 'default' => 'MINI CLEAN BUSINESS SOLUTIONS'],
            ['key' => 'job_position', 'label' => 'Job Position / Designation', 'default' => ''],
            ['key' => 'employment_start', 'label' => 'Employment Start (e.g. JULY 2025)', 'default' => ''],
            ['key' => 'employment_end', 'label' => 'Employment End (e.g. present)', 'default' => 'present'],
            ['key' => 'issue_city', 'label' => 'Issue City', 'default' => 'Davao City'],
            ['key' => 'signatory_name', 'label' => 'Signatory Name', 'default' => 'Nitish G. Khemani'],
            ['key' => 'signatory_title', 'label' => 'Signatory Title', 'default' => 'Founder/CEO'],
            ['key' => 'signatory_signature_img', 'label' => 'Signatory Signature (optional image URL/path)', 'default' => ''],
            ['key' => 'office_contact', 'label' => 'Office Contact Number', 'default' => '09954450819'],
            ['key' => 'mailing_address', 'label' => 'Mailing Address', 'default' => 'Dr 8 Unit 2 MS Land Complex, Mc Arthur Highway, Matina Crossing, Talomo Dist. Davao City'],
        ]);

        DB::table('employee_file_templates')
            ->where('slug', 'certificate-of-employment')
            ->update([
                'body' => $body,
                'custom_fields' => $customFields,
                'description' => 'MC Monde / Infosoft certificate of employment layout (COE-MC_MONDE_with_logo).',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
