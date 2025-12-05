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
        if (! Schema::hasTable('leave_requests')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Update the CHECK constraint
            DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_type_check');
            DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_type_check CHECK (type IN ('vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'))");
        } elseif ($driver === 'mysql') {
            // MySQL: Modify the ENUM column
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset') DEFAULT 'vacation_leave'");
        } else {
            // SQLite: Recreate table
            Schema::create('leave_requests_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime', 'offset'])->default('vacation_leave');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->text('reason')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('type');
            });

            DB::statement('
                INSERT INTO leave_requests_temp (id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at)
                SELECT id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at
                FROM leave_requests
            ');

            Schema::drop('leave_requests');
            Schema::rename('leave_requests_temp', 'leave_requests');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('leave_requests')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: Revert the CHECK constraint
            DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_type_check');
            DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_type_check CHECK (type IN ('vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime'))");
        } elseif ($driver === 'mysql') {
            // MySQL: Revert the ENUM column
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime') DEFAULT 'vacation_leave'");
        } else {
            // SQLite: Recreate table
            Schema::create('leave_requests_original', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['vacation_leave', 'sick_leave', 'work_from_home', 'absent', 'overtime'])->default('vacation_leave');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->text('reason')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index('type');
            });

            DB::statement('
                INSERT INTO leave_requests_original (id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at)
                SELECT id, user_id, type, start_date, end_date, reason, status, admin_notes, reviewed_by, reviewed_at, created_at, updated_at
                FROM leave_requests
            ');

            Schema::drop('leave_requests');
            Schema::rename('leave_requests_original', 'leave_requests');
        }
    }
};
