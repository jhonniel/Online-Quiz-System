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
        Schema::table('questions', function (Blueprint $table) {
            $table->text('alternative_answer_1')->nullable();
            $table->text('alternative_answer_2')->nullable();
            $table->text('alternative_answer_3')->nullable();
            $table->boolean('requires_manual_grading')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'alternative_answer_1',
                'alternative_answer_2',
                'alternative_answer_3',
                'requires_manual_grading'
            ]);
        });
    }
};
