<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sayit_chat_rooms', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('creator_codename');
        });
    }

    public function down(): void
    {
        Schema::table('sayit_chat_rooms', function (Blueprint $table) {
            $table->dropColumn('avatar_path');
        });
    }
};
