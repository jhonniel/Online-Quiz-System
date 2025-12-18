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
        Schema::create('file_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('can_view')->default(true);
            $table->boolean('can_upload')->default(false);
            $table->foreignId('granted_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Ensure one permission record per file-user combination
            $table->unique(['file_id', 'user_id']);
            $table->index(['file_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_user_permissions');
    }
};
