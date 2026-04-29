<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite uses a plain string column for role in this project.
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'user', 'student', 'employee', 'teacher', 'applicant', 'technician'))");
            return;
        }

        // MySQL/MariaDB enum update
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'student', 'employee', 'teacher', 'applicant', 'technician') DEFAULT 'student'");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'user', 'student', 'employee', 'applicant', 'technician'))");
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'student', 'employee', 'applicant', 'technician') DEFAULT 'student'");
    }
};
