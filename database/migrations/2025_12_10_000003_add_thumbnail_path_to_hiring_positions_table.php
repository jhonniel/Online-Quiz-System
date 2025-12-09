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
        Schema::table('hiring_positions', function (Blueprint $table) {
            if (!Schema::hasColumn('hiring_positions', 'thumbnail_path')) {
                $table->string('thumbnail_path')->nullable()->after('application_count');
            }
        });
    }

    /**
    * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::table('hiring_positions', function (Blueprint $table) {
            if (Schema::hasColumn('hiring_positions', 'thumbnail_path')) {
                $table->dropColumn('thumbnail_path');
            }
        });
    }
};
