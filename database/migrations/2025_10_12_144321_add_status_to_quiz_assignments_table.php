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
        Schema::table('quiz_assignments', function (Blueprint $table) {
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned')->after('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_assignments', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
