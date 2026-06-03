<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $body = <<<'HTML'
<div style="color:#000000;font-family:'DejaVu Sans',Arial,Helvetica,sans-serif;">
    <div style="text-align:center;padding-top:4px;">
        <div style="margin-bottom:6px;">{{system_logo_img}}</div>
        <p style="font-size:10px;margin:8px 0 2px;line-height:1.4;">Owned and operated by: {{company_operated_by}}</p>
        <p style="font-size:10px;margin:0 0 14px;line-height:1.45;">{{header_address}}</p>
        <div style="border-top:1px solid #A0D8E6;font-size:0;line-height:0;height:1px;">&nbsp;</div>
    </div>

    <h1 style="text-align:center;font-family:'DejaVu Serif',Georgia,'Times New Roman',serif;font-size:30px;font-weight:bold;color:#3A6D7E;margin:26px 0 30px;letter-spacing:0.4px;text-transform:uppercase;line-height:1.2;">Certificate of Employment</h1>

    <div style="text-align:center;margin-bottom:26px;">
        <p style="font-family:'DejaVu Serif',Georgia,'Times New Roman',serif;font-style:italic;font-size:13px;margin:0 0 10px;line-height:1.5;">This is to certify that</p>
        <p style="font-family:'DejaVu Sans',Arial,Helvetica,sans-serif;font-size:21px;font-weight:bold;text-transform:uppercase;text-decoration:underline;margin:0 0 10px;line-height:1.3;letter-spacing:0.2px;">{{employee_name_upper}}</p>
        <p style="font-family:'DejaVu Serif',Georgia,'Times New Roman',serif;font-style:italic;font-size:13px;margin:0 0 5px;line-height:1.5;">Currently employed</p>
        <p style="font-family:'DejaVu Serif',Georgia,'Times New Roman',serif;font-style:italic;font-size:13px;margin:0 0 5px;line-height:1.5;">in</p>
        <p style="font-family:'DejaVu Sans',Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;text-transform:uppercase;margin:0 0 8px;line-height:1.4;">{{company_name}}</p>
        <p style="font-family:'DejaVu Serif',Georgia,'Times New Roman',serif;font-style:italic;font-size:13px;margin:0 0 5px;line-height:1.5;">as</p>
        <p style="font-family:'DejaVu Sans',Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;text-transform:uppercase;margin:0 0 8px;line-height:1.4;">{{job_position}}</p>
        <p style="font-family:'DejaVu Serif',Georgia,'Times New Roman',serif;font-style:italic;font-size:13px;margin:0;line-height:1.5;">From {{employment_start}} up to {{employment_end}}</p>
    </div>

    <p style="text-align:center;font-family:'DejaVu Sans',Arial,Helvetica,sans-serif;font-size:12px;line-height:1.65;margin:0 auto 22px;max-width:490px;">
        This certification is being issued upon request of aforementioned name for whatever lawful purpose it may serve his best.
    </p>

    <p style="text-align:center;font-family:'DejaVu Sans',Arial,Helvetica,sans-serif;font-size:12px;font-weight:bold;margin:0 0 38px;line-height:1.5;">
        Given this {{date_today_formal}} at {{issue_city}}
    </p>

    <div style="text-align:center;margin-bottom:44px;">
        {{signatory_signature_img}}
        <p style="font-family:'DejaVu Sans',Arial,Helvetica,sans-serif;font-weight:bold;font-size:13px;margin:6px 0 2px;line-height:1.4;">{{signatory_name}}</p>
        <p style="font-family:'DejaVu Sans',Arial,Helvetica,sans-serif;font-size:12px;margin:0;line-height:1.4;">{{signatory_title}}</p>
        <p style="font-family:'DejaVu Sans',Arial,Helvetica,sans-serif;font-size:12px;margin:4px 0 0;line-height:1.4;text-transform:uppercase;">{{company_name}}</p>
    </div>

    <div style="border-top:1px solid #A0D8E6;font-size:0;line-height:0;height:1px;margin:0 0 10px;">&nbsp;</div>
    <p style="text-align:left;font-size:10px;margin:3px 0;line-height:1.4;">Office Contact No.: {{office_contact}}</p>
    <p style="text-align:left;font-size:10px;margin:3px 0;line-height:1.45;">Mailing Address: {{mailing_address}}</p>
</div>
HTML;

        DB::table('employee_file_templates')
            ->where('slug', 'certificate-of-employment')
            ->update([
                'body' => $body,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
