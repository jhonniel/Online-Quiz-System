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
        
        if ($driver === 'sqlite') {
            // On SQLite we already use a plain string column for role; nothing to change here.
            return;
        } elseif ($driver === 'pgsql') {
            // For PostgreSQL, we need to alter the column type or use a check constraint
            // First, change the column to varchar and add a check constraint
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'user', 'student', 'employee', 'applicant'))");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'student'");
        } else {
            // For MySQL, modify the enum
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'student', 'employee', 'applicant') DEFAULT 'student'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // No need to revert for SQLite
        } elseif ($driver === 'pgsql') {
            // Revert PostgreSQL changes
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'user'))");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'user'");
        } else {
            // Revert to original enum values for MySQL
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user') DEFAULT 'user'");
        }
    }
};
