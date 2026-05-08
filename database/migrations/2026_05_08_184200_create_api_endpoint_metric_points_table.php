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
        Schema::create('api_endpoint_metric_points', function (Blueprint $table) {
            $table->id();
            $table->string('route_key')->index();
            $table->boolean('is_success');
            $table->unsignedInteger('status_code')->nullable();
            $table->decimal('response_time_ms', 10, 2)->nullable();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_endpoint_metric_points');
    }
};
