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
        
        Schema::create('custom_boards', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Use string for PostgreSQL, enum for MySQL/SQLite
            if ($driver === 'pgsql') {
                $table->string('type', 20)->default('personal');
            } else {
                $table->enum('type', ['personal', 'group'])->default('personal');
            }
            
            $table->string('name'); // Column name/status
            $table->string('status_key'); // Unique key for the status (e.g., 'todo', 'custom_review')
            $table->string('color', 50)->default('gray'); // Color for the column (e.g., 'pink', 'blue', 'green')
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false); // Mark default columns (todo, in_progress, done)
            $table->timestamps();
            
            // Ensure unique status_key per user, type, and task_list_id
            // Note: task_list_id will be added in a later migration
            $table->unique(['user_id', 'type', 'status_key']);
        });
        
        // Add check constraint for PostgreSQL
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE custom_boards ADD CONSTRAINT custom_boards_type_check CHECK (type IN ('personal', 'group'))");
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
            DB::statement('ALTER TABLE custom_boards DROP CONSTRAINT IF EXISTS custom_boards_type_check');
        }
        
        Schema::dropIfExists('custom_boards');
    }
};
