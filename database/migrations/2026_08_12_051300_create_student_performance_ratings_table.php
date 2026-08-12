<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_performance_ratings')) {
            return;
        }

        Schema::create('student_performance_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('rated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedTinyInteger('leadership_self_discipline')->nullable();
            $table->unsignedTinyInteger('leadership_responsibility')->nullable();
            $table->unsignedTinyInteger('leadership_understands_instructions')->nullable();
            $table->unsignedTinyInteger('leadership_accepts_suggestions')->nullable();

            $table->unsignedTinyInteger('attitude_time_use')->nullable();
            $table->unsignedTinyInteger('attitude_punctuality')->nullable();
            $table->unsignedTinyInteger('attitude_follows_rules')->nullable();
            $table->unsignedTinyInteger('attitude_courteous')->nullable();

            $table->unsignedTinyInteger('performance_accuracy')->nullable();
            $table->unsignedTinyInteger('performance_timely_tasks')->nullable();
            $table->unsignedTinyInteger('performance_follows_directions')->nullable();
            $table->unsignedTinyInteger('performance_quality_cooperation')->nullable();

            $table->unsignedTinyInteger('learning_journal_evaluation')->nullable();
            $table->unsignedTinyInteger('requirements_assessment')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_performance_ratings');
    }
};
