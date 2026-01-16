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
        
        Schema::create('tasks', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            
            // Use string for PostgreSQL, enum for MySQL/SQLite
            if ($driver === 'pgsql') {
                $table->string('type', 20)->default('personal');
                $table->string('status', 20)->default('todo');
            } else {
                $table->enum('type', ['personal', 'group'])->default('personal');
                $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo');
            }
            
            $table->integer('order')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('due_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        
        // Add check constraints for PostgreSQL
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_type_check CHECK (type IN ('personal', 'group'))");
            DB::statement("ALTER TABLE tasks ADD CONSTRAINT tasks_status_check CHECK (status IN ('todo', 'in_progress', 'done'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        // Drop check constraints for PostgreSQL
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_type_check');
            DB::statement('ALTER TABLE tasks DROP CONSTRAINT IF EXISTS tasks_status_check');
        }
        
        Schema::dropIfExists('tasks');
    }
};
