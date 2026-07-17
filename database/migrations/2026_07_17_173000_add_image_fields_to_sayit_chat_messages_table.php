<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sayit_chat_messages', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
            $table->timestamp('image_expires_at')->nullable()->after('image_path');
            $table->index('image_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('sayit_chat_messages', function (Blueprint $table) {
            $table->dropIndex(['image_expires_at']);
            $table->dropColumn(['image_path', 'image_expires_at']);
        });
    }
};
