<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sayit_game_sessions', function (Blueprint $table) {
            $table->json('players')->nullable()->after('state');
            $table->string('host_player_id', 64)->nullable()->after('players');
            $table->boolean('require_code')->default(false)->after('host_player_id');
            $table->index(['status', 'require_code', 'game_type']);
        });
    }

    public function down(): void
    {
        Schema::table('sayit_game_sessions', function (Blueprint $table) {
            $table->dropIndex(['status', 'require_code', 'game_type']);
            $table->dropColumn(['players', 'host_player_id', 'require_code']);
        });
    }
};
