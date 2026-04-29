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

        // SQLite does not support ALTER COLUMN nullability directly; rebuild table.
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::statement('ALTER TABLE user_activities RENAME TO user_activities_old');
            DB::statement('DROP INDEX IF EXISTS user_activities_user_id_activity_type_index');
            DB::statement('DROP INDEX IF EXISTS user_activities_activity_type_created_at_index');
            DB::statement('DROP INDEX IF EXISTS user_activities_created_at_index');

            Schema::create('user_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('activity_type');
                $table->string('action')->nullable();
                $table->string('page_url')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at');

                $table->index(['user_id', 'activity_type']);
                $table->index(['activity_type', 'created_at']);
                $table->index('created_at');
            });

            DB::statement("
                INSERT INTO user_activities (id, user_id, activity_type, action, page_url, ip_address, user_agent, metadata, created_at)
                SELECT id, user_id, activity_type, action, page_url, ip_address, user_agent, metadata, created_at
                FROM user_activities_old
            ");

            DB::statement('DROP TABLE user_activities_old');
            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

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

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::statement('ALTER TABLE user_activities RENAME TO user_activities_old');
            DB::statement('DROP INDEX IF EXISTS user_activities_user_id_activity_type_index');
            DB::statement('DROP INDEX IF EXISTS user_activities_activity_type_created_at_index');
            DB::statement('DROP INDEX IF EXISTS user_activities_created_at_index');

            Schema::create('user_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('activity_type');
                $table->string('action')->nullable();
                $table->string('page_url')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at');

                $table->index(['user_id', 'activity_type']);
                $table->index(['activity_type', 'created_at']);
                $table->index('created_at');
            });

            // Keep only rows with user_id to satisfy NOT NULL constraint on rollback.
            DB::statement("
                INSERT INTO user_activities (id, user_id, activity_type, action, page_url, ip_address, user_agent, metadata, created_at)
                SELECT id, user_id, activity_type, action, page_url, ip_address, user_agent, metadata, created_at
                FROM user_activities_old
                WHERE user_id IS NOT NULL
            ");

            DB::statement('DROP TABLE user_activities_old');
            DB::statement('PRAGMA foreign_keys = ON');

            return;
        }

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
