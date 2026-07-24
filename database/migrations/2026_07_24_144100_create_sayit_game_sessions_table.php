<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sayit_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('game_type', 32);
            $table->string('status', 32)->default('playing');
            $table->json('state')->nullable();
            $table->string('player_label')->nullable();
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();

            $table->index(['game_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sayit_game_sessions');
    }
};
