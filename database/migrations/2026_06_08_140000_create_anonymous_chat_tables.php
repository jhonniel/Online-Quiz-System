<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anonymous_chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_two_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_one_id', 'user_two_id']);
            $table->index('updated_at');
        });

        Schema::create('anonymous_chat_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anonymous_chat_room_id')->constrained('anonymous_chat_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('display_alias', 120);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['anonymous_chat_room_id', 'user_id']);
        });

        Schema::create('anonymous_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anonymous_chat_room_id')->constrained('anonymous_chat_rooms')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();

            $table->index(['anonymous_chat_room_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anonymous_chat_messages');
        Schema::dropIfExists('anonymous_chat_participants');
        Schema::dropIfExists('anonymous_chat_rooms');
    }
};
