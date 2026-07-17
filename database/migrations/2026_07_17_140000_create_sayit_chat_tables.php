<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sayit_chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('creator_codename');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('last_message_at');
        });

        Schema::create('sayit_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sayit_chat_room_id')->constrained('sayit_chat_rooms')->cascadeOnDelete();
            $table->string('codename');
            $table->text('body');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sayit_chat_room_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sayit_chat_messages');
        Schema::dropIfExists('sayit_chat_rooms');
    }
};
