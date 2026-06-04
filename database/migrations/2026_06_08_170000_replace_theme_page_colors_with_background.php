<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'theme_page_background')) {
                $table->string('theme_page_background', 7)->nullable()->after('theme_color');
            }

            if (Schema::hasColumn('users', 'theme_page_colors')) {
                $table->dropColumn('theme_page_colors');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'theme_page_colors')) {
                $table->json('theme_page_colors')->nullable()->after('theme_color');
            }

            if (Schema::hasColumn('users', 'theme_page_background')) {
                $table->dropColumn('theme_page_background');
            }
        });
    }
};
