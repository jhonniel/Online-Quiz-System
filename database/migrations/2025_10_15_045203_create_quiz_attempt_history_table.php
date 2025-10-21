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
        Schema::create('quiz_attempt_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('attempt_number');
            $table->integer('score');
            $table->integer('total_questions');
            $table->integer('correct_answers');
            $table->integer('time_taken_seconds')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at');
            $table->enum('status', ['completed', 'time_expired', 'cancelled'])->default('completed');
            $table->json('answers')->nullable(); // Store all answers for this attempt
            $table->timestamps();

            $table->index(['quiz_id', 'user_id', 'attempt_number']);
            $table->index(['user_id', 'quiz_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_history');
    }
};
