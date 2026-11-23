<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('starlinks', function (Blueprint $table) {
            if (! Schema::hasColumn('starlinks', 'last_billing_notified_at')) {
                $table->timestamp('last_billing_notified_at')->nullable()->after('end_user_email');
            }
            if (! Schema::hasColumn('starlinks', 'last_billing_notified_for')) {
                $table->date('last_billing_notified_for')->nullable()->after('last_billing_notified_at');
            }
            if (! Schema::hasColumn('starlinks', 'last_billing_notified_kind')) {
                $table->string('last_billing_notified_kind', 32)->nullable()->after('last_billing_notified_for');
            }
        });
    }

    public function down(): void
    {
        Schema::table('starlinks', function (Blueprint $table) {
            if (Schema::hasColumn('starlinks', 'last_billing_notified_kind')) {
                $table->dropColumn('last_billing_notified_kind');
            }
            if (Schema::hasColumn('starlinks', 'last_billing_notified_for')) {
                $table->dropColumn('last_billing_notified_for');
            }
            if (Schema::hasColumn('starlinks', 'last_billing_notified_at')) {
                $table->dropColumn('last_billing_notified_at');
            }
        });
    }
};
