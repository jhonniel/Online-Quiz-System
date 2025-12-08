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
        Schema::create('leave_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->onDelete('cascade');
            $table->string('action'); // 'approved', 'rejected', 'resubmission_requested', 'updated', 'created'
            $table->string('status_before')->nullable(); // Previous status
            $table->string('status_after')->nullable(); // New status
            $table->text('notes')->nullable(); // Admin notes or change description
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade'); // Who performed the action
            $table->json('changes')->nullable(); // Store field changes as JSON
            $table->timestamps();

            $table->index('leave_request_id');
            $table->index('performed_by');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_logs');
    }
};
