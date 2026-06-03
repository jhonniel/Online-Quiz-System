<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Layout matches kanidaw.docx.html (pdf24): 49.66667em × 70.16666em page with background image + absolute text layers.
        $body = <<<'HTML'
<div class="coe-kanidaw-page" style="position:relative;width:49.66667em;height:70.16666em;margin:0 auto;font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:12px;color:#000;line-height:1.22;overflow:hidden;">
    <img src="{{coe_full_document_img}}" alt="" style="position:absolute;left:0;top:0;width:100%;height:100%;object-fit:fill;z-index:0;">

    <div style="position:absolute;left:12.0875em;top:11.5943em;z-index:1;white-space:nowrap;font-size:0.666667em;">{{mailing_address}}&nbsp;</div>

    <div style="position:absolute;left:13.362em;top:21.6916em;z-index:2;white-space:nowrap;font-size:1.5em;font-weight:bold;text-transform:uppercase;text-decoration:underline;">{{employee_name_upper}}&nbsp;</div>

    <div style="position:absolute;left:18.2808em;top:28.2877em;z-index:2;white-space:nowrap;font-size:0.916667em;font-weight:bold;text-transform:uppercase;">{{company_name}}&nbsp;</div>

    <div style="position:absolute;left:19.5701em;top:32.8653em;z-index:2;white-space:nowrap;font-size:0.916667em;font-weight:bold;text-transform:uppercase;">{{job_position}}&nbsp;</div>

    <div style="position:absolute;left:18.8315em;top:33.9859em;z-index:2;white-space:nowrap;font-size:1em;">From {{employment_start}} up to {{employment_end}}&nbsp;</div>

    <div style="position:absolute;left:15.6172em;top:41.2084em;z-index:2;white-space:nowrap;font-size:1em;font-weight:bold;">Given this {{date_today_formal}} at {{issue_city}}&nbsp;</div>

    <div style="position:absolute;left:21.0707em;top:46.0912em;z-index:2;white-space:nowrap;font-size:1em;font-weight:bold;">{{signatory_name}}&nbsp;</div>

    <div style="position:absolute;left:22.0783em;top:47.3119em;z-index:2;white-space:nowrap;font-size:1em;">{{signatory_title}}&nbsp;</div>

    <div style="position:absolute;left:17.83em;top:48.5326em;z-index:2;white-space:nowrap;font-size:1em;font-weight:bold;text-transform:uppercase;">{{company_name}}&nbsp;</div>

    <div style="position:absolute;left:6em;top:53.4196em;z-index:2;white-space:nowrap;font-size:0.833333em;">Office Contact No.: {{office_contact}}&nbsp;</div>

    <div style="position:absolute;left:6em;top:54.4369em;z-index:2;white-space:nowrap;font-size:0.833333em;max-width:38em;">Mailing Address: {{mailing_address}}&nbsp;</div>
</div>
HTML;

        DB::table('employee_file_templates')
            ->where('slug', 'certificate-of-employment')
            ->update([
                'body' => $body,
                'description' => 'Matches kanidaw.docx.html: full-page certificate image with pdf24-style text overlays.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
