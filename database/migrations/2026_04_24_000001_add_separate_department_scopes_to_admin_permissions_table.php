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
            $table->json('allowed_employee_departments')->nullable();
            $table->json('allowed_student_departments')->nullable();
        });

        // Backfill existing shared scopes into both new columns.
        DB::table('admin_permissions')
            ->whereNotNull('allowed_departments')
            ->update([
                'allowed_employee_departments' => DB::raw('allowed_departments'),
                'allowed_student_departments' => DB::raw('allowed_departments'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_permissions', function (Blueprint $table) {
            $table->dropColumn(['allowed_employee_departments', 'allowed_student_departments']);
        });
    }
};
