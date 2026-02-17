<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('confession_posts', function (Blueprint $table) {
            $table->string('text_size', 20)->default('normal');
        });
    }

    public function down(): void
    {
        Schema::table('confession_posts', function (Blueprint $table) {
            $table->dropColumn('text_size');
        });
    }
};
