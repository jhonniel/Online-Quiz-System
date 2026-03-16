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
        
        // Remove enum constraint to allow custom statuses
        if ($driver === 'pgsql') {
            // Drop the check constraint
            DB::statement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_status_check');
            // Change status to varchar to allow custom values
            DB::statement("ALTER TABLE tasks ALTER COLUMN status TYPE VARCHAR(50)");
        } elseif (Schema::getConnection()->isDoctrineAvailable()) {
            // For MySQL/SQLite, we need to modify the column
            Schema::table('tasks', function (Blueprint $table) {
                $table->string('status', 50)->default('todo')->change();
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
            // Restore enum constraint
            DB::statement("ALTER TABLE tasks ALTER COLUMN status TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('todo', 'in_progress', 'done'))");
        } elseif (Schema::getConnection()->isDoctrineAvailable()) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo')->change();
            });
        }
    }
};
