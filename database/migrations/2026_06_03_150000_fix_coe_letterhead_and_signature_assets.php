<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $body = DB::table('employee_file_templates')
            ->where('slug', 'certificate-of-employment')
            ->value('body');

        if (! is_string($body) || $body === '') {
            return;
        }

        // Tighter title spacing now that letterhead no longer includes the document title.
        $body = str_replace(
            'margin:18px 0 24px;',
            'margin:8px 0 20px;',
            $body
        );

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
