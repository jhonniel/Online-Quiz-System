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
        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('content_management')->default(false);
            $table->boolean('analytics_reports')->default(false);
            $table->boolean('employee_management')->default(false);
            $table->boolean('student_management')->default(false);
            $table->boolean('hiring_process')->default(false);
            $table->boolean('communication')->default(false);
            $table->boolean('user_management')->default(false);
            $table->boolean('system')->default(false);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_permissions');
    }
};
