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
        
        // Drop constraint if exists (PostgreSQL)
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_priority_check');
        }
        
        // Change priority from enum to string to allow custom values
        if ($driver === 'pgsql') {
            // PostgreSQL: Already string, just ensure it's varchar(50)
            DB::statement("ALTER TABLE tasks ALTER COLUMN priority TYPE VARCHAR(50)");
        } elseif (Schema::getConnection()->isDoctrineAvailable()) {
            // MySQL/SQLite with Doctrine DBAL: Change enum to string
            Schema::table('tasks', function (Blueprint $table) {
                $table->string('priority', 50)->default('medium')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'pgsql') {
            // Revert to check constraint
            DB::statement("ALTER TABLE tasks ALTER COLUMN priority TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_priority_check CHECK (priority IN ('low', 'medium', 'high', 'urgent'))");
        } elseif (Schema::getConnection()->isDoctrineAvailable()) {
            // Revert to enum when Doctrine DBAL is available
            Schema::table('tasks', function (Blueprint $table) {
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->change();
            });
        }
    }
};
