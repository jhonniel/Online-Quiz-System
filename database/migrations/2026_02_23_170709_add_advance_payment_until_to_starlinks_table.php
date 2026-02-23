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
        Schema::table('starlinks', function (Blueprint $table) {
            $table->date('advance_payment_until')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('starlinks', function (Blueprint $table) {
            $table->dropColumn('advance_payment_until');
        });
    }
};
