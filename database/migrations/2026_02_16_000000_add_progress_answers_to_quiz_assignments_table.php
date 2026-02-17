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
            $table->json('progress_answers')->nullable()->after('last_attempt_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_assignments', function (Blueprint $table) {
            $table->dropColumn('progress_answers');
        });
    }
};
