<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_reports', function (Blueprint $table) {
            $table->string('payment_status', 30)->default('pending_for_payment')->after('status');
            $table->decimal('amount_paid', 12, 2)->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_reports', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'amount_paid']);
        });
    }
};
