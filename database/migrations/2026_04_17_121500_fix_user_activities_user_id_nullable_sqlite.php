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
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }

        $columns = DB::select('PRAGMA table_info(user_activities)');
        $userId = collect($columns)->firstWhere('name', 'user_id');
        if ($userId && (int) $userId->notnull === 0) {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement('ALTER TABLE user_activities RENAME TO user_activities_old_fix');
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
            FROM user_activities_old_fix
        ");

        DB::statement('DROP TABLE user_activities_old_fix');
        DB::statement('PRAGMA foreign_keys = ON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: this is a one-time SQLite schema repair migration.
    }
};

