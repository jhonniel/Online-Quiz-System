<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('path');
            $table->string('disk', 40)->default('local');
            $table->string('mime_type', 120);
            $table->unsignedInteger('size_bytes');
            $table->string('caption', 500)->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
            $table->index('expires_at');
        });

        Schema::create('user_story_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_story_id')->constrained('user_stories')->cascadeOnDelete();
            $table->foreignId('viewer_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('viewed_at');
            $table->unique(['user_story_id', 'viewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_story_views');
        Schema::dropIfExists('user_stories');
    }
};
