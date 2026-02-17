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
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Find and drop constraints by querying the database
            // First, find the actual constraint names
            try {
                $constraints = DB::select("
                    SELECT conname 
                    FROM pg_constraint 
                    WHERE conrelid = 'custom_boards'::regclass 
                    AND contype = 'u'
                ");
                
                // Drop constraints that match our patterns
                foreach ($constraints as $constraint) {
                    $constraintName = $constraint->conname;
                    // Check if it's related to status_key or the composite unique constraint
                    if (strpos($constraintName, 'status_key') !== false || 
                        strpos($constraintName, 'user_id') !== false) {
                        try {
                            DB::statement("ALTER TABLE custom_boards DROP CONSTRAINT IF EXISTS \"{$constraintName}\"");
                        } catch (\Exception $e) {
                            // Constraint might not exist, continue
                        }
                    }
                }
            } catch (\Exception $e) {
                // If query fails, try dropping common constraint names
                $commonNames = [
                    'custom_boards_status_key_unique',
                    'custom_boards_user_id_type_status_key_unique',
                    'custom_boards_user_id_type_status_key'
                ];
                foreach ($commonNames as $name) {
                    try {
                        DB::statement("ALTER TABLE custom_boards DROP CONSTRAINT IF EXISTS \"{$name}\"");
                    } catch (\Exception $e2) {
                        // Ignore
                    }
                }
            }
        } else {
            // For MySQL
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
        if ($driver === 'pgsql') {
            // PostgreSQL: Use raw SQL with IF EXISTS
            try {
                DB::statement('ALTER TABLE custom_boards DROP CONSTRAINT IF EXISTS custom_boards_user_type_list_status_unique');
            } catch (\Exception $e) {
                // Constraint might not exist
            }
        } else {
            Schema::table('custom_boards', function (Blueprint $table) {
                try {
                    $table->dropUnique('custom_boards_user_type_list_status_unique');
                } catch (\Exception $e) {
                    // Constraint might not exist
                }
            });
        }
        
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
        Schema::table('custom_boards', function (Blueprint $table) use ($driver) {
            if ($driver === 'pgsql') {
                // PostgreSQL: Drop foreign key first, then column
                try {
                    DB::statement('ALTER TABLE custom_boards DROP CONSTRAINT IF EXISTS custom_boards_task_list_id_foreign');
                } catch (\Exception $e) {
                    // Constraint might not exist
                }
            } else {
                try {
                    $table->dropForeign(['task_list_id']);
                } catch (\Exception $e) {
                    // Constraint might not exist
                }
            }
            $table->dropColumn('task_list_id');
        });
    }
};
