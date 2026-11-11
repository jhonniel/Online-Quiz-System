<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Employees excluded from a holiday: that day is not credited 8 hours for them
     * and is treated like a regular workday (ABSENT unless they have Hubstaff time).
     */
    public function up(): void
    {
        if (Schema::hasTable('dtr_holiday_exclusions')) {
            return;
        }

        Schema::create('dtr_holiday_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dtr_holiday_id')->constrained('dtr_holidays')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('excluded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['dtr_holiday_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtr_holiday_exclusions');
    }
};
