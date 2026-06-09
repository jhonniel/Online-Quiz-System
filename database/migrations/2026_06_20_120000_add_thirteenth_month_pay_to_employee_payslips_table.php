<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_payslips', 'thirteenth_month_pay')) {
                $table->decimal('thirteenth_month_pay', 12, 2)->default(0)->after('allowances');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            if (Schema::hasColumn('employee_payslips', 'thirteenth_month_pay')) {
                $table->dropColumn('thirteenth_month_pay');
            }
        });
    }
};
