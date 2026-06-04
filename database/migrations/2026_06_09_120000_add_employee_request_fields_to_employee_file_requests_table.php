<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_file_requests')) {
            return;
        }

        Schema::table('employee_file_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_file_requests', 'status')) {
                $table->string('status', 20)->default('fulfilled')->after('title');
            }
            if (! Schema::hasColumn('employee_file_requests', 'request_type')) {
                $table->string('request_type', 50)->nullable()->after('status');
            }
            if (! Schema::hasColumn('employee_file_requests', 'employee_notes')) {
                $table->text('employee_notes')->nullable()->after('request_type');
            }
            if (! Schema::hasColumn('employee_file_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('employee_notes');
            }
            if (! Schema::hasColumn('employee_file_requests', 'fulfilled_at')) {
                $table->timestamp('fulfilled_at')->nullable()->after('admin_notes');
            }
        });

        DB::table('employee_file_requests')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update([
                'status' => 'fulfilled',
                'fulfilled_at' => DB::raw('COALESCE(fulfilled_at, updated_at, created_at)'),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('employee_file_requests')) {
            return;
        }

        Schema::table('employee_file_requests', function (Blueprint $table) {
            $columns = ['status', 'request_type', 'employee_notes', 'admin_notes', 'fulfilled_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('employee_file_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
