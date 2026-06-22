<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_geolocations', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_label')->nullable();
            $table->string('source', 40)->nullable();
            $table->timestamp('resolved_at');
            $table->timestamps();

            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_geolocations');
    }
};
