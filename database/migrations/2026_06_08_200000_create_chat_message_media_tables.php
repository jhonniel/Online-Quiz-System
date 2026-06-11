<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_message_media', function (Blueprint $table) {
            $table->id();
            $table->string('chat_type', 20);
            $table->unsignedBigInteger('message_id');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('path');
            $table->string('disk', 40)->default('local');
            $table->string('mime_type', 120);
            $table->unsignedInteger('size_bytes');
            $table->string('mode', 20);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['chat_type', 'message_id']);
            $table->index('expires_at');
        });

        Schema::create('chat_message_media_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_media_id')->constrained('chat_message_media')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->unique(['chat_message_media_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_media_views');
        Schema::dropIfExists('chat_message_media');
    }
};
