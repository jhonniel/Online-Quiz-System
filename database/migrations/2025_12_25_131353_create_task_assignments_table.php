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
        
        Schema::create('task_assignments', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
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
        
        // Add check constraint for PostgreSQL
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE task_assignments ADD CONSTRAINT task_assignments_role_check CHECK (role IN ('owner', 'assignee', 'viewer'))");
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
