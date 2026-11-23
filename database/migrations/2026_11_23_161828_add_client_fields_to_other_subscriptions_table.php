<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('other_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('other_subscriptions', 'client_name')) {
                $table->string('client_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('other_subscriptions', 'client_email')) {
                $table->string('client_email')->nullable()->after('client_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('other_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('other_subscriptions', 'client_email')) {
                $table->dropColumn('client_email');
            }
            if (Schema::hasColumn('other_subscriptions', 'client_name')) {
                $table->dropColumn('client_name');
            }
        });
    }
};
