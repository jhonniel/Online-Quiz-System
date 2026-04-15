<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Drop FK first so we can alter nullability without doctrine/dbal.
        if ($driver !== 'sqlite') {
            Schema::table('user_activities', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE user_activities ALTER COLUMN user_id DROP NOT NULL');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE user_activities MODIFY user_id BIGINT UNSIGNED NULL');
        }

        if ($driver !== 'sqlite') {
            Schema::table('user_activities', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            Schema::table('user_activities', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE user_activities ALTER COLUMN user_id SET NOT NULL');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE user_activities MODIFY user_id BIGINT UNSIGNED NOT NULL');
        }

        if ($driver !== 'sqlite') {
            Schema::table('user_activities', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};
