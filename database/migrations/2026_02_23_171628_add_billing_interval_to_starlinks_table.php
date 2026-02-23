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
            $table->string('billing_interval', 20)->default('monthly')->after('advance_payment_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('starlinks', function (Blueprint $table) {
            $table->dropColumn('billing_interval');
        });
    }
};
