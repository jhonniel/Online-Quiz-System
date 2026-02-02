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
        Schema::table('tasks', function (Blueprint $table) use ($driver) {
            if ($driver === 'pgsql') {
                // PostgreSQL: Already string, just ensure it's varchar(50)
                DB::statement("ALTER TABLE tasks ALTER COLUMN priority TYPE VARCHAR(50)");
            } else {
                // MySQL/SQLite: Change enum to string
                $table->string('priority', 50)->default('medium')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        Schema::table('tasks', function (Blueprint $table) use ($driver) {
            if ($driver === 'pgsql') {
                // Revert to check constraint
                DB::statement("ALTER TABLE tasks ALTER COLUMN priority TYPE VARCHAR(20)");
                DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_priority_check CHECK (priority IN ('low', 'medium', 'high', 'urgent'))");
            } else {
                // Revert to enum (this might not work perfectly, but attempt it)
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->change();
            }
        });
    }
};
