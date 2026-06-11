<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anonymous_chat_rooms', function (Blueprint $table) {
            $table->dropUnique(['user_one_id', 'user_two_id']);
            $table->unique(
                ['created_by', 'user_one_id', 'user_two_id'],
                'anonymous_chat_rooms_creator_pair_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('anonymous_chat_rooms', function (Blueprint $table) {
            $table->dropUnique('anonymous_chat_rooms_creator_pair_unique');
            $table->unique(['user_one_id', 'user_two_id']);
        });
    }
};
