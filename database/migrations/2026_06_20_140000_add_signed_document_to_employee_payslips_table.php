<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_payslips', 'signed_at')) {
                $table->timestamp('signed_at')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('employee_payslips', 'signed_document_path')) {
                $table->string('signed_document_path')->nullable()->after('signed_at');
            }
            if (! Schema::hasColumn('employee_payslips', 'storage_disk')) {
                $table->string('storage_disk')->nullable()->after('signed_document_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            if (Schema::hasColumn('employee_payslips', 'storage_disk')) {
                $table->dropColumn('storage_disk');
            }
            if (Schema::hasColumn('employee_payslips', 'signed_document_path')) {
                $table->dropColumn('signed_document_path');
            }
            if (Schema::hasColumn('employee_payslips', 'signed_at')) {
                $table->dropColumn('signed_at');
            }
        });
    }
};
