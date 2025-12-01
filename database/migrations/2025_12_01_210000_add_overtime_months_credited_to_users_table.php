<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Number of months of overtime history to credit when computing balances.
            // Typical values: 12 (current year), 9, 6, 3, 1.
            $table->unsignedTinyInteger('overtime_months_credited')
                ->default(12)
                ->after('leave_credits')
                ->comment('Months of overtime history credited for balance calculations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('overtime_months_credited');
        });
    }
};


