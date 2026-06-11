<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_announcements', function (Blueprint $table) {
            $table->string('feature_link_url', 500)->nullable()->after('content');
            $table->string('feature_link_label', 100)->nullable()->after('feature_link_url');
        });
    }

    public function down(): void
    {
        Schema::table('system_announcements', function (Blueprint $table) {
            $table->dropColumn(['feature_link_url', 'feature_link_label']);
        });
    }
};
