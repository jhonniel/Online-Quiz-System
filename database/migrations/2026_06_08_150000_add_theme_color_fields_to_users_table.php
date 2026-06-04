<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'theme_color_enabled')) {
                $table->boolean('theme_color_enabled')->default(false)->after('bio');
            }
            if (! Schema::hasColumn('users', 'theme_color')) {
                $table->string('theme_color', 7)->nullable()->after('theme_color_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'theme_color')) {
                $table->dropColumn('theme_color');
            }
            if (Schema::hasColumn('users', 'theme_color_enabled')) {
                $table->dropColumn('theme_color_enabled');
            }
        });
    }
};
