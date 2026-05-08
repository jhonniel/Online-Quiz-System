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
        Schema::create('api_endpoint_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('route_key')->unique();
            $table->string('method', 16);
            $table->string('uri');
            $table->string('route_name')->nullable();
            $table->unsignedBigInteger('request_count')->default(0);
            $table->unsignedBigInteger('success_count')->default(0);
            $table->unsignedBigInteger('failure_count')->default(0);
            $table->unsignedInteger('last_status_code')->nullable();
            $table->decimal('avg_response_time_ms', 10, 2)->default(0);
            $table->timestamp('last_response_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_endpoint_metrics');
    }
};
