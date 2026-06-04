<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_file_requests')) {
            return;
        }

        Schema::table('employee_file_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_file_requests', 'storage_disk')) {
                $table->string('storage_disk', 32)->nullable()->after('pdf_path');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_file_requests', 'storage_disk')) {
            Schema::table('employee_file_requests', function (Blueprint $table) {
                $table->dropColumn('storage_disk');
            });
        }
    }
};
