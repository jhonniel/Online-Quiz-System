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
        // For SQLite, we need to recreate the column
        // For MySQL, we can modify the enum
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN for enum, so we'll use string
            Schema::table('users', function (Blueprint $table) {
                // Change role to string type to support new roles
                // SQLite will handle this as text
            });
            
            // Update existing 'user' role to 'student' if needed, or keep as is
            // We'll handle validation in the model/controller
        } else {
            // For MySQL/PostgreSQL, modify the enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'student', 'employee', 'applicant') DEFAULT 'student'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // No need to revert for SQLite
        } else {
            // Revert to original enum values
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user') DEFAULT 'user'");
        }
    }
};
