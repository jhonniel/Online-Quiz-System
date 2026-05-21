<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->json('allowed_analytics_features')->nullable()->after('analytics_reports');
        });
    }

    public function down(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->dropColumn('allowed_analytics_features');
        });
    }
};
