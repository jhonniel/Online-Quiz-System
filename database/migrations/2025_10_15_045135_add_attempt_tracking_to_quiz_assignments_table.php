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
            $table->integer('attempt_count')->default(1)->after('status');
            $table->integer('total_score')->nullable()->after('attempt_count');
            $table->integer('best_score')->nullable()->after('total_score');
            $table->timestamp('last_attempt_at')->nullable()->after('best_score');
            $table->boolean('can_retake')->default(false)->after('last_attempt_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'attempt_count',
                'total_score',
                'best_score',
                'last_attempt_at',
                'can_retake'
            ]);
        });
    }
};
