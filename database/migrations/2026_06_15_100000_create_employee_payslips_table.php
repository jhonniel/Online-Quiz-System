<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('company_name')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('employee_name');
            $table->string('employee_email')->nullable();
            $table->string('position')->nullable();
            $table->date('date_hired')->nullable();
            $table->decimal('rate_per_day', 12, 2)->default(0);
            $table->decimal('sss', 12, 2)->default(0);
            $table->decimal('phic', 12, 2)->default(0);
            $table->decimal('hdmf', 12, 2)->default(0);
            $table->decimal('late_hours', 12, 2)->default(0);
            $table->decimal('absences_days', 12, 2)->default(0);
            $table->decimal('withholding_tax', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->smallInteger('total_working_days')->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('holiday_pay', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->string('prepared_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'period_start', 'period_end']);
            $table->index(['employee_name', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payslips');
    }
};
