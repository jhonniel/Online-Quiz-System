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
        Schema::create('task_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('role', 20)->default('viewer'); // owner, assignee, viewer
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Ensure one pending invitation per user per task (only for specific user invitations)
            // Note: Public invitations (user_id = null) are not included in this constraint
            $table->index(['token']);
            $table->index(['user_id', 'status']);
            
            // Partial unique index: one pending invitation per user per task (only for specific user invitations)
            // This is handled at the application level since SQLite doesn't support partial indexes well
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_invitations');
    }
};
