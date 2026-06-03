<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $body = <<<'HTML'
<div style="background:#ffffff;font-family:DejaVu Sans,Arial,Helvetica,sans-serif;color:#000000;">
    {{coe_letterhead_img}}

    <h1 style="text-align:center;font-family:DejaVu Serif,Georgia,serif;font-size:28px;font-weight:bold;color:#2F5564;margin:18px 0 24px;text-transform:uppercase;letter-spacing:0.5px;line-height:1.2;">Certificate of Employment</h1>

    <div style="text-align:center;margin-bottom:22px;">
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:12px;margin:0 0 8px;line-height:1.5;">This is to certify that</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:19px;font-weight:bold;text-transform:uppercase;text-decoration:underline;margin:0 0 8px;line-height:1.3;">{{employee_name_upper}}</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:12px;margin:0 0 3px;line-height:1.5;">Currently employed</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:12px;margin:0 0 3px;line-height:1.5;">in</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:13px;font-weight:bold;text-transform:uppercase;margin:0 0 6px;line-height:1.4;">{{company_name}}</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:12px;margin:0 0 3px;line-height:1.5;">as</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:13px;font-weight:bold;text-transform:uppercase;margin:0 0 6px;line-height:1.4;">{{job_position}}</p>
        <p style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:12px;margin:0;line-height:1.5;">From {{employment_start}} up to {{employment_end}}</p>
    </div>

    <p style="text-align:center;font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:11px;line-height:1.65;margin:0 auto 18px;max-width:480px;">
        This certification is being issued upon request of aforementioned name for whatever lawful purpose it may serve his best.
    </p>

    <p style="text-align:center;font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;margin:0 0 30px;line-height:1.5;">
        Given this {{date_today_formal}} at {{issue_city}}
    </p>

    <div style="text-align:center;margin-bottom:32px;">
        {{signatory_signature_img}}
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-weight:bold;font-size:12px;margin:6px 0 2px;line-height:1.4;">{{signatory_name}}</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:11px;margin:0;line-height:1.4;">{{signatory_title}}</p>
        <p style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:11px;margin:4px 0 0;line-height:1.4;text-transform:uppercase;">{{company_name}}</p>
    </div>

    <div style="border-top:1px solid #A6D9EB;height:0;margin:0 0 8px;"></div>
    <p style="text-align:left;font-size:9px;margin:2px 0;line-height:1.4;">Office Contact No.: {{office_contact}}</p>
    <p style="text-align:left;font-size:9px;margin:2px 0;line-height:1.45;">Mailing Address: {{mailing_address}}</p>
</div>
HTML;

        DB::table('employee_file_templates')
            ->where('slug', 'certificate-of-employment')
            ->update([
                'body' => $body,
                'description' => 'MC Monde certificate of employment — letterhead image with HTML body (matches official layout).',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
