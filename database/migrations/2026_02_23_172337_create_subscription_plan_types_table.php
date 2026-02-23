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
        Schema::create('subscription_plan_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subscription_type', 20); // starlink | omada
            $table->string('billing_type', 20); // monthly | yearly | custom
            $table->unsignedSmallInteger('billing_interval_months')->nullable(); // for custom: e.g. 3 = every 3 months
            $table->unsignedTinyInteger('billing_day')->nullable(); // 1-31, day of month
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plan_types');
    }
};
