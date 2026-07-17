<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sayit_chat_rooms', function (Blueprint $table) {
            $table->string('password_hash')->nullable()->after('avatar_path');
            $table->text('password_encrypted')->nullable()->after('password_hash');
        });
    }

    public function down(): void
    {
        Schema::table('sayit_chat_rooms', function (Blueprint $table) {
            $table->dropColumn(['password_hash', 'password_encrypted']);
        });
    }
};
