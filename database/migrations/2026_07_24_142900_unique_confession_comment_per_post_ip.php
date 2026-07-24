<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One comment per session identity (codename) per post — shared IPs can still each comment once.
        $duplicates = DB::table('confession_comments')
            ->select('confession_post_id', 'codename', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('codename')
            ->where('codename', '!=', '')
            ->groupBy('confession_post_id', 'codename')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $row) {
            DB::table('confession_comments')
                ->where('confession_post_id', $row->confession_post_id)
                ->where('codename', $row->codename)
                ->where('id', '!=', $row->keep_id)
                ->delete();
        }

        Schema::table('confession_comments', function (Blueprint $table) {
            $table->unique(['confession_post_id', 'codename'], 'confession_comments_post_codename_unique');
        });
    }

    public function down(): void
    {
        Schema::table('confession_comments', function (Blueprint $table) {
            $table->dropUnique('confession_comments_post_codename_unique');
        });
    }
};
