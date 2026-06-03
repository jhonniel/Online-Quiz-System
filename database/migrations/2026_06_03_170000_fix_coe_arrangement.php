<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Full-page PNG already contains sample text; overlays caused duplicates. Use letterhead image + HTML body only.
        $body = <<<'HTML'
<div class="coe-document" style="background:#ffffff;font-family:DejaVu Sans,Arial,Helvetica,sans-serif;color:#000000;max-width:49.66667em;margin:0 auto;">
    {{coe_letterhead_img}}

    <h1 style="text-align:center;font-family:DejaVu Serif,Georgia,serif;font-size:22pt;font-weight:bold;color:#2F5564;margin:4px 0 16px;text-transform:uppercase;letter-spacing:0.5px;line-height:1.2;">Certificate of Employment</h1>

    <div style="text-align:center;margin-bottom:18px;">
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:11pt;margin:0 0 6px;line-height:1.5;">This is to certify that</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:16pt;font-weight:bold;text-transform:uppercase;text-decoration:underline;margin:0 0 8px;line-height:1.3;">{{employee_name_upper}}</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:11pt;margin:0 0 2px;line-height:1.5;">Currently employed</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:11pt;margin:0 0 2px;line-height:1.5;">in</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:11pt;font-weight:bold;text-transform:uppercase;margin:0 0 4px;line-height:1.4;">{{company_name}}</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:11pt;margin:0 0 2px;line-height:1.5;">as</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:11pt;font-weight:bold;text-transform:uppercase;margin:0 0 4px;line-height:1.4;">{{job_position}}</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:11pt;margin:0;line-height:1.5;">From {{employment_start}} up to {{employment_end}}</p>
    </div>

    <p style="text-align:center;font-size:10pt;line-height:1.6;margin:0 auto 14px;max-width:36em;">
        This certification is being issued upon request of aforementioned name for whatever lawful purpose it may serve his best.
    </p>

    <p style="text-align:center;font-size:10pt;font-weight:bold;margin:0 0 22px;line-height:1.5;">
        Given this {{date_today_formal}} at {{issue_city}}
    </p>

    <div style="text-align:center;margin-bottom:24px;">
        {{signatory_signature_img}}
        <p style="font-weight:bold;font-size:11pt;margin:4px 0 2px;line-height:1.4;">{{signatory_name}}</p>
        <p style="font-size:10pt;margin:0;line-height:1.4;">{{signatory_title}}</p>
        <p style="font-size:10pt;margin:4px 0 0;line-height:1.4;text-transform:uppercase;">{{company_name}}</p>
    </div>

    <div style="border-top:1px solid #A6D9EB;margin:0 0 8px;"></div>
    <p style="font-size:9pt;margin:2px 0;line-height:1.4;">Office Contact No.: {{office_contact}}</p>
    <p style="font-size:9pt;margin:2px 0;line-height:1.45;">Mailing Address: {{mailing_address}}</p>
</div>
HTML;

        DB::table('employee_file_templates')
            ->where('slug', 'certificate-of-employment')
            ->update([
                'body' => $body,
                'description' => 'MC Monde COE — letterhead graphic plus HTML body (no duplicate text overlays on full-page image).',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
