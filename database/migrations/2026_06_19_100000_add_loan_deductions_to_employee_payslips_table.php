<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_payslips', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_payslips', 'ca')) {
                $table->decimal('ca', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('employee_payslips', 'govt_loans')) {
                $table->decimal('govt_loans', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('employee_payslips', 'loans')) {
                $table->decimal('loans', 12, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['ca', 'govt_loans', 'loans'],
            fn (string $column): bool => Schema::hasColumn('employee_payslips', $column)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('employee_payslips', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
