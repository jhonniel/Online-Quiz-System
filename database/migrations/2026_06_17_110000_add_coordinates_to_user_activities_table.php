<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('ip_address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('location_label')->nullable()->after('longitude');

            $table->index(['ip_address', 'latitude']);
        });
    }

    public function down(): void
    {
        Schema::table('user_activities', function (Blueprint $table) {
            $table->dropIndex(['ip_address', 'latitude']);
            $table->dropColumn(['latitude', 'longitude', 'location_label']);
        });
    }
};
