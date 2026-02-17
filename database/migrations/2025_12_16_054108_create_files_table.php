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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name')->nullable();
            $table->string('path'); // Storage path
            $table->string('type')->default('file'); // 'file' or 'folder'
            $table->string('mime_type')->nullable(); // For files only
            $table->unsignedBigInteger('size')->nullable(); // File size in bytes
            $table->unsignedBigInteger('folder_id')->nullable(); // Parent folder
            $table->foreign('folder_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['folder_id', 'type']);
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
