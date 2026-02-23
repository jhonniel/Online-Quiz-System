<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('starlinks', function (Blueprint $table) {
            $table->foreignId('subscription_plan_type_id')->nullable()->after('plan')->constrained()->nullOnDelete();
        });

        Schema::table('omadas', function (Blueprint $table) {
            $table->foreignId('subscription_plan_type_id')->nullable()->after('license_expiration')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('starlinks', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_type_id']);
        });

        Schema::table('omadas', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_type_id']);
        });
    }
};
