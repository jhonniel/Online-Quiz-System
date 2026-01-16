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
        
        // Check if table already exists (from previous partial migration)
        if (!Schema::hasTable('task_assignments')) {
            // Create table first without foreign key constraints
            Schema::create('task_assignments', function (Blueprint $table) use ($driver) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                
                // Use string for PostgreSQL, enum for MySQL/SQLite
                if ($driver === 'pgsql') {
                    $table->string('role', 20)->default('viewer');
                } else {
                    $table->enum('role', ['owner', 'assignee', 'viewer'])->default('viewer');
                }
                
                $table->timestamps();
                
                $table->unique(['task_id', 'user_id']);
            });
        }
        
        // Check if foreign key constraint already exists
        $foreignKeyExists = false;
        if ($driver === 'pgsql') {
            $constraint = DB::selectOne("
                SELECT constraint_name 
                FROM information_schema.table_constraints 
                WHERE table_name = 'task_assignments' 
                AND constraint_type = 'FOREIGN KEY' 
                AND constraint_name = 'task_assignments_task_id_foreign'
            ");
            $foreignKeyExists = $constraint !== null;
        } else {
            // For MySQL, check if foreign key exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'task_assignments' 
                AND CONSTRAINT_NAME = 'task_assignments_task_id_foreign'
            ");
            $foreignKeyExists = count($foreignKeys) > 0;
        }
        
        // Add foreign key constraint if it doesn't exist and tasks table exists
        if (!$foreignKeyExists && Schema::hasTable('tasks')) {
            Schema::table('task_assignments', function (Blueprint $table) {
                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            });
        }
        
        // Add check constraint for PostgreSQL if it doesn't exist
        if ($driver === 'pgsql') {
            $checkExists = DB::selectOne("
                SELECT constraint_name 
                FROM information_schema.table_constraints 
                WHERE table_name = 'task_assignments' 
                AND constraint_type = 'CHECK' 
                AND constraint_name = 'task_assignments_role_check'
            ");
            
            if (!$checkExists) {
                DB::statement("ALTER TABLE task_assignments ADD CONSTRAINT task_assignments_role_check CHECK (role IN ('owner', 'assignee', 'viewer'))");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        // Drop check constraint for PostgreSQL
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE task_assignments DROP CONSTRAINT IF EXISTS task_assignments_role_check');
        }
        
        Schema::dropIfExists('task_assignments');
    }
};
