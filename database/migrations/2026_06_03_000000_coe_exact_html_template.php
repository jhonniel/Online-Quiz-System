<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Matches COE-MC_MONDE_with_logo.html: container + full certificate image with dynamic text overlays.
        $body = <<<'HTML'
<div style="position:relative;width:100%;">
    <img src="{{coe_full_document_img}}" alt="Certificate of Employment" style="width:100%;height:auto;display:block;">

    <div style="position:absolute;left:5%;right:5%;top:28.2%;height:4%;background:#ffffff;text-align:center;overflow:hidden;">
        <span style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:17px;font-weight:bold;text-transform:uppercase;text-decoration:underline;line-height:1.4;">{{employee_name_upper}}</span>
    </div>

    <div style="position:absolute;left:5%;right:5%;top:40.8%;height:2.6%;background:#ffffff;text-align:center;overflow:hidden;">
        <span style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;text-transform:uppercase;line-height:1.4;">{{job_position}}</span>
    </div>

    <div style="position:absolute;left:5%;right:5%;top:43.4%;height:2.4%;background:#ffffff;text-align:center;overflow:hidden;">
        <span style="font-family:DejaVu Serif,Georgia,serif;font-style:italic;font-size:11px;line-height:1.4;">From {{employment_start}} up to {{employment_end}}</span>
    </div>

    <div style="position:absolute;left:5%;right:5%;top:52.6%;height:2.6%;background:#ffffff;text-align:center;overflow:hidden;">
        <span style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:10px;font-weight:bold;line-height:1.4;">Given this {{date_today_formal}} at {{issue_city}}</span>
    </div>

    <div style="position:absolute;left:5%;right:5%;top:71.6%;height:2.2%;background:#ffffff;text-align:center;overflow:hidden;">
        <span style="font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:11px;font-weight:bold;line-height:1.4;">{{signatory_name}}</span>
    </div>
</div>
HTML;

        DB::table('employee_file_templates')
            ->where('slug', 'certificate-of-employment')
            ->update([
                'body' => $body,
                'description' => 'Exact copy of COE-MC_MONDE_with_logo.html with dynamic employee fields overlaid on the official certificate image.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
