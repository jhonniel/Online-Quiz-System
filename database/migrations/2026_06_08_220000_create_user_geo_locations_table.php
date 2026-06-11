<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_geo_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 10, 2)->nullable();
            $table->string('source', 32)->default('browser');
            $table->string('context', 32)->default('login');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->index(['user_id', 'captured_at']);
            $table->index('captured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_geo_locations');
    }
};
