<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confession_banned_words', function (Blueprint $table) {
            $table->string('display_style', 20)->default('full')->after('word');
        });
    }

    public function down(): void
    {
        Schema::table('confession_banned_words', function (Blueprint $table) {
            $table->dropColumn('display_style');
        });
    }
};
