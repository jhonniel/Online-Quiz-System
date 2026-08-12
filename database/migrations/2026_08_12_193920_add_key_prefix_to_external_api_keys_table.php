<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('external_api_keys')) {
            return;
        }

        Schema::table('external_api_keys', function (Blueprint $table) {
            if (! Schema::hasColumn('external_api_keys', 'key_prefix')) {
                $table->string('key_prefix', 24)->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('external_api_keys')) {
            return;
        }

        Schema::table('external_api_keys', function (Blueprint $table) {
            if (Schema::hasColumn('external_api_keys', 'key_prefix')) {
                $table->dropColumn('key_prefix');
            }
        });
    }
};
