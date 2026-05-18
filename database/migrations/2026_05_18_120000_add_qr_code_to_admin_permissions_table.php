<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->boolean('qr_code')->default(false)->after('system');
        });

        // Previously, any user with an admin_permissions row could be scanned; keep that behavior.
        DB::table('admin_permissions')->update(['qr_code' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->dropColumn('qr_code');
        });
    }
};
