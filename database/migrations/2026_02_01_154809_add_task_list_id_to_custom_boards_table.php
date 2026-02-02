<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        // Add task_list_id column first
        Schema::table('custom_boards', function (Blueprint $table) use ($driver) {
            if ($driver === 'mysql') {
                $table->foreignId('task_list_id')->nullable()->after('type')->constrained('task_lists')->onDelete('cascade');
            } else {
                // PostgreSQL and SQLite don't support 'after' clause
                $table->foreignId('task_list_id')->nullable()->constrained('task_lists')->onDelete('cascade');
            }
        });
        
        // Drop old unique constraints
        if ($driver === 'sqlite') {
            // SQLite: Drop indexes using raw SQL
            try {
                DB::statement('DROP INDEX IF EXISTS custom_boards_status_key_unique');
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
            try {
                DB::statement('DROP INDEX IF EXISTS custom_boards_user_id_type_status_key_unique');
            } catch (\Exception $e) {
                // Index might not exist, continue
            }
        } else {
            // For MySQL/PostgreSQL
            Schema::table('custom_boards', function (Blueprint $table) {
                try {
                    $table->dropUnique(['status_key']);
                } catch (\Exception $e) {
                    // Constraint might not exist
                }
                try {
                    $table->dropUnique(['user_id', 'type', 'status_key']);
                } catch (\Exception $e) {
                    // Constraint might not exist
                }
            });
        }
        
        // Add new composite unique constraint
        Schema::table('custom_boards', function (Blueprint $table) {
            $table->unique(['user_id', 'type', 'task_list_id', 'status_key'], 'custom_boards_user_type_list_status_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        // Drop new unique constraint
        Schema::table('custom_boards', function (Blueprint $table) {
            try {
                $table->dropUnique('custom_boards_user_type_list_status_unique');
            } catch (\Exception $e) {
                // Constraint might not exist
            }
        });
        
        // Recreate old unique constraints
        if ($driver === 'sqlite') {
            // SQLite: Create indexes using raw SQL
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS custom_boards_user_id_type_status_key_unique ON custom_boards(user_id, type, status_key)');
        } else {
            Schema::table('custom_boards', function (Blueprint $table) {
                $table->unique(['user_id', 'type', 'status_key']);
            });
        }
        
        // Drop task_list_id column
        Schema::table('custom_boards', function (Blueprint $table) {
            $table->dropForeign(['task_list_id']);
            $table->dropColumn('task_list_id');
        });
    }
};
