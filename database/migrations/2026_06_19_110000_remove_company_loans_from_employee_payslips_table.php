<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('employee_payslips', 'company_loans')) {
            return;
        }

        Schema::table('employee_payslips', function (Blueprint $table) {
            $table->dropColumn('company_loans');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_payslips', 'company_loans')) {
            return;
        }

        Schema::table('employee_payslips', function (Blueprint $table) {
            $table->decimal('company_loans', 12, 2)->default(0);
        });
    }
};
